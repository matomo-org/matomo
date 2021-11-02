/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import Alert from './Alert.vue';
import createAngularJsAdapter from '../createAngularJsAdapter';

export default createAngularJsAdapter({
  component: Alert,
  scope: {
    severity: {
      vue: 'severity',
      angularJsBind: '@piwikAlert',
    },
  },
  directiveName: 'piwikAlert',
  transclude: true,
});
