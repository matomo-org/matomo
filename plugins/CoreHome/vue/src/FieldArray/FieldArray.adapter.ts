/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { INgModelController } from 'angular';
import createAngularJsAdapter from '../createAngularJsAdapter';
import FieldArray from './FieldArray.vue';

export default createAngularJsAdapter({
  component: FieldArray,
  require: '?ngModel',
  scope: {
    name: {
      angularJsBind: '=',
    },
    field: {
      angularJsBind: '=',
    },
  },
  directiveName: 'matomoFieldArray',
  events: {
    'update:modelValue': (newValue, vm, scope, element, attrs, ngModel) => {
      if (newValue !== vm.modelValue) {
        element.trigger('change', newValue);

        if (ngModel) {
          ngModel.$setViewValue(newValue);
        }
      }
    },
  },
  postCreate(vm, scope, element, attrs, controller) {
    const ngModel = controller as INgModelController;

    // setup ng-model mapping
    if (ngModel) {
      ngModel.$setViewValue(vm.modelValue);

      ngModel.$render = () => {
        if (window.angular.isString(ngModel.$viewValue)) {
          vm.modelValue = JSON.parse(ngModel.$viewValue);
        } else {
          vm.modelValue = ngModel.$viewValue;
        }
      };
    }
  },
});
