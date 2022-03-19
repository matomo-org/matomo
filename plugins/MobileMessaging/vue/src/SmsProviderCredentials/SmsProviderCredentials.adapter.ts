/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { createAngularJsAdapter } from 'CoreHome';
import smsProviderCredentials from './smsProviderCredentials.vue';

export default createAngularJsAdapter({
  component: smsProviderCredentials,
  scope: {
    provider: {
      angularJsBind: '=',
    },
    credentials: {
      angularJsBind: '=value',
    },
  },
  directiveName: 'smsProviderCredentials',
  transclude: true,
});
