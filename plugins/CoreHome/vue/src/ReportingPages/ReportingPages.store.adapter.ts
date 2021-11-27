/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import ReportingPagesStoreInstance from './ReportingPages.store';
import { cloneThenApply } from '../createAngularJsAdapter';

function reportingPagesModelAdapter() {
  return {
    get pages() {
      return ReportingPagesStoreInstance.pages.value;
    },
    findPageInCategory:
      ReportingPagesStoreInstance.findPageInCategory.bind(ReportingPagesStoreInstance),
    findPage: ReportingPagesStoreInstance.findPage.bind(ReportingPagesStoreInstance),
    reloadAllPages: () => ReportingPagesStoreInstance.reloadAllPages()
      .then((p) => cloneThenApply(p)),
    getAllPages: () => ReportingPagesStoreInstance.getAllPages()
      .then((p) => cloneThenApply(p)),
  };
}

angular.module('piwikApp.service').factory('reportingPagesModel', reportingPagesModelAdapter);
