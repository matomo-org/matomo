/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import createAngularJsAdapter from '../createAngularJsAdapter';
import Widget from './Widget.vue';

export default createAngularJsAdapter({
  component: Widget,
  scope: {
    widget: {
      angularJsBind: '=?piwikWidget',
    },
    widgetized: {
      angularJsBind: '=?',
    },
    containerid: {
      angularJsBind: '@',
    },
  },
  directiveName: 'piwikWidget',
});
