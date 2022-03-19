/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { nextTick } from 'vue';
import { createAngularJsAdapter, removeAngularJsSpecificProperties } from 'CoreHome';
import { INgModelController, ITimeoutService } from 'angular';
import SmsProviderCredentials from './SmsProviderCredentials.vue';

export default createAngularJsAdapter<[ITimeoutService]>({
  component: SmsProviderCredentials,
  scope: {
    provider: {
      angularJsBind: '=',
    },
    credentials: {
      angularJsBind: '=value',
    },
  },
  directiveName: 'smsProviderCredentials',
  transclude: true,
  events: {
    'update:modelValue': (newValue, vm, scope, element, attrs, ngModel, $timeout) => {
      const currentValue = ngModel ? ngModel.$viewValue : scope.value;
      if (newValue !== currentValue) {
        $timeout(() => {
          if (!ngModel) {
            scope.value = newValue;
            return;
          }

          // ngModel being used
          (ngModel as INgModelController).$setViewValue(newValue);
          (ngModel as INgModelController).$render(); // not detected by the watch for some reason
        });
      }
    },
  },
  postCreate(vm, scope, element, attrs, controller) {
    const ngModel = controller as INgModelController;

    if (!ngModel) {
      scope.$watch('value', (newVal: unknown) => {
        if (newVal !== vm.modelValue) {
          nextTick(() => {
            vm.modelValue = newVal;
          });
        }
      });
      return;
    }

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
