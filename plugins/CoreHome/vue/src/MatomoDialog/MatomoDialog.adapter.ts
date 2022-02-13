/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { IParseService, IScope } from 'angular';
import MatomoDialog from './MatomoDialog.vue';
import createAngularJsAdapter from '../createAngularJsAdapter';

export default createAngularJsAdapter<[IParseService]>({
  component: MatomoDialog,
  scope: {
    show: {
      vue: 'modelValue',
      default: false,
    },
    element: {
      default: (scope: IScope, element: JQLite) => element[0],
    },
  },
  events: {
    yes: ($event, vm, scope, element, attrs) => {
      if (attrs.yes) {
        scope.$eval(attrs.yes);
        setTimeout(() => { scope.$apply(); }, 0);
      }
    },
    no: ($event, vm, scope, element, attrs) => {
      if (attrs.no) {
        scope.$eval(attrs.no);
        setTimeout(() => { scope.$apply(); }, 0);
      }
    },
    validation: ($event, vm, scope, element, attrs) => {
      if (attrs.no) {
        scope.$eval(attrs.no);
        setTimeout(() => { scope.$apply(); }, 0);
      }
    },
    close: ($event, vm, scope, element, attrs) => {
      if (attrs.close) {
        scope.$eval(attrs.close);
        setTimeout(() => { scope.$apply(); }, 0);
      }
    },
    'update:modelValue': (newValue, vm, scope, element, attrs, controller, $parse: IParseService) => {
      setTimeout(() => {
        scope.$apply($parse(attrs.piwikDialog).assign(scope, newValue));
      }, 0);
    },
  },
  $inject: ['$parse'],
  directiveName: 'piwikDialog',
  transclude: true,
  mountPointFactory: (scope, element) => {
    const vueRootPlaceholder = $('<div class="vue-placeholder"/>');
    vueRootPlaceholder.appendTo(element);
    return vueRootPlaceholder[0];
  },
  postCreate: (vm, scope, element, attrs) => {
    scope.$watch(attrs.piwikDialog, (newValue: boolean, oldValue: boolean) => {
      if (oldValue !== newValue) {
        vm.modelValue = newValue || false;
      }
    });
  },
  noScope: true,
});
