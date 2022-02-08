/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { reactive, readonly, computed } from 'vue';
import { AjaxHelper, lazyInitSingleton } from 'CoreHome';

interface CurrencyStoreState {
  isLoading: boolean;
  currencies: Record<string, string>;
}

class CurrencyStore {
  private privateState = reactive<CurrencyStoreState>({
    isLoading: false,
    currencies: {},
  });

  readonly currencies = computed(() => readonly(this.privateState).currencies);

  readonly isLoading = computed(() => readonly(this.privateState).isLoading);

  constructor() {
    this.fetchCurrencies();
  }

  private fetchCurrencies() {
    this.privateState.isLoading = true;
    AjaxHelper.fetch<CurrencyStoreState['currencies']>({
      method: 'SitesManager.getCurrencyList',
    }).then((currencies) => {
      this.privateState.currencies = currencies;
    }).finally(() => {
      this.privateState.isLoading = false;
    });
  }
}

export default lazyInitSingleton(CurrencyStore) as CurrencyStore;
