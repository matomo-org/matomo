/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { reactive, readonly, computed } from 'vue';
import { AjaxHelper } from 'CoreHome';

interface Timezone {
  group: string;
  label: string;
  code: string;
}

interface TimezoneStoreState {
  isLoading: boolean;
  timezones: Timezone[];
  timezoneSupportEnabled: boolean;
}

type GetTimezoneListResponse = Record<string, Record<string, string>>;

interface IsTimezoneSupportedResponse {
  value: boolean;
}

class TimezoneStore {
  private privateState = reactive<TimezoneStoreState>({
    isLoading: false,
    timezones: [],
    timezoneSupportEnabled: false,
  });

  private readonly state = computed(() => readonly(this.privateState));

  readonly timezones = computed(() => this.state.value.timezones);

  readonly timezoneSupportEnabled = computed(() => this.state.value.timezoneSupportEnabled);

  readonly isLoading = computed(() => this.state.value.isLoading);

  private initializePromise: Promise<void>|null = null;

  init() {
    if (!this.initializePromise) {
      this.privateState.isLoading = true;
      this.initializePromise = Promise.all([
        this.checkTimezoneSupportEnabled(),
        this.fetchTimezones(),
      ]).finally(() => {
        this.privateState.isLoading = false;
      }) as unknown as Promise<void>;
    }

    return this.initializePromise;
  }

  private fetchTimezones() {
    return AjaxHelper.fetch<GetTimezoneListResponse>({
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
    return AjaxHelper.fetch<IsTimezoneSupportedResponse>({
      method: 'SitesManager.isTimezoneSupportEnabled',
    }).then((response) => {
      this.privateState.timezoneSupportEnabled = response.value;
    });
  }
}

export default new TimezoneStore();
