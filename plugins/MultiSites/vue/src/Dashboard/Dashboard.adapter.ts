/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import {
  createAngularJsAdapter,
  transformAngularJsBoolAttr,
  transformAngularJsIntAttr,
} from 'CoreHome';
import Dashboard from './Dashboard.vue';

export default createAngularJsAdapter({
  component: Dashboard,
  scope: {
    displayRevenueColumn: {
      angularJsBind: '@',
      transform: transformAngularJsBoolAttr,
    },
    showSparklines: {
      angularJsBind: '@',
      transform: transformAngularJsBoolAttr,
    },
    dateSparkline: {
      angularJsBind: '@',
    },
    pageSize: {
      angularJsBind: '@',
      transform: transformAngularJsIntAttr,
    },
    autoRefreshTodayReport: {
      angularJsBind: '@',
      transform: transformAngularJsIntAttr,
    },
  },
  directiveName: 'piwikMultisitesDashboard',
});
