/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import {
  computed,
  readonly,
  reactive,
  DeepReadonly,
} from 'vue';
import { AjaxHelper } from 'CoreHome';
import { DashboardLayout, Dashboard } from '../types';

interface DashboardStoreState {
  dashboards: Dashboard[];
}

class DashboardStore {
  private privateState = reactive<DashboardStoreState>({
    dashboards: [],
  });

  readonly state = computed(() => readonly(this.privateState));

  readonly dashboards = computed(() => this.state.value.dashboards);

  private dashboardsPromise: Promise<DeepReadonly<Dashboard[]>>|null = null;

  getDashboard(dashboardId: string|number) {
    return this.getAllDashboards().then(
      (dashboards) => dashboards.find(
        (b) => parseInt(`${b.id}`, 10) === parseInt(`${dashboardId}`, 10),
      ),
    );
  }

  getDashboardLayout(dashboardId: string|number): Promise<DashboardLayout> {
    return AjaxHelper.fetch<DashboardLayout>(
      {
        module: 'Dashboard',
        action: 'getDashboardLayout',
        idDashboard: dashboardId,
      },
      {
        withTokenInUrl: true,
      },
    );
  }

  reloadAllDashboards(): ReturnType<DashboardStore['getAllDashboards']> {
    this.dashboardsPromise = null;
    return this.getAllDashboards();
  }

  getAllDashboards(): Promise<DeepReadonly<Dashboard[]>> {
    if (!this.dashboardsPromise) {
      this.dashboardsPromise = AjaxHelper.fetch<Dashboard[]>({
        method: 'Dashboard.getDashboards',
        filter_limit: '-1',
      }).then((response) => {
        if (response) {
          this.privateState.dashboards = response;
        }

        return this.dashboards.value;
      });
    }

    return this.dashboardsPromise;
  }
}

export default new DashboardStore();
