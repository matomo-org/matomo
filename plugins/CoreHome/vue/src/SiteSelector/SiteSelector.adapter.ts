/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import {
  IAttributes,
  INgModelController,
  IScope,
  ITimeoutService,
} from 'angular';
import { nextTick } from 'vue';
import createAngularJsAdapter from '../createAngularJsAdapter';
import SiteSelector from './SiteSelector.vue';
import Matomo from '../Matomo/Matomo';

export default createAngularJsAdapter<[ITimeoutService]>({
  component: SiteSelector,
  require: '?ngModel',
  scope: {
    showSelectedSite: {
      angularJsBind: '=',
    },
    showAllSitesItem: {
      angularJsBind: '=',
    },
    switchSiteOnSelect: {
      angularJsBind: '=',
    },
    onlySitesWithAdminAccess: {
      angularJsBind: '=',
    },
    name: {
      angularJsBind: '@',
    },
    allSitesText: {
      angularJsBind: '@',
    },
    allSitesLocation: {
      angularJsBind: '@',
    },
    placeholder: {
      angularJsBind: '@',
    },
    modelValue: {
      default(scope: IScope, element: JQLite, attrs: IAttributes) {
        if (attrs.siteid && attrs.sitename) {
          return { id: attrs.siteid, name: Matomo.helper.htmlDecode(attrs.sitename) };
        }

        if (Matomo.idSite) {
          return {
            id: Matomo.idSite,
            name: Matomo.helper.htmlDecode(Matomo.siteName),
          };
        }

        return undefined;
      },
    },
  },
  $inject: ['$timeout'],
  directiveName: 'piwikSiteselector',
  events: {
    'update:modelValue': (newValue, vm, scope, element, attrs, ngModel, $timeout) => {
      if ((newValue && !vm.modelValue)
        || (!newValue && vm.modelValue)
        || newValue.id !== vm.modelValue.id
      ) {
        $timeout(() => {
          scope.value = newValue;

          element.attr('siteid', newValue.id);
          element.trigger('change', newValue);

          if (ngModel) {
            ngModel.$setViewValue(newValue);
            ngModel.$render(); // not called automatically by the digest
          }
        });
      }
    },
    blur(event, vm, scope) {
      setTimeout(() => scope.$apply());
    },
  },
  postCreate(vm, scope, element, attrs, controller) {
    const ngModel = controller as INgModelController;

    scope.$watch('value', (newVal: unknown) => {
      nextTick(() => {
        if (newVal !== vm.modelValue) {
          vm.modelValue = newVal;
        }
      });
    });

    if (attrs.siteid && attrs.sitename) {
      scope.value = { id: attrs.siteid, name: Matomo.helper.htmlDecode(attrs.sitename) };
      vm.modelValue = scope.value;
    } else if (Matomo.idSite) {
      scope.value = {
        id: Matomo.idSite,
        name: Matomo.helper.htmlDecode(Matomo.siteName),
      };
      vm.modelValue = scope.value;
    }

    // setup ng-model mapping
    if (ngModel) {
      ngModel.$setViewValue(vm.modelValue);

      ngModel.$render = () => {
        nextTick(() => {
          nextTick(() => {
            if (window.angular.isString(ngModel.$viewValue)) {
              vm.modelValue = JSON.parse(ngModel.$viewValue);
            } else {
              vm.modelValue = ngModel.$viewValue;
            }
          });
        });
      };
    }
  },
});
