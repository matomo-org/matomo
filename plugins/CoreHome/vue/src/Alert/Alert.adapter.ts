/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { createApp, ref } from 'vue';
import Alert from './Alert.vue';

interface AlertAdapterScope extends ng.IScope {
  severity: string;
}

export default function alertAdapter(): ng.IDirective {
  return {
    restrict: 'A',
    transclude: true,
    scope: {
      severity: '@piwikAlert',
    },
    template: '<div ng-transclude/>',
    compile: function alertAdapterCompile() {
      return {
        post: function alertAdapterPostLink(
          scope: AlertAdapterScope,
          element: ng.IAugmentedJQuery,
        ) {
          const clone = element.find('[ng-transclude]');

          const app = createApp({
            template: '<alert :severity="severity"><div ref="transcludeTarget"/></alert>',
            data() {
              return { severity: scope.severity };
            },
            setup() {
              const transcludeTarget = ref(null);
              return {
                transcludeTarget,
              };
            },
          });
          app.config.globalProperties.$sanitize = window.vueSanitize;
          app.component('alert', Alert);
          const vm = app.mount(element[0]);

          scope.$watch('severity', (newValue: string) => {
            vm.severity = newValue;
          });

          $(vm.transcludeTarget).append(clone);
        },
      };
    },
  };
}

alertAdapter.$inject = [];

angular.module('piwikApp').directive('piwikAlert', alertAdapter);
