/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { createAngularJsAdapter } from 'CoreHome';
import ReportExport from './ReportExport.vue';

export default createAngularJsAdapter({
  component: ReportExport,
  scope: {
    reportTitle: {
      angularJsBind: '@',
    },
    requestParams: {
      angularJsBind: '@',
    },
    reportFormats: {
      angularJsBind: '@',
    },
    apiMethod: {
      angularJsBind: '@',
    },
    maxFilterLimit: {
      angularJsBind: '@',
    },
  },
  directiveName: 'piwikReportExport',
});
