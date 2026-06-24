<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div
    class="sparkline"
    :class="{ notLinkable: !areSparklinesLinkable }"
    :data-graph-params="graphParamsAttr"
    :data-series-indices="seriesIndicesAttr"
  >
    <h6
      v-if="title"
      class="sparkline-title"
    >
      {{ title }}
    </h6>
    <Sparkline
      :params="sparkline.url"
      :series-indices="sparkline.seriesIndices"
    />
    <div
      v-if="value !== null"
      class="sparkline__value"
    >
      {{ value }}
    </div>
  </div>
</template>

<script lang="ts">
import { computed, defineComponent, PropType } from 'vue';
import { Sparkline } from 'CoreHome';
import { SparklineEntry, SparklineMetric } from './types';

export default defineComponent({
  name: 'SparklineCard',
  components: {
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
  },
  setup(props) {
    const primaryMetric = computed<SparklineMetric | undefined>(
      () => props.sparkline.metrics?.['']?.[0],
    );

    // sparkline.title is null in no-comparison mode, so the primary metric's
    // description ("Visits", "Bounce rate", ...) is used as the card heading.
    const title = computed(
      () => props.sparkline.title || primaryMetric.value?.description || '',
    );

    // The value is already locale-formatted by the backend; render it directly,
    // without any number filter (re-parsing a formatted string would corrupt it).
    const value = computed(() => {
      const metric = primaryMetric.value;
      return metric && metric.value !== undefined ? metric.value : null;
    });

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
      title,
      value,
      graphParamsAttr,
      seriesIndicesAttr,
    };
  },
});
</script>
