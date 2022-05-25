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
  DeepReadonly,
} from 'vue';
import { AjaxHelper } from 'CoreHome';
import Capability from './Capability';

interface CapabilitiesStoreState {
  isLoading: boolean;
  capabilities: Capability[];
}

class CapabilitiesStore {
  private privateState = reactive<CapabilitiesStoreState>({
    isLoading: false,
    capabilities: [],
  });

  private readonly state = computed(() => readonly(this.privateState));

  readonly capabilities = computed(() => this.state.value.capabilities);

  readonly isLoading = computed(() => this.state.value.isLoading);

  private fetchPromise?: Promise<DeepReadonly<Capability[]>>;

  init() {
    return this.fetchCapabilities();
  }

  public fetchCapabilities(): Promise<DeepReadonly<Capability[]>> {
    if (!this.fetchPromise) {
      this.privateState.isLoading = true;
      this.fetchPromise = AjaxHelper.fetch<Capability[]>({
        method: 'UsersManager.getAvailableCapabilities',
      }).then((capabilities) => {
        this.privateState.capabilities = capabilities;
        return this.capabilities.value;
      }).finally(() => {
        this.privateState.isLoading = false;
      });
    }

    return this.fetchPromise!;
  }
}

export default new CapabilitiesStore();
