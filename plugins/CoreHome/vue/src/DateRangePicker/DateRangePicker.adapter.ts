/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import createAngularJsAdapter from '../createAngularJsAdapter';
import DateRangePicker from './DateRangePicker.vue';

export default createAngularJsAdapter({
  component: DateRangePicker,
  scope: {
    startDate: {
      angularJsBind: '<',
    },
    endDate: {
      angularJsBind: '<',
    },
    rangeChange: {
      angularJsBind: '&',
    },
    submit: {
      angularJsBind: '&',
    },
  },
  directiveName: 'piwikDateRangePicker',
  restrict: 'E',
});
