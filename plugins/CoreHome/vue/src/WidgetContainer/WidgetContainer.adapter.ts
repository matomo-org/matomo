/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import createAngularJsAdapter from '../createAngularJsAdapter';
import WidgetContainer from './WidgetContainer.vue';

export default createAngularJsAdapter({
  component: WidgetContainer,
  scope: {
    container: {
      angularJsBind: '=piwikWidgetContainer',
    },
  },
  directiveName: 'piwikWidgetContainer',
});
