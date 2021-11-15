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
import translate from './translate';

interface SingleScopeVarInfo {
  vue?: string;
  default?: any; // eslint-disable-line
  transform?: (v: unknown) => unknown;
  angularJsBind?: string;
}

type ScopeMapping = { [scopeVarName: string]: SingleScopeVarInfo };

type AdapterFunction<InjectTypes, R = void> = (
  scope: ng.IScope,
  element: ng.IAugmentedJQuery,
  attrs: ng.IAttributes,
  ...injected: InjectTypes,
) => R;

type EventAdapterFunction<InjectTypes, R = void> = (
  $event: any, // eslint-disable-line
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

type EventMapping<InjectTypes> = { [vueEventName: string]: EventAdapterFunction<InjectTypes> };

type ComponentType = ReturnType<typeof defineComponent>;

let transcludeCounter = 0;

function toKebabCase(arg: string): string {
  return arg.substring(0, 1).toLowerCase() + arg.substring(1)
    .replace(/[A-Z]/g, (s) => `-${s.toLowerCase()}`);
}

function toAngularJsCamelCase(arg: string): string {
  return arg.substring(0, 1).toLowerCase() + arg.substring(1)
    .replace(/-([a-z])/g, (s, p) => p.toUpperCase());
}

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
  restrict?: string,
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
    restrict = 'A',
  } = options;

  const currentTranscludeCounter = transcludeCounter;
  if (transclude) {
    transcludeCounter += 1;
  }

  const angularJsScope = {};
  Object.entries(scope).forEach(([scopeVarName, info]) => {
    if (!info.vue) {
      info.vue = scopeVarName;
    }
    if (info.angularJsBind) {
      angularJsScope[scopeVarName] = info.angularJsBind;
    }
  });

  function angularJsAdapter(...injectedServices: InjectTypes) {
    const adapter: ng.IDirective = {
      restrict,
      scope: noScope ? undefined : angularJsScope,
      compile: function angularJsAdapterCompile() {
        return {
          post: function angularJsAdapterLink(
            ngScope: ng.IScope,
            ngElement: ng.IAugmentedJQuery,
            ngAttrs: ng.IAttributes,
          ) {
            const clone = transclude ? ngElement.find(`[ng-transclude][counter=${currentTranscludeCounter}]`) : null;

            // build the root vue template
            let rootVueTemplate = '<root-component';
            Object.entries(events).forEach((info) => {
              const [eventName] = info;
              rootVueTemplate += ` @${eventName}="onEventHandler('${eventName}', $event)"`;
            });
            Object.entries(scope).forEach(([key, info]) => {
              if (info.angularJsBind === '&') {
                const eventName = toKebabCase(key);
                if (!events[eventName]) { // pass through scope & w/o a custom event handler
                  rootVueTemplate += ` @${eventName}="onEventHandler('${eventName}', $event)"`;
                }
              } else {
                rootVueTemplate += ` :${info.vue}="${info.vue}"`;
              }
            });
            rootVueTemplate += '>';
            if (transclude) {
              rootVueTemplate += '<div ref="transcludeTarget"/>';
            }
            rootVueTemplate += '</root-component>';

            // build the vue app
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
                  if (info.transform) {
                    value = info.transform(value);
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
                onEventHandler(name: string, $event: any) { // eslint-disable-line
                  const scopePropertyName = toAngularJsCamelCase(name);
                  if (ngScope[scopePropertyName]) {
                    ngScope[scopePropertyName]($event);
                  }

                  if (events[name]) {
                    events[name]($event, ngScope, ngElement, ngAttrs, ...injectedServices);
                  }
                },
              },
            });
            app.config.globalProperties.$sanitize = window.vueSanitize;
            app.config.globalProperties.translate = translate;
            app.component('root-component', component);

            // mount the app
            const mountPoint = mountPointFactory
              ? mountPointFactory(ngScope, ngElement, ngAttrs, ...injectedServices)
              : ngElement[0];
            const vm = app.mount(mountPoint);

            // setup watches to bind between angularjs + vue
            Object.entries(scope).forEach(([scopeVarName, info]) => {
              if (!info.angularJsBind || info.angularJsBind === '&') {
                return;
              }

              ngScope.$watch(scopeVarName, (newValue: any) => { // eslint-disable-line
                let newValueFinal = newValue;
                if (typeof info.default !== 'undefined' && typeof newValue === 'undefined') {
                  newValueFinal = info.default instanceof Function
                    ? info.default(ngScope, ngElement, ngAttrs, ...injectedServices)
                    : info.default;
                }
                if (info.transform) {
                  newValueFinal = info.transform(newValueFinal);
                }
                vm[scopeVarName] = newValueFinal;
              });
            });

            if (transclude) {
              $(vm.transcludeTarget).append(clone);
            }

            if (postCreate) {
              postCreate(vm, ngScope, ngElement, ngAttrs, ...injectedServices);
            }

            ngElement.on('$destroy', () => {
              app.unmount();
            });
          },
        };
      },
    };

    if (transclude) {
      adapter.transclude = true;
      adapter.template = `<div ng-transclude counter="${currentTranscludeCounter}"/>`;
    }

    return adapter;
  }

  angularJsAdapter.$inject = $inject || [];

  angular.module('piwikApp').directive(directiveName, angularJsAdapter);

  return angularJsAdapter;
}
