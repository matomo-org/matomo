/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { ITimeoutService } from 'angular';
import { createAngularJsAdapter } from 'CoreHome';
import CapabilitiesEdit from './CapabilitiesEdit.vue';
import Capability from '../CapabilitiesStore/Capability';

export default createAngularJsAdapter<[ITimeoutService]>({
  component: CapabilitiesEdit,
  scope: {
    idsite: {
      angularJsBind: '<',
    },
    siteName: {
      angularJsBind: '<',
    },
    userLogin: {
      angularJsBind: '<',
    },
    userRole: {
      angularJsBind: '<',
    },
    capabilities: {
      angularJsBind: '<',
    },
    onCapabilitiesChange: {
      angularJsBind: '&',
      vue: 'change',
    },
  },
  directiveName: 'piwikCapabilitiesEdit',
  restrict: 'E',
  $inject: ['$timeout'],
  events: {
    change(caps: Capability[], vm, scope, element, attrs, controller, $timeout) {
      $timeout(() => {
        if (scope.onCapabilitiesChange) {
          scope.onCapabilitiesChange.call({
            capabilities: caps,
          });
        }
      });
    },
  },
});
