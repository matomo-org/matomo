<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div v-if="comparisonsService.isComparing()" ref="root">
    <h3>{{ translate('General_Comparisons') }}</h3>
    <div
      class="comparison card"
      v-for="(comparison, $index) in comparisonsService.getSegmentComparisons()"
      :key="comparison.index"
    >
      <div class="comparison-type">{{ translate('General_Segment') }}</div>
      <div
        class="title"
        :title="comparison.title + '<br/>' + decodeURIComponent(comparison.params.segment)"
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
        v-for="periodComparison in comparisonsService.getPeriodComparisons()"
        :key="periodComparison.index"
        :title="getComparisonTooltip(comparison, periodComparison)"
      >
        <span
          class="comparison-dot"
          :style="{
            'background-color': comparisonsService.getSeriesColor(comparison, periodComparison)
          }"
        />
        <span class="comparison-period-label">
          {{ periodComparison.title }} ({{ getComparisonPeriodType(periodComparison) }})
        </span>
      </div>
      <a
        class="remove-button"
        v-on:click="comparisonsService.removeSegmentComparison($index)"
        v-if="comparisonsService.getSegmentComparisons().length > 1"
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
import { defineComponent, ref } from 'vue';
import ComparisonsStore, { AnyComparison } from './Comparisons.store';
import Matomo from '../Matomo/Matomo';
import MatomoUrl from '../MatomoUrl/MatomoUrl';
import AjaxHelper from '../AjaxHelper/AjaxHelper';
import translate from '../translate';

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

export default defineComponent({
  props: {
  },
  data() {
    return {
      comparisonsService: ComparisonsStore,
      comparisonTooltips: null,
    };
  },
  methods: {
    comparisonHasSegment(comparison: AnyComparison) {
      return typeof comparison.params.segment !== 'undefined';
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

      return this.comparisonTooltips[periodComparison.index][segmentComparison.index];
    },
    getUrlToSegment(segment: string) {
      let { hash } = window.location;
      hash = window.broadcast.updateParamValue('comparePeriods[]=', hash);
      hash = window.broadcast.updateParamValue('compareDates[]=', hash);
      hash = window.broadcast.updateParamValue('compareSegments[]=', hash);
      hash = window.broadcast.updateParamValue(`segment=${encodeURIComponent(segment)}`, hash);
      return window.location.search + hash;
    },
    setUpTooltips() {
      const { $ } = window;
      $(this.$refs.root).tooltip({
        track: true,
        content: function transformTooltipContent() {
          const title = $(this).attr('title');
          return window.vueSanitize(title.replace(/\n/g, '<br />'));
        },
        show: { delay: 200, duration: 200 },
        hide: false,
      });
    },
    onComparisonsChanged() {
      this.comparisonTooltips = null;

      if (!this.comparisonsService.isComparing()) {
        return;
      }

      const periodComparisons = this.comparisonsService.getPeriodComparisons();
      const segmentComparisons = this.comparisonsService.getSegmentComparisons();
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
          this.comparisonTooltips[periodComp.index] = {};

          segmentComparisons.forEach((segmentComp) => {
            const tooltip = this.generateComparisonTooltip(report, periodComp, segmentComp);
            this.comparisonTooltips[periodComp.index][segmentComp.index] = tooltip;
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

      const firstRowIndex = this.comparisonsService.getComparisonSeriesIndex(
        periodComp.index,
        0,
      );

      const firstRow = visitsSummary.reportData.comparisons[firstRowIndex];

      const comparisonRowIndex = this.comparisonsService.getComparisonSeriesIndex(
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
        `'${comparisonRow.compareSegmentPretty}'`,
        comparisonRow.comparePeriodPretty,
        visitsPercent,
        comparisonRow.nb_visits.toString(),
        firstRow.nb_visits.toString(),
      ]);
      if (periodComp.index > 0) {
        tooltip += '<br/><br/>';
        tooltip += translate('General_ComparisonCardTooltip2', [
          comparisonRow.nb_visits_change.toString(),
          firstPeriodRow.compareSegmentPretty,
          firstPeriodRow.comparePeriodPretty,
        ]);
      }

      tooltip += '</div>';
      return tooltip;
    },
  },
  setup() { // TODO: is this even needed?
    const root = ref(null);
    return { root };
  },
  mounted() {
    Matomo.on('piwikComparisonsChanged', () => this.onComparisonsChanged());

    this.onComparisonsChanged();

    setTimeout(() => this.setUpTooltips());
  },
  unmounted() {
    try {
      window.$(this.refs.root).tooltip('destroy');
    } catch (e) {
      // ignore
      console.log('does this always happen?'); // TODO: Remove
    }
  },
});
</script>
