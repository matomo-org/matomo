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
  timezoneSupportEnabled: boolean;
}

type GetTimezoneListResponse = Record<string, Record<string, string>>;

interface IsTimezoneSupportedResponse {
  value: boolean;
}

class TimezoneStore {
  private privateState = reactive<TimezoneStoreState>({
    timezones: [],
    timezoneSupportEnabled: false,
  });

  private readonly state = computed(() => readonly(this.privateState));

  readonly timezones = computed(() => this.state.value.timezones);

  readonly timezoneSupportEnabled = computed(() => this.state.value.timezoneSupportEnabled);

  constructor() {
    this.checkTimezoneSupportEnabled();
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

  private checkTimezoneSupportEnabled() {
    AjaxHelper.fetch<IsTimezoneSupportedResponse>({
      method: 'SitesManager.isTimezoneSupportEnabled',
    }).then((response) => {
      this.privateState.timezoneSupportEnabled = response.value;
    });
  }
}

export default lazyInitSingleton(TimezoneStore);
