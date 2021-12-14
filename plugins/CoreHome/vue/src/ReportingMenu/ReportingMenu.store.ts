/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { computed, reactive, readonly } from 'vue';
import ReportingPagesStoreInstance from '../ReportingPages/ReportingPages.store';
import MatomoUrl from '../MatomoUrl/MatomoUrl';
import translate from '../translate';

export interface Orderable {
  order: number;
}

export interface Subcategory extends Orderable {
  id: string;
  name: string;
  isGroup: boolean;
  icon?: string;
  tooltip?: string;
  help?: string;
  subcategories: Subcategory[];
}

export interface Category extends Orderable {
  id: string;
  name: string;
  icon?: string;
  tooltip?: string;
  subcategories: Subcategory[];
}

interface ReportingMenuStoreState {
  activeCategoryId: string;
  activeSubcategoryId: string;
  activeSubsubcategoryId: string;
}

interface SubcategoryFindResult {
  category: Category;
  subcategory: Subcategory;
  subsubcategory: Subcategory;
}

function isNumeric(text) {
  const n = parseFloat(text);
  return !Number.isNaN(n) && Number.isFinite(n);
}

export class ReportingMenuStore {
  private privateState = reactive<ReportingMenuStoreState>({
    activeCategoryId: null,
    activeSubcategoryId: null,
    activeSubsubcategoryId: null,
  });

  private state = computed(() => readonly(this.privateState));

  readonly activeCategory = computed(() => this.state.value.activeCategoryId
    || MatomoUrl.parsed.value.category);

  readonly activeSubcategory = computed(() => this.state.value.activeSubcategoryId
    || MatomoUrl.parsed.value.subcategory);

  readonly activeSubsubcategory = computed(() => {
    const manuallySetId = this.state.value.activeSubsubcategoryId;
    if (manuallySetId) {
      return manuallySetId;
    }

    // default to activeSubcategory if the activeSubcategory is part of a group
    const foundCategory = this.findSubcategory(
      this.activeCategory.value,
      this.activeSubcategory.value,
    );

    if (foundCategory.subsubcategory
      && foundCategory.subsubcategory.id === this.activeSubcategory.value
    ) {
      return foundCategory.subsubcategory.id;
    }

    return null;
  });

  readonly menu = computed(() => this.buildMenuFromPages());

  fetchMenuItems(): Promise<typeof ReportingPagesStoreInstance['menu']['value']> {
    return ReportingPagesStoreInstance.getAllPages().then(() => this.menu.value);
  }

  reloadMenuItems(): Promise<typeof ReportingMenuStore['menu']['value']> {
    return ReportingPagesStoreInstance.reloadAllPages().then(() => this.menu.value);
  }

  findSubcategory(categoryId: string, subcategoryId: string): SubcategoryFindResult {
    let foundCategory = null;
    let foundSubcategory = null;
    let foundSubSubcategory = null;

    this.menu.value.forEach((category) => {
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

    const displayedCategory = MatomoUrl.parsed.value.category;
    const displayedSubcategory = MatomoUrl.parsed.value.subcategory;

    const pages = ReportingPagesStoreInstance.pages.value;

    const categoriesHandled = {};
    pages.forEach((page) => {
      const category = { ...page.category } as Category;
      const categoryId = category.id;
      const isCategoryDisplayed = categoryId === displayedCategory;

      if (categoriesHandled[categoryId]) {
        return;
      }

      categoriesHandled[categoryId] = true;

      category.subcategories = [];

      let categoryGroups: Subcategory;

      const pagesWithCategory = pages.filter((p) => p.category.id === categoryId);
      pagesWithCategory.forEach((p) => {
        const subcategory = { ...p.subcategory } as Subcategory;
        const isSubcategoryDisplayed = subcategory.id === displayedSubcategory
          && isCategoryDisplayed;

        if (p.widgets && p.widgets[0] && isNumeric(p.subcategory.id)) {
          // we handle a goal or something like it
          if (!categoryGroups) {
            categoryGroups = { ...subcategory } as Subcategory;
            categoryGroups.name = translate('CoreHome_ChooseX', [category.name]);
            categoryGroups.isGroup = true;
            categoryGroups.subcategories = [];
            categoryGroups.order = 10;
          }

          if (isSubcategoryDisplayed) {
            categoryGroups.name = subcategory.name;
          }

          const entityId = page.subcategory.id;
          subcategory.tooltip = `${subcategory.name} (id = ${entityId})`;

          categoryGroups.subcategories.push(subcategory);
          return;
        }

        category.subcategories.push(subcategory);
      });

      if (categoryGroups
        && categoryGroups.subcategories
        && categoryGroups.subcategories.length <= 5
      ) {
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
      if (lhs.order < rhs.order) {
        return -1;
      }

      if (lhs.order > rhs.order) {
        return 1;
      }

      return 0;
    });
    return result;
  }

  toggleCategory(category: Category): boolean {
    this.privateState.activeSubcategoryId = null;
    this.privateState.activeSubsubcategoryId = null;

    if (this.privateState.activeCategoryId === category.id) {
      this.privateState.activeCategoryId = null;
      return false;
    }

    this.privateState.activeCategoryId = category.id;
    return true;
  }

  enterSubcategory(
    category?: Category,
    subcategory?: Subcategory,
    subsubcategory?: Subcategory,
  ): void {
    if (!category || !subcategory) {
      return;
    }

    this.privateState.activeCategoryId = category.id;
    this.privateState.activeSubcategoryId = subcategory.id;

    if (subsubcategory) {
      this.privateState.activeSubsubcategoryId = subsubcategory.id;
    }
  }
}

export default new ReportingMenuStore();
