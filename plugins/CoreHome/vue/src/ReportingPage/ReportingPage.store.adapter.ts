/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import ReportingPageStoreInstance from './ReportingPage.store';

function reportingPageModelAdapter() {
  return {
    get page() {
      return ReportingPageStoreInstance.page.value;
    },
    get widgets() {
      return ReportingPageStoreInstance.widgets.value;
    },
    resetPage: ReportingPageStoreInstance.resetPage.bind(ReportingPageStoreInstance),
    fetchPage: ReportingPageStoreInstance.fetchPage.bind(ReportingPageStoreInstance),
  };
}

window.angular.module('piwikApp.service').factory('reportingPageModel', reportingPageModelAdapter);
