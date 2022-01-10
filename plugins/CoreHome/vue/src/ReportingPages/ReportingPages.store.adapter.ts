/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import ReportingPagesStoreInstance from './ReportingPages.store';
import { cloneThenApply, clone } from '../createAngularJsAdapter';

function reportingPagesModelAdapter() {
  return {
    get pages() {
      return ReportingPagesStoreInstance.pages.value;
    },
    findPageInCategory: (
      ...args: Parameters<typeof ReportingPagesStoreInstance.findPageInCategory>
    ) => clone(ReportingPagesStoreInstance.findPageInCategory(...args)),
    findPage: (...args: Parameters<typeof ReportingPagesStoreInstance.findPage>) => clone(
      ReportingPagesStoreInstance.findPage(...args),
    ),
    reloadAllPages: () => ReportingPagesStoreInstance.reloadAllPages()
      .then((p) => cloneThenApply(p)),
    getAllPages: () => ReportingPagesStoreInstance.getAllPages()
      .then((p) => cloneThenApply(p)),
  };
}

window.angular.module('piwikApp.service').factory('reportingPagesModel', reportingPagesModelAdapter);
