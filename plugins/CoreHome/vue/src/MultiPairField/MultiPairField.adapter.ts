/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { createAngularJsAdapter } from 'CoreHome';
import MultiPairField from './MultiPairField.vue';

export default createAngularJsAdapter({
  component: MultiPairField,
  scope: {
    name: {
      angularJsBind: '=',
    },
    field1: {
      angularJsBind: '=',
    },
    field2: {
      angularJsBind: '=',
    },
    field3: {
      angularJsBind: '=',
    },
    field4: {
      angularJsBind: '=',
    },
  },
  directiveName: 'matomoMultiPairField',
});
