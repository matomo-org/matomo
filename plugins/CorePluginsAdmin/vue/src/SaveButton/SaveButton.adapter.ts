/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { createAngularJsAdapter } from 'CoreHome';
import SaveButton from './SaveButton.vue';

export default createAngularJsAdapter({
  component: SaveButton,
  scope: {
    saving: {
      angularJsBind: '=?',
    },
    value: {
      angularJsBind: '@?',
    },
    disabled: {
      angularJsBind: '=?',
    },
    onconfirm: {
      angularJsBind: '&?',
    },
  },
  directiveName: 'piwikSaveButton',
});
