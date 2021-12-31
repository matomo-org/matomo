/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import createAngularJsAdapter from '../createAngularJsAdapter';
import Progressbar from './Progressbar.vue';

export default createAngularJsAdapter({
  component: Progressbar,
  scope: {
    progress: {
      angularJsBind: '=',
    },
    label: {
      angularJsBind: '=',
    },
  },
  directiveName: 'piwikProgressbar',
});
