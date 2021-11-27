/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { reactive, computed, readonly } from 'vue';

interface Subcategory {
  id: string;
  name: string;
  active: boolean;
  isGroup: boolean;
  subcategories: Subcategory[];
}

interface Category {
  id: string;
  name: string;
  active: boolean;
  subcategories: Subcategory[];
}

interface ReportingMenuStoreState {
  menu: Category[];
}

interface SubcategoryFindResult {
  category: Category;
  subcategory: Subcategory;
  subsubcategory: Subcategory;
}

function isNumeric(text) {
  return !Number.isNaN(parseFloat(text)) && isFinite(text);
}

export default class ReportingMenuStore {
  private privateState = reactive<ReportingMenuStoreState>({
    menu: [],
  });

  private state = readonly(this.privateState);

  readonly menu = computed(() => this.state.menu);

  fetchMenuItems() {
    // TODO
  }

  reloadMenuItems() {
    // TODO
  }

  findSubcategory(categoryId: string, subcategoryId: string): SubcategoryFindResult {
    // TODO
    let foundCategory = null;
    let foundSubcategory = null;
    let foundSubSubcategory = null;

    this.privateState.menu.forEach((category) => {
      if (category.id !== categoryId) {
        return;
      }

      (category.subcategories || []).forEach((subcategory) => {
        if (subcategory.id === subcategoryId) {
          foundCategory = category;
          foundSubcategory = subcategory;
        }

        if (subcategory.isGroup) {
          (subcategory.subcategories || []).forEach((subcat) => {
            if (subcat.id === subcategoryId) {
              foundCategory = category;
              foundSubcategory = subcategory;
              foundSubSubcategory = subcat;
            }
          });
        }
      });
    });

    return {
      category: foundCategory,
      subcategory: foundSubcategory,
      subsubcategory: foundSubSubcategory,
    };
  }
}
