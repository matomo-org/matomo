/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { reactive } from 'vue';
import Matomo from '../Matomo/Matomo';
import type { DataTableAction, FooterIconGroup } from './DataTableActions.vue';

/**
 * What _dataTableActions.twig publishes about one report. Declared beside the store rather than in
 * a reader, so the template that writes it and everything that reads it share one shape.
 */
export interface ReportActionsConfig {
  showFooter: boolean;
  showFooterIcons: boolean;
  footerIcons: FooterIconGroup[];
  viewDataTable: string;
  clientSideParameters: Record<string, unknown>;
  isDataTableEmpty: boolean;
  showFlattenTable: boolean;
  reportSupportsFlatten: boolean;
  reportSupportsPercentageValues: boolean;
  exportSupportsFlatten: boolean;
  hasMultipleDimensions: boolean;
  showTotalsRow: boolean;
  showExcludeLowPopulation: boolean;
  showPivotBySubtable: boolean;
  dataTableActions: DataTableAction[];
  showExport: boolean;
  showExportAsImageIcon: boolean;
  showAnnotations: boolean;
  showPeriods: boolean;
  selectablePeriods: string[];
  requestParams: Record<string, unknown>;
  maxFilterLimit: number;
  apiMethodToRequestDataTable: string;
  pivotDimensionName: string|null;
  actionTranslations: Record<string, string>;
  // Read by dataTable.js only: the header mounts its search separately.
  showSearch?: boolean;
}

interface ReportActionsState {
  configByReport: Record<string, Partial<ReportActionsConfig>>;
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

  get(reportKey: string): Partial<ReportActionsConfig> {
    const published = this.privateState.configByReport;

    // Reads the keys, not only its own: a reader whose key follows the DOM has to wake when a new
    // one is written, or it keeps returning the empty config its old key still points at.
    if (!reportKey || !Object.keys(published).length) {
      return {};
    }

    return published[reportKey] || {};
  }

  set(reportKey: string, config: Partial<ReportActionsConfig>): void {
    if (reportKey) {
      this.privateState.configByReport[reportKey] = config;
    }
  }
}

export default new ReportActionsStore();
