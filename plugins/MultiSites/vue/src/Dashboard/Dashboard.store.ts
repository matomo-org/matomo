/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import {
  reactive,
  computed,
  readonly,
} from 'vue';
import {
  AjaxHelper,
  translate,
  Site,
  NumberFormatter,
} from 'CoreHome';

interface SiteWithMetrics extends Site {
  label: string;
  nb_actions: string|number;
  nb_pageviews: string|number;
  nb_visits: string|number;
  hits: string|number;
  pageviews_evolution: string;
  hits_evolution: string;
  revenue: string;
  revenue_evolution: string;
  visits_evolution: string;
  ratio?: number|string;
  previous_nb_visits?: string|number;
  previous_nb_pageviews?: string|number;
  previous_hits?: string|number;
  previous_revenue?: string|number;
  currencySymbol: string;
  periodName: string;
  previousRange: string;
  tooltip?: string;
}

interface SiteTotals {
  nb_actions: string|number;
  nb_pageviews: string|number;
  hits: string|number;
  nb_visits: string|number;
  revenue: string|number;
  previous_nb_visits: string|number;
}

interface DashboardStoreState {
  sites: SiteWithMetrics[];
  isLoading: boolean;
  pageSize: number;
  currentPage: number;
  totalVisits: string|number;
  totalPageviews: string|number;
  totalHits: string|number;
  totalActions: string|number;
  totalRevenue: string|number;
  searchTerm: string;
  lastVisits: string|number;
  lastVisitsDate: string;
  numberOfSites: number;
  loadingMessage: string;
  reverse: boolean;
  sortColumn: string;
  refreshInterval?: number;
  errorLoadingSites: boolean;
}

interface GetAllWithGroupsResponse {
  lastDate: string;
  numSites: number;
  sites: SiteWithMetrics[];
  totals: SiteTotals;
}

class DashboardStore {
  private privateState = reactive<DashboardStoreState>({
    sites: [],
    isLoading: false,
    pageSize: 25,
    currentPage: 0,
    totalVisits: '?',
    totalPageviews: '?',
    totalHits: '?',
    totalActions: '?',
    totalRevenue: '?',
    searchTerm: '',
    lastVisits: '?',
    lastVisitsDate: '?',
    numberOfSites: 0,
    loadingMessage: translate('MultiSites_LoadingWebsites'),
    reverse: true,
    sortColumn: 'nb_visits',
    refreshInterval: 0,
    errorLoadingSites: false,
  });

  private refreshTimeout: ReturnType<typeof setTimeout>|null = null;

  private fetchAbort: AbortController|null = null;

  readonly state = computed(() => readonly(this.privateState));

  readonly numberOfFilteredSites = computed(() => this.state.value.numberOfSites);

  readonly numberOfPages = computed(
    () => Math.ceil(this.numberOfFilteredSites.value / this.state.value.pageSize - 1),
  );

  readonly currentPagingOffset = computed(
    () => Math.ceil(this.state.value.currentPage * this.state.value.pageSize),
  );

  readonly paginationLowerBound = computed(() => this.currentPagingOffset.value + 1);

  readonly paginationUpperBound = computed(() => {
    let end = this.currentPagingOffset.value + this.state.value.pageSize;
    const max = this.numberOfFilteredSites.value;

    if (end > max) {
      end = max;
    }

    return end;
  });

  cancelRefereshInterval(): void {
    if (this.refreshTimeout) {
      clearTimeout(this.refreshTimeout);
      this.refreshTimeout = null;
    }
  }

