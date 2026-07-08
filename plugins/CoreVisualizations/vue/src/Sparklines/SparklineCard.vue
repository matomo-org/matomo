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
  </div>
</template>

<script lang="ts">
import { computed, defineComponent, PropType } from 'vue';
import { MatomoUrl } from 'CoreHome';
import NoComparison from './NoComparison.vue';
import DateComparison from './DateComparison.vue';
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

    // The legacy click-to-evolution wiring (window.initializeSparklines) reads these
    // attributes off the .sparkline wrapper, so only emit them when populated.
    const graphParamsAttr = computed(() => {
      const { graphParams, url } = props.sparkline;

      // Prefer explicit backend graphParams (set for comparison/segment sparklines).
      if (graphParams && Object.keys(graphParams).length) {
        return JSON.stringify(graphParams);
      }

      // Otherwise derive the evolution-graph reload params from the sparkline url. The reused
      // CoreHome Sparkline renders the image with `src` (no `data-src`), so the click handler's
      // own url fallback (sparkline.js) can't read the columns off the img — we supply them here
      // so data-graph-params is always populated. Mirrors the columns/rows/idGoal the legacy
      // fallback would have parsed.
      if (url) {
        const parsed = MatomoUrl.parse(url.substring(url.indexOf('?') + 1));
        const derived: Record<string, unknown> = {};
        (['columns', 'rows', 'idGoal'] as const).forEach((key) => {
          if (parsed[key]) {
            derived[key] = parsed[key];
          }
        });

        if (Object.keys(derived).length) {
          return JSON.stringify(derived);
        }
      }

      return null;
    });

    const seriesIndicesAttr = computed(() => {
      const { seriesIndices } = props.sparkline;
      return seriesIndices && seriesIndices.length ? JSON.stringify(seriesIndices) : null;
    });

    return {
      isComparison,
      graphParamsAttr,
      seriesIndicesAttr,
    };
  },
});
</script>
