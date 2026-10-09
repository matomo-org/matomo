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
  // Keyed by widget unique id. A Map is used deliberately: widget ids originate from the DOM and a
  // plain object would let an id such as `__proto__` reach the prototype through the inherited setter,
  // so a later lookup for an unrelated widget could return another widget's stored filters. Map keys
  // are plain data and never touch the prototype chain, which keeps each widget's filters isolated.
  widgetSearchFilters: Map<string, Record<string, string>>;
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
    widgetSearchFilters: new Map(),
  });

  private state = computed(() => readonly(this.privateState));

  resetSearchFilters(): void {
    this.privateState.widgetSearchFilters = new Map();
  }

  getSearchFilters(widgetId: string): Record<string, string> {
    return this.state.value.widgetSearchFilters.get(widgetId) || {};
  }

  setSearchFilters(widgetId: string, filters: Record<string, string>): void {
    if (widgetId) {
      this.privateState.widgetSearchFilters.set(widgetId, filters);
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
