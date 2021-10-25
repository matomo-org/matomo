/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import createAngularJsAdapter from '../createAngularJsAdapter';
import PeriodDatePicker from './PeriodDatePicker.vue';

export default createAngularJsAdapter({
  component: PeriodDatePicker,
  scope: {
    period: {
      angularJsBind: '<',
    },
    date: {
      angularJsBind: '<',
    },
    select: {
      angularJsBind: '&',
    },
  },
  directiveName: 'piwikPeriodDatePicker',
  restrict: 'E',
});
