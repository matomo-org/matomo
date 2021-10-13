/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import {
  createApp,
  defineComponent,
  ref,
  ComponentPublicInstance,
} from 'vue';

interface SingleScopeVarInfo {
  vue: string;
  default?: any; // eslint-disable-line
  angularJsBind?: string;
}

type ScopeMapping = { [scopeVarName: string]: SingleScopeVarInfo };

type AdapterFunction<InjectTypes, R = void> = (
  scope: ng.IScope,
  element: ng.IAugmentedJQuery,
  attrs: ng.IAttributes,
  ...injected: InjectTypes,
) => R;

type PostCreateFunction<InjectTypes, R = void> = (
  vm: ComponentPublicInstance,
  scope: ng.IScope,
  element: ng.IAugmentedJQuery,
  attrs: ng.IAttributes,
  ...injected: InjectTypes,
) => R;

type EventMapping<InjectTypes> = { [vueEventName: string]: AdapterFunction<InjectTypes> };

type ComponentType = ReturnType<typeof defineComponent>;

export default function createAngularJsAdapter<InjectTypes = []>(options: {
  component: ComponentType,
  scope?: ScopeMapping,
  directiveName: string,
  events?: EventMapping<InjectTypes>,
  $inject?: string[],
  transclude?: boolean,
  mountPointFactory?: AdapterFunction<InjectTypes, HTMLElement>,
  postCreate?: PostCreateFunction<InjectTypes>,
  noScope?: boolean,
}): ng.IDirectiveFactory {
  const {
    component,
    scope = {},
    events = {},
    $inject,
    directiveName,
    transclude,
    mountPointFactory,
    postCreate,
    noScope,
  } = options;

  const angularJsScope = {};
  Object.entries(scope).forEach(([scopeVarName, info]) => {
    if (info.angularJsBind) {
      angularJsScope[scopeVarName] = info.angularJsBind;
    }
  });

  function angularJsAdapter(...injectedServices: InjectTypes) {
    const adapter: ng.IDirective = {
      restrict: 'A',
      scope: noScope ? undefined : angularJsScope,
      compile: function angularJsAdapterCompile() {
        return {
          post: function angularJsAdapterLink(
            ngScope: ng.IScope,
            ngElement: ng.IAugmentedJQuery,
            ngAttrs: ng.IAttributes,
          ) {
            const clone = ngElement.find('[ng-transclude]');

            let rootVueTemplate = '<root-component';
            Object.entries(scope).forEach(([, info]) => {
              rootVueTemplate += ` :${info.vue}="${info.vue}"`;
            });
            Object.entries(events).forEach((info) => {
              const [eventName] = info;
              rootVueTemplate += ` @${eventName}="onEventHandler('${eventName}')"`;
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
                  let value = ngScope[scopeVarName];
                  if (typeof value === 'undefined' && typeof info.default !== 'undefined') {
                    value = info.default instanceof Function
                      ? info.default(ngScope, ngElement, ngAttrs, ...injectedServices)
                      : info.default;
                  }
                  initialData[info.vue] = value;
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
              methods: {
                onEventHandler(name: string) {
                  if (events[name]) {
                    events[name](ngScope, ngElement, ngAttrs, ...injectedServices);
                  }
                },
              },
            });
            app.config.globalProperties.$sanitize = window.vueSanitize;
            app.component('root-component', component);

            const mountPoint = mountPointFactory
              ? mountPointFactory(ngScope, ngElement, ngAttrs, ...injectedServices)
              : ngElement[0];
            const vm = app.mount(mountPoint);

            Object.entries(scope).forEach(([scopeVarName, info]) => {
              if (!info.angularJsBind) {
                return;
              }

              ngScope.$watch(scopeVarName, (newValue: any) => { // eslint-disable-line
                if (typeof info.default !== 'undefined' && typeof newValue === 'undefined') {
                  vm[scopeVarName] = info.default instanceof Function
                    ? info.default(ngScope, ngElement, ngAttrs, ...injectedServices)
                    : info.default;
                } else {
                  vm[scopeVarName] = newValue;
                }
              });
            });

            if (transclude) {
              $(vm.transcludeTarget).append(clone);
            }

            if (postCreate) {
              postCreate(vm, ngScope, ngElement, ngAttrs, ...injectedServices);
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
