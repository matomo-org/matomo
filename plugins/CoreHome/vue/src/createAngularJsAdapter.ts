/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { createApp, defineComponent, ref } from 'vue';

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
  transclude?: boolean,
}): ng.IDirectiveFactory {
  const {
    component,
    element,
    scope,
    $inject,
    directiveName,
    transclude,
  } = options;

  const angularJsScope = {};
  Object.entries(scope).forEach(([scopeVarName, info]) => {
    angularJsScope[scopeVarName] = info.angularJsBind;
  });

  function angularJsAdapter() {
    const adapter: ng.IDirective = {
      restrict: 'A',
      scope: angularJsScope,
      compile: function angularJsAdapterCompile() {
        return {
          post: function angularJsAdapterLink(
            ngScope: ng.IScope,
            ngElement: ng.IAugmentedJQuery,
          ) {
            const clone = ngElement.find('[ng-transclude]');

            let rootVueTemplate = '<root-component';
            Object.entries(scope).forEach(([, info]) => {
              rootVueTemplate += ` :${info.vue}="${info.vue}"`;
            });
            rootVueTemplate += '>';
            if (transclude) {
              rootVueTemplate += '<div ref="transcludeTarget"/>';
            }
            rootVueTemplate += '</root-component>';

            const app = createApp({
              template: rootVueTemplate,
              data() {
                const initialData = {};
                Object.entries(scope).forEach(([scopeVarName, info]) => {
                  initialData[info.vue] = ngScope[scopeVarName];
                });
                return initialData;
              },
              setup() {
                if (transclude) {
                  const transcludeTarget = ref(null);
                  return {
                    transcludeTarget,
                  };
                }

                return undefined;
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

            if (transclude) {
              $(vm.transcludeTarget).append(clone);
            }
          },
        };
      },
    };

    if (transclude) {
      adapter.transclude = true;
      adapter.template = '<div ng-transclude/>';
    }

    return adapter;
  }

  angularJsAdapter.$inject = $inject || [];

  angular.module('piwikApp').directive(directiveName, angularJsAdapter);

  return angularJsAdapter;
}
