/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { reactive, readonly, computed } from 'vue';
import { AjaxHelper, lazyInitSingleton } from 'CoreHome';

interface CurrencyStoreState {
  currencies: Record<string, string>;
}

class CurrencyStore {
  private privateState = reactive<CurrencyStoreState>({
    currencies: {},
  });

  readonly currencies = computed(() => readonly(this.privateState).currencies);

  constructor() {
    this.fetchCurrencies();
  }

  private fetchCurrencies() {
    AjaxHelper.fetch<CurrencyStoreState['currencies']>({
      method: 'SitesManager.getCurrencyList',
    }).then((currencies) => {
      this.privateState.currencies = currencies;
    });
  }
}

export default lazyInitSingleton(CurrencyStore);
