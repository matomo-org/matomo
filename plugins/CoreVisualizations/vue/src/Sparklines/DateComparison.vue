<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div class="dateComparison">
    <div class="dateComparison__metric">{{ metricTitle }}</div>
    <div class="dateComparison__periods">
      <div
        v-for="period in periods"
        :key="period.label"
        class="dateComparison__period"
      >
        <DateAtom :label="period.label" />
        <MetricValue
          :value="period.primaryValue"
          :secondary-value="period.secondaryValue"
          :secondary-label="period.secondaryLabel"
        >
          <template
            v-if="period.evolution"
            #evolution
          >
            <EvolutionBadge
              :percent="period.evolution.percent"
              :trend="period.evolution.trend"
              :is-lower-value-better="period.evolution.isLowerValueBetter"
              :tooltip="period.evolution.tooltip || ''"
            />
          </template>
        </MetricValue>
      </div>
    </div>
    <div class="sparklineSlot">
      <Sparkline :width="380" :height="40"
        :params="sparkline.url"
        :series-indices="sparkline.seriesIndices"
      />
    </div>
  </div>
</template>

<script lang="ts">
import { computed, defineComponent, PropType } from 'vue';
import { Sparkline, NumberFormatter } from 'CoreHome';
import MetricValue from '../MetricValue/MetricValue.vue';
import EvolutionBadge from '../EvolutionBadge/EvolutionBadge.vue';
import DateAtom from './DateAtom.vue';
import { SparklineEntry, SparklineEvolution } from './types';

interface PeriodColumn {
  label: string;
  primaryValue: string | number;
  evolution?: SparklineEvolution;
  secondaryValue?: string | number;
  secondaryLabel?: string;
}

/**
 * Date-comparison body for a sparkline card: metric name as title, one column per compared date,
 * and a sparkline drawing a coloured series per date. Sparklines arrive grouped by date label
 * (one column each, in seriesIndices order). Only two-date comparison reaches here.
 */
export default defineComponent({
  name: 'DateComparison',
  components: {
    DateAtom,
    MetricValue,
    EvolutionBadge,
    Sparkline,
  },
  props: {
    sparkline: {
      type: Object as PropType<SparklineEntry>,
      required: true,
    },
  },
  setup(props) {
    // All groups share the metric, so take its name from the first.
    const metricTitle = computed(() => {
      const firstGroup = Object.values(props.sparkline.metrics || {})[0];
      const primary = firstGroup?.[0];
      return primary?.title || primary?.description || '';
    });

    // Format raw numbers; leave already-formatted strings (eg "50%") as-is, like NoComparison.
    const formatValue = (
      value?: string | number,
    ): string | number | undefined => (
      typeof value === 'number' ? NumberFormatter.formatNumber(value, 2) : value
    );

    // One column per compared date (backend group order): primary metric is the large value +
    // evolution, optional second metric is the "unique" sub-line.
    const periods = computed<PeriodColumn[]>(() => {
      const metrics = props.sparkline.metrics || {};
      return Object.keys(metrics).map((label) => {
        const groupMetrics = metrics[label] || [];
        const primary = groupMetrics[0];
        const secondary = groupMetrics[1];
        return {
          label,
          primaryValue: formatValue(primary?.value) ?? '',
          evolution: primary?.evolution,
          secondaryValue: formatValue(secondary?.value),
          secondaryLabel: secondary?.description,
        };
      });
    });

    return {
      metricTitle,
      periods,
    };
  },
});
</script>
