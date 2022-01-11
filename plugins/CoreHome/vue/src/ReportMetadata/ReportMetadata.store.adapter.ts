/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import ReportMetadataStoreInstance from './ReportMetadata.store';
import { cloneThenApply } from '../createAngularJsAdapter';

window.angular.module('piwikApp.service').factory('reportMetadataModel', () => ({
  get reports() {
    return ReportMetadataStoreInstance.reports.value;
  },
  findReport:
    ReportMetadataStoreInstance.findReport.bind(ReportMetadataStoreInstance),
  fetchReportMetadata: () => ReportMetadataStoreInstance.fetchReportMetadata()
    .then((m) => cloneThenApply(m)),
}));
