/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { INgModelController, ITimeoutService } from 'angular';
import {
  createAngularJsAdapter,
  transformAngularJsBoolAttr,
  transformAngularJsIntAttr,
} from 'CoreHome';
import Field from './Field.vue';

export default createAngularJsAdapter<[ITimeoutService]>({
  component: Field,
  require: '?ngModel',
  scope: {
    uicontrol: {
      angularJsBind: '@',
    },
    name: {
      angularJsBind: '@',
    },
    value: {
      vue: 'modelValue',
      angularJsBind: '@',
      transform(value, vm, scope) {
        // vue components expect object data as input, so we parse JSON data
        // for angularjs directives that use JSON.
        if (typeof value === 'string' && value
          && (scope.varType === 'array'
            || scope.uicontrol === 'multituple'
            || scope.uicontrol === 'field-array'
            || scope.uicontrol === 'multiselect')
        ) {
          return JSON.parse(value);
        }

        return value;
      },
    },
    default: {
      vue: 'defaultValue',
      angularJsBind: '@',
    },
    options: {
      angularJsBind: '=',
    },
    description: {
      angularJsBind: '@',
    },
    introduction: {
      angularJsBind: '@',
    },
    title: {
      angularJsBind: '@',
    },
    inlineHelp: {
      angularJsBind: '@',
    },
    disabled: {
      angularJsBind: '=',
      transform: transformAngularJsBoolAttr,
    },
    uiControlAttributes: {
      angularJsBind: '=',
    },
    uiControlOptions: {
      angularJsBind: '=',
    },
    autocomplete: {
      angularJsBind: '@',
      transform: transformAngularJsBoolAttr,
    },
    condition: {
      angularJsBind: '@',
      transform(value, vm, scope) {
        let transformed = value;
        if (value) {
          transformed = (values: unknown[]) => scope.$eval(value, values);
        }
        return transformed;
      },
    },
    varType: {
      angularJsBind: '@',
    },
    autofocus: {
      angularJsBind: '@',
      transform: transformAngularJsBoolAttr,
    },
    tabindex: {
      angularJsBind: '@',
      transform: transformAngularJsIntAttr,
    },
    fullWidth: {
      angularJsBind: '@',
      transform: transformAngularJsBoolAttr,
    },
    maxlength: {
      angularJsBind: '@',
      transform: transformAngularJsIntAttr,
    },
    required: {
      angularJsBind: '@',
      transform: transformAngularJsBoolAttr,
    },
    placeholder: {
      angularJsBind: '@',
    },
    rows: {
      angularJsBind: '@',
      transform: transformAngularJsIntAttr,
    },
    min: {
      angularJsBind: '@',
      transform: transformAngularJsIntAttr,
    },
    max: {
      angularJsBind: '@',
      transform: transformAngularJsIntAttr,
    },
  },
  directiveName: 'piwikField',
  $inject: ['$timeout'],
  events: {
    'update:modelValue': (newValue, vm, scope, element, attrs, ngModel, $timeout) => {
      if (newValue !== scope.value) {
        $timeout(() => {
          scope.value = JSON.parse(JSON.stringify(newValue));

          if (ngModel) {
            (ngModel as INgModelController).$setViewValue(scope.value);
          }
        });
      }
    },
  },
  postCreate(vm, scope, element, attrs, controller) {
    const ngModel = controller as INgModelController;

    scope.$watch('value', (newVal, oldVal) => {
      if (newVal !== oldVal) {
        vm.modelValue = JSON.parse(JSON.stringify(newVal));

        if (ngModel) {
          ngModel.$setViewValue(vm.modelValue);
        }
      }
    });

    if (ngModel) {
      ngModel.$setViewValue(vm.modelValue);

      ngModel.$render = () => {
        vm.modelValue = ngModel.$viewValue;
      };
    }
  },
});
