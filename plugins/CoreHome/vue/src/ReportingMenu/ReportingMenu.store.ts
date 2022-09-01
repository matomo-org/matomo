/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { computed, reactive, readonly } from 'vue';
import ReportingPagesStoreInstance from '../ReportingPages/ReportingPages.store';
import MatomoUrl from '../MatomoUrl/MatomoUrl';
import { translate } from '../translate';
import { sortOrderables } from '../Orderable';
import { Category, CategoryContainer, getCategoryChildren } from './Category';
import { getSubcategoryChildren, Subcategory, SubcategoryContainer } from './Subcategory';

interface ReportingMenuStoreState {
  activeCategoryId?: string|null;
  activeSubcategoryId: string|null;
  activeSubsubcategoryId: string|null;
}

interface SubcategoryFindResult {
  category?: Category;
  subcategory?: Subcategory;
  subsubcategory?: Subcategory;
}

function isNumeric(text: string) {
  const n = parseFloat(text);
  return !Number.isNaN(n) && Number.isFinite(n);
}

export class ReportingMenuStore {
  private privateState = reactive<ReportingMenuStoreState>({
    activeSubcategoryId: null,
    activeSubsubcategoryId: null,
  });

  private state = computed(() => readonly(this.privateState));

  readonly activeCategory = computed(
    () => (typeof this.state.value.activeCategoryId !== 'undefined'
      ? this.state.value.activeCategoryId
      : MatomoUrl.parsed.value.category as string),
  );

  readonly activeSubcategory = computed(
    () => this.state.value.activeSubcategoryId || MatomoUrl.parsed.value.subcategory as string,
  );

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

  fetchMenuItems(): Promise<ReportingMenuStore['menu']['value']> {
    return ReportingPagesStoreInstance.getAllPages().then(() => this.menu.value);
  }

  reloadMenuItems(): Promise<ReportingMenuStore['menu']['value']> {
    return ReportingPagesStoreInstance.reloadAllPages().then(() => this.menu.value);
  }

  findSubcategory(categoryId: string|null, subcategoryId: string): SubcategoryFindResult {
    let foundCategory: Category|undefined = undefined;
    let foundSubcategory: Subcategory|undefined = undefined;
    let foundSubSubcategory: Subcategory|undefined = undefined;

    this.menu.value.forEach((category) => {
      if (category.id !== categoryId) {
        return;
      }

      (getCategoryChildren(category) || []).forEach((subcategory) => {
        if (subcategory.id === subcategoryId) {
          foundCategory = category;
          foundSubcategory = subcategory;
        }

        if (subcategory.isGroup) {
          (getSubcategoryChildren(subcategory) || []).forEach((subcat) => {
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
    const menu: Category[] = [];

    const displayedCategory = MatomoUrl.parsed.value.category as string;
    const displayedSubcategory = MatomoUrl.parsed.value.subcategory as string;

    const pages = ReportingPagesStoreInstance.pages.value;

    const categoriesHandled: Record<string, boolean> = {};
    pages.forEach((page) => {
      const category = { ...page.category } as Category;
      const categoryId = category.id;
      const isCategoryDisplayed = categoryId === displayedCategory;

      if (categoriesHandled[categoryId]) {
        return;
      }

      categoriesHandled[categoryId] = true;

      (category as CategoryContainer).subcategories = [];

      let categoryGroups: Subcategory|null = null;

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
            (categoryGroups as SubcategoryContainer).subcategories = [];
            categoryGroups.order = 10;
          }

          if (isSubcategoryDisplayed) {
            categoryGroups.name = subcategory.name;
          }

          const entityId = page.subcategory.id;
          subcategory.tooltip = `${subcategory.name} (id = ${entityId})`;

          (categoryGroups as SubcategoryContainer).subcategories.push(subcategory);
          return;
        }

        (category as CategoryContainer).subcategories.push(subcategory);
      });

      if (categoryGroups
        && (categoryGroups as SubcategoryContainer).subcategories
        && (categoryGroups as SubcategoryContainer).subcategories.length <= 5
      ) {
        (categoryGroups as SubcategoryContainer).subcategories.forEach(
          (sub) => (category as CategoryContainer).subcategories.push(sub),
        );
      } else if (categoryGroups) {
        (category as CategoryContainer).subcategories.push(categoryGroups);
      }

      (category as CategoryContainer).subcategories = sortOrderables(getCategoryChildren(category));

      menu.push(category);
    });

    return sortOrderables(menu);
  }

  toggleCategory(category: Category): boolean {
    this.privateState.activeSubcategoryId = null;
    this.privateState.activeSubsubcategoryId = null;

    if (this.activeCategory.value === category.id) {
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
