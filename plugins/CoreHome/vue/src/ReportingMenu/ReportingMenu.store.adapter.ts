/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import ReportingMenuStoreInstance from './ReportingMenu.store';
import { cloneThenApply } from '../createAngularJsAdapter';

// removed boolean active property from objects in vue so we can keep the store immutable, but,
// angularjs version should still have them
function addActiveMenuItems(menu: typeof ReportingMenuStoreInstance.menu.value) {
  menu.forEach((category) => {
    if (category.id === ReportingMenuStoreInstance.activeCategory.value) {
      category.active = true;

      (category.subcategories || []).forEach((subcat) => {
        if (subcat.id === ReportingMenuStoreInstance.activeSubcategory.value) {
          subcat.active = true;

          (subcat.subcategories || []).forEach((subsubcat) => {
            if (subsubcat.id === ReportingMenuStoreInstance.activeSubsubcategory.value) {
              subsubcat.active = true;
            }
          });
        }
      });
    }
  });
  return menu;
}

function reportingMenuModelAdapter() {
  return {
    get menu() {
      return ReportingMenuStoreInstance.menu.value;
    },
    findSubcategory:
      ReportingMenuStoreInstance.findSubcategory.bind(ReportingMenuStoreInstance),
    reloadMenuItems: () => ReportingMenuStoreInstance.reloadMenuItems()
      .then((p) => addActiveMenuItems(cloneThenApply(p))),
    fetchMenuItems: () => ReportingMenuStoreInstance.fetchMenuItems()
      .then((p) => addActiveMenuItems(cloneThenApply(p))),
  };
}

angular.module('piwikApp.service').factory('reportingMenuModel', reportingMenuModelAdapter);
