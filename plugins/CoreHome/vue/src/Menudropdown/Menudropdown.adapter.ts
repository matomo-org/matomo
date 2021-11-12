/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import createAngularJsAdapter from '../createAngularJsAdapter';
import Menudropdown from './Menudropdown.vue';

export default createAngularJsAdapter({
  component: Menudropdown,
  scope: {
    menuTitle: {
      angularJsBind: '@',
    },
    tooltip: {
      angularJsBind: '@',
    },
    showSearch: {
      angularJsBind: '=',
    },
    menuTitleChangeOnClick: {
      angularJsBind: '=',
    },
  },
  directiveName: 'piwikMenudropdown',
  transclude: true,
  events: {
    'after-select': ($event, scope) => {
      setTimeout(() => {
        scope.$apply();
      }, 0);
    },
  },
});
