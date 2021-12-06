/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { ITimeoutService } from 'angular';
import { createAngularJsAdapter } from 'CoreHome';
import SaveButton from './SaveButton.vue';

export default createAngularJsAdapter<[ITimeoutService]>({
  component: SaveButton,
  scope: {
    saving: {
      angularJsBind: '=?',
    },
    value: {
      angularJsBind: '@?',
    },
    disabled: {
      angularJsBind: '=?',
    },
    onconfirm: {
      angularJsBind: '&?',
      vue: 'confirm',
    },
  },
  events: {
    confirm($event, vm, scope, element, attrs, controller, $timeout) {
      element[0].click(); // this directive can be used on input's with ng-click specified
      $timeout();
    },
  },
  replace: true,
  directiveName: 'piwikSaveButton',
  $inject: ['$timeout'],
});
