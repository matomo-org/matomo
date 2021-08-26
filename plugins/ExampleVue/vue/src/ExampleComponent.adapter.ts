import { createApp } from 'vue';
import ExampleComponent from './ExampleComponent.vue';

export default function exampleVueComponentAdapter(): ng.IDirective {
  return {
    restrict: 'A',
    scope: {
    },
    template: '<div>am i herE?</div>',
    link: function exampleVueComponentAdapterLink(scope: ng.IScope, element: ng.IAugmentedJQuery) {
      const vueApp = createApp(ExampleComponent);
      vueApp.mount(element[0]);
    },
  };
}

exampleVueComponentAdapter.$inject = [];

angular.module('piwikApp').directive('exampleVueComponent', exampleVueComponentAdapter);
