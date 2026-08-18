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
    <!-- Both bodies put the sparkline last, so the shell owns the one slot: it keeps the fixed
         card height and the reused Sparkline sizing in one place (see SparklineCard.less). The
         slot's measured size drives both the requested image and the size it is drawn at, so the
         image is never rescaled by CSS. Held back until measured — a card in a collapsed widget or
         an inactive tab measures 0 and becomes measurable later. -->
    <!-- The tooltip goes on the slot, not the image: the slot is there even while the image is
         still loading. -->
    <div
      ref="sparklineSlot"
      class="sparklineCard__sparkline"
      :style="sparklineSizeVars"
      :title="sparkline.tooltip || undefined"
    >
      <Sparkline
        v-if="sparklineWidth > 0 && sparklineHeight > 0"
        :width="sparklineWidth"
        :height="sparklineHeight"
        :params="sparkline.url"
        :series-indices="sparkline.seriesIndices ?? undefined"
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

    // Displayed sparkline size, measured from the slot itself. The same numbers are handed to
    // Sparkline (which requests the image at 2x them) and published as custom properties the .less
    // draws the image at, so the two can't drift and CSS never has to rescale the image.
    const sparklineSlot = ref<HTMLElement | null>(null);
    const { width: sparklineWidth, height: sparklineHeight } = useSparklineSlotSize(sparklineSlot);
    const sparklineSizeVars = computed(() => ({
      '--sparklineCard-img-width': `${sparklineWidth.value}px`,
      '--sparklineCard-img-height': `${sparklineHeight.value}px`,
    }));

    return {
      isComparison,
      graphParamsAttr,
      seriesIndicesAttr,
      sparklineSlot,
      sparklineWidth,
      sparklineHeight,
      sparklineSizeVars,
    };
  },
});
</script>
