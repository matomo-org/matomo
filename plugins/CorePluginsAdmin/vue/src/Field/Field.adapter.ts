/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { INgModelController, IScope, ITimeoutService } from 'angular';
import { nextTick, shallowRef } from 'vue';
import {
  createAngularJsAdapter,
  transformAngularJsBoolAttr,
  transformAngularJsIntAttr,
  removeAngularJsSpecificProperties,
  Matomo,
  useExternalPluginComponent,
} from 'CoreHome';
import Field from './Field.vue';
import FieldAngularJsTemplate from '../FormField/FieldAngularJsTemplate.vue';

function handleJsonValue(value: unknown, varType: string, uiControl: string): unknown {
  if (typeof value === 'string'
    && value
    && (varType === 'array'
      || uiControl === 'multituple'
      || uiControl === 'field-array'
      || uiControl === 'multiselect'
      || uiControl === 'site')
  ) {
    const result = JSON.parse(value);

    // the angularjs site field supplied siteid/sitename properties which initializes the
    // siteselector value. the sitename is assumed to be encoded, and is decoded once.
    // so the value for 'site' Field's in angularjs is assumed to be encoded.
    if (uiControl === 'site') {
      result.name = Matomo.helper.htmlDecode(result.name);
    }

    return result;
  }

  if (uiControl === 'checkbox' && varType !== 'array') {
    return transformAngularJsBoolAttr(value);
  }

  return value;
}

interface ExternalComponentRef {
  plugin: string;
  name: string;
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
      // eslint-disable-next-line @typescript-eslint/no-explicit-any
      transform(value: unknown, vm: unknown, scope: any): unknown {
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
    },
    condition: {
      angularJsBind: '@',
      transform(
        value: unknown,
        vm: unknown,
        scope: IScope,
      ): ((values: unknown[]) => boolean)|undefined {
        if (!value) {
          return undefined;
        }

        return (values: unknown[]) => (scope.$eval(value as string, values) as boolean);
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
    component: {
      angularJsBind: '<',
      // eslint-disable-next-line @typescript-eslint/no-explicit-any
      transform(value: unknown, vm: unknown, scope: any) {
        if (!value) {
          return undefined;
        }

        if (scope.templateFile) {
          return shallowRef(FieldAngularJsTemplate);
        }

        const { plugin, name } = value as ExternalComponentRef;
        if (!plugin || !name) {
          throw new Error('Invalid component property given to piwik-field directive, must '
            + 'be {plugin: \'...\',name: \'...\'}');
        }

        return shallowRef(useExternalPluginComponent(plugin, name));
      },
    },
  },
  directiveName: 'piwikField',
  $inject: ['$timeout'],
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
          const transformed = handleJsonValue(newVal, scope.varType, scope.uicontrol);

          nextTick(() => {
            vm.modelValue = transformed;
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
      const transformed = handleJsonValue(scope.value, scope.varType, scope.uicontrol);
      (ngModel as INgModelController).$setViewValue(transformed);
    } else {
      ngModel.$setViewValue(vm.modelValue);
    }

    // to provide same behavior in angularjs/<4.6.0, we trigger a model update to the same
    // value, but only for 'site' uicontrols. this only happened for site selectors, no others.
    if (scope.uicontrol === 'site' && ngModel.$viewValue) {
      setTimeout(() => {
        ngModel.$setViewValue({ ...ngModel.$viewValue });
      });
    }
  },
});
