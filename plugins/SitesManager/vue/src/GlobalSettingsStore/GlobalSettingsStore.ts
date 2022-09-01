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
import { AjaxHelper } from 'CoreHome';
import GlobalSettings from './GlobalSettings';

interface GlobalSettingsStoreState {
  isLoading: boolean;
  globalSettings: GlobalSettings;
}

interface SaveGlobalSettingsParams {
  keepURLFragments: boolean;
  currency: string;
  timezone: string;
  excludedIps: string;
  excludedQueryParameters: string;
  excludedUserAgents: string;
  excludedReferrers: string;
  searchKeywordParameters: string;
  searchCategoryParameters: string;
}

class GlobalSettingsStore {
  private privateState = reactive<GlobalSettingsStoreState>({
    isLoading: false,
    globalSettings: {
      keepURLFragmentsGlobal: false,
      defaultCurrency: '',
      defaultTimezone: '',
      excludedIpsGlobal: '',
      excludedQueryParametersGlobal: '',
      excludedUserAgentsGlobal: '',
      excludedReferrersGlobal: '',
      searchKeywordParametersGlobal: '',
      searchCategoryParametersGlobal: '',
    },
  });

  readonly isLoading = computed(() => readonly(this.privateState).isLoading);

  readonly globalSettings = computed(() => readonly(this.privateState).globalSettings);

  init() {
    return this.fetchGlobalSettings();
  }

  saveGlobalSettings(settings: SaveGlobalSettingsParams) {
    this.privateState.isLoading = true;
    return AjaxHelper.post(
      {
        module: 'SitesManager',
        format: 'json',
        action: 'setGlobalSettings',
      },
      settings,
      {
        withTokenInUrl: true,
      },
    ).finally(() => {
      this.privateState.isLoading = false;
    });
  }

  private fetchGlobalSettings() {
    this.privateState.isLoading = true;
    AjaxHelper.fetch<GlobalSettings>({
      module: 'SitesManager',
      action: 'getGlobalSettings',
    }).then((response) => {
      this.privateState.globalSettings = {
        ...response,

        // the API can return false for these
        excludedIpsGlobal: response.excludedIpsGlobal || '',
        excludedQueryParametersGlobal: response.excludedQueryParametersGlobal || '',
        excludedUserAgentsGlobal: response.excludedUserAgentsGlobal || '',
        excludedReferrersGlobal: response.excludedReferrersGlobal || '',
        searchKeywordParametersGlobal: response.searchKeywordParametersGlobal || '',
        searchCategoryParametersGlobal: response.searchCategoryParametersGlobal || '',
      };
    }).finally(() => {
      this.privateState.isLoading = false;
    });
  }
}

export default new GlobalSettingsStore();
