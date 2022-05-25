/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import {
  reactive,
  computed,
  readonly,
  DeepReadonly,
} from 'vue';
import AjaxHelper from '../AjaxHelper/AjaxHelper';
import MatomoUrl from '../MatomoUrl/MatomoUrl';
import Matomo from '../Matomo/Matomo';

interface ReportRef {
  module: string;
  action: string;
}

interface Report {
  relatedReports: ReportRef[];
  module: string;
  action: string;
  documentation?: string;
}

interface ReportMetadataStoreState {
  reports: Report[];
}

export class ReportMetadataStore {
  private privateState = reactive<ReportMetadataStoreState>({
    reports: [],
  });

  private state = readonly(this.privateState);

  readonly reports = computed(() => this.state.reports);

  private reportsPromise?: Promise<Report[]>;

  // TODO: it used to return an empty array when nothing was found, will that be an issue?
  findReport(reportModule?: string, reportAction?: string): DeepReadonly<Report>|undefined {
    return this.reports.value.find((r) => r.module === reportModule && r.action === reportAction);
  }

  fetchReportMetadata(): Promise<ReportMetadataStore['reports']['value']> {
    if (!this.reportsPromise) {
      this.reportsPromise = AjaxHelper.fetch({
        method: 'API.getReportMetadata',
        filter_limit: '-1',
        idSite: Matomo.idSite || MatomoUrl.parsed.value.idSite,
      }).then((response) => {
        this.privateState.reports = response;
        return response;
      });
    }

    return this.reportsPromise.then(() => this.reports.value);
  }
}

export default new ReportMetadataStore();
