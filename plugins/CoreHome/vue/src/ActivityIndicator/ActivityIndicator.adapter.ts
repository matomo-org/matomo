import { createApp } from 'vue';
import ActivityIndicator from './ActivityIndicator.vue';
import translate from '../translate';

interface ActivityIndicatorAdapterScope extends ng.IScope {
  loading: boolean;
  loadingMessage: string;
}

export default function activityIndicatorAdapter(): ng.IDirective {
  return {
    restrict: 'A',
    scope: {
      loading: '<',
      loadingMessage: '<',
    },
    template: '',
    link: function activityIndicatorAdapterLink(
      scope: ActivityIndicatorAdapterScope,
      element: ng.IAugmentedJQuery,
    ) {
      const app = createApp({
        template: '<activity-indicator :loading="loading" :loadingMessage="loadingMessage"/>',
        data() {
          return {
            loading: scope.loading,
            loadingMessage: scope.loadingMessage,
          };
        },
      });
      app.component('activity-indicator', ActivityIndicator);
      const vm = app.mount(element[0]);

      scope.$watch('loading', (newValue: boolean) => {
        vm.loading = newValue;
      });

      scope.$watch('loadingMessage', (newValue: string) => {
        vm.loadingMessage = newValue || translate('General_LoadingData');
      });
    },
  };
}

activityIndicatorAdapter.$inject = [];

angular.module('piwikApp').directive('piwikActivityIndicator', activityIndicatorAdapter);
