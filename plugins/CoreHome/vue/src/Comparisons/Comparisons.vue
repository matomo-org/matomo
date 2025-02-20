<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div
    v-if="isComparing"
    ref="root"
    class="matomo-comparisons"
    v-tooltips="{ duration: 200, delay: 200, content: transformTooltipContent }"
  >
    <h3>{{ translate('General_Comparisons') }}</h3>
    <div
      class="comparison card"
      v-for="(comparison, $index) in segmentComparisons"
      :key="comparison.index"
    >
      <div class="comparison-type">{{ translate('General_Segment') }}</div>
      <div
        class="title"
        :title="getTitleTooltip(comparison)"
      >
        <a
          target="_blank"
          :href="getUrlToSegment(comparison.params.segment)"
        >
          {{ comparison.title }}
        </a>
      </div>
      <div
        class="comparison-period"
        v-for="periodComparison in periodComparisons"
        :key="periodComparison.index"
        :title="getComparisonTooltip(comparison, periodComparison)"
      >
        <span
          class="comparison-dot"
          :style="{
            'background-color': getSeriesColor(comparison, periodComparison)
          }"
        />
        <span class="comparison-period-label">
          {{ periodComparison.title }} ({{ getComparisonPeriodType(periodComparison) }})
        </span>
      </div>
      <a
        class="remove-button"
        v-on:click="removeSegmentComparison($index)"
        v-if="segmentComparisons.length > 1"
      >
        <span
          class="icon icon-close"
          :title="translate('General_ClickToRemoveComp')"
        />
      </a>
    </div>
    <div
      class="loadingPiwik"
      style="display:none;"
    >
      <img
        src="plugins/Morpheus/images/loading-blue.gif"
        :alt="translate('General_LoadingData')"
      />
      {{ translate('General_LoadingData') }}
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, computed } from 'vue';
import { AnyComparison } from './Comparisons.store';
import ComparisonsStoreInstance from './Comparisons.store.instance';
import Matomo from '../Matomo/Matomo';
import MatomoUrl from '../MatomoUrl/MatomoUrl';
import AjaxHelper from '../AjaxHelper/AjaxHelper';
import { translate } from '../translate';
import Tooltips from '../Tooltips/Tooltips';

interface ProcessedReportComparison {
  compareSegmentPretty: string;
  comparePeriodPretty: string;
  nb_visits: number;
  nb_visits_change: number;
}

interface ProcessedReportData {
  comparisons?: ProcessedReportComparison[];
}

interface ProcessedReportResponse {
  reportData: ProcessedReportData;
}

interface ComparisonState {
  comparisonTooltips: Record<string, Record<string, string>>|null;
}

