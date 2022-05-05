/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import {
  reactive,
  watch,
  computed,
  readonly,
} from 'vue';
import MatomoUrl from '../MatomoUrl/MatomoUrl';
import Matomo from '../Matomo/Matomo';
import { translate } from '../translate';
import Periods from '../Periods/Periods';
import AjaxHelper from '../AjaxHelper/AjaxHelper';
import SegmentsStore from '../Segmentation/Segments.store';

const SERIES_COLOR_COUNT = 8;
const SERIES_SHADE_COUNT = 3;

export interface SegmentComparison {
  params: {
    segment: string,
  },
  title: string,
  index: number,
}

export interface PeriodComparison {
  params: {
    period: string,
    date: string,
  },
  title: string,
  index: number,
}

export interface AnyComparison {
  params: { [name: string]: string },
  title: string,
  index: number,
}

export interface ComparisonsStoreState {
  comparisonsDisabledFor: string[];
}

export interface ComparisonSeriesInfo {
  index: number;
  params: { [key: string]: string };
  color: string;
}

function wrapArray<T>(values: T | T[]): T[] {
  if (!values) {
    return [];
  }
  return Array.isArray(values) ? values : [values];
}

export default class ComparisonsStore {
  private privateState = reactive<ComparisonsStoreState>({
    comparisonsDisabledFor: [],
  });

  readonly state = readonly(this.privateState); // for tests

  private colors: { [key: string]: string } = {};

  readonly segmentComparisons = computed(() => this.parseSegmentComparisons());

  readonly periodComparisons = computed(() => this.parsePeriodComparisons());

  readonly isEnabled = computed(() => this.checkEnabledForCurrentPage());

  constructor() {
    this.loadComparisonsDisabledFor();

    $(() => {
      this.colors = this.getAllSeriesColors() as { [key: string]: string };
    });

    watch(
      () => this.getComparisons(),
      () => Matomo.postEvent('piwikComparisonsChanged'),
      { deep: true },
    );
  }

  getComparisons(): AnyComparison[] {
    return (this.getSegmentComparisons() as AnyComparison[])
      .concat(this.getPeriodComparisons() as AnyComparison[]);
  }

  isComparing(): boolean {
    return this.isComparisonEnabled()
      // first two in each array are for the currently selected segment/period
      && (this.segmentComparisons.value.length > 1
        || this.periodComparisons.value.length > 1);
  }

  isComparingPeriods(): boolean {
    return this.getPeriodComparisons().length > 1; // first is currently selected period
  }

  getSegmentComparisons(): SegmentComparison[] {
    if (!this.isComparisonEnabled()) {
      return [];
    }

    return this.segmentComparisons.value;
  }

  getPeriodComparisons(): PeriodComparison[] {
    if (!this.isComparisonEnabled()) {
      return [];
    }

    return this.periodComparisons.value;
  }

  getSeriesColor(
    segmentComparison: SegmentComparison,
    periodComparison: PeriodComparison,
    metricIndex = 0,
  ): string {
    const seriesIndex = this.getComparisonSeriesIndex(
      periodComparison.index,
      segmentComparison.index,
    ) % SERIES_COLOR_COUNT;

    if (metricIndex === 0) {
      return this.colors[`series${seriesIndex}`];
    }

    const shadeIndex = metricIndex % SERIES_SHADE_COUNT;
    return this.colors[`series${seriesIndex}-shade${shadeIndex}`];
  }

  getSeriesColorName(seriesIndex: number, metricIndex: number): string {
    let colorName = `series${(seriesIndex % SERIES_COLOR_COUNT)}`;
    if (metricIndex > 0) {
      colorName += `-shade${(metricIndex % SERIES_SHADE_COUNT)}`;
    }
    return colorName;
  }

  isComparisonEnabled(): boolean {
    return this.isEnabled.value;
  }

  getIndividualComparisonRowIndices(seriesIndex: number): {
    segmentIndex: number,
    periodIndex: number,
  } {
    const segmentCount = this.getSegmentComparisons().length;
    const segmentIndex = seriesIndex % segmentCount;
    const periodIndex = Math.floor(seriesIndex / segmentCount);

    return {
      segmentIndex,
      periodIndex,
    };
  }

  getComparisonSeriesIndex(periodIndex: number, segmentIndex: number): number {
    const segmentCount = this.getSegmentComparisons().length;
    return periodIndex * segmentCount + segmentIndex;
  }

  getAllComparisonSeries(): ComparisonSeriesInfo[] {
    const seriesInfo: ComparisonSeriesInfo[] = [];

    let seriesIndex = 0;
    this.getPeriodComparisons().forEach((periodComp) => {
      this.getSegmentComparisons().forEach((segmentComp) => {
        seriesInfo.push({
          index: seriesIndex,
          params: { ...segmentComp.params, ...periodComp.params },
          color: this.colors[`series${seriesIndex}`],
        });
        seriesIndex += 1;
      });
    });

    return seriesInfo;
  }

  removeSegmentComparison(index: number): void {
    if (!this.isComparisonEnabled()) {
      throw new Error('Comparison disabled.');
    }

    const newComparisons: SegmentComparison[] = [...this.segmentComparisons.value];
    newComparisons.splice(index, 1);

    const extraParams: {[key: string]: string} = {};
    if (index === 0) {
      extraParams.segment = newComparisons[0].params.segment;
    }

    this.updateQueryParamsFromComparisons(
      newComparisons,
      this.periodComparisons.value,
      extraParams,
    );
  }

  addSegmentComparison(params: { [name: string]: string }): void {
    if (!this.isComparisonEnabled()) {
      throw new Error('Comparison disabled.');
    }

    const newComparisons = this.segmentComparisons.value
      .concat([{ params, index: -1, title: '' } as SegmentComparison]);
    this.updateQueryParamsFromComparisons(newComparisons, this.periodComparisons.value);
  }

