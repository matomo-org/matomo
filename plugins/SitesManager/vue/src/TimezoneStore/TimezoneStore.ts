/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { reactive, readonly, computed } from 'vue';
import { AjaxHelper, lazyInitSingleton } from 'CoreHome';

interface Timezone {
  group: string;
  label: string;
  code: string;
}

interface TimezoneStoreState {
  timezones: Timezone[];
}

type GetTimezoneListResponse = Record<string, Record<string, string>>;

class TimezoneStore {
  private privateState = reactive<TimezoneStoreState>({
    timezones: [],
  });

  readonly timezones = computed(() => readonly(this.privateState).timezones);

  constructor() {
    this.fetchTimezones();
  }

  private fetchTimezones() {
    AjaxHelper.fetch<GetTimezoneListResponse>({
      method: 'SitesManager.getTimezonesList',
    }).then((grouped) => {
      const flattened: Timezone[] = [];
      Object.entries(grouped).forEach(([group, timezonesGroup]) => {
        Object.entries(timezonesGroup).forEach(([label, code]) => {
          flattened.push({
            group,
            label,
            code,
          });
        });
      });
      this.privateState.timezones = flattened;
    });
  }
}

export default lazyInitSingleton<TimezoneStore>();
