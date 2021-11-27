/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { computed } from 'vue';
import ReportingPagesStoreInstance from '../ReportingPages/ReportingPages.store';
import MatomoUrl from '../MatomoUrl/MatomoUrl';
import translate from '../translate';

interface Orderable {
  order: number;
}

interface Subcategory extends Orderable {
  id: string;
  name: string;
  active: boolean;
  isGroup: boolean;
  tooltip?: string;
  subcategories: Subcategory[];
}

interface Category extends Orderable {
  id: string;
  name: string;
  active: boolean;
  subcategories: Subcategory[];
  tooltip?: string;
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
  private category = computed(() => MatomoUrl.parsed.value.category);
  private subcategory = computed(() => MatomoUrl.parsed.value.subcategory);

  readonly menu = computed(() => this.buildMenuFromPages());

  fetchMenuItems(): typeof ReportingPagesStoreInstance['menu']['value'] {
    return this.menu.value;
  }

  reloadMenuItems(): typeof ReportingMenuStore['menu']['value'] {
    return ReportingPagesStoreInstance.reloadAllPages().then(() => this.menu.value);
  }

  findSubcategory(categoryId: string, subcategoryId: string): SubcategoryFindResult {
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

  private buildMenuFromPages() {
    const menu = [];

    const activeCategory = this.category.value;
    const activeSubcategory = this.subcategory.value;

    const pages = ReportingPagesStoreInstance.pages.value;

    const categoriesHandled = {};
    pages.forEach((page) => {
      const category = { ...page.category } as Category;
      const categoryId = category.id;

      if (categoriesHandled[categoryId]) {
        return;
      }

      categoriesHandled[categoryId] = true;

      if (activeCategory && category.id === activeCategory) {
        // this doesn't really belong here but placed it here for convenience
        category.active = true;
      }

      category.subcategories = [];

      let categoryGroups: Subcategory = undefined;

      const pagesWithCategory = pages.filter((p) => p.category.id === categoryId);
      pagesWithCategory.forEach((p) => {
        const subcategory = {...p.subcategory} as Subcategory;

        if (subcategory.id === activeSubcategory && categoryId === activeCategory) {
          subcategory.active = true;
        }

        if (p.widgets && p.widgets[0] && isNumeric(p.subcategory.id)) {
          // we handle a goal or something like it
          if (!categoryGroups) {
            categoryGroups = { ...subcategory } as Subcategory;
            categoryGroups.name = translate('CoreHome_ChooseX', [category.name]);
            categoryGroups.isGroup = true;
            categoryGroups.subcategories = [];
            categoryGroups.order = 10;
          }

          if (subcategory.active) {
            categoryGroups.name = subcategory.name;
            categoryGroups.active = true;
          }

          const entityId = page.subcategory.id;
          subcategory.tooltip = `${subcategory.name} (id = ${entityId})`;

          categoryGroups.subcategories.push(subcategory);
          return;
        }

        category.subcategories.push(subcategory);
      });

      if (categoryGroups && categoryGroups.subcategories && categoryGroups.subcategories.length <= 5) {
        categoryGroups.subcategories.forEach((sub) => category.subcategories.push(sub));
      } else if (categoryGroups) {
        category.subcategories.push(categoryGroups);
      }

      category.subcategories = this.sortMenuItems(category.subcategories);

      menu.push(category);
    });

    return this.sortMenuItems(menu);
  }

  private sortMenuItems<T extends Orderable>(menu: T[]): T[] {
    const result = [...menu];
    result.sort((lhs, rhs) => {
      if (lhs < rhs) {
        return -1;
      }

      if (rhs > lhs) {
        return;
      }

      return 0;
    });
    return result;
  }
}
