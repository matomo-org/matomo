/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import createAngularJsAdapter from '../createAngularJsAdapter';
import PeriodSelector from './PeriodSelector.vue';

export default createAngularJsAdapter({
  component: PeriodSelector,
  scope: {
    periods: {
      angularJsBind: '<',
    },
  },
  directiveName: 'piwikPeriodSelector',
});
