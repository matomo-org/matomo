/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/* eslint-disable @typescript-eslint/no-explicit-any */

import {
  defineComponent,
  ref,
} from 'vue';
import { IDirectiveFactory, IDirectivePrePost, Injectable } from 'angular';
import Matomo from './Matomo/Matomo';
import createVueApp from './createVueApp';

interface SingleScopeVarInfo<InjectTypes extends unknown[]> {
  vue?: string;
  default?: any;
  transform?: (
    v: unknown,
    vm: any,
    scope: any,
    element: ng.IAugmentedJQuery,
    attrs: ng.IAttributes,
    otherController?: ng.IController,
    ...injected: InjectTypes
  ) => unknown;
  angularJsBind?: string;
  deepWatch?: boolean;
}

type ScopeMapping<InjectTypes extends unknown[]> = {
  [scopeVarName: string]: SingleScopeVarInfo<InjectTypes>,
};

type AdapterFunction<InjectTypes extends unknown[], R = void> = (
  scope: any,
  element: ng.IAugmentedJQuery,
  attrs: ng.IAttributes,
  ...injected: InjectTypes
) => R;

type EventAdapterFunction<InjectTypes extends unknown[], R = void> = (
  $event: any,
  vm: any,
  scope: any,
  element: ng.IAugmentedJQuery,
  attrs: ng.IAttributes,
  otherController?: ng.IController,
  ...injected: InjectTypes
) => R;

type PostCreateFunction<InjectTypes extends unknown[], R = void> = (
  vm: any,
  scope: any,
  element: ng.IAugmentedJQuery,
  attrs: ng.IAttributes,
  otherController?: ng.IController,
  ...injected: InjectTypes
) => R;

type EventMapping<InjectTypes extends unknown[]> = {
  [vueEventName: string]: EventAdapterFunction<InjectTypes>,
};

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

export default function createAngularJsAdapter<InjectTypes extends unknown[] = []>(options: {
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
  replace?: boolean,
}): Injectable<ng.IDirectiveFactory> {
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
    replace,
  } = options;

  const currentTranscludeCounter = transcludeCounter;
  if (transclude) {
    transcludeCounter += 1;
  }

  const vueToAngular: Record<string, string> = {};
  const angularJsScope: Record<string, string> = {};
  Object.entries(scope).forEach(([scopeVarName, info]) => {
    if (!info.vue) {
      info.vue = scopeVarName;
    }
    if (info.angularJsBind) {
      angularJsScope[scopeVarName] = info.angularJsBind;
    }
    vueToAngular[info.vue] = scopeVarName;
  });

  function angularJsAdapter(...injectedServices: InjectTypes): ng.IDirective {
    const adapter: ng.IDirective = {
      restrict,
      require,
      priority,
      scope: noScope ? undefined : angularJsScope,
      compile: function angularJsAdapterCompile(): IDirectivePrePost {
        return {
          post: function angularJsAdapterLink(
            ngScope: any,
            ngElement: ng.IAugmentedJQuery,
            ngAttrs: ng.IAttributes,
            ngController?: ng.IController,
          ) {
            const transcludeClone = transclude
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
                const eventName = toKebabCase(info.vue!);
                if (!events[info.vue!]) { // pass through scope & w/o a custom event handler
                  rootVueTemplate += ` @${eventName}="onEventHandler('${info.vue!}', $event)"`;
                }
              } else {
                rootVueTemplate += ` :${toKebabCase(info.vue!)}="${info.vue}"`;
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
                const initialData: Record<string, unknown> = {};
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
                  initialData[info.vue!] = value;
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
                onEventHandler(name: string, $event: any) {
                  let scopePropertyName = toAngularJsCamelCase(name);
                  scopePropertyName = vueToAngular[scopePropertyName] || scopePropertyName;
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
            const vm: any = app.mount(mountPoint);

            // setup watches to bind between angularjs + vue
            Object.entries(scope).forEach(([scopeVarName, info]) => {
              if (!info.angularJsBind || info.angularJsBind === '&' || info.angularJsBind === '&?') {
                return;
              }

              ngScope.$watch(scopeVarName, (newValue: any, oldValue: any) => {
                if (newValue === oldValue
                  && JSON.stringify(vm[info.vue!]) === JSON.stringify(newValue)
                ) {
                  return; // initial
                }

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

                vm[info.vue!] = newValueFinal;
              }, info.deepWatch);
            });

            if (transclude && transcludeClone) {
              $(vm.transcludeTarget).append(transcludeClone);
            }

            if (postCreate) {
              postCreate(vm, ngScope, ngElement, ngAttrs, ngController, ...injectedServices);
            }

            // specifying replace: true on the directive does nothing w/ vue inside, so
            // handle it here.
            if (replace) {
              // transfer attributes from angularjs element that are not in scope to
              // mount point element
              Array.from(ngElement[0].attributes).forEach((attr) => {
                if (scope[attr.nodeName]) {
                  return;
                }
                if (mountPoint.firstElementChild) {
                  mountPoint.firstElementChild.setAttribute(attr.nodeName, attr.nodeValue!);
                }
              });

              ngElement.replaceWith(window.$(mountPoint).children());
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

  window.angular.module('piwikApp').directive(
    directiveName,
    angularJsAdapter as unknown as Injectable<IDirectiveFactory>,
  );

  return angularJsAdapter as unknown as Injectable<IDirectiveFactory>;
}

export function transformAngularJsBoolAttr(v: unknown): boolean|undefined {
  if (typeof v === 'undefined') {
    return undefined;
  }

  if (v === 'true') {
    return true;
  }

  return !!v && v as number > 0 && v !== '0';
}

export function transformAngularJsIntAttr(v: unknown): number|undefined|null {
  if (typeof v === 'undefined') {
    return undefined;
  }

  if (v === null) {
    return null;
  }

  return parseInt(v as string, 10);
}

// utility function for service adapters
export function clone<T>(p: T): T {
  if (typeof p === 'undefined') {
    return p;
  }

  return JSON.parse(JSON.stringify(p)) as T;
}

export function cloneThenApply<T>(p: T): T {
  const result = clone(p);
  Matomo.helper.getAngularDependency('$rootScope').$applyAsync();
  return result;
}
