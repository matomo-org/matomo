/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { reactive } from 'vue';
import MatomoUrl from '../MatomoUrl/MatomoUrl';
import Matomo from '../Matomo/Matomo';
import translate from '../translate';
import Periods from '../Periods/Periods';
import AjaxHelper from '../AjaxHelper/AjaxHelper';

const SERIES_COLOR_COUNT = 8;
const SERIES_SHADE_COUNT = 3;

export interface SegmentComparison {
  params: {
    segment: string,
  },
  title?: string,
  index?: number,
}

export interface PeriodComparison {
  params: {
    period: string,
    date: string,
  },
  title?: string,
  index?: number,
}

export interface AnyComparison {
  params: { [name: string]: string },
  title: string,
  index: number,
}

export interface ComparisonsStoreState {
  segmentComparisons: SegmentComparison[];
  periodComparisons: PeriodComparison[];
  comparisonsDisabledFor: string[];
  isEnabled: boolean|null;
}

export interface ComparisonSeriesInfo {
  index: number;
  params: { [key: string]: string };
  color: string;
}

class ComparisonsStore {
  public readonly state = reactive<ComparisonsStoreState>({
    segmentComparisons: [],
    periodComparisons: [],
    comparisonsDisabledFor: [],
    isEnabled: null,
  });

  private colors: string[];

  constructor() {
    MatomoUrl.onLocationChange(() => this.updateComparisonsFromQueryParams());
    Matomo.on('piwikSegmentationInited', () => this.updateComparisonsFromQueryParams());

    this.loadComparisonsDisabledFor();

    window.$(() => {
      this.updateComparisonsFromQueryParams();
      this.colors = this.getAllSeriesColors();
    });
  }

  getComparisons(): AnyComparison[] {
    return (this.getSegmentComparisons() as AnyComparison[])
      .concat(this.getPeriodComparisons() as AnyComparison[]);
  }

  isComparing(): boolean {
    return this.isComparisonEnabled()
      // first two in each array are for the currently selected segment/period
      && (this.state.segmentComparisons.length > 1 || this.state.periodComparisons.length > 1);
  }

  isComparingPeriods(): boolean {
    return this.getPeriodComparisons().length > 1; // first is currently selected period
  }

  getSegmentComparisons(): SegmentComparison[] {
    if (!this.isComparisonEnabled()) {
      return [];
    }

    return this.state.segmentComparisons;
  }

