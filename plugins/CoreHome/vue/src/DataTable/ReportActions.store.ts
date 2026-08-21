/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { reactive } from 'vue';
import Matomo from '../Matomo/Matomo';

export type ReportActionsConfig = Record<string, unknown>;

interface ReportActionsState {
  configByReport: Record<string, ReportActionsConfig>;
}

/**
 * What each report offers in the menu its header hosts, keyed by reportIdentity().
 *
 * The header outlives its table - it is rendered outside the fragment an ajax reload replaces - so
 * after the first load it cannot read the report's config from its own markup. dataTable.js
 * publishes the reloaded config here and the header reads it, which is what stops a menu from
 * describing the report as it was when the page was built.
 */
export class ReportActionsStore {
  constructor() {
    // A published config outlives the table it came from, so it has to go when the page changes:
    // otherwise the next report mounting under the same key reads the previous page's config,
    // which is older than what twig has just rendered into its header.
    Matomo.on('matomoPageChange', () => {
      this.reset();
    });
  }

  private privateState = reactive<ReportActionsState>({
    configByReport: {},
  });

  reset(): void {
    this.privateState.configByReport = {};
  }

  get(reportKey: string): ReportActionsConfig {
    if (!reportKey) {
      return {};
    }

    return this.privateState.configByReport[reportKey] || {};
  }

  set(reportKey: string, config: ReportActionsConfig): void {
    if (reportKey) {
      this.privateState.configByReport[reportKey] = config;
    }
  }
}

export default new ReportActionsStore();
