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
        v-if="safeTitleUrl"
        :href="safeTitleUrl"
        rel="noreferrer noopener"
        target="_blank"
        :title="report.actionName"
      ><span class="transitionsCenterCard__glyph icon-outlink"></span></a>
      <span class="transitionsCenterCard__icon" v-else>
        <span class="transitionsCenterCard__glyph icon-document"></span>
      </span>

      <span class="transitionsCenterCard__title" :title="titleTooltip">
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
        <component
          :is="isActionable(metric) ? 'a' : 'div'"
          class="transitionsCenterCard__metric"
          :class="metricClasses(metric)"
          v-for="metric in metricsFor(side)"
          :key="metric.key"
          :href="isActionable(metric) ? '#' : null"
          :title="metric.tooltip"
          @click.prevent="onMetricClick(metric)"
          @mouseenter="onMetricHighlight(metric)"
          @mouseleave="$emit('unhighlight')"
        >
          <span class="transitionsCenterCard__dot" :class="dotClass(metric)"></span>
          <span class="transitionsCenterCard__metricLabel"
            >{{ metric.labelBefore
            }}<strong class="transitionsCenterCard__metricValue">{{ metric.valueLabel }}</strong
            >{{ metric.labelAfter }}</span
          >
        </component>
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
    /**
     * An action name is tracked data, so it can be any string. Only a value DOMPurify accepts as
     * an href reaches the link; anything else falls through to the plain icon.
     */
    safeTitleUrl(): string {
      return this.report.titleUrl ? this.$sanitizeUrl(this.report.titleUrl) : '';
    },
    /**
     * Only worth a tooltip when the title was shortened; page titles are shown in full. Undefined
     * rather than null, because that is what Vue's typing for a DOM attribute accepts.
     */
    titleTooltip(): string|undefined {
      return this.report.title === this.report.actionName ? undefined : this.report.actionName;
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
    /** A group with no transitions has nothing to open, however expandable it is in principle. */
    isActionable(metric: TransitionsMetricData): boolean {
      return metric.canExpand && metric.value > 0;
    },
    metricClasses(metric: TransitionsMetricData): Record<string, boolean> {
      return {
        'transitionsCenterCard__metric--actionable': this.isActionable(metric),
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
      if (this.isActionable(metric)) {
        this.$emit('open', metric.groupName);
      }
    },
    /**
     * A metric at zero has no ribbons to emphasise, so it stays inert on hover. The value decides
     * that, not expandability: direct entries cannot be opened but do highlight.
     */
    onMetricHighlight(metric: TransitionsMetricData) {
      if (metric.value > 0) {
        this.$emit('highlight', metric.groupName);
      }
    },
  },
});
</script>
