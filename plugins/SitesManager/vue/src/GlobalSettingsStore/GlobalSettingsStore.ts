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
import { AjaxHelper, lazyInitSingleton } from 'CoreHome';

interface GlobalSettings {
  keepURLFragmentsGlobal: boolean;
  defaultCurrency: string;
  defaultTimezone: string;
  excludedIpsGlobal: string;
  excludedQueryParametersGlobal: string;
  excludedUserAgentsGlobal: string;
  searchKeywordParametersGlobal: string;
  searchCategoryParametersGlobal: string;
}

interface GlobalSettingsStoreStae {
  globalSettings: GlobalSettings|null;
}

class GlobalSettingsStore {
  private privateState = reactive<GlobalSettingsStoreStae>({
    globalSettings: null,
  });

  readonly globalSettings = computed(() => readonly(this.privateState).globalSettings);

  constructor() {
    this.fetchGlobalSettings();
  }

  private fetchGlobalSettings() {
    AjaxHelper.fetch<GlobalSettings>({
      module: 'SitesManager',
      action: 'getGlobalSettings',
    }).then((response) => {
      this.privateState.globalSettings = {
        ...response,

        // the API can return false for these
        excludedIpsGlobal: response.excludedIpsGlobal || '';
        excludedQueryParametersGlobal: response.excludedQueryParametersGlobal || '';
        excludedUserAgentsGlobal: response.excludedUserAgentsGlobal || '';
        searchKeywordParametersGlobal: response.searchKeywordParametersGlobal || '';
        searchCategoryParametersGlobal: response.searchCategoryParametersGlobal || '';
      };
    });
  }
}

export default lazyInitSingleton(GlobalSettingsStore);
