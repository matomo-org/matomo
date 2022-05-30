/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { computed, reactive } from 'vue';

interface ManageGoalsStoreState {
  idGoal?: number;
}

class ManageGoalsStore {
  private privateState = reactive<ManageGoalsStoreState>({});

  readonly idGoal = computed(() => this.privateState.idGoal);

  setIdGoalShown(idGoal?: number) {
    this.privateState.idGoal = idGoal;
  }
}

export default new ManageGoalsStore();
