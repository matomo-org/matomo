/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import ActivityIndicator from './ActivityIndicator.vue';
import { translate } from '../translate';
import createAngularJsAdapter from '../createAngularJsAdapter';

export default createAngularJsAdapter({
  component: ActivityIndicator,
  scope: {
    loading: {
      vue: 'loading',
      angularJsBind: '<',
    },
    loadingMessage: {
      vue: 'loadingMessage',
      angularJsBind: '<',
      default: () => translate('General_LoadingData'),
    },
  },
  $inject: [],
  directiveName: 'piwikActivityIndicator',
});
