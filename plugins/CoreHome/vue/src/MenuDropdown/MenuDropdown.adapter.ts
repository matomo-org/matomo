/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import createAngularJsAdapter from '../createAngularJsAdapter';
import MenuDropdown from './MenuDropdown.vue';

export default createAngularJsAdapter({
  component: MenuDropdown,
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
    'after-select': ($event, vm, scope) => {
      setTimeout(() => {
        scope.$apply();
      }, 0);
    },
  },
});