  getPeriodComparisons(): PeriodComparison[] {
    if (!this.isComparisonEnabled()) {
      return [];
    }

    return this.state.periodComparisons;
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

  getSeriesColorName(seriesIndex, metricIndex): string {
    let colorName = `series${(seriesIndex % SERIES_COLOR_COUNT)}`;
    if (metricIndex > 0) {
      colorName += `-shade${(metricIndex % SERIES_SHADE_COUNT)}`;
    }
    return colorName;
  }

  isComparisonEnabled(): boolean {
    return this.state.isEnabled;
  }

  getIndividualComparisonRowIndices(seriesIndex): { segmentIndex: number, periodIndex: number } {
    const segmentCount = this.getSegmentComparisons().length;
    const segmentIndex = seriesIndex % segmentCount;
    const periodIndex = Math.floor(seriesIndex / segmentCount);

    return {
      segmentIndex,
      periodIndex,
    };
  }

  getComparisonSeriesIndex(periodIndex, segmentIndex): number {
    const segmentCount = this.getSegmentComparisons().length;
    return periodIndex * segmentCount + segmentIndex;
  }

  getAllComparisonSeries(): ComparisonSeriesInfo[] {
    const seriesInfo = [];

    let seriesIndex = 0;
    this.getPeriodComparisons().forEach((periodComp) => {
      this.getSegmentComparisons().forEach((segmentComp) => {
        seriesInfo.push({
          index: seriesIndex,
          params: $.extend({}, segmentComp.params, periodComp.params),
          color: this.colors[`series${seriesIndex}`],
        });
        seriesIndex += 1;
      });
    });

    return seriesInfo;
  }

  removeSegmentComparison(index): void {
    if (!this.isComparisonEnabled()) {
      throw new Error('Comparison disabled.');
    }

    const newComparisons = [].concat(this.state.segmentComparisons);
    newComparisons.splice(index, 1);

    const extraParams: {[key: string]: string} = {};
    if (index === 0) {
      extraParams.segment = newComparisons[0].params.segment;
    }

    this.updateQueryParamsFromComparisons(
      newComparisons,
      this.state.periodComparisons,
      extraParams,
    );
  }

  addSegmentComparison(params): void {
    if (!this.isComparisonEnabled()) {
      throw new Error('Comparison disabled.');
    }

    const newComparisons = this.state.segmentComparisons.concat([{ params }]);
    this.updateQueryParamsFromComparisons(newComparisons, this.state.periodComparisons);
  }

  updateQueryParamsFromComparisons(
    segmentComparisons: SegmentComparison[],
    periodComparisons: PeriodComparison[],
    extraParams = {},
  ) {
    // get unique segments/periods/dates from new Comparisons
    const compareSegments = {};
    const comparePeriodDatePairs = {};

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

    const comparePeriods = [];
    const compareDates = [];
    Object.keys(comparePeriodDatePairs).forEach((pair) => {
      const parts = pair.split('|');
      comparePeriods.push(parts[0]);
      compareDates.push(parts[1]);
    });

    const compareParams = {
      compareSegments: Object.keys(compareSegments),
      comparePeriods,
      compareDates,
    };

    // change the page w/ these new param values
    if (Matomo.helper.isAngularRenderingThePage()) {
      const search = new URLSearchParams(window.location.hash.replace(/^[#?/]+/, ''));
      const newSearch = {
        ...search,
        ...compareParams,
        ...extraParams,
      };

      delete newSearch['compareSegments[]'];
      delete newSearch['comparePeriods[]'];
      delete newSearch['compareDates[]'];

      if (JSON.stringify(newSearch) !== JSON.stringify(search)) {
        const newSearchQuery = $.param(newSearch).replace(/%5B%5D/g, '[]');
        window.broadcast.propagateAjax(newSearchQuery);
      }

      return;
    }

    const paramsToRemove = [];
    ['compareSegments', 'comparePeriods', 'compareDates'].forEach((name) => {
      if (!compareParams[name].length) {
        paramsToRemove.push(name);
      }
    });

    // angular is not rendering the page (ie, we are in the embedded dashboard) or we need to change
    // the segment
    // TODO: move this to URL service?
    const url = $.param({ ...extraParams }).replace(/%5B%5D/g, '[]');
    const strHash = $.param({ ...compareParams }).replace(/%5B%5D/g, '[]');

    window.broadcast.propagateNewPage(url, undefined, strHash, paramsToRemove);
  }

  private getAllSeriesColors() {
    const { ColorManager } = Matomo;
    const seriesColorNames = [];

    for (let i = 0; i < SERIES_COLOR_COUNT; i += 1) {
      seriesColorNames.push(`series${i}`);
      for (let j = 0; j < SERIES_SHADE_COUNT; j += 1) {
        seriesColorNames.push(`series${i}-shade${j}`);
      }
    }

    return ColorManager.getColors('comparison-series-color', seriesColorNames);
  }

  private updateComparisonsFromQueryParams() {
    let title;
    let availableSegments = [];
    try {
      availableSegments = $('.segmentEditorPanel').data('uiControlObject').impl.availableSegments || [];
    } catch (e) {
      // segment editor is not initialized yet
    }

    let compareSegments = MatomoUrl.getSearchParam('compareSegments') || [];
    compareSegments = compareSegments instanceof Array ? compareSegments : [compareSegments];

    let comparePeriods = MatomoUrl.getSearchParam('comparePeriods') || [];
    comparePeriods = comparePeriods instanceof Array ? comparePeriods : [comparePeriods];

    let compareDates = MatomoUrl.getSearchParam('compareDates') || [];
    compareDates = compareDates instanceof Array ? compareDates : [compareDates];

    // add base comparisons
    compareSegments.unshift(MatomoUrl.getSearchParam('segment'));
    comparePeriods.unshift(MatomoUrl.getSearchParam('period'));
    compareDates.unshift(MatomoUrl.getSearchParam('date'));

    const newSegmentComparisons = [];
    compareSegments.forEach((segment, idx) => {
      let storedSegment = null;

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

    const newPeriodComparisons = [];
    for (let i = 0; i < Math.min(compareDates.length, comparePeriods.length); i += 1) {
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

    this.checkEnabledForCurrentPage();
    this.setComparisons(newSegmentComparisons, newPeriodComparisons);
  }

  private checkEnabledForCurrentPage() {
    // category/subcategory is not included on top bar pages, so in that case we use module/action
    const category = MatomoUrl.getSearchParam('category') || MatomoUrl.getSearchParam('module');
    const subcategory = MatomoUrl.getSearchParam('subcategory') || MatomoUrl.getSearchParam('action');

    const id = `${category}.${subcategory}`;
    const isEnabled = this.state.comparisonsDisabledFor.indexOf(id) === -1 && this.state.comparisonsDisabledFor.indexOf(`${category}.*`) === -1;

    document.documentElement.classList.toggle('comparisonsDisabled', !isEnabled);

    this.state.isEnabled = isEnabled;
  }

  private setComparisons(newSegmentComparisons, newPeriodComparisons) {
    const oldSegmentComparisons = this.state.segmentComparisons;
    const oldPeriodComparisons = this.state.periodComparisons;

    this.state.segmentComparisons = Object.freeze(newSegmentComparisons);
    this.state.periodComparisons = Object.freeze(newPeriodComparisons);

    if (JSON.stringify(oldPeriodComparisons) !== JSON.stringify(this.state.periodComparisons)
      || JSON.stringify(oldSegmentComparisons) !== JSON.stringify(this.state.segmentComparisons)
    ) {
      Matomo.postEvent('piwikComparisonsChanged');
    }
  }

  private loadComparisonsDisabledFor() {
    AjaxHelper.fetch({
      module: 'API',
      method: 'API.getPagesComparisonsDisabledFor',
    }).then((result) => {
      this.state.comparisonsDisabledFor = result;
      this.checkEnabledForCurrentPage();
    });
  }
}

export default new ComparisonsStore();
