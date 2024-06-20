/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { computed, reactive, readonly } from 'vue';
import { AjaxHelper, Matomo, Periods } from 'CoreHome';

import { EvolutionTrend } from '../types';

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

interface DashboardMetrics {
  hits_evolution: string;
  hits_evolution_trend: EvolutionTrend;
  nb_hits: string;
  nb_pageviews: string;
  nb_visits: string;
  pageviews_evolution: string;
  pageviews_evolution_trend: EvolutionTrend;
  visits_evolution: string;
  visits_evolution_trend: EvolutionTrend;
  revenue: string;
  revenue_evolution: string;
  revenue_evolution_trend: EvolutionTrend;
}

interface DashboardStoreState {
  dashboardKPIs: DashboardKPIData;
  isLoadingKPIs: boolean;
}

interface GetDashboardMockDataResponseTotals extends DashboardMetrics {
  nb_hits_badge: string;
  nb_pageviews_badge: string;
  nb_visits_badge: string;
  revenue_badge: string;
}

interface GetDashboardMockDataResponse {
  totals: GetDashboardMockDataResponseTotals;
}

class DashboardStore {
  private fetchAbort: AbortController|null = null;

  private privateState = reactive<DashboardStoreState>({
    dashboardKPIs: {
      evolutionPeriod: 'day',
      hits: '?',
      hitsBadge: '',
      hitsEvolution: '?',
      hitsTrend: 0,
      pageviews: '?',
      pageviewsBadge: '',
      pageviewsEvolution: '?',
      pageviewsTrend: 0,
      revenue: '?',
      revenueBadge: '',
      revenueEvolution: '?',
      revenueTrend: 0,
      visits: '?',
      visitsBadge: '',
      visitsEvolution: '?',
      visitsTrend: 0,
    },
    isLoadingKPIs: false,
  });

  private autoRefreshInterval = 0;

  private autoRefreshTimeout: ReturnType<typeof setTimeout>|null = null;

  readonly state = computed(() => readonly(this.privateState));

  refreshData() {
    if (this.fetchAbort) {
      this.fetchAbort.abort();
      this.fetchAbort = null;

      this.cancelAutoRefresh();
    }

    this.fetchAbort = new AbortController();
    this.privateState.isLoadingKPIs = true;

    const params: QueryParameters = {
      method: 'MultiSites.mockDashboardData',
    };

    return AjaxHelper.fetch<GetDashboardMockDataResponse>(
      params,
      { abortController: this.fetchAbort },
    ).then((response) => {
      this.updateDashboardKPIs(response);
    }).finally(() => {
      this.privateState.isLoadingKPIs = false;
      this.fetchAbort = null;

      this.startAutoRefresh();
    });
  }

  setAutoRefreshInterval(interval: number) {
    this.autoRefreshInterval = interval;
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
}

export default new DashboardStore();
