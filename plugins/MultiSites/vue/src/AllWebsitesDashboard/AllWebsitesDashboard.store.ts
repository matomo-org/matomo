/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { computed, reactive, readonly } from 'vue';

import { EvolutionTrend } from '../types';

interface DashboardKPIData {
  evolutionPeriod: string;
  hits: string;
  hitsEvolution: string;
  hitsTrend: EvolutionTrend;
  pageviews: string;
  pageviewsEvolution: string;
  pageviewsTrend: EvolutionTrend;
  revenue: string;
  revenueEvolution: string;
  revenueTrend: EvolutionTrend;
  visits: string;
  visitsEvolution: string;
  visitsTrend: EvolutionTrend;
}

interface DashboardStoreState {
  dashboardKPIs: DashboardKPIData;
  isLoadingKPIs: boolean;
}

class DashboardStore {
  private privateState = reactive<DashboardStoreState>({
    dashboardKPIs: {
      evolutionPeriod: '?',
      hits: '?',
      hitsEvolution: '?',
      hitsTrend: 0,
      pageviews: '?',
      pageviewsEvolution: '?',
      pageviewsTrend: 0,
      revenue: '?',
      revenueEvolution: '?',
      revenueTrend: 0,
      visits: '?',
      visitsEvolution: '?',
      visitsTrend: 0,
    },
    isLoadingKPIs: false,
  });

  readonly state = computed(() => readonly(this.privateState));

  refreshData() {
    this.privateState.isLoadingKPIs = true;

    window.setTimeout(() => {
      this.privateState.dashboardKPIs = {
        evolutionPeriod: 'last time',
        hits: '2,345',
        hitsEvolution: '3,456%',
        hitsTrend: -1,
        pageviews: '3,456',
        pageviewsEvolution: '0,0%',
        pageviewsTrend: 0,
        revenue: '2,345',
        revenueEvolution: '0,0%',
        revenueTrend: 0,
        visits: '2,345',
        visitsEvolution: '1,234%',
        visitsTrend: 1,
      };

      this.privateState.isLoadingKPIs = false;
    }, 2500);
  }
}

export default new DashboardStore();