  updateWebsitesList(report: GetAllWithGroupsResponse): void {
    if (!report) {
      this.onError();
      return;
    }

    const allSites = report.sites;
    allSites.forEach((site) => {
      if (site.ratio !== 1 && site.ratio !== '1') {
        const percent = NumberFormatter.formatPercent(
          Math.round(parseInt(site.ratio! as string, 10) * 100),
        );

        let metricName = null;
        let previousTotal = '0';
        let currentTotal = '0';
        let evolution = '0';
        let previousTotalAdjusted = '0';

        if (this.state.value.sortColumn === 'nb_visits'
          || this.state.value.sortColumn === 'visits_evolution'
        ) {
          previousTotal = NumberFormatter.formatNumber(site.previous_nb_visits! as string);
          currentTotal = NumberFormatter.formatNumber(site.nb_visits);
          evolution = NumberFormatter.formatPercent(site.visits_evolution);
          metricName = translate('General_ColumnNbVisits');
          previousTotalAdjusted = NumberFormatter.formatNumber(
            Math.round(parseInt(site.previous_nb_visits as string, 10)
              * parseInt(site.ratio as string, 10)),
          );
        }

        if (this.state.value.sortColumn === 'pageviews_evolution') {
          previousTotal = `${site.previous_nb_pageviews}`;
          currentTotal = `${site.nb_pageviews}`;
          evolution = NumberFormatter.formatPercent(site.pageviews_evolution);
          metricName = translate('General_ColumnPageviews');
          previousTotalAdjusted = NumberFormatter.formatNumber(
            Math.round(parseInt(site.previous_nb_pageviews as string, 10)
              * parseInt(site.ratio as string, 10)),
          );
        }

        if (this.state.value.sortColumn === 'hits_evolution') {
          previousTotal = `${site.previous_hits}`;
          currentTotal = `${site.hits}`;
          evolution = NumberFormatter.formatPercent(site.hits_evolution);
          metricName = translate('General_ColumnHits');
          previousTotalAdjusted = NumberFormatter.formatNumber(
            Math.round(parseInt(site.previous_hits as string, 10)
              * parseInt(site.ratio as string, 10)),
          );
        }

        if (this.state.value.sortColumn === 'revenue_evolution') {
          previousTotal = NumberFormatter.formatCurrency(
            site.previous_revenue! as string,
            site.currencySymbol,
          );
          currentTotal = NumberFormatter.formatCurrency(site.revenue, site.currencySymbol);
          evolution = NumberFormatter.formatPercent(site.revenue_evolution);
          metricName = translate('General_ColumnRevenue');
          previousTotalAdjusted = NumberFormatter.formatCurrency(
            Math.round(parseInt(site.previous_revenue as string, 10)
              * parseInt(site.ratio as string, 10)),
            site.currencySymbol,
          );
        }

        if (metricName) {
          site.tooltip = `${translate('MultiSites_EvolutionComparisonIncomplete', [percent])}\n`;
          site.tooltip += `${translate('MultiSites_EvolutionComparisonProportional', [
            percent,
            `${previousTotalAdjusted}`,
            metricName,
            `${previousTotal}`,
          ])}\n`;

          switch (site.periodName) {
            case 'day':
              site.tooltip += translate('MultiSites_EvolutionComparisonDay', [
                `${currentTotal}`,
                metricName,
                `${previousTotalAdjusted}`,
                site.previousRange,
                `${evolution}`,
              ]);
              break;

            case 'week':
              site.tooltip += translate('MultiSites_EvolutionComparisonWeek', [
                `${currentTotal}`,
                metricName,
                `${previousTotalAdjusted}`,
                site.previousRange,
                `${evolution}`,
              ]);
              break;

            case 'month':
              site.tooltip += translate('MultiSites_EvolutionComparisonMonth', [
                `${currentTotal}`,
                metricName,
                `${previousTotalAdjusted}`,
                site.previousRange,
                `${evolution}`,
              ]);
              break;

            case 'year':
              site.tooltip += translate('MultiSites_EvolutionComparisonYear', [
                `${currentTotal}`,
                metricName,
                `${previousTotalAdjusted}`,
                site.previousRange,
                `${evolution}`,
              ]);
              break;
            default:
              break;
          }
        }
      }
    });
    this.privateState.totalVisits = report.totals.nb_visits;
    this.privateState.totalPageviews = report.totals.nb_pageviews;
    this.privateState.totalHits = report.totals.hits;
    this.privateState.totalActions = report.totals.nb_actions;
    this.privateState.totalRevenue = report.totals.revenue;
    this.privateState.lastVisits = report.totals.previous_nb_visits;
    this.privateState.sites = allSites;
    this.privateState.numberOfSites = report.numSites;
    this.privateState.lastVisitsDate = report.lastDate;
  }

  sortBy(metric: string): void {
    if (this.state.value.sortColumn === metric) {
      this.privateState.reverse = !this.state.value.reverse;
    }

    this.privateState.sortColumn = metric;
    this.fetchAllSites();
  }

  previousPage(): void {
    this.privateState.currentPage = this.state.value.currentPage - 1;
    this.fetchAllSites();
  }

  nextPage(): void {
    this.privateState.currentPage = this.state.value.currentPage + 1;
    this.fetchAllSites();
  }

  searchSite(term: string): void {
    this.privateState.searchTerm = term;
    this.privateState.currentPage = 0;
    this.fetchAllSites();
  }

  fetchAllSites(): Promise<void> {
    if (this.fetchAbort) {
      this.fetchAbort.abort();
      this.fetchAbort = null;

      this.cancelRefereshInterval();
    }

    this.privateState.isLoading = true;
    this.privateState.errorLoadingSites = false;
    const params: QueryParameters = {
      method: 'MultiSites.getAllWithGroups',
      hideMetricsDoc: '1',
      filter_sort_order: 'asc',
      filter_limit: this.state.value.pageSize,
      filter_offset: this.currentPagingOffset.value,
      showColumns: [
        'label',
        'nb_visits',
        'nb_pageviews',
        'hits',
        'visits_evolution',
        'visits_evolution_trend',
        'pageviews_evolution',
        'pageviews_evolution_trend',
        'hits_evolution',
        'hits_evolution_trend',
        'revenue_evolution',
        'revenue_evolution_trend',
        'nb_actions',
        'revenue',
      ].join(','),
    };

    if (this.privateState.searchTerm) {
      params.pattern = this.privateState.searchTerm;
    }

    if (this.privateState.sortColumn) {
      params.filter_sort_column = this.privateState.sortColumn;
    }

    if (this.privateState.reverse) {
      params.filter_sort_order = 'desc';
    }

    this.fetchAbort = new AbortController();
    return AjaxHelper.fetch<GetAllWithGroupsResponse>(
      params,
      { abortController: this.fetchAbort },
    ).then((response) => {
      this.updateWebsitesList(response);
    }).catch(() => {
      this.onError();
    }).finally(() => {
      this.privateState.isLoading = false;
      this.fetchAbort = null;

      if (this.state.value.refreshInterval && this.state.value.refreshInterval > 0) {
        this.cancelRefereshInterval();
        this.refreshTimeout = setTimeout(() => {
          this.refreshTimeout = null;
          this.fetchAllSites();
        }, this.state.value.refreshInterval! * 1000);
      }
    });
  }

  private onError(): void {
    this.privateState.errorLoadingSites = true;
    this.privateState.sites = [];
  }

  setRefreshInterval(interval?: number): void {
    this.privateState.refreshInterval = interval;
  }

  setPageSize(pageSize: number): void {
    this.privateState.pageSize = pageSize;
  }
}

export default new DashboardStore();
