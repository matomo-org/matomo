/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { createApp } from 'vue';
import { IParseService } from 'angular';
import MatomoDialog from './MatomoDialog.vue';

export default function matomoDialogAdapter($parse: IParseService): ng.IDirective {
  return {
    restrict: 'A',
    link: function matomoDialogAdapterLink(
      scope: ng.IScope,
      element: ng.IAugmentedJQuery,
      attrs: ng.IAttributes,
    ) {
      const vueRootPlaceholder = $('<div class="vue-placeholder"/>');
      vueRootPlaceholder.appendTo(element);

      const app = createApp({
        template: '<matomo-dialog :show="show" :element="element" @yes="onYes()" @no="onNo()" @close="onClose()" @close-end="onCloseEnd()"/>',
        data() {
          return {
            show: false,
            element: null,
          };
        },
        methods: {
          onYes() {
            if (attrs.yes) {
              scope.$eval(attrs.yes);
              setTimeout(() => { scope.$apply(); }, 0);
            }
          },
          onNo() {
            if (attrs.no) {
              scope.$eval(attrs.no);
              setTimeout(() => { scope.$apply(); }, 0);
            }
          },
          onClose() {
            if (attrs.close) {
              scope.$eval(attrs.close);
              setTimeout(() => { scope.$apply(); }, 0);
            }
          },
          onCloseEnd() {
            setTimeout(() => {
              scope.$apply($parse(attrs.piwikDialog).assign(scope, false));
            }, 0);
          },
        },
      });
      app.config.globalProperties.$sanitize = window.vueSanitize;
      app.component('matomo-dialog', MatomoDialog);
      const vm = app.mount(vueRootPlaceholder[0]);

      vm.element = element[0]; // eslint-disable-line

      scope.$watch(attrs.piwikDialog, (newValue: boolean) => {
        vm.show = newValue || false;
      });
    },
  };
}

matomoDialogAdapter.$inject = ['$parse'];

angular.module('piwikApp').directive('piwikDialog', matomoDialogAdapter);