  private updateQueryParamsFromComparisons(
    segmentComparisons: SegmentComparison[],
    periodComparisons: PeriodComparison[],
    extraParams = {},
  ) {
    // get unique segments/periods/dates from new Comparisons
    const compareSegments: {[key: string]: boolean} = {};
    const comparePeriodDatePairs: {[key: string]: boolean} = {};

    let firstSegment = false;
    let firstPeriod = false;

    segmentComparisons.forEach((comparison) => {
      if (firstSegment) {
        compareSegments[comparison.params.segment] = true;
      } else {
        firstSegment = true;
      }
    });

    periodComparisons.forEach((comparison) => {
      if (firstPeriod) {
        comparePeriodDatePairs[`${comparison.params.period}|${comparison.params.date}`] = true;
      } else {
        firstPeriod = true;
      }
    });

    const comparePeriods: string[] = [];
    const compareDates: string[] = [];
    Object.keys(comparePeriodDatePairs).forEach((pair) => {
      const parts = pair.split('|');
      comparePeriods.push(parts[0]);
      compareDates.push(parts[1]);
    });

    const compareParams: {[key: string]: string[]} = {
      compareSegments: Object.keys(compareSegments),
      comparePeriods,
      compareDates,
    };

    // change the page w/ these new param values
    const baseParams = Matomo.helper.isAngularRenderingThePage()
      ? MatomoUrl.hashParsed.value
      : MatomoUrl.urlParsed.value;
    MatomoUrl.updateLocation({
      ...baseParams,
      ...compareParams,
      ...extraParams,
    });
  }

  private getAllSeriesColors() {
    const { ColorManager } = Matomo;
    if (!ColorManager) {
      return [];
    }

    const seriesColorNames = [];

    for (let i = 0; i < SERIES_COLOR_COUNT; i += 1) {
      seriesColorNames.push(`series${i}`);
      for (let j = 0; j < SERIES_SHADE_COUNT; j += 1) {
        seriesColorNames.push(`series${i}-shade${j}`);
      }
    }

    return ColorManager.getColors('comparison-series-color', seriesColorNames);
  }

  private loadComparisonsDisabledFor() {
    const matomoModule: string = MatomoUrl.parsed.value.module as string;

    // check if body id #installation exist
    if (window.piwik.installation) {
      this.privateState.comparisonsDisabledFor = [];
      return;
    }

    if (matomoModule === 'CoreUpdater'
      || matomoModule === 'Installation'
      || matomoModule === 'Overlay'
      || window.piwik.isPagesComparisonApiDisabled
    ) {
      this.privateState.comparisonsDisabledFor = [];
      return;
    }

    AjaxHelper.fetch({
      module: 'API',
      method: 'API.getPagesComparisonsDisabledFor',
    }).then((result) => {
      this.privateState.comparisonsDisabledFor = result;
    });
  }

  private parseSegmentComparisons(): SegmentComparison[] {
    const { availableSegments } = SegmentsStore.state;

    const compareSegments: string[] = [
      ...wrapArray(MatomoUrl.parsed.value.compareSegments as string[]),
    ];

    // add base comparisons
    compareSegments.unshift(MatomoUrl.parsed.value.segment as string || '');

    const newSegmentComparisons: SegmentComparison[] = [];
    compareSegments.forEach((segment, idx) => {
      let storedSegment!: { definition: string, name: string };

      availableSegments.forEach((s) => {
        if (s.definition === segment
          || s.definition === decodeURIComponent(segment)
          || decodeURIComponent(s.definition) === segment
        ) {
          storedSegment = s;
        }
      });

      let segmentTitle = storedSegment ? storedSegment.name : translate('General_Unknown');
      if (segment.trim() === '') {
        segmentTitle = translate('SegmentEditor_DefaultAllVisits');
      }

      newSegmentComparisons.push({
        params: {
          segment,
        },
        title: Matomo.helper.htmlDecode(segmentTitle),
        index: idx,
      });
    });

    return newSegmentComparisons;
  }

  private parsePeriodComparisons(): PeriodComparison[] {
    const comparePeriods: string[] = [
      ...wrapArray(MatomoUrl.parsed.value.comparePeriods as string[]),
    ];

    const compareDates: string[] = [
      ...wrapArray(MatomoUrl.parsed.value.compareDates as string[]),
    ];

    comparePeriods.unshift(MatomoUrl.parsed.value.period as string);
    compareDates.unshift(MatomoUrl.parsed.value.date as string);

    const newPeriodComparisons: PeriodComparison[] = [];
    for (let i = 0; i < Math.min(compareDates.length, comparePeriods.length); i += 1) {
      let title;
      try {
        title = Periods.parse(comparePeriods[i], compareDates[i]).getPrettyString();
      } catch (e) {
        title = translate('General_Error');
      }

      newPeriodComparisons.push({
        params: {
          date: compareDates[i],
          period: comparePeriods[i],
        },
        title,
        index: i,
      });
    }

    return newPeriodComparisons;
  }

  private checkEnabledForCurrentPage() {
    // category/subcategory is not included on top bar pages, so in that case we use module/action
    const category = MatomoUrl.parsed.value.category || MatomoUrl.parsed.value.module;
    const subcategory = MatomoUrl.parsed.value.subcategory
      || MatomoUrl.parsed.value.action;

    const id = `${category}.${subcategory}`;
    const isEnabled = this.privateState.comparisonsDisabledFor.indexOf(id) === -1
      && this.privateState.comparisonsDisabledFor.indexOf(`${category}.*`) === -1;

    document.documentElement.classList.toggle('comparisonsDisabled', !isEnabled);

    return isEnabled;
  }
}
