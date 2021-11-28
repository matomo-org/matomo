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
import { Subcategory } from '../ReportingMenu/ReportingMenu.store';

export interface WidgetData {
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
}

export interface ContainerWidgetData extends WidgetData {
  isFirstInPage?: boolean;
  widgets: WidgetData[];
}

interface WidgetsStoreState {
  categorizedWidgets: Record<string, WidgetData[]>;
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

  private fetchAvailableWidgets() {
    window.widgetsHelper.getAvailableWidgets((categorizedWidgets) => {
      this.privateState.categorizedWidgets = categorizedWidgets;
    });
  }

  reloadAvailableWidgets() {
    if (typeof window.widgetsHelper === 'object' && window.widgetsHelper.availableWidgets) {
      // lets also update widgetslist so will be easier to update list of available widgets in
      // dashboard selector immediately
      delete window.widgetsHelper.availableWidgets;
      this.fetchAvailableWidgets();
    }
  }
}

export default new WidgetsStore();
