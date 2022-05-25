/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import {
  reactive,
  computed, readonly, DeepReadonly,
} from 'vue';
import AjaxHelper from '../AjaxHelper/AjaxHelper';
import { Widget } from '../Widget/types';

interface CategoryRef {
  id: string;
  name: string;
}

interface SubcategoryRef {
  id: string;
  name: string;
}

export interface Page {
  category: CategoryRef;
  subcategory: SubcategoryRef;
  widgets: Widget[];
}

interface ReportingPagesStoreState {
  pages: Page[];
}

export class ReportingPagesStore {
  private privateState = reactive<ReportingPagesStoreState>({
    pages: [],
  });

  private state = computed(() => readonly(this.privateState));

  private fetchAllPagesPromise?: Promise<DeepReadonly<Page[]>>;

  readonly pages = computed(() => this.state.value.pages);

  findPageInCategory(categoryId: string): DeepReadonly<Page>|undefined {
    // happens when user switches between sites, in this case check if the same category exists and
    // if so, select first entry from that category
    return this.pages.value.find((p) => p
      && p.category && p.category.id === categoryId && p.subcategory && p.subcategory.id);
  }

  findPage(categoryId: string, subcategoryId: string): DeepReadonly<Page>|undefined {
    return this.pages.value.find((p) => p
      && p.category && p.subcategory && p.category.id === categoryId
      && `${p.subcategory.id}` === subcategoryId);
  }

  reloadAllPages(): Promise<ReportingPagesStore['pages']['value']> {
    delete this.fetchAllPagesPromise;
    return this.getAllPages();
  }

  getAllPages(): Promise<ReportingPagesStore['pages']['value']> {
    if (!this.fetchAllPagesPromise) {
      this.fetchAllPagesPromise = AjaxHelper.fetch({
        method: 'API.getReportPagesMetadata',
        filter_limit: '-1',
      }).then((response) => {
        this.privateState.pages = response;
        return this.pages.value;
      });
    }

    return this.fetchAllPagesPromise.then(() => this.pages.value);
  }
}

export default new ReportingPagesStore();
