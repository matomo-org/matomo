/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { ITimeoutService } from 'angular';
import createAngularJsAdapter from '../createAngularJsAdapter';
import DatePicker from './DatePicker.vue';

export default createAngularJsAdapter({
  component: DatePicker,
  scope: {
    selectedDateStart: {
      angularJsBind: '<',
    },
    selectedDateEnd: {
      angularJsBind: '<',
    },
    highlightedDateStart: {
      angularJsBind: '<',
    },
    highlightedDateEnd: {
      angularJsBind: '<',
    },
    viewDate: {
      angularJsBind: '<',
    },
    stepMonths: {
      angularJsBind: '<',
    },
    disableMonthDropdown: {
      angularJsBind: '<',
    },
    options: {
      angularJsBind: '<',
    },
    cellHover: {
      angularJsBind: '&',
    },
    cellHoverLeave: {
      angularJsBind: '&',
    },
    dateSelect: {
      angularJsBind: '&',
    },
  },
  directiveName: 'piwikDatePicker',
  events: {
    'cell-hover': (event, scope, element, attrs, $timeout: ITimeoutService) => {
      $timeout(); // trigger new digest
    },
    'cell-hover-leave': (event, scope, element, attrs, $timeout: ITimeoutService) => {
      $timeout(); // trigger new digest
    },
    'date-select': (event, scope, element, attrs, $timeout: ITimeoutService) => {
      $timeout(); // trigger new digest
    },
  },
  $inject: ['$timeout'],
});
