/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { ITimeoutService } from 'angular';
import createAngularJsAdapter from '../createAngularJsAdapter';
import QuickAccess from './QuickAccess.vue';

export default createAngularJsAdapter<[ITimeoutService]>({
  component: QuickAccess,
  directiveName: 'piwikQuickAccess',
  events: {
    itemSelected(event, vm, scope, elem, attrs, controller, $timeout: ITimeoutService) {
      $timeout();
    },
    blur(event, vm, scope) {
      setTimeout(() => scope.$apply());
    },
  },
});
