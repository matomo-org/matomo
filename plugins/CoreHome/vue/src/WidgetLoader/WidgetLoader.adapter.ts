/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import createAngularJsAdapter from '../createAngularJsAdapter';
import WidgetLoader from './WidgetLoader.vue';

export default createAngularJsAdapter({
  component: WidgetLoader,
  scope: {
    piwikWidgetLoader: {
      vue: 'widgetParams',
      angularJsBind: '=',
    },
    widgetName: {
      angularJsBind: '@',
    },
  },
  directiveName: 'piwikWidgetLoader',
});
