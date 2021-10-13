/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { createApp, defineComponent } from 'vue';

interface SingleScopeVarInfo {
  vue: string;
  default?: any; // eslint-disable-line
  angularJsBind: string;
}

type ScopeMapping = { [scopeVarName: string]: SingleScopeVarInfo };
type ComponentType = ReturnType<typeof defineComponent>;

export default function createAngularJsAdapter(options: {
  component: ComponentType,
  element?: ng.IAugmentedJQuery,
  scope: ScopeMapping,
  $inject: string[],
  directiveName: string,
}): ng.IDirectiveFactory {
  const {
    component,
    element,
    scope,
    $inject,
    directiveName,
  } = options;

  const angularJsScope = {};
  Object.entries(scope).forEach(([scopeVarName, info]) => {
    angularJsScope[scopeVarName] = info.angularJsBind;
  });

  function angularJsAdapter() {
    return {
      restrict: 'A',
      scope: angularJsScope,
      link: function activityIndicatorAdapterLink(
        ngScope: ng.IScope,
        ngElement: ng.IAugmentedJQuery,
      ) {
        let rootVueTemplate = '<root-component';
        Object.entries(scope).forEach(([, info]) => {
          rootVueTemplate += ` :${info.vue}="${info.vue}"`;
        });
        rootVueTemplate += '/>';

        const app = createApp({
          template: rootVueTemplate,
          data() {
            const initialData = {};
            Object.entries(scope).forEach(([scopeVarName, info]) => {
              initialData[info.vue] = ngScope[scopeVarName];
            });
            return initialData;
          },
        });
        app.config.globalProperties.$sanitize = window.vueSanitize;
        app.component('root-component', component);
        const vm = app.mount((element && element[0]) || ngElement[0]);

        Object.entries(scope).forEach(([scopeVarName, info]) => {
          ngScope.$watch(scopeVarName, (newValue: any) => { // eslint-disable-line
            if (typeof info.default !== 'undefined' && typeof newValue === 'undefined') {
              vm[scopeVarName] = info.default instanceof Function
                ? info.default(scope, element)
                : info.default;
            } else {
              vm[scopeVarName] = newValue;
            }
          });
        });
      },
    };
  }

  angularJsAdapter.$inject = $inject || [];

  angular.module('piwikApp').directive(directiveName, angularJsAdapter);

  return angularJsAdapter;
}
