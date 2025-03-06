/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { computed, reactive, readonly } from 'vue';
import MatomoUrl from '../MatomoUrl/MatomoUrl';
import Matomo from '../Matomo/Matomo';

interface SearchFiltersPersistenceState {
  module: string;
  action: string;
  category: string;
  subcategory: string;
  idSite: string;
  widgetSearchFilters: Record<string, Record<string, string>>;
}

export class SearchFiltersPersistenceStore {
  constructor() {
    Matomo.on('matomoPageChange', () => {
      if (!this.isCurrentPage()) {
        this.resetSearchFilters();
      }

      this.updateCurrentRoutingFromUrl();
    });
  }

  private privateState = reactive<SearchFiltersPersistenceState>({
    module: '',
    action: '',
    category: '',
    subcategory: '',
    idSite: '',
    widgetSearchFilters: {},
  });

  private state = computed(() => readonly(this.privateState));

  resetSearchFilters(): void {
    this.privateState.widgetSearchFilters = {};
  }

  getSearchFilters(widgetId: string): Record<string, string> {
    return this.state.value.widgetSearchFilters[widgetId] || {};
  }

  setSearchFilters(widgetId: string, filters: Record<string, string>): void {
    if (widgetId) {
      this.privateState.widgetSearchFilters[widgetId] = filters;
    }
  }

  updateCurrentRoutingFromUrl(): void {
    const url = MatomoUrl.parsed.value;

    this.privateState.module = url.module as string;
    this.privateState.action = url.action as string;
    this.privateState.category = url.category as string;
    this.privateState.subcategory = url.subcategory as string;
    this.privateState.idSite = url.idSite as string;
  }

  isCurrentPage(): boolean {
    const url = MatomoUrl.parsed.value;

    return (
      this.state.value.module === url.module
      && this.state.value.action === url.action
      && this.state.value.category === url.category
      && this.state.value.subcategory === url.subcategory
      && this.state.value.idSite === url.idSite) as boolean;
  }
}

export default new SearchFiltersPersistenceStore();
