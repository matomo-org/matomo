/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { computed, reactive, readonly } from 'vue';
import { AjaxHelper, Matomo, Periods } from 'CoreHome';

import {
  DashboardMetrics,
  DashboardSiteData,
  DashboardSortOrder,
  EvolutionTrend,
} from '../types';

interface DashboardKPIData {
  evolutionPeriod: string;
  hits: string;
  hitsBadge: string;
  hitsEvolution: string;
  hitsTrend: EvolutionTrend;
  pageviews: string;
  pageviewsBadge: string;
  pageviewsEvolution: string;
  pageviewsTrend: EvolutionTrend;
  revenue: string;
  revenueBadge: string;
  revenueEvolution: string;
  revenueTrend: EvolutionTrend;
  visits: string;
  visitsBadge: string;
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
  sparklineDate: string;
  sortColumn: string;
  sortOrder: DashboardSortOrder;
}

interface GetDashboardMockDataResponseTotals extends DashboardMetrics {
  nb_hits_badge: string;
  nb_pageviews_badge: string;
  nb_visits_badge: string;
  revenue_badge: string;
}

interface GetDashboardMockDataResponse {
  sites: DashboardSiteData[];
  totals: GetDashboardMockDataResponseTotals;
  numSites: number;
  sparklineDate: string;
}

class DashboardStore {
  private fetchAbort: AbortController|null = null;

  private privateState = reactive<DashboardStoreState>({
    dashboardKPIs: {
      evolutionPeriod: 'day',
      hits: '?',
      hitsBadge: '',
      hitsEvolution: '',
      hitsTrend: 0,
      pageviews: '?',
      pageviewsBadge: '',
      pageviewsEvolution: '',
      pageviewsTrend: 0,
      revenue: '?',
      revenueBadge: '',
      revenueEvolution: '',
      revenueTrend: 0,
      visits: '?',
      visitsBadge: '',
      visitsEvolution: '',
      visitsTrend: 0,
    },
    dashboardSites: [],
    errorLoading: false,
    isLoadingKPIs: false,
    isLoadingSites: false,
    numSites: 0,
    paginationCurrentPage: 0,
    sparklineDate: '',
    sortColumn: 'nb_visits',
    sortOrder: 'desc',
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

  refreshData(onlySites = false) {
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
      method: 'MultiSites.mockDashboardData',
      filter_limit: this.pageSize,
      filter_offset: this.currentPagingOffset.value,
      filter_sort_column: this.privateState.sortColumn,
      filter_sort_order: this.privateState.sortOrder,
      showColumns: [
        'hits_evolution',
        'hits_evolution_trend',
        'label',
        'nb_hits',
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

    return AjaxHelper.fetch<GetDashboardMockDataResponse>(
      params,
      { abortController: this.fetchAbort },
    ).then((response) => {
      if (!onlySites) {
        this.updateDashboardKPIs(response);
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

  private updateDashboardKPIs(response: GetDashboardMockDataResponse) {
    this.privateState.dashboardKPIs = {
      evolutionPeriod: Matomo.period as string,
      hits: response.totals.nb_hits,
      hitsBadge: response.totals.nb_hits_badge,
      hitsEvolution: response.totals.hits_evolution,
      hitsTrend: response.totals.hits_evolution_trend,
      pageviews: response.totals.nb_pageviews,
      pageviewsBadge: response.totals.nb_pageviews_badge,
      pageviewsEvolution: response.totals.pageviews_evolution,
      pageviewsTrend: response.totals.pageviews_evolution_trend,
      revenue: response.totals.revenue,
      revenueBadge: response.totals.revenue_badge,
      revenueEvolution: response.totals.revenue_evolution,
      revenueTrend: response.totals.revenue_evolution_trend,
      visits: response.totals.nb_visits,
      visitsBadge: response.totals.nb_visits_badge,
      visitsEvolution: response.totals.visits_evolution,
      visitsTrend: response.totals.visits_evolution_trend,
    };
  }

  private updateDashboardSites(response: GetDashboardMockDataResponse) {
    this.privateState.dashboardSites = response.sites;
    this.privateState.numSites = response.numSites;
    this.privateState.sparklineDate = response.sparklineDate;
  }
}

export default new DashboardStore();
