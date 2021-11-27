/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import ReportingMenuStoreInstance from './ReportingMenu.store';
import { cloneThenApply } from '../createAngularJsAdapter';

// TODO: removed boolean active property from objects, angularjs version should have them
function addActiveMenuItems(menu: typeof ReportingMenuStoreInstance.menu.value) {
  // TODO
  return menu;
}

function reportingMenuModelAdapter() {
  return {
    get menu() {
      return ReportingMenuStoreInstance.menu.value;
    },
    findSubcategory:
      ReportingMenuStoreInstance.findSubcategory.bind(ReportingMenuStoreInstance),
    reloadMenuItems: ReportingMenuStoreInstance.reloadMenuItems()
      .then((p) => addActiveMenuItems(cloneThenApply(p))),
    fetchMenuItems: () => ReportingMenuStoreInstance.fetchMenuItems()
      .then((p) => addActiveMenuItems(cloneThenApply(p))),
  };
}

angular.module('piwikApp.service').factory('reportingMenuModel', reportingMenuModelAdapter);
