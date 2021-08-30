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
