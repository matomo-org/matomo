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
    <div class="sparklineSegmentComparisonRow__periods">
      <template
        v-for="(period, index) in periods"
        :key="period.label"
      >
        <div
          v-if="index > 0"
          class="sparklineSegmentComparisonRow__separator"
        />
        <div class="sparklineSegmentComparisonRow__date">
          <!-- Only label the columns when there is more than one date to tell apart; segment-only
               comparison has a single column and keeps its bare-value look. -->
          <DateAtom
            v-if="isMultiPeriod"
            :label="period.label"
          />
          <MetricValue
            class="metricValue--noTitle"
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
      </template>
    </div>
    <div
      class="sparklineSegmentComparisonRow__sparkline"
      :class="{ 'sparklineSegmentComparisonRow__sparkline--wide': isMultiPeriod }"
    >
      <Sparkline
        :width="sparklineWidth"
        :height="40"
        :params="segment.url"
        :series-indices="segment.seriesIndices"
      />
    </div>
  </div>
</template>

<script lang="ts">
import { computed, defineComponent, PropType } from 'vue';
import { Sparkline } from 'CoreHome';
import MetricValue from '../MetricValue/MetricValue.vue';
import EvolutionBadge from '../EvolutionBadge/EvolutionBadge.vue';
import DateAtom from './DateAtom.vue';
import { SparklineEntry, SparklineEvolution } from './types';

// Kept structurally identical to DateComparison.vue's PeriodColumn so both column layouts can be
// lifted into a shared component later without reshaping data (see the plan). Moves to types.ts
// on extraction.
interface PeriodColumn {
  label: string;
  primaryValue: string | number;
  evolution?: SparklineEvolution;
  secondaryValue?: string | number;
  secondaryLabel?: string;
}

/**
 * One compared segment inside a segment-comparison card: a presentational block with a segment-name
 * chip, one value column per compared date (a bare value for segment-only, or a labelled column
 * with an evolution badge per date for segment + date), and its own single- or multi-series
 * sparkline. The row is not a link — the whole card is the single `.sparkline` click-to-evolution
 * unit (SegmentComparisonCard), since every segment reloads the same evolution graph.
 */
export default defineComponent({
  name: 'SegmentComparisonRow',
  components: {
    DateAtom,
    MetricValue,
    EvolutionBadge,
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

    // One column per compared date, in backend order via `metricsOrder` (not Object.keys, which JS
    // re-sorts for integer-like year labels). Segment-only comparison has exactly one column, so
    // this is one-or-many. Values pass raw to MetricValue, which locale-formats numbers. Mirrors
    // DateComparison.vue's `periods`.
    const periods = computed<PeriodColumn[]>(() => {
      const metrics = props.segment.metrics || {};
      const order = props.segment.metricsOrder || [];
      return order.map((label) => {
        const groupMetrics = metrics[label] || [];
        const primary = groupMetrics[0];
        const secondary = groupMetrics[1];
        return {
          label,
          primaryValue: primary?.value ?? '',
          evolution: primary?.evolution,
          secondaryValue: secondary?.value,
          secondaryLabel: secondary?.description,
        };
      });
    });

    // More than one compared date (segment + date) → label the columns and widen the sparkline.
    const isMultiPeriod = computed(() => periods.value.length > 1);

    // Displayed sparkline width; segment + date rows draw one series per date so they are wider,
    // matching the date-comparison card. Kept in sync with the `--wide` max-width in the .less
    // (Sparkline renders the PNG at 2x this; the CSS cap stops it scaling past that crisp source).
    const sparklineWidth = computed(() => (isMultiPeriod.value ? 760 : 380));

    return {
      segmentLabel,
      periods,
      isMultiPeriod,
      sparklineWidth,
    };
  },
});
</script>
