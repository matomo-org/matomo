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
  removeAngularJsSpecificProperties,
  transformAngularJsBoolAttr,
} from 'CoreHome';
import SegmentGenerator from './SegmentGenerator.vue';

export default createAngularJsAdapter<[ITimeoutService]>({
  component: SegmentGenerator,
  require: '?ngModel',
  scope: {
    segmentDefinition: {
      angularJsBind: '@',
      vue: 'modelValue',
    },
    addInitialCondition: {
      angularJsBind: '=',
      transform: transformAngularJsBoolAttr,
    },
    visitSegmentsOnly: {
      angularJsBind: '=',
      transform: transformAngularJsBoolAttr,
    },
    idsite: {
      angularJsBind: '=',
    },
  },
  directiveName: 'piwikSegmentGenerator',
  $inject: ['$timeout'],
  events: {
    'update:modelValue': (newValue, vm, scope, element, attrs, ngModel, $timeout) => {
      const currentValue = ngModel ? ngModel.$viewValue : scope.segmentDefinition;
      if (newValue !== currentValue) {
        $timeout(() => {
          if (!ngModel) {
            scope.segmentDefinition = newValue;
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
    // methods to forward for BC
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    (element.scope() as any).segmentGenerator = {
      getSegmentString(): string {
        return vm.modelValue;
      },
    };

    const ngModel = controller as INgModelController;
    if (!ngModel) {
      scope.$watch('segmentDefinition', (newVal: unknown) => {
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

    if (typeof scope.segmentDefinition !== 'undefined') {
      (ngModel as INgModelController).$setViewValue(scope.segmentDefinition);
    } else {
      ngModel.$setViewValue(vm.modelValue);
    }
  },
});