export default defineComponent({
  props: {
  },
  directives: {
    Tooltips,
  },
  data(): ComparisonState {
    return {
      comparisonTooltips: null,
    };
  },
  setup() {
    // accessing has to be done through a computed property so we can use the computed
    // instance directly in the template. unfortunately, vue won't register to changes.
    const isComparing = computed(
      () => ComparisonsStoreInstance.isComparing() && !window.broadcast.isNoDataPage(),
    );
    const segmentComparisons = computed(() => ComparisonsStoreInstance.getSegmentComparisons());
    const periodComparisons = computed(() => ComparisonsStoreInstance.getPeriodComparisons());
    const getSeriesColor = ComparisonsStoreInstance.getSeriesColor.bind(ComparisonsStoreInstance);

    function transformTooltipContent(this: HTMLElement) {
      const title = window.$(this).attr('title');
      if (!title) {
        return title;
      }
      return window.vueSanitize(title.replace(/\n/g, '<br />'));
    }

    return {
      isComparing,
      segmentComparisons,
      periodComparisons,
      getSeriesColor,
      transformTooltipContent,
    };
  },
  methods: {
    comparisonHasSegment(comparison: AnyComparison) {
      return typeof comparison.params.segment !== 'undefined';
    },
    removeSegmentComparison(index: number) {
      // otherwise the tooltip will be stuck on the screen
      window.$(this.$refs.root as HTMLElement).tooltip('destroy');
      ComparisonsStoreInstance.removeSegmentComparison(index);
    },
    getComparisonPeriodType(comparison: AnyComparison) {
      const { period } = comparison.params;
      if (period === 'range') {
        return translate('CoreHome_PeriodRange');
      }
      const periodStr = translate(
        `Intl_Period${period.substring(0, 1).toUpperCase()}${period.substring(1)}`,
      );
      return periodStr.substring(0, 1).toUpperCase() + periodStr.substring(1);
    },
    getComparisonTooltip(
      segmentComparison: AnyComparison,
      periodComparison: AnyComparison,
    ): string|undefined {
      if (!this.comparisonTooltips
        || !Object.keys(this.comparisonTooltips).length
      ) {
        return undefined;
      }

      return (this.comparisonTooltips[periodComparison.index] || {})[segmentComparison.index];
    },
    getTitleTooltip(comparison: AnyComparison): string {
      return `${this.htmlentities(comparison.title)}<br/>`
        + `${this.htmlentities(decodeURIComponent(comparison.params.segment))}`;
    },
    getUrlToSegment(segment: string) {
      const hash = { ...MatomoUrl.hashParsed.value };
      delete hash.comparePeriods;
      delete hash.compareDates;
      delete hash.compareSegments;
      hash.segment = segment;
      return `${window.location.search}#?${MatomoUrl.stringify(hash)}`;
    },
    onComparisonsChanged() {
      this.comparisonTooltips = null;

      if (!ComparisonsStoreInstance.isComparing()) {
        return;
      }

      const periodComparisons = ComparisonsStoreInstance.getPeriodComparisons();
      const segmentComparisons = ComparisonsStoreInstance.getSegmentComparisons();
      AjaxHelper.fetch({
        method: 'API.getProcessedReport',
        apiModule: 'VisitsSummary',
        apiAction: 'get',
        compare: '1',
        compareSegments: MatomoUrl.getSearchParam('compareSegments'),
        comparePeriods: MatomoUrl.getSearchParam('comparePeriods'),
        compareDates: MatomoUrl.getSearchParam('compareDates'),
        format_metrics: '1',
      }).then((report) => {
        this.comparisonTooltips = {};
        periodComparisons.forEach((periodComp) => {
          this.comparisonTooltips![periodComp.index] = {};

          segmentComparisons.forEach((segmentComp) => {
            const tooltip = this.generateComparisonTooltip(report, periodComp, segmentComp);
            this.comparisonTooltips![periodComp.index][segmentComp.index] = tooltip;
          });
        });
      });
    },
    generateComparisonTooltip(
      visitsSummary: ProcessedReportResponse,
      periodComp: AnyComparison,
      segmentComp: AnyComparison,
    ): string {
      if (!visitsSummary.reportData.comparisons) { // sanity check
        return '';
      }

      const firstRowIndex = ComparisonsStoreInstance.getComparisonSeriesIndex(
        periodComp.index,
        0,
      );

      const firstRow = visitsSummary.reportData.comparisons[firstRowIndex];

      const comparisonRowIndex = ComparisonsStoreInstance.getComparisonSeriesIndex(
        periodComp.index,
        segmentComp.index,
      );
      const comparisonRow = visitsSummary.reportData.comparisons[comparisonRowIndex];

      const firstPeriodRow = visitsSummary.reportData.comparisons[segmentComp.index];

      let tooltip = '<div class="comparison-card-tooltip">';

      let visitsPercent = ((comparisonRow.nb_visits / firstRow.nb_visits) * 100)
        .toFixed(2);
      visitsPercent = `${visitsPercent}%`;

      tooltip += translate('General_ComparisonCardTooltip1', [
        `'${this.htmlentities(comparisonRow.compareSegmentPretty)}'`,
        comparisonRow.comparePeriodPretty,
        visitsPercent,
        comparisonRow.nb_visits.toString(),
        firstRow.nb_visits.toString(),
      ]);
      if (periodComp.index > 0) {
        tooltip += '<br/><br/>';
        tooltip += translate('General_ComparisonCardTooltip2', [
          comparisonRow.nb_visits_change.toString(),
          this.htmlentities(firstPeriodRow.compareSegmentPretty),
          firstPeriodRow.comparePeriodPretty,
        ]);
      }

      tooltip += '</div>';
      return tooltip;
    },
    htmlentities(str: string): string {
      return Matomo.helper.htmlEntities(str);
    },
  },
  mounted() {
    Matomo.on('piwikComparisonsChanged', () => {
      this.onComparisonsChanged();
    });

    this.onComparisonsChanged();
  },
});
</script>
