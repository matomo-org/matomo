/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { createApp, defineAsyncComponent } from 'vue';

const AsyncExampleComponent = defineAsyncComponent(() => import('./ExampleComponent.vue'));

export default function exampleVueComponentAdapter(): ng.IDirective {
  return {
    restrict: 'A',
    scope: {
    },
    template: '',
    link: function exampleVueComponentAdapterLink(scope: ng.IScope, element: ng.IAugmentedJQuery) {
      const vueApp = createApp(AsyncExampleComponent);
      vueApp.mount(element[0]);
    },
  };
}

exampleVueComponentAdapter.$inject = [];

angular.module('piwikApp').directive('exampleVueComponent', exampleVueComponentAdapter);
