/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import {
  defineComponent,
  ref,
  ComponentPublicInstance,
} from 'vue';
import Matomo from './Matomo/Matomo';
import createVueApp from './createVueApp';

interface SingleScopeVarInfo<InjectTypes> {
  vue?: string;
  default?: any; // eslint-disable-line
  transform?: (
    v: unknown,
    vm: ComponentPublicInstance,
    scope: ng.IScope,
    element: ng.IAugmentedJQuery,
    attrs: ng.IAttributes,
    otherController: ng.IControllerService,
    ...injected: InjectTypes,
  ) => unknown;
  angularJsBind?: string;
}

type ScopeMapping<InjectTypes> = { [scopeVarName: string]: SingleScopeVarInfo<InjectTypes> };

type AdapterFunction<InjectTypes, R = void> = (
  scope: ng.IScope,
  element: ng.IAugmentedJQuery,
  attrs: ng.IAttributes,
  ...injected: InjectTypes,
) => R;

type EventAdapterFunction<InjectTypes, R = void> = (
  $event: any, // eslint-disable-line
  vm: ComponentPublicInstance,
  scope: ng.IScope,
  element: ng.IAugmentedJQuery,
  attrs: ng.IAttributes,
  otherController: ng.IControllerService,
  ...injected: InjectTypes,
) => R;

