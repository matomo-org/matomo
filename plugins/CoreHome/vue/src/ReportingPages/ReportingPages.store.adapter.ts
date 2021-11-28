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
    findPage: (...args: Parameters<typeof ReportingPagesStoreInstance['findPage']>) => {
      const page = ReportingPagesStoreInstance.findPage(...args);
      return cloneThenApply(page);
    },
    reloadAllPages: () => ReportingPagesStoreInstance.reloadAllPages()
      .then((p) => cloneThenApply(p)),
    getAllPages: () => ReportingPagesStoreInstance.getAllPages()
      .then((p) => cloneThenApply(p)),
  };
}

angular.module('piwikApp.service').factory('reportingPagesModel', reportingPagesModelAdapter);
