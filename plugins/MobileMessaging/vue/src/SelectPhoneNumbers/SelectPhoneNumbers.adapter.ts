/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { INgModelController, ITimeoutService } from 'angular';
import { nextTick } from 'vue';
import { createAngularJsAdapter, removeAngularJsSpecificProperties } from 'CoreHome';
import SelectPhoneNumbers from './SelectPhoneNumbers.vue';

export default createAngularJsAdapter<[ITimeoutService]>({
  component: SelectPhoneNumbers,
  require: '?ngModel',
  scope: {
    phoneNumbers: {
      angularJsBind: '<',
    },
    withIntroduction: {
      angularJsBind: '<',
    },
    value: {
      angularJsBind: '<',
      vue: 'modelValue',
    },
  },
  $inject: ['$timeout'],
  directiveName: 'matomoSelectPhoneNumbers',
  events: {
    'update:modelValue': (newValue, vm, scope, element, attrs, ngModel, $timeout) => {
      if (!ngModel) {
        return;
      }

      if (newValue !== (ngModel as INgModelController).$viewValue) {
        $timeout(() => {
          (ngModel as INgModelController).$setViewValue(newValue);
          (ngModel as INgModelController).$render(); // not detected by the watch for some reason
        });
      }
    },
  },
  postCreate(vm, scope, element, attrs, controller) {
    const ngModel = controller as INgModelController;

    // ngModel being used
    ngModel.$render = () => {
      nextTick(() => {
        vm.modelValue = removeAngularJsSpecificProperties(ngModel.$viewValue);
      });
    };

    if (typeof scope.value !== 'undefined') {
      (ngModel as INgModelController).$setViewValue(scope.value);
    } else {
      ngModel.$setViewValue(vm.modelValue);
    }
  },
});
