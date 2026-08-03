<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div class="sparklineNoComparison">
    <MetricValue
      class="metricValue--fixedHeight"
      :title="title"
      :value="primaryValue"
      :secondary-value="secondaryValue"
      :secondary-label="secondaryLabel"
      :documentation="documentation"
    >
      <template
        v-if="sparkline.evolution"
        #evolution
      >
        <EvolutionBadge
          :percent="sparkline.evolution.percent"
          :trend="sparkline.evolution.trend"
          :is-lower-value-better="sparkline.evolution.isLowerValueBetter"
          :tooltip="sparkline.evolution.tooltip || ''"
        />
      </template>
    </MetricValue>
  </div>
</template>

<script lang="ts">
import { computed, defineComponent, PropType } from 'vue';
import MetricValue from '../MetricValue/MetricValue.vue';
import EvolutionBadge from '../EvolutionBadge/EvolutionBadge.vue';
import { SparklineEntry, SparklineMetric } from './types';

/**
 * No-comparison body for a sparkline card: the metric readout only (the shell renders the shared
 * sparkline below it). Composes the MetricValue + EvolutionBadge atoms. In no-comparison mode the
 * metrics live under the '' group key: the first is the primary value, an optional second is the
 * "unique" line.
 */
export default defineComponent({
  name: 'NoComparison',
  components: {
    MetricValue,
    EvolutionBadge,
  },
  props: {
    sparkline: {
      type: Object as PropType<SparklineEntry>,
      required: true,
    },
    // Backend map of metric column -> documentation string (from Sparklines.php).
    allMetricsDocumentation: {
      type: Object as PropType<Record<string, string>>,
      default: () => ({}),
    },
  },
  setup(props) {
    const primaryMetric = computed<SparklineMetric | undefined>(
      () => props.sparkline.metrics?.['']?.[0],
    );

    // Optional secondary metric (eg "9,527 unique"), shown only when the report adds a
    // second metric to the same sparkline. MetricValue hides the line when it is absent.
    const secondaryMetric = computed<SparklineMetric | undefined>(
      () => props.sparkline.metrics?.['']?.[1],
    );

    // Prefer the metric's column name (eg "Bounce Rate"), falling back to the label.
    const title = computed(
      () => primaryMetric.value?.title || primaryMetric.value?.description || '',
    );

    // Documentation for the primary metric, looked up by its column. Empty for sparklines added
    // without a column (column === '') or metrics with no registered documentation.
    const documentation = computed(
      () => props.allMetricsDocumentation[primaryMetric.value?.column ?? ''] || undefined,
    );

    // Values are passed raw to MetricValue, which locale-formats numbers and renders
    // already-formatted strings verbatim.
    const primaryValue = computed(() => primaryMetric.value?.value ?? '');
    const secondaryValue = computed(() => secondaryMetric.value?.value);
    const secondaryLabel = computed(() => secondaryMetric.value?.description);

    return {
      title,
      documentation,
      primaryValue,
      secondaryValue,
      secondaryLabel,
    };
  },
});
</script>
