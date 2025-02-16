/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { computed, reactive, readonly } from 'vue';
import {
  AjaxHelper,
  Matomo,
  Periods,
  NumberFormatter,
} from 'CoreHome';

import {
  DashboardMetrics,
  DashboardSiteData,
  DashboardSortOrder,
  EvolutionTrend, KPICardBadge,
} from '../types';

interface DashboardKPIData {
  badges: Record<string, KPICardBadge | null>;
  evolutionPeriod: string;
  hits: string;
  hitsCompact: string;
  hitsEvolution: string;
  hitsTrend: EvolutionTrend;
  pageviews: string;
  pageviewsCompact: string;
  pageviewsEvolution: string;
  pageviewsTrend: EvolutionTrend;
  revenue: string;
  revenueCompact: string;
  revenueEvolution: string;
  revenueTrend: EvolutionTrend;
  visits: string;
  visitsCompact: string;
  visitsEvolution: string;
  visitsTrend: EvolutionTrend;
}

interface DashboardStoreState {
  dashboardKPIs: DashboardKPIData;
  dashboardSites: DashboardSiteData[];
  errorLoading: boolean;
  isLoadingKPIs: boolean;
  isLoadingSites: boolean;
  numSites: number;
  paginationCurrentPage: number;
  sortColumn: string;
  sortOrder: DashboardSortOrder;
}

interface GetAllWithGroupsDataResponse {
  sites: DashboardSiteData[];
  totals: DashboardMetrics;
  numSites: number;
}

const DEFAULT_SORT_ORDER = 'desc';
const DEFAULT_SORT_COLUMN = 'nb_visits';

class DashboardStore {
  private fetchAbort: AbortController|null = null;

  private privateState = reactive<DashboardStoreState>({
    dashboardKPIs: {
      badges: {},
      evolutionPeriod: 'day',
      hits: '?',
      hitsCompact: '?',
      hitsEvolution: '',
      hitsTrend: 0,
      pageviews: '?',
      pageviewsCompact: '?',
      pageviewsEvolution: '',
      pageviewsTrend: 0,
      revenue: '?',
      revenueCompact: '?',
      revenueEvolution: '',
      revenueTrend: 0,
      visits: '?',
      visitsCompact: '?',
      visitsEvolution: '',
      visitsTrend: 0,
    },
    dashboardSites: [],
    errorLoading: false,
    isLoadingKPIs: false,
    isLoadingSites: false,
    numSites: 0,
    paginationCurrentPage: 0,
    sortColumn: DEFAULT_SORT_COLUMN,
    sortOrder: DEFAULT_SORT_ORDER,
  });

  private autoRefreshInterval = 0;

  private autoRefreshTimeout: ReturnType<typeof setTimeout>|null = null;

  private pageSize = 25;

  private searchTerm = '';

  readonly state = computed(() => readonly(this.privateState));

  readonly numberOfPages = computed(
    () => Math.ceil(this.state.value.numSites / this.pageSize - 1),
  );

  readonly currentPagingOffset = computed(
    () => Math.ceil(this.state.value.paginationCurrentPage * this.pageSize),
  );

  readonly paginationLowerBound = computed(() => {
    if (this.state.value.numSites === 0) {
      return 0;
    }

    return 1 + this.currentPagingOffset.value;
  });

  readonly paginationUpperBound = computed(() => {
    if (this.state.value.numSites === 0) {
      return 0;
    }

    const end = this.pageSize + this.currentPagingOffset.value;
    const max = this.state.value.numSites;

    if (end < max) {
      return end;
    }

    return max;
  });

  reloadDashboard(): void {
    this.privateState.sortColumn = DEFAULT_SORT_COLUMN;
    this.privateState.sortOrder = DEFAULT_SORT_ORDER;
    this.privateState.paginationCurrentPage = 0;

    this.refreshData();
  }

  navigateNextPage(): void {
    if (this.privateState.paginationCurrentPage === this.numberOfPages.value) {
      return;
    }

    this.privateState.paginationCurrentPage += 1;

    this.refreshData(true);
  }

  navigatePreviousPage(): void {
    if (this.privateState.paginationCurrentPage === 0) {
      return;
    }

    this.privateState.paginationCurrentPage -= 1;

    this.refreshData(true);
  }

  searchSite(term: string): void {
    this.searchTerm = term;
    this.privateState.paginationCurrentPage = 0;

    this.refreshData(true);
  }

  setAutoRefreshInterval(interval: number) {
    this.autoRefreshInterval = interval;
  }

  setPageSize(size: number) {
    this.pageSize = size;
  }

  sortBy(column: string) {
    if (this.privateState.sortColumn === column) {
      this.privateState.sortOrder = this.privateState.sortOrder === 'desc' ? 'asc' : 'desc';
    } else {
      this.privateState.sortOrder = column === 'label' ? 'asc' : 'desc';
    }

    this.privateState.sortColumn = column;

    this.refreshData(true);
  }

  private cancelAutoRefresh() {
    if (!this.autoRefreshTimeout) {
      return;
    }

    clearTimeout(this.autoRefreshTimeout);

    this.autoRefreshTimeout = null;
  }

