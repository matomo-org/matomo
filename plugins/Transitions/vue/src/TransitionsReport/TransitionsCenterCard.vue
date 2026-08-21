<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div class="transitionsCenterCard">
    <div class="transitionsCenterCard__header">
      <a
        class="transitionsCenterCard__icon"
        v-if="report.titleUrl"
        :href="report.titleUrl"
        rel="noreferrer noopener"
        target="_blank"
        :title="report.actionName"
      ><span class="transitionsCenterCard__glyph icon-outlink"></span></a>
      <span class="transitionsCenterCard__icon" v-else>
        <span class="transitionsCenterCard__glyph icon-document"></span>
      </span>

      <span class="transitionsCenterCard__title" :title="report.actionName">
        {{ report.title }}
      </span>
      <span class="transitionsCenterCard__pageviews" :title="report.pageviewsTooltip">
        {{ report.pageviewsLabel }}
      </span>
    </div>

    <div
      class="transitionsCenterCard__metricGroup"
      :class="{ 'transitionsCenterCard__metricGroup--divided': side === 'outgoing' }"
      v-for="side in sides"
      :key="side"
    >
      <span class="transitionsCenterCard__metricHeading">{{ headingFor(side) }}</span>
      <span class="transitionsCenterCard__metricTotal">{{ totalFor(side) }}</span>

      <div class="transitionsCenterCard__metricList">
        <div
          class="transitionsCenterCard__metric"
          :class="metricClasses(metric)"
          v-for="metric in metricsFor(side)"
          :key="metric.key"
          :title="metric.tooltip"
          @click="onMetricClick(metric)"
          @mouseenter="$emit('highlight', metric.groupName)"
          @mouseleave="$emit('unhighlight')"
        >
          <span class="transitionsCenterCard__dot" :class="dotClass(metric)"></span>
          <span class="transitionsCenterCard__metricLabel"
            >{{ metric.labelBefore
            }}<strong class="transitionsCenterCard__metricValue">{{ metric.valueLabel }}</strong
            >{{ metric.labelAfter }}</span
          >
        </div>
      </div>
    </div>

    <p class="transitionsCenterCard__loops" v-if="report.loops > 0" :title="report.loopsTooltip">
      {{ report.loopsLabel }}
    </p>
  </div>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { translate, NumberFormatter } from 'CoreHome';
import { TransitionsMetricData, TransitionsReportData, TransitionsSide } from './types';

export default defineComponent({
  props: {
    report: {
      type: Object as PropType<TransitionsReportData>,
      required: true,
    },
    highlightedGroup: {
      type: String,
      default: '',
    },
  },
  emits: ['open', 'highlight', 'unhighlight'],
  computed: {
    sides(): TransitionsSide[] {
      return ['incoming', 'outgoing'];
    },
  },
  methods: {
    headingFor(side: TransitionsSide): string {
      return side === 'incoming'
        ? translate('Transitions_IncomingTraffic')
        : translate('Transitions_OutgoingTraffic');
    },
    totalFor(side: TransitionsSide): string {
      return NumberFormatter.formatNumber(
        side === 'incoming' ? this.report.incomingTotal : this.report.outgoingTotal,
      );
    },
    /** Every metric is listed, including the ones at zero, so the breakdown is always complete. */
    metricsFor(side: TransitionsSide): TransitionsMetricData[] {
      return this.report.metrics.filter((metric) => metric.side === side);
    },
    metricClasses(metric: TransitionsMetricData): Record<string, boolean> {
      return {
        'transitionsCenterCard__metric--actionable': metric.canExpand && metric.value > 0,
        'transitionsCenterCard__metric--highlighted': metric.groupName === this.highlightedGroup,
      };
    },
    dotClass(metric: TransitionsMetricData): string {
      if (metric.value <= 0) {
        return 'transitionsCenterCard__dot--empty';
      }

      return metric.side === 'incoming'
        ? 'transitionsCenterCard__dot--incoming'
        : 'transitionsCenterCard__dot--outgoing';
    },
    onMetricClick(metric: TransitionsMetricData) {
      if (metric.canExpand && metric.value > 0) {
        this.$emit('open', metric.groupName);
      }
    },
  },
});
</script>
