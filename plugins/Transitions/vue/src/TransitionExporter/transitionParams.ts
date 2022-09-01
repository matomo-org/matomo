/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { ref } from 'vue';
import { Matomo } from 'CoreHome';

interface DataChangedParams {
  actionType: string;
  actionName: string;
}

const actionType = ref('');
const actionName = ref('');

const onDataChanged = (params: DataChangedParams) => {
  actionType.value = params.actionType;
  actionName.value = params.actionName;
};

Matomo.on('Transitions.dataChanged', onDataChanged);

export {
  actionType,
  actionName,
};
