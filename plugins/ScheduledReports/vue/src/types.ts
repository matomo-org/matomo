/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

export interface Report {
  [name: string]: unknown;

  idreport: string|number;
  idsite: string|number;
  login: string;
  description: string;
  idsegment: string|number|null;
  period: string;
  hour: string|number;
  type: string;
  format: string;
  reports: string[];
  parameters: Record<string, unknown>; // QueryParameters
  ts_created: string;
  ts_last_sent: string|null;
  deleted: string|number;
  evolution_graph_within_period: string|number;
  evolution_graph_period_n: string|number;
  period_param: string;
  evolutionPeriodFor: string;
  evolutionPeriodN: number;
  periodParam: string;
  recipients: string[];
}

interface ReportPluginGlobal {
  defaultPeriod: string;
  defaultHour: string;
  defaultReportType: string;
  defaultReportFormat: string;
  reportList: Report[];
  createReportString: string;
  updateReportString: string;
  defaultEvolutionPeriodN: number;
  periodTranslations: Record<string, { single: string, plural: string }>;
}

declare global {
  interface Window {
    ReportPlugin: ReportPluginGlobal;
    getReportParametersFunctions: Record<string, (report: Report) => Record<string, unknown>>;
    resetReportParametersFunctions: Record<string, (report: Report) => void>;
    updateReportParametersFunctions: Record<string, (report: Report) => void>;
  }
}
