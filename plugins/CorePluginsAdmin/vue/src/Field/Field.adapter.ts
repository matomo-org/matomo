/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { INgModelController, ITimeoutService } from 'angular';
import { nextTick } from 'vue';
import {
  createAngularJsAdapter,
  transformAngularJsBoolAttr,
  transformAngularJsIntAttr,
  processScopeProperty,
} from 'CoreHome';
import Field from './Field.vue';

function handleJsonValue(value: unknown, varType: string, uiControl: string) {
  if (typeof value === 'string'
    && value
    && (varType === 'array'
      || uiControl === 'multituple'
      || uiControl === 'field-array'
      || uiControl === 'multiselect'
      || uiControl === 'site')
  ) {
    return JSON.parse(value);
  }

  return value;
}

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
        return handleJsonValue(value, scope.varType, scope.uicontrol);
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
    'update:modelValue': (newValue, vm, scope, element, attrs, ngModel) => {
      if (newValue !== scope.value) {
        scope.value = newValue;

        if (ngModel) {
          (ngModel as INgModelController).$setViewValue(scope.value);
        }
      }
    },
  },
  postCreate(vm, scope, element, attrs, controller) {
    const ngModel = controller as INgModelController;

    scope.$watch('value', (newVal, oldVal) => {
      if (newVal !== vm.modelValue) {
        const transformed = handleJsonValue(scope.value, scope.varType, scope.uicontrol);
        vm.modelValue = transformed;

        if (newVal === oldVal && ngModel) { // first update
          (ngModel as INgModelController).$setViewValue(transformed);
        }
      }
    });

    if (typeof scope.value !== 'undefined') {
      const transformed = handleJsonValue(scope.value, scope.varType, scope.uicontrol);
      vm.modelValue = JSON.parse(JSON.stringify(transformed));
    }

    if (ngModel) {
      ngModel.$render = () => {
        nextTick(() => {
          vm.modelValue = processScopeProperty(ngModel.$viewValue);
        });
      };

      ngModel.$setViewValue(vm.modelValue);
    }
  },
});
