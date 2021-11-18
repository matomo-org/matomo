/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { createAngularJsAdapter } from 'CoreHome';
import FormField from './FormField.vue';

export default createAngularJsAdapter({
  component: FormField,
  scope: {
    modelValue: {
      default(scope) {
        const field = scope.piwikFormField;

        // vue components expect object data as input, so we parse JSON data
        // for angularjs directives that use JSON.
        if ((field.type === 'array' && typeof field.value === 'string' && field.value)
          || field.uiControl === 'multituple'
          || field.uiControl === 'field-array'
        ) {
          field.value = JSON.parse(field.value);
        }

        return field.value;
      },
    },
    piwikFormField: {
      vue: 'formField',
      angularJsBind: '=',
      transform(value, vm, scope) {
        let transformed = value;
        if (value.condition) {
          transformed = {
            ...value,
            condition: (values: unknown[]) => scope.$eval(value.condition, values),
          };
        }
        return transformed;
      },
    },
    allSettings: {
      angularJsBind: '=',
    },
  },
  directiveName: 'piwikFormField',
  events: {
    'update:modelValue': (newValue, vm, scope) => {
      if (newValue !== scope.piwikFormField.value) {
        scope.piwikFormField.value = newValue;
        scope.$apply();
      }
    },
  },
  postCreate(vm, scope) {
    scope.$watch('piwikFormField.value', (newVal, oldVal) => {
      if (newVal !== oldVal) {
        vm.modelValue = newVal;
      }
    });
  },
});
