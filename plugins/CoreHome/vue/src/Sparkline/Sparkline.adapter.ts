/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import createAngularJsAdapter from '../createAngularJsAdapter';
import Sparkline from './Sparkline.vue';

export default createAngularJsAdapter({
  component: Sparkline,
  scope: {
    seriesIndices: {
      angularJsBind: '<',
    },
    params: {
      angularJsBind: '<',
    },
  },
  directiveName: 'piwikSparkline',
  restrict: 'E',
});