  private refreshData(onlySites = false) {
    if (this.fetchAbort) {
      this.fetchAbort.abort();
      this.fetchAbort = null;

      this.cancelAutoRefresh();
    }

    this.fetchAbort = new AbortController();
    this.privateState.errorLoading = false;
    this.privateState.isLoadingKPIs = !onlySites;
    this.privateState.isLoadingSites = true;

    const params: QueryParameters = {
      method: 'MultiSites.getAllWithGroups',
      filter_limit: this.pageSize,
      filter_offset: this.currentPagingOffset.value,
      filter_sort_column: this.privateState.sortColumn,
      filter_sort_order: this.privateState.sortOrder,
      format_metrics: 0,
      showColumns: [
        'hits_evolution',
        'hits_evolution_trend',
        'label',
        'hits',
        'nb_pageviews',
        'nb_visits',
        'pageviews_evolution',
        'pageviews_evolution_trend',
        'revenue',
        'revenue_evolution',
        'revenue_evolution_trend',
        'visits_evolution',
        'visits_evolution_trend',
      ].join(','),
    };

    if (this.searchTerm) {
      params.pattern = this.searchTerm;
    }
    return AjaxHelper.fetch<GetAllWithGroupsDataResponse>(
      params,
      {
        abortController: this.fetchAbort,
        createErrorNotification: false,
      },
    ).then((response) => {
      if (!onlySites) {
        this.updateDashboardKPIs(response);
        Matomo.postEvent('MultiSites.DashboardKPIs.updated', {
          parameters: (new AjaxHelper()).mixinDefaultGetParams({
            filter_limit: this.pageSize,
            filter_offset: this.currentPagingOffset.value,
            filter_sort_column: this.privateState.sortColumn,
            filter_sort_order: this.privateState.sortOrder,
            pattern: this.searchTerm,
          }),
          kpis: this.privateState.dashboardKPIs,
        });
      }

      this.updateDashboardSites(response);
    }).catch(() => {
      this.privateState.dashboardSites = [];
      this.privateState.errorLoading = true;
    }).finally(() => {
      this.privateState.isLoadingKPIs = false;
      this.privateState.isLoadingSites = false;
      this.fetchAbort = null;

      this.startAutoRefresh();
    });
  }

  private startAutoRefresh() {
    this.cancelAutoRefresh();

    if (this.autoRefreshInterval <= 0) {
      return;
    }

    let currentPeriod;

    try {
      currentPeriod = Periods.parse(
        Matomo.period as string,
        Matomo.currentDateString as string,
      );
    } catch (e) {
      // gracefully ignore period parsing errors
    }

    if (!currentPeriod || !currentPeriod.containsToday()) {
      return;
    }

    this.autoRefreshTimeout = setTimeout(() => {
      this.autoRefreshTimeout = null;
      this.refreshData();
    }, this.autoRefreshInterval * 1000);
  }

  private updateDashboardKPIs(response: GetAllWithGroupsDataResponse) {
    this.privateState.dashboardKPIs = {
      badges: {
        hits: null,
        pageviews: null,
        revenue: null,
        visits: null,
      },
      evolutionPeriod: Matomo.period as string,
      hits: NumberFormatter.formatNumber(response.totals.hits),
      hitsCompact: NumberFormatter.formatNumberCompact(response.totals.hits),
      hitsEvolution: NumberFormatter.calculateAndFormatEvolution(
        response.totals.hits,
        response.totals.previous_hits,
        true,
      ),
      hitsTrend: Math.sign(
        response.totals.hits - response.totals.previous_hits,
      ) as EvolutionTrend,
      pageviews: NumberFormatter.formatNumber(response.totals.nb_pageviews),
      pageviewsCompact: NumberFormatter.formatNumberCompact(response.totals.nb_pageviews),
      pageviewsEvolution: NumberFormatter.calculateAndFormatEvolution(
        response.totals.nb_pageviews,
        response.totals.previous_nb_pageviews,
        true,
      ),
      pageviewsTrend: Math.sign(
        response.totals.nb_pageviews - response.totals.previous_nb_pageviews,
      ) as EvolutionTrend,
      revenue: NumberFormatter.formatCurrency(response.totals.revenue, ''),
      revenueCompact: NumberFormatter.formatCurrencyCompact(response.totals.revenue, ''),
      revenueEvolution: NumberFormatter.calculateAndFormatEvolution(
        response.totals.revenue,
        response.totals.previous_revenue,
        true,
      ),
      revenueTrend: Math.sign(
        response.totals.revenue - response.totals.previous_revenue,
      ) as EvolutionTrend,
      visits: NumberFormatter.formatNumber(response.totals.nb_visits),
      visitsCompact: NumberFormatter.formatNumberCompact(response.totals.nb_visits),
      visitsEvolution: NumberFormatter.calculateAndFormatEvolution(
        response.totals.nb_visits,
        response.totals.previous_nb_visits,
        true,
      ),
      visitsTrend: Math.sign(
        response.totals.nb_visits - response.totals.previous_nb_visits,
      ) as EvolutionTrend,
    };
  }

  private updateDashboardSites(response: GetAllWithGroupsDataResponse) {
    this.privateState.dashboardSites = response.sites;
    this.privateState.numSites = response.numSites;
  }
}

export default new DashboardStore();
