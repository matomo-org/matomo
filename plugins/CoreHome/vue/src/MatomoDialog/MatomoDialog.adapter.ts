/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { IParseService } from 'angular';
import { ComponentPublicInstance } from 'vue';
import MatomoDialog from './MatomoDialog.vue';
import createAngularJsAdapter from '../createAngularJsAdapter';

export default createAngularJsAdapter<[IParseService]>({
  component: MatomoDialog,
  scope: {
    show: {
      vue: 'show',
      default: false,
    },
    element: {
      vue: 'element',
      default: (scope, element) => element[0],
    },
  },
  events: {
    yes: (scope, element, attrs) => {
      if (attrs.yes) {
        scope.$eval(attrs.yes);
        setTimeout(() => { scope.$apply(); }, 0);
      }
    },
    no: (scope, element, attrs) => {
      if (attrs.no) {
        scope.$eval(attrs.no);
        setTimeout(() => { scope.$apply(); }, 0);
      }
    },
    close: (scope, element, attrs) => {
      if (attrs.close) {
        scope.$eval(attrs.close);
        setTimeout(() => { scope.$apply(); }, 0);
      }
    },
    closeEnd: (scope, element, attrs, $parse: IParseService) => {
      setTimeout(() => {
        scope.$apply($parse(attrs.piwikDialog).assign(scope, false));
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
  postCreate: (vm: ComponentPublicInstance, scope, element, attrs) => {
    scope.$watch(attrs.piwikDialog, (newValue: boolean) => {
      vm.show = newValue || false;
    });
  },
  noScope: true,
});
