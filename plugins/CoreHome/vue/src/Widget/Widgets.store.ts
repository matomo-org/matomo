/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import {
  reactive,
  readonly,
  computed,
  DeepReadonly,
} from 'vue';
import Subcategory from '../ReportingMenu/Subcategory';
import MatomoUrl from '../MatomoUrl/MatomoUrl';
import { Orderable } from "../Orderable";

export interface Widget extends Orderable {
  uniqueId: string;
  module: string;
  action: string;
  viewDataTable: string;
  parameters: Record<string, unknown>;
  subcategory: Subcategory;
  isContainer?: boolean;
  isReport?: boolean;
  middlewareParameters?: QueryParameters;
  documentation?: string;
  layout?: string;
  isWide?: boolean;
  isFirstInPage?: boolean;
  widgets: Widget[];
}

export interface GroupedWidgets {
  group: boolean;
  left?: (Widget | GroupedWidgets)[];
  right?: (Widget | GroupedWidgets)[];
}

interface WidgetsStoreState {
  isFetchedFirstTime: boolean;
  categorizedWidgets: Record<string, Widget[]>;
}

class WidgetsStore {
  private privateState = reactive<WidgetsStoreState>({
    isFetchedFirstTime: false,
    categorizedWidgets: {},
  });

  private state = computed((): DeepReadonly<WidgetsStoreState> => {
    if (!this.privateState.isFetchedFirstTime) {
      // initiating a side effect in a computed property seems wrong, but it needs to be
      // executed after knowing a user's logged in and it will succeed.
      this.fetchAvailableWidgets();
    }

    return readonly(this.privateState);
  });

  readonly widgets = computed(() => this.state.value.categorizedWidgets);

  private fetchAvailableWidgets(): Promise<WidgetsStore['widgets']['value']> {
    // if there's no idSite, don't make the request since it will just fail
    if (!MatomoUrl.parsed.value.idSite) {
      return Promise.resolve(this.widgets.value);
    }

    this.privateState.isFetchedFirstTime = true;
    return new Promise((resolve, reject) => {
      try {
        window.widgetsHelper.getAvailableWidgets((categorizedWidgets) => {
          this.privateState.categorizedWidgets = categorizedWidgets;
          resolve(this.widgets.value);
        });
      } catch (e) {
        reject(e);
      }
    });
  }

  reloadAvailableWidgets(): Promise<WidgetsStore['widgets']['value']> {
    if (typeof window.widgetsHelper === 'object' && window.widgetsHelper.availableWidgets) {
      // lets also update widgetslist so will be easier to update list of available widgets in
      // dashboard selector immediately
      delete window.widgetsHelper.availableWidgets;
    }

    return this.fetchAvailableWidgets();
  }
}

export default new WidgetsStore();
