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
    <!-- Segment header, set only in comparison mode (sparkline.title is null otherwise).
         The seam for Phase 3 comparison bodies, which render under the same title. -->
    <div
      v-if="sparkline.title"
      class="sparklineCard__title"
    >
      {{ sparkline.title }}
    </div>
    <NoComparison
      :sparkline="sparkline"
      :all-metrics-documentation="allMetricsDocumentation"
    />
  </div>
</template>

<script lang="ts">
import { computed, defineComponent, PropType } from 'vue';
import NoComparison from './NoComparison.vue';
import { SparklineEntry } from './types';

/**
 * Card shell: the frame around a sparkline body. Owns the legacy `.sparkline` wrapper and
 * its evolution-graph data attributes; delegates the content to a body component. Only the
 * no-comparison body exists today; Phase 3 adds the comparison body behind the same shell.
 */
export default defineComponent({
  name: 'SparklineCard',
  components: {
    NoComparison,
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
    // The legacy click-to-evolution wiring (window.initializeSparklines) reads these
    // attributes off the .sparkline wrapper, so only emit them when populated.
    const graphParamsAttr = computed(() => {
      const { graphParams } = props.sparkline;
      return graphParams && Object.keys(graphParams).length ? JSON.stringify(graphParams) : null;
    });

    const seriesIndicesAttr = computed(() => {
      const { seriesIndices } = props.sparkline;
      return seriesIndices && seriesIndices.length ? JSON.stringify(seriesIndices) : null;
    });

    return {
      graphParamsAttr,
      seriesIndicesAttr,
    };
  },
});
</script>
