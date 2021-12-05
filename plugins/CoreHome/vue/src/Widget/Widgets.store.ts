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
} from 'vue';
import Subcategory from '../ReportingMenu/Subcategory';
import MatomoUrl from '../MatomoUrl/MatomoUrl';

export interface WidgetLeaf {
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
}

export interface ContainerWidget extends WidgetLeaf {
  isFirstInPage?: boolean;
  widgets: (WidgetLeaf | ContainerWidget)[];
}

export type Widget = WidgetLeaf | ContainerWidget;

interface WidgetsStoreState {
  categorizedWidgets: Record<string, Widget[]>;
}

class WidgetsStore {
  private privateState = reactive<WidgetsStoreState>({
    categorizedWidgets: {},
  });

  private state = computed(() => readonly(this.privateState));

  readonly widgets = computed(() => this.state.value.categorizedWidgets);

  constructor() {
    this.fetchAvailableWidgets();
  }

  private fetchAvailableWidgets(): Promise<typeof WidgetsStore['widgets']['value']> {
    // if there's no idSite, don't make the request since it will just fail
    if (!MatomoUrl.parsed.value.idSite) {
      return Promise.resolve(this.widgets.value);
    }

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

  reloadAvailableWidgets(): Promise<typeof WidgetsStore['widgets']['value']> {
    if (typeof window.widgetsHelper === 'object' && window.widgetsHelper.availableWidgets) {
      // lets also update widgetslist so will be easier to update list of available widgets in
      // dashboard selector immediately
      delete window.widgetsHelper.availableWidgets;
    }

    return this.fetchAvailableWidgets();
  }
}

export default new WidgetsStore();
