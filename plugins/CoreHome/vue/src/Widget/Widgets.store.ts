/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import {
  reactive,
  readonly,
  computed,
  DeepReadonly,
} from 'vue';
import MatomoUrl from '../MatomoUrl/MatomoUrl';
import { Widget, WidgetContainer } from './types';
import Matomo from '../Matomo/Matomo';

interface WidgetsStoreState {
  isFetchedFirstTime: boolean;
  categorizedWidgets: Record<string, Widget[]>;
}

export function getWidgetChildren(widget: Widget): Widget[] {
  const container = widget as WidgetContainer;
  if (container.widgets) {
    return container.widgets;
  }
  return [];
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
        window.widgetsHelper.getAvailableWidgets((widgets: Record<string, unknown[]>) => {
          const casted = widgets as unknown as Record<string, Widget[]>;
          this.privateState.categorizedWidgets = casted;
          resolve(this.widgets.value);
        });
      } catch (e) {
        reject(e);
      }
    });
  }

  reloadAvailableWidgets(): Promise<WidgetsStore['widgets']['value']> {
    // Let's also update widgetslist so will be easier to update list of available widgets in
    // dashboard selector immediately
    window.widgetsHelper.clearAvailableWidgets();

    const fetchPromise = this.fetchAvailableWidgets();
    fetchPromise.then(() => {
      Matomo.postEvent('WidgetsStore.reloaded');
    });

    return fetchPromise;
  }
}

export default new WidgetsStore();
