/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import createAngularJsAdapter from '../createAngularJsAdapter';
import ReportingPage from './ReportingPage.vue';

export default createAngularJsAdapter({
  component: ReportingPage,
  directiveName: 'piwikReportingPage',
});
