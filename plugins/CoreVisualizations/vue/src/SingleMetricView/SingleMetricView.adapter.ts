/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { createAngularJsAdapter } from 'CoreHome';
import SingleMetricView from './SingleMetricView.vue';

export default createAngularJsAdapter({
  component: SingleMetricView,
  scope: {
    metric: {
      angularJsBind: '<',
    },
    idGoal: {
      angularJsBind: '<',
    },
    metricTranslations: {
      angularJsBind: '<',
    },
    metricDocumentations: {
      angularJsBind: '<',
    },
    goals: {
      angularJsBind: '<',
    },
    goalMetrics: {
      angularJsBind: '<',
    },
  },
  directiveName: 'piwikSingleMetricView',
  restrict: 'E',
  postCreate(vm, scope, element) {
    element.closest('.widgetContent').on('widget:destroy', () => {
      scope.$parent.$destroy();
    }).on('widget:reload', () => {
      scope.$parent.$destroy();
    });
  },
});