type PostCreateFunction<InjectTypes, R = void> = (
  vm: ComponentPublicInstance,
  scope: ng.IScope,
  element: ng.IAugmentedJQuery,
  attrs: ng.IAttributes,
  otherController: ng.IControllerService,
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

export function removeAngularJsSpecificProperties<T>(newValue: T): T {
  if (typeof newValue === 'object'
    && newValue !== null
    && Object.getPrototypeOf(newValue) === Object.prototype
  ) {
    return Object.fromEntries(Object.entries(newValue).filter((pair) => !/^\$/.test(pair[0]))) as T;
  }

  return newValue;
}

export default function createAngularJsAdapter<InjectTypes = []>(options: {
  component: ComponentType,
  require?: string,
  scope?: ScopeMapping<InjectTypes>,
  directiveName: string,
  events?: EventMapping<InjectTypes>,
  $inject?: string[],
  transclude?: boolean,
  mountPointFactory?: AdapterFunction<InjectTypes, HTMLElement>,
  postCreate?: PostCreateFunction<InjectTypes>,
  noScope?: boolean,
  restrict?: string,
  priority?: number,
}): ng.IDirectiveFactory {
  const {
    component,
    require,
    scope = {},
    events = {},
    $inject,
    directiveName,
    transclude,
    mountPointFactory,
    postCreate,
    noScope,
    restrict = 'A',
    priority,
  } = options;

  const currentTranscludeCounter = transcludeCounter;
  if (transclude) {
    transcludeCounter += 1;
  }

  const vueToAngular = {};
  const angularJsScope = {};
  Object.entries(scope).forEach(([scopeVarName, info]) => {
    if (!info.vue) {
      info.vue = scopeVarName;
    }
    if (info.angularJsBind) {
      angularJsScope[scopeVarName] = info.angularJsBind;
    }
    vueToAngular[info.vue] = scopeVarName;
  });

  function angularJsAdapter(...injectedServices: InjectTypes) {
    const adapter: ng.IDirective = {
      restrict,
      require,
      priority,
      scope: noScope ? undefined : angularJsScope,
      compile: function angularJsAdapterCompile() {
        return {
          post: function angularJsAdapterLink(
            ngScope: ng.IScope,
            ngElement: ng.IAugmentedJQuery,
            ngAttrs: ng.IAttributes,
            ngController: ng.IControllerService,
          ) {
            const cloneElement = transclude
              ? ngElement.find(`[ng-transclude][counter=${currentTranscludeCounter}]`)
              : null;

            // build the root vue template
            let rootVueTemplate = '<root-component';
            Object.entries(events).forEach((info) => {
              const [eventName] = info;
              rootVueTemplate += ` @${toKebabCase(eventName)}="onEventHandler('${eventName}', $event)"`;
            });
            Object.entries(scope).forEach(([, info]) => {
              if (info.angularJsBind === '&' || info.angularJsBind === '&?') {
                const eventName = toKebabCase(info.vue);
                if (!events[eventName]) { // pass through scope & w/o a custom event handler
                  rootVueTemplate += ` @${eventName}="onEventHandler('${eventName}', $event)"`;
                }
              } else {
                rootVueTemplate += ` :${toKebabCase(info.vue)}="${info.vue}"`;
              }
            });
            rootVueTemplate += '>';
            if (transclude) {
              rootVueTemplate += '<div ref="transcludeTarget"/>';
            }
            rootVueTemplate += '</root-component>';

            // build the vue app
            const app = createVueApp({
              template: rootVueTemplate,
              data() {
                const initialData = {};
                Object.entries(scope).forEach(([scopeVarName, info]) => {
                  let value = removeAngularJsSpecificProperties(ngScope[scopeVarName]);
                  if (typeof value === 'undefined' && typeof info.default !== 'undefined') {
                    value = info.default instanceof Function
                      ? info.default(ngScope, ngElement, ngAttrs, ...injectedServices)
                      : info.default;
                  }
                  if (info.transform) {
                    value = info.transform(
                      value,
                      this,
                      ngScope,
                      ngElement,
                      ngAttrs,
                      ngController,
                      ...injectedServices,
                    );
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
                  const scopePropertyName = toAngularJsCamelCase(vueToAngular[name] || name);
                  if (ngScope[scopePropertyName]) {
                    ngScope[scopePropertyName]($event);
                  }

                  if (events[name]) {
                    events[name](
                      $event,
                      this,
                      ngScope,
                      ngElement,
                      ngAttrs,
                      ngController,
                      ...injectedServices,
                    );
                  }
                },
              },
            });
            app.component('root-component', component);

            // mount the app
            const mountPoint = mountPointFactory
              ? mountPointFactory(ngScope, ngElement, ngAttrs, ...injectedServices)
              : ngElement[0];
            const vm = app.mount(mountPoint);

            // setup watches to bind between angularjs + vue
            Object.entries(scope).forEach(([scopeVarName, info]) => {
              if (!info.angularJsBind || info.angularJsBind === '&' || info.angularJsBind === '&?') {
                return;
              }

              ngScope.$watch(scopeVarName, (newValue: any) => { // eslint-disable-line
                let newValueFinal = removeAngularJsSpecificProperties(newValue);
                if (typeof info.default !== 'undefined' && typeof newValue === 'undefined') {
                  newValueFinal = info.default instanceof Function
                    ? info.default(ngScope, ngElement, ngAttrs, ...injectedServices)
                    : info.default;
                }
                if (info.transform) {
                  newValueFinal = info.transform(
                    newValueFinal,
                    vm,
                    ngScope,
                    ngElement,
                    ngAttrs,
                    ngController,
                    ...injectedServices,
                  );
                }
                vm[info.vue] = newValueFinal;
              });
            });

            if (transclude) {
              $(vm.transcludeTarget).append(cloneElement);
            }

            if (postCreate) {
              postCreate(vm, ngScope, ngElement, ngAttrs, ngController, ...injectedServices);
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

export function transformAngularJsBoolAttr(v: unknown): boolean|undefined {
  if (typeof v === 'undefined') {
    return undefined;
  }

  if (v === 'true') {
    return true;
  }

  return !!v && v > 0 && v !== '0';
}

export function transformAngularJsIntAttr(v: string): number {
  if (typeof v === 'undefined') {
    return undefined;
  }

  if (v === null) {
    return null;
  }

  return parseInt(v, 10);
}

// utility function for service adapters
export function clone<T>(o: T): T {
  return JSON.parse(JSON.stringify(o)) as T;
}

export function cloneThenApply<T>(o: T): T {
  const result = clone(o);
  Matomo.helper.getAngularDependency('$rootScope').$applyAsync();
  return result;
}
