import { createApp, defineAsyncComponent } from 'vue';

const AsyncActivityIndicator = defineAsyncComponent(() => import('./ActivityIndicator.vue'));

export default function activityIndicatorAdapter(): ng.IDirective {
  return {
    restrict: 'A',
    scope: {
    },
    template: '',
    link: function activityIndicatorAdapterLink(scope: ng.IScope, element: ng.IAugmentedJQuery) {
      const vueApp = createApp(AsyncActivityIndicator);
      vueApp.mount(element[0]);
    },
  };
}

activityIndicatorAdapter.$inject = [];

angular.module('piwikApp').directive('activityIndicator', activityIndicatorAdapter);
