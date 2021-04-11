/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
import { ComponentFactoryResolver, NgZone } from '@angular/core';
import { $COMPILE, $INJECTOR, $PARSE, INJECTOR_KEY, LAZY_MODULE_REF, REQUIRE_INJECTOR, REQUIRE_NG_MODEL } from './constants';
import { DowngradeComponentAdapter } from './downgrade_component_adapter';
import { SyncPromise } from './promise_util';
import { controllerKey, getDowngradedModuleCount, getTypeName, getUpgradeAppType, validateInjectionKey } from './util';
/**
 * @description
 *
 * A helper function that allows an Angular component to be used from AngularJS.
 *
 * *Part of the [upgrade/static](api?query=upgrade%2Fstatic)
 * library for hybrid upgrade apps that support AOT compilation*
 *
 * This helper function returns a factory function to be used for registering
 * an AngularJS wrapper directive for "downgrading" an Angular component.
 *
 * @usageNotes
 * ### Examples
 *
 * Let's assume that you have an Angular component called `ng2Heroes` that needs
 * to be made available in AngularJS templates.
 *
 * {@example upgrade/static/ts/full/module.ts region="ng2-heroes"}
 *
 * We must create an AngularJS [directive](https://docs.angularjs.org/guide/directive)
 * that will make this Angular component available inside AngularJS templates.
 * The `downgradeComponent()` function returns a factory function that we
 * can use to define the AngularJS directive that wraps the "downgraded" component.
 *
 * {@example upgrade/static/ts/full/module.ts region="ng2-heroes-wrapper"}
 *
 * For more details and examples on downgrading Angular components to AngularJS components please
 * visit the [Upgrade guide](guide/upgrade#using-angular-components-from-angularjs-code).
 *
 * @param info contains information about the Component that is being downgraded:
 *
 * - `component: Type<any>`: The type of the Component that will be downgraded
 * - `downgradedModule?: string`: The name of the downgraded module (if any) that the component
 *   "belongs to", as returned by a call to `downgradeModule()`. It is the module, whose
 *   corresponding Angular module will be bootstrapped, when the component needs to be instantiated.
 *   <br />
 *   (This option is only necessary when using `downgradeModule()` to downgrade more than one
 *   Angular module.)
 * - `propagateDigest?: boolean`: Whether to perform {@link ChangeDetectorRef#detectChanges
 *   change detection} on the component on every
 *   [$digest](https://docs.angularjs.org/api/ng/type/$rootScope.Scope#$digest). If set to `false`,
 *   change detection will still be performed when any of the component's inputs changes.
 *   (Default: true)
 *
 * @returns a factory function that can be used to register the component in an
 * AngularJS module.
 *
 * @publicApi
 */
export function downgradeComponent(info) {
    const directiveFactory = function ($compile, $injector, $parse) {
        // When using `downgradeModule()`, we need to handle certain things specially. For example:
        // - We always need to attach the component view to the `ApplicationRef` for it to be
        //   dirty-checked.
        // - We need to ensure callbacks to Angular APIs (e.g. change detection) are run inside the
        //   Angular zone.
        //   NOTE: This is not needed, when using `UpgradeModule`, because `$digest()` will be run
        //         inside the Angular zone (except if explicitly escaped, in which case we shouldn't
        //         force it back in).
        const isNgUpgradeLite = getUpgradeAppType($injector) === 3 /* Lite */;
        const wrapCallback = !isNgUpgradeLite ? cb => cb : cb => () => NgZone.isInAngularZone() ? cb() : ngZone.run(cb);
        let ngZone;
        // When downgrading multiple modules, special handling is needed wrt injectors.
        const hasMultipleDowngradedModules = isNgUpgradeLite && (getDowngradedModuleCount($injector) > 1);
        return {
            restrict: 'E',
            terminal: true,
            require: [REQUIRE_INJECTOR, REQUIRE_NG_MODEL],
            link: (scope, element, attrs, required) => {
                // We might have to compile the contents asynchronously, because this might have been
                // triggered by `UpgradeNg1ComponentAdapterBuilder`, before the Angular templates have
                // been compiled.
                const ngModel = required[1];
                const parentInjector = required[0];
                let moduleInjector = undefined;
                let ranAsync = false;
                if (!parentInjector || hasMultipleDowngradedModules) {
                    const downgradedModule = info.downgradedModule || '';
                    const lazyModuleRefKey = `${LAZY_MODULE_REF}${downgradedModule}`;
                    const attemptedAction = `instantiating component '${getTypeName(info.component)}'`;
                    validateInjectionKey($injector, downgradedModule, lazyModuleRefKey, attemptedAction);
                    const lazyModuleRef = $injector.get(lazyModuleRefKey);
                    moduleInjector = lazyModuleRef.injector || lazyModuleRef.promise;
                }
                // Notes:
                //
                // There are two injectors: `finalModuleInjector` and `finalParentInjector` (they might be
                // the same instance, but that is irrelevant):
                // - `finalModuleInjector` is used to retrieve `ComponentFactoryResolver`, thus it must be
                //   on the same tree as the `NgModule` that declares this downgraded component.
                // - `finalParentInjector` is used for all other injection purposes.
                //   (Note that Angular knows to only traverse the component-tree part of that injector,
                //   when looking for an injectable and then switch to the module injector.)
                //
                // There are basically three cases:
                // - If there is no parent component (thus no `parentInjector`), we bootstrap the downgraded
                //   `NgModule` and use its injector as both `finalModuleInjector` and
                //   `finalParentInjector`.
                // - If there is a parent component (and thus a `parentInjector`) and we are sure that it
                //   belongs to the same `NgModule` as this downgraded component (e.g. because there is only
                //   one downgraded module, we use that `parentInjector` as both `finalModuleInjector` and
                //   `finalParentInjector`.
                // - If there is a parent component, but it may belong to a different `NgModule`, then we
                //   use the `parentInjector` as `finalParentInjector` and this downgraded component's
                //   declaring `NgModule`'s injector as `finalModuleInjector`.
                //   Note 1: If the `NgModule` is already bootstrapped, we just get its injector (we don't
                //           bootstrap again).
                //   Note 2: It is possible that (while there are multiple downgraded modules) this
                //           downgraded component and its parent component both belong to the same NgModule.
                //           In that case, we could have used the `parentInjector` as both
                //           `finalModuleInjector` and `finalParentInjector`, but (for simplicity) we are
                //           treating this case as if they belong to different `NgModule`s. That doesn't
                //           really affect anything, since `parentInjector` has `moduleInjector` as ancestor
                //           and trying to resolve `ComponentFactoryResolver` from either one will return
                //           the same instance.
                // If there is a parent component, use its injector as parent injector.
                // If this is a "top-level" Angular component, use the module injector.
                const finalParentInjector = parentInjector || moduleInjector;
                // If this is a "top-level" Angular component or the parent component may belong to a
                // different `NgModule`, use the module injector for module-specific dependencies.
                // If there is a parent component that belongs to the same `NgModule`, use its injector.
                const finalModuleInjector = moduleInjector || parentInjector;
                const doDowngrade = (injector, moduleInjector) => {
                    // Retrieve `ComponentFactoryResolver` from the injector tied to the `NgModule` this
                    // component belongs to.
                    const componentFactoryResolver = moduleInjector.get(ComponentFactoryResolver);
                    const componentFactory = componentFactoryResolver.resolveComponentFactory(info.component);
                    if (!componentFactory) {
                        throw new Error(`Expecting ComponentFactory for: ${getTypeName(info.component)}`);
                    }
                    const injectorPromise = new ParentInjectorPromise(element);
                    const facade = new DowngradeComponentAdapter(element, attrs, scope, ngModel, injector, $compile, $parse, componentFactory, wrapCallback);
                    const projectableNodes = facade.compileContents();
                    facade.createComponent(projectableNodes);
                    facade.setupInputs(isNgUpgradeLite, info.propagateDigest);
                    facade.setupOutputs();
                    facade.registerCleanup();
                    injectorPromise.resolve(facade.getInjector());
                    if (ranAsync) {
                        // If this is run async, it is possible that it is not run inside a
                        // digest and initial input values will not be detected.
                        scope.$evalAsync(() => { });
                    }
                };
                const downgradeFn = !isNgUpgradeLite ? doDowngrade : (pInjector, mInjector) => {
                    if (!ngZone) {
                        ngZone = pInjector.get(NgZone);
                    }
                    wrapCallback(() => doDowngrade(pInjector, mInjector))();
                };
                // NOTE:
                // Not using `ParentInjectorPromise.all()` (which is inherited from `SyncPromise`), because
                // Closure Compiler (or some related tool) complains:
                // `TypeError: ...$src$downgrade_component_ParentInjectorPromise.all is not a function`
                SyncPromise.all([finalParentInjector, finalModuleInjector])
                    .then(([pInjector, mInjector]) => downgradeFn(pInjector, mInjector));
                ranAsync = true;
            }
        };
    };
    // bracket-notation because of closure - see #14441
    directiveFactory['$inject'] = [$COMPILE, $INJECTOR, $PARSE];
    return directiveFactory;
}
/**
 * Synchronous promise-like object to wrap parent injectors,
 * to preserve the synchronous nature of AngularJS's `$compile`.
 */
class ParentInjectorPromise extends SyncPromise {
    constructor(element) {
        super();
        this.element = element;
        this.injectorKey = controllerKey(INJECTOR_KEY);
        // Store the promise on the element.
        element.data(this.injectorKey, this);
    }
    resolve(injector) {
        // Store the real injector on the element.
        this.element.data(this.injectorKey, injector);
        // Release the element to prevent memory leaks.
        this.element = null;
        // Resolve the promise.
        super.resolve(injector);
    }
}
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiZG93bmdyYWRlX2NvbXBvbmVudC5qcyIsInNvdXJjZVJvb3QiOiIiLCJzb3VyY2VzIjpbIi4uLy4uLy4uLy4uLy4uLy4uLy4uLy4uL3BhY2thZ2VzL3VwZ3JhZGUvc3JjL2NvbW1vbi9zcmMvZG93bmdyYWRlX2NvbXBvbmVudC50cyJdLCJuYW1lcyI6W10sIm1hcHBpbmdzIjoiQUFBQTs7Ozs7O0dBTUc7QUFFSCxPQUFPLEVBQW1CLHdCQUF3QixFQUFZLE1BQU0sRUFBTyxNQUFNLGVBQWUsQ0FBQztBQUdqRyxPQUFPLEVBQUMsUUFBUSxFQUFFLFNBQVMsRUFBRSxNQUFNLEVBQUUsWUFBWSxFQUFFLGVBQWUsRUFBRSxnQkFBZ0IsRUFBRSxnQkFBZ0IsRUFBQyxNQUFNLGFBQWEsQ0FBQztBQUMzSCxPQUFPLEVBQUMseUJBQXlCLEVBQUMsTUFBTSwrQkFBK0IsQ0FBQztBQUN4RSxPQUFPLEVBQUMsV0FBVyxFQUFXLE1BQU0sZ0JBQWdCLENBQUM7QUFDckQsT0FBTyxFQUFDLGFBQWEsRUFBRSx3QkFBd0IsRUFBRSxXQUFXLEVBQUUsaUJBQWlCLEVBQWlDLG9CQUFvQixFQUFDLE1BQU0sUUFBUSxDQUFDO0FBR3BKOzs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7R0FnREc7QUFDSCxNQUFNLFVBQVUsa0JBQWtCLENBQUMsSUFVbEM7SUFDQyxNQUFNLGdCQUFnQixHQUF1QixVQUN6QyxRQUF5QixFQUFFLFNBQTJCLEVBQUUsTUFBcUI7UUFDL0UsMkZBQTJGO1FBQzNGLHFGQUFxRjtRQUNyRixtQkFBbUI7UUFDbkIsMkZBQTJGO1FBQzNGLGtCQUFrQjtRQUNsQiwwRkFBMEY7UUFDMUYsNEZBQTRGO1FBQzVGLDZCQUE2QjtRQUM3QixNQUFNLGVBQWUsR0FBRyxpQkFBaUIsQ0FBQyxTQUFTLENBQUMsaUJBQXdCLENBQUM7UUFDN0UsTUFBTSxZQUFZLEdBQ2QsQ0FBQyxlQUFlLENBQUMsQ0FBQyxDQUFDLEVBQUUsQ0FBQyxFQUFFLENBQUMs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QUFHLElBQUssQ0FBQztRQUVyQix1QkFBdUI7UUFDdkIsS0FBSyxDQUFDLE9BQU8sQ0FBQyxRQUFRLENBQUMsQ0FBQztJQUMxQixDQUFDO0NBQ0YiLCJzb3VyY2VzQ29udGVudCI6WyIvKipcbiAqIEBsaWNlbnNlXG4gKiBDb3B5cmlnaHQgR29vZ2xlIExMQyBBbGwgUmlnaHRzIFJlc2VydmVkLlxuICpcbiAqIFVzZSBvZiB0aGlzIHNvdXJjZSBjb2RlIGlzIGdvdmVybmVkIGJ5IGFuIE1JVC1zdHlsZSBsaWNlbnNlIHRoYXQgY2FuIGJlXG4gKiBmb3VuZCBpbiB0aGUgTElDRU5TRSBmaWxlIGF0IGh0dHBzOi8vYW5ndWxhci5pby9saWNlbnNlXG4gKi9cblxuaW1wb3J0IHtDb21wb25lbnRGYWN0b3J5LCBDb21wb25lbnRGYWN0b3J5UmVzb2x2ZXIsIEluamVjdG9yLCBOZ1pvbmUsIFR5cGV9IGZyb20gJ0Bhbmd1bGFyL2NvcmUnO1xuXG5pbXBvcnQge0lBbm5vdGF0ZWRGdW5jdGlvbiwgSUF0dHJpYnV0ZXMsIElBdWdtZW50ZWRKUXVlcnksIElDb21waWxlU2VydmljZSwgSURpcmVjdGl2ZSwgSUluamVjdG9yU2VydmljZSwgSU5nTW9kZWxDb250cm9sbGVyLCBJUGFyc2VTZXJ2aWNlLCBJU2NvcGV9IGZyb20gJy4vYW5ndWxhcjEnO1xuaW1wb3J0IHskQ09NUElMRSwgJElOSkVDVE9SLCAkUEFSU0UsIElOSkVDVE9SX0tFWSwgTEFaWV9NT0RVTEVfUkVGLCBSRVFVSVJFX0lOSkVDVE9SLCBSRVFVSVJFX05HX01PREVMfSBmcm9tICcuL2NvbnN0YW50cyc7XG5pbXBvcnQge0Rvd25ncmFkZUNvbXBvbmVudEFkYXB0ZXJ9IGZyb20gJy4vZG93bmdyYWRlX2NvbXBvbmVudF9hZGFwdGVyJztcbmltcG9ydCB7U3luY1Byb21pc2UsIFRoZW5hYmxlfSBmcm9tICcuL3Byb21pc2VfdXRpbCc7XG5pbXBvcnQge2NvbnRyb2xsZXJLZXksIGdldERvd25ncmFkZWRNb2R1bGVDb3VudCwgZ2V0VHlwZU5hbWUsIGdldFVwZ3JhZGVBcHBUeXBlLCBMYXp5TW9kdWxlUmVmLCBVcGdyYWRlQXBwVHlwZSwgdmFsaWRhdGVJbmplY3Rpb25LZXl9IGZyb20gJy4vdXRpbCc7XG5cblxuLyoqXG4gKiBAZGVzY3JpcHRpb25cbiAqXG4gKiBBIGhlbHBlciBmdW5jdGlvbiB0aGF0IGFsbG93cyBhbiBBbmd1bGFyIGNvbXBvbmVudCB0byBiZSB1c2VkIGZyb20gQW5ndWxhckpTLlxuICpcbiAqICpQYXJ0IG9mIHRoZSBbdXBncmFkZS9zdGF0aWNdKGFwaT9xdWVyeT11cGdyYWRlJTJGc3RhdGljKVxuICogbGlicmFyeSBmb3IgaHlicmlkIHVwZ3JhZGUgYXBwcyB0aGF0IHN1cHBvcnQgQU9UIGNvbXBpbGF0aW9uKlxuICpcbiAqIFRoaXMgaGVscGVyIGZ1bmN0aW9uIHJldHVybnMgYSBmYWN0b3J5IGZ1bmN0aW9uIHRvIGJlIHVzZWQgZm9yIHJlZ2lzdGVyaW5nXG4gKiBhbiBBbmd1bGFySlMgd3JhcHBlciBkaXJlY3RpdmUgZm9yIFwiZG93bmdyYWRpbmdcIiBhbiBBbmd1bGFyIGNvbXBvbmVudC5cbiAqXG4gKiBAdXNhZ2VOb3Rlc1xuICogIyMjIEV4YW1wbGVzXG4gKlxuICogTGV0J3MgYXNzdW1lIHRoYXQgeW91IGhhdmUgYW4gQW5ndWxhciBjb21wb25lbnQgY2FsbGVkIGBuZzJIZXJvZXNgIHRoYXQgbmVlZHNcbiAqIHRvIGJlIG1hZGUgYXZhaWxhYmxlIGluIEFuZ3VsYXJKUyB0ZW1wbGF0ZXMuXG4gKlxuICoge0BleGFtcGxlIHVwZ3JhZGUvc3RhdGljL3RzL2Z1bGwvbW9kdWxlLnRzIHJlZ2lvbj1cIm5nMi1oZXJvZXNcIn1cbiAqXG4gKiBXZSBtdXN0IGNyZWF0ZSBhbiBBbmd1bGFySlMgW2RpcmVjdGl2ZV0oaHR0cHM6Ly9kb2NzLmFuZ3VsYXJqcy5vcmcvZ3VpZGUvZGlyZWN0aXZlKVxuICogdGhhdCB3aWxsIG1ha2UgdGhpcyBBbmd1bGFyIGNvbXBvbmVudCBhdmFpbGFibGUgaW5zaWRlIEFuZ3VsYXJKUyB0ZW1wbGF0ZXMuXG4gKiBUaGUgYGRvd25ncmFkZUNvbXBvbmVudCgpYCBmdW5jdGlvbiByZXR1cm5zIGEgZmFjdG9yeSBmdW5jdGlvbiB0aGF0IHdlXG4gKiBjYW4gdXNlIHRvIGRlZmluZSB0aGUgQW5ndWxhckpTIGRpcmVjdGl2ZSB0aGF0IHdyYXBzIHRoZSBcImRvd25ncmFkZWRcIiBjb21wb25lbnQuXG4gKlxuICoge0BleGFtcGxlIHVwZ3JhZGUvc3RhdGljL3RzL2Z1bGwvbW9kdWxlLnRzIHJlZ2lvbj1cIm5nMi1oZXJvZXMtd3JhcHBlclwifVxuICpcbiAqIEZvciBtb3JlIGRldGFpbHMgYW5kIGV4YW1wbGVzIG9uIGRvd25ncmFkaW5nIEFuZ3VsYXIgY29tcG9uZW50cyB0byBBbmd1bGFySlMgY29tcG9uZW50cyBwbGVhc2VcbiAqIHZpc2l0IHRoZSBbVXBncmFkZSBndWlkZV0oZ3VpZGUvdXBncmFkZSN1c2luZy1hbmd1bGFyLWNvbXBvbmVudHMtZnJvbS1hbmd1bGFyanMtY29kZSkuXG4gKlxuICogQHBhcmFtIGluZm8gY29udGFpbnMgaW5mb3JtYXRpb24gYWJvdXQgdGhlIENvbXBvbmVudCB0aGF0IGlzIGJlaW5nIGRvd25ncmFkZWQ6XG4gKlxuICogLSBgY29tcG9uZW50OiBUeXBlPGFueT5gOiBUaGUgdHlwZSBvZiB0aGUgQ29tcG9uZW50IHRoYXQgd2lsbCBiZSBkb3duZ3JhZGVkXG4gKiAtIGBkb3duZ3JhZGVkTW9kdWxlPzogc3RyaW5nYDogVGhlIG5hbWUgb2YgdGhlIGRvd25ncmFkZWQgbW9kdWxlIChpZiBhbnkpIHRoYXQgdGhlIGNvbXBvbmVudFxuICogICBcImJlbG9uZ3MgdG9cIiwgYXMgcmV0dXJuZWQgYnkgYSBjYWxsIHRvIGBkb3duZ3JhZGVNb2R1bGUoKWAuIEl0IGlzIHRoZSBtb2R1bGUsIHdob3NlXG4gKiAgIGNvcnJlc3BvbmRpbmcgQW5ndWxhciBtb2R1bGUgd2lsbCBiZSBib290c3RyYXBwZWQsIHdoZW4gdGhlIGNvbXBvbmVudCBuZWVkcyB0byBiZSBpbnN0YW50aWF0ZWQuXG4gKiAgIDxiciAvPlxuICogICAoVGhpcyBvcHRpb24gaXMgb25seSBuZWNlc3Nhcnkgd2hlbiB1c2luZyBgZG93bmdyYWRlTW9kdWxlKClgIHRvIGRvd25ncmFkZSBtb3JlIHRoYW4gb25lXG4gKiAgIEFuZ3VsYXIgbW9kdWxlLilcbiAqIC0gYHByb3BhZ2F0ZURpZ2VzdD86IGJvb2xlYW5gOiBXaGV0aGVyIHRvIHBlcmZvcm0ge0BsaW5rIENoYW5nZURldGVjdG9yUmVmI2RldGVjdENoYW5nZXNcbiAqICAgY2hhbmdlIGRldGVjdGlvbn0gb24gdGhlIGNvbXBvbmVudCBvbiBldmVyeVxuICogICBbJGRpZ2VzdF0oaHR0cHM6Ly9kb2NzLmFuZ3VsYXJqcy5vcmcvYXBpL25nL3R5cGUvJHJvb3RTY29wZS5TY29wZSMkZGlnZXN0KS4gSWYgc2V0IHRvIGBmYWxzZWAsXG4gKiAgIGNoYW5nZSBkZXRlY3Rpb24gd2lsbCBzdGlsbCBiZSBwZXJmb3JtZWQgd2hlbiBhbnkgb2YgdGhlIGNvbXBvbmVudCdzIGlucHV0cyBjaGFuZ2VzLlxuICogICAoRGVmYXVsdDogdHJ1ZSlcbiAqXG4gKiBAcmV0dXJucyBhIGZhY3RvcnkgZnVuY3Rpb24gdGhhdCBjYW4gYmUgdXNlZCB0byByZWdpc3RlciB0aGUgY29tcG9uZW50IGluIGFuXG4gKiBBbmd1bGFySlMgbW9kdWxlLlxuICpcbiAqIEBwdWJsaWNBcGlcbiAqL1xuZXhwb3J0IGZ1bmN0aW9uIGRvd25ncmFkZUNvbXBvbmVudChpbmZvOiB7XG4gIGNvbXBvbmVudDogVHlwZTxhbnk+O1xuICBkb3duZ3JhZGVkTW9kdWxlPzogc3RyaW5nO1xuICBwcm9wYWdhdGVEaWdlc3Q/OiBib29sZWFuO1xuICAvKiogQGRlcHJlY2F0ZWQgc2luY2UgdjQuIFRoaXMgcGFyYW1ldGVyIGlzIG5vIGxvbmdlciB1c2VkICovXG4gIGlucHV0cz86IHN0cmluZ1tdO1xuICAvKiogQGRlcHJlY2F0ZWQgc2luY2UgdjQuIFRoaXMgcGFyYW1ldGVyIGlzIG5vIGxvbmdlciB1c2VkICovXG4gIG91dHB1dHM/OiBzdHJpbmdbXTtcbiAgLyoqIEBkZXByZWNhdGVkIHNpbmNlIHY0LiBUaGlzIHBhcmFtZXRlciBpcyBubyBsb25nZXIgdXNlZCAqL1xuICBzZWxlY3RvcnM/OiBzdHJpbmdbXTtcbn0pOiBhbnkgLyogYW5ndWxhci5JSW5qZWN0YWJsZSAqLyB7XG4gIGNvbnN0IGRpcmVjdGl2ZUZhY3Rvcnk6IElBbm5vdGF0ZWRGdW5jdGlvbiA9IGZ1bmN0aW9uKFxuICAgICAgJGNvbXBpbGU6IElDb21waWxlU2VydmljZSwgJGluamVjdG9yOiBJSW5qZWN0b3JTZXJ2aWNlLCAkcGFyc2U6IElQYXJzZVNlcnZpY2UpOiBJRGlyZWN0aXZlIHtcbiAgICAvLyBXaGVuIHVzaW5nIGBkb3duZ3JhZGVNb2R1bGUoKWAsIHdlIG5lZWQgdG8gaGFuZGxlIGNlcnRhaW4gdGhpbmdzIHNwZWNpYWxseS4gRm9yIGV4YW1wbGU6XG4gICAgLy8gLSBXZSBhbHdheXMgbmVlZCB0byBhdHRhY2ggdGhlIGNvbXBvbmVudCB2aWV3IHRvIHRoZSBgQXBwbGljYXRpb25SZWZgIGZvciBpdCB0byBiZVxuICAgIC8vICAgZGlydHktY2hlY2tlZC5cbiAgICAvLyAtIFdlIG5lZWQgdG8gZW5zdXJlIGNhbGxiYWNrcyB0byBBbmd1bGFyIEFQSXMgKGUuZy4gY2hhbmdlIGRldGVjdGlvbikgYXJlIHJ1biBpbnNpZGUgdGhlXG4gICAgLy8gICBBbmd1bGFyIHpvbmUuXG4gICAgLy8gICBOT1RFOiBUaGlzIGlzIG5vdCBuZWVkZWQsIHdoZW4gdXNpbmcgYFVwZ3JhZGVNb2R1bGVgLCBiZWNhdXNlIGAkZGlnZXN0KClgIHdpbGwgYmUgcnVuXG4gICAgLy8gICAgICAgICBpbnNpZGUgdGhlIEFuZ3VsYXIgem9uZSAoZXhjZXB0IGlmIGV4cGxpY2l0bHkgZXNjYXBlZCwgaW4gd2hpY2ggY2FzZSB3ZSBzaG91bGRuJ3RcbiAgICAvLyAgICAgICAgIGZvcmNlIGl0IGJhY2sgaW4pLlxuICAgIGNvbnN0IGlzTmdVcGdyYWRlTGl0ZSA9IGdldFVwZ3JhZGVBcHBUeXBlKCRpbmplY3RvcikgPT09IFVwZ3JhZGVBcHBUeXBlLkxpdGU7XG4gICAgY29uc3Qgd3JhcENhbGxiYWNrOiA8VD4oY2I6ICgpID0+IFQpID0+IHR5cGVvZiBjYiA9XG4gICAgICAgICFpc05nVXBncmFkZUxpdGUgPyBjYiA9PiBjYiA6IGNiID0+ICgpID0+IE5nWm9uZS5pc0luQW5ndWxhclpvbmUoKSA/IGNiKCkgOiBuZ1pvbmUucnVuKGNiKTtcbiAgICBsZXQgbmdab25lOiBOZ1pvbmU7XG5cbiAgICAvLyBXaGVuIGRvd25ncmFkaW5nIG11bHRpcGxlIG1vZHVsZXMsIHNwZWNpYWwgaGFuZGxpbmcgaXMgbmVlZGVkIHdydCBpbmplY3RvcnMuXG4gICAgY29uc3QgaGFzTXVsdGlwbGVEb3duZ3JhZGVkTW9kdWxlcyA9XG4gICAgICAgIGlzTmdVcGdyYWRlTGl0ZSAmJiAoZ2V0RG93bmdyYWRlZE1vZHVsZUNvdW50KCRpbmplY3RvcikgPiAxKTtcblxuICAgIHJldHVybiB7XG4gICAgICByZXN0cmljdDogJ0UnLFxuICAgICAgdGVybWluYWw6IHRydWUsXG4gICAgICByZXF1aXJlOiBbUkVRVUlSRV9JTkpFQ1RPUiwgUkVRVUlSRV9OR19NT0RFTF0sXG4gICAgICBsaW5rOiAoc2NvcGU6IElTY29wZSwgZWxlbWVudDogSUF1Z21lbnRlZEpRdWVyeSwgYXR0cnM6IElBdHRyaWJ1dGVzLCByZXF1aXJlZDogYW55W10pID0+IHtcbiAgICAgICAgLy8gV2UgbWlnaHQgaGF2ZSB0byBjb21waWxlIHRoZSBjb250ZW50cyBhc3luY2hyb25vdXNseSwgYmVjYXVzZSB0aGlzIG1pZ2h0IGhhdmUgYmVlblxuICAgICAgICAvLyB0cmlnZ2VyZWQgYnkgYFVwZ3JhZGVOZzFDb21wb25lbnRBZGFwdGVyQnVpbGRlcmAsIGJlZm9yZSB0aGUgQW5ndWxhciB0ZW1wbGF0ZXMgaGF2ZVxuICAgICAgICAvLyBiZWVuIGNvbXBpbGVkLlxuXG4gICAgICAgIGNvbnN0IG5nTW9kZWw6IElOZ01vZGVsQ29udHJvbGxlciA9IHJlcXVpcmVkWzFdO1xuICAgICAgICBjb25zdCBwYXJlbnRJbmplY3RvcjogSW5qZWN0b3J8VGhlbmFibGU8SW5qZWN0b3I+fHVuZGVmaW5lZCA9IHJlcXVpcmVkWzBdO1xuICAgICAgICBsZXQgbW9kdWxlSW5qZWN0b3I6IEluamVjdG9yfFRoZW5hYmxlPEluamVjdG9yPnx1bmRlZmluZWQgPSB1bmRlZmluZWQ7XG4gICAgICAgIGxldCByYW5Bc3luYyA9IGZhbHNlO1xuXG4gICAgICAgIGlmICghcGFyZW50SW5qZWN0b3IgfHwgaGFzTXVsdGlwbGVEb3duZ3JhZGVkTW9kdWxlcykge1xuICAgICAgICAgIGNvbnN0IGRvd25ncmFkZWRNb2R1bGUgPSBpbmZvLmRvd25ncmFkZWRNb2R1bGUgfHwgJyc7XG4gICAgICAgICAgY29uc3QgbGF6eU1vZHVsZVJlZktleSA9IGAke0xBWllfTU9EVUxFX1JFRn0ke2Rvd25ncmFkZWRNb2R1bGV9YDtcbiAgICAgICAgICBjb25zdCBhdHRlbXB0ZWRBY3Rpb24gPSBgaW5zdGFudGlhdGluZyBjb21wb25lbnQgJyR7Z2V0VHlwZU5hbWUoaW5mby5jb21wb25lbnQpfSdgO1xuXG4gICAgICAgICAgdmFsaWRhdGVJbmplY3Rpb25LZXkoJGluamVjdG9yLCBkb3duZ3JhZGVkTW9kdWxlLCBsYXp5TW9kdWxlUmVmS2V5LCBhdHRlbXB0ZWRBY3Rpb24pO1xuXG4gICAgICAgICAgY29uc3QgbGF6eU1vZHVsZVJlZiA9ICRpbmplY3Rvci5nZXQobGF6eU1vZHVsZVJlZktleSkgYXMgTGF6eU1vZHVsZVJlZjtcbiAgICAgICAgICBtb2R1bGVJbmplY3RvciA9IGxhenlNb2R1bGVSZWYuaW5qZWN0b3IgfHwgbGF6eU1vZHVsZVJlZi5wcm9taXNlIGFzIFByb21pc2U8SW5qZWN0b3I+O1xuICAgICAgICB9XG5cbiAgICAgICAgLy8gTm90ZXM6XG4gICAgICAgIC8vXG4gICAgICAgIC8vIFRoZXJlIGFyZSB0d28gaW5qZWN0b3JzOiBgZmluYWxNb2R1bGVJbmplY3RvcmAgYW5kIGBmaW5hbFBhcmVudEluamVjdG9yYCAodGhleSBtaWdodCBiZVxuICAgICAgICAvLyB0aGUgc2FtZSBpbnN0YW5jZSwgYnV0IHRoYXQgaXMgaXJyZWxldmFudCk6XG4gICAgICAgIC8vIC0gYGZpbmFsTW9kdWxlSW5qZWN0b3JgIGlzIHVzZWQgdG8gcmV0cmlldmUgYENvbXBvbmVudEZhY3RvcnlSZXNvbHZlcmAsIHRodXMgaXQgbXVzdCBiZVxuICAgICAgICAvLyAgIG9uIHRoZSBzYW1lIHRyZWUgYXMgdGhlIGBOZ01vZHVsZWAgdGhhdCBkZWNsYXJlcyB0aGlzIGRvd25ncmFkZWQgY29tcG9uZW50LlxuICAgICAgICAvLyAtIGBmaW5hbFBhcmVudEluamVjdG9yYCBpcyB1c2VkIGZvciBhbGwgb3RoZXIgaW5qZWN0aW9uIHB1cnBvc2VzLlxuICAgICAgICAvLyAgIChOb3RlIHRoYXQgQW5ndWxhciBrbm93cyB0byBvbmx5IHRyYXZlcnNlIHRoZSBjb21wb25lbnQtdHJlZSBwYXJ0IG9mIHRoYXQgaW5qZWN0b3IsXG4gICAgICAgIC8vICAgd2hlbiBsb29raW5nIGZvciBhbiBpbmplY3RhYmxlIGFuZCB0aGVuIHN3aXRjaCB0byB0aGUgbW9kdWxlIGluamVjdG9yLilcbiAgICAgICAgLy9cbiAgICAgICAgLy8gVGhlcmUgYXJlIGJhc2ljYWxseSB0aHJlZSBjYXNlczpcbiAgICAgICAgLy8gLSBJZiB0aGVyZSBpcyBubyBwYXJlbnQgY29tcG9uZW50ICh0aHVzIG5vIGBwYXJlbnRJbmplY3RvcmApLCB3ZSBib290c3RyYXAgdGhlIGRvd25ncmFkZWRcbiAgICAgICAgLy8gICBgTmdNb2R1bGVgIGFuZCB1c2UgaXRzIGluamVjdG9yIGFzIGJvdGggYGZpbmFsTW9kdWxlSW5qZWN0b3JgIGFuZFxuICAgICAgICAvLyAgIGBmaW5hbFBhcmVudEluamVjdG9yYC5cbiAgICAgICAgLy8gLSBJZiB0aGVyZSBpcyBhIHBhcmVudCBjb21wb25lbnQgKGFuZCB0aHVzIGEgYHBhcmVudEluamVjdG9yYCkgYW5kIHdlIGFyZSBzdXJlIHRoYXQgaXRcbiAgICAgICAgLy8gICBiZWxvbmdzIHRvIHRoZSBzYW1lIGBOZ01vZHVsZWAgYXMgdGhpcyBkb3duZ3JhZGVkIGNvbXBvbmVudCAoZS5nLiBiZWNhdXNlIHRoZXJlIGlzIG9ubHlcbiAgICAgICAgLy8gICBvbmUgZG93bmdyYWRlZCBtb2R1bGUsIHdlIHVzZSB0aGF0IGBwYXJlbnRJbmplY3RvcmAgYXMgYm90aCBgZmluYWxNb2R1bGVJbmplY3RvcmAgYW5kXG4gICAgICAgIC8vICAgYGZpbmFsUGFyZW50SW5qZWN0b3JgLlxuICAgICAgICAvLyAtIElmIHRoZXJlIGlzIGEgcGFyZW50IGNvbXBvbmVudCwgYnV0IGl0IG1heSBiZWxvbmcgdG8gYSBkaWZmZXJlbnQgYE5nTW9kdWxlYCwgdGhlbiB3ZVxuICAgICAgICAvLyAgIHVzZSB0aGUgYHBhcmVudEluamVjdG9yYCBhcyBgZmluYWxQYXJlbnRJbmplY3RvcmAgYW5kIHRoaXMgZG93bmdyYWRlZCBjb21wb25lbnQnc1xuICAgICAgICAvLyAgIGRlY2xhcmluZyBgTmdNb2R1bGVgJ3MgaW5qZWN0b3IgYXMgYGZpbmFsTW9kdWxlSW5qZWN0b3JgLlxuICAgICAgICAvLyAgIE5vdGUgMTogSWYgdGhlIGBOZ01vZHVsZWAgaXMgYWxyZWFkeSBib290c3RyYXBwZWQsIHdlIGp1c3QgZ2V0IGl0cyBpbmplY3RvciAod2UgZG9uJ3RcbiAgICAgICAgLy8gICAgICAgICAgIGJvb3RzdHJhcCBhZ2FpbikuXG4gICAgICAgIC8vICAgTm90ZSAyOiBJdCBpcyBwb3NzaWJsZSB0aGF0ICh3aGlsZSB0aGVyZSBhcmUgbXVsdGlwbGUgZG93bmdyYWRlZCBtb2R1bGVzKSB0aGlzXG4gICAgICAgIC8vICAgICAgICAgICBkb3duZ3JhZGVkIGNvbXBvbmVudCBhbmQgaXRzIHBhcmVudCBjb21wb25lbnQgYm90aCBiZWxvbmcgdG8gdGhlIHNhbWUgTmdNb2R1bGUuXG4gICAgICAgIC8vICAgICAgICAgICBJbiB0aGF0IGNhc2UsIHdlIGNvdWxkIGhhdmUgdXNlZCB0aGUgYHBhcmVudEluamVjdG9yYCBhcyBib3RoXG4gICAgICAgIC8vICAgICAgICAgICBgZmluYWxNb2R1bGVJbmplY3RvcmAgYW5kIGBmaW5hbFBhcmVudEluamVjdG9yYCwgYnV0IChmb3Igc2ltcGxpY2l0eSkgd2UgYXJlXG4gICAgICAgIC8vICAgICAgICAgICB0cmVhdGluZyB0aGlzIGNhc2UgYXMgaWYgdGhleSBiZWxvbmcgdG8gZGlmZmVyZW50IGBOZ01vZHVsZWBzLiBUaGF0IGRvZXNuJ3RcbiAgICAgICAgLy8gICAgICAgICAgIHJlYWxseSBhZmZlY3QgYW55dGhpbmcsIHNpbmNlIGBwYXJlbnRJbmplY3RvcmAgaGFzIGBtb2R1bGVJbmplY3RvcmAgYXMgYW5jZXN0b3JcbiAgICAgICAgLy8gICAgICAgICAgIGFuZCB0cnlpbmcgdG8gcmVzb2x2ZSBgQ29tcG9uZW50RmFjdG9yeVJlc29sdmVyYCBmcm9tIGVpdGhlciBvbmUgd2lsbCByZXR1cm5cbiAgICAgICAgLy8gICAgICAgICAgIHRoZSBzYW1lIGluc3RhbmNlLlxuXG4gICAgICAgIC8vIElmIHRoZXJlIGlzIGEgcGFyZW50IGNvbXBvbmVudCwgdXNlIGl0cyBpbmplY3RvciBhcyBwYXJlbnQgaW5qZWN0b3IuXG4gICAgICAgIC8vIElmIHRoaXMgaXMgYSBcInRvcC1sZXZlbFwiIEFuZ3VsYXIgY29tcG9uZW50LCB1c2UgdGhlIG1vZHVsZSBpbmplY3Rvci5cbiAgICAgICAgY29uc3QgZmluYWxQYXJlbnRJbmplY3RvciA9IHBhcmVudEluamVjdG9yIHx8IG1vZHVsZUluamVjdG9yITtcblxuICAgICAgICAvLyBJZiB0aGlzIGlzIGEgXCJ0b3AtbGV2ZWxcIiBBbmd1bGFyIGNvbXBvbmVudCBvciB0aGUgcGFyZW50IGNvbXBvbmVudCBtYXkgYmVsb25nIHRvIGFcbiAgICAgICAgLy8gZGlmZmVyZW50IGBOZ01vZHVsZWAsIHVzZSB0aGUgbW9kdWxlIGluamVjdG9yIGZvciBtb2R1bGUtc3BlY2lmaWMgZGVwZW5kZW5jaWVzLlxuICAgICAgICAvLyBJZiB0aGVyZSBpcyBhIHBhcmVudCBjb21wb25lbnQgdGhhdCBiZWxvbmdzIHRvIHRoZSBzYW1lIGBOZ01vZHVsZWAsIHVzZSBpdHMgaW5qZWN0b3IuXG4gICAgICAgIGNvbnN0IGZpbmFsTW9kdWxlSW5qZWN0b3IgPSBtb2R1bGVJbmplY3RvciB8fCBwYXJlbnRJbmplY3RvciE7XG5cbiAgICAgICAgY29uc3QgZG9Eb3duZ3JhZGUgPSAoaW5qZWN0b3I6IEluamVjdG9yLCBtb2R1bGVJbmplY3RvcjogSW5qZWN0b3IpID0+IHtcbiAgICAgICAgICAvLyBSZXRyaWV2ZSBgQ29tcG9uZW50RmFjdG9yeVJlc29sdmVyYCBmcm9tIHRoZSBpbmplY3RvciB0aWVkIHRvIHRoZSBgTmdNb2R1bGVgIHRoaXNcbiAgICAgICAgICAvLyBjb21wb25lbnQgYmVsb25ncyB0by5cbiAgICAgICAgICBjb25zdCBjb21wb25lbnRGYWN0b3J5UmVzb2x2ZXI6IENvbXBvbmVudEZhY3RvcnlSZXNvbHZlciA9XG4gICAgICAgICAgICAgIG1vZHVsZUluamVjdG9yLmdldChDb21wb25lbnRGYWN0b3J5UmVzb2x2ZXIpO1xuICAgICAgICAgIGNvbnN0IGNvbXBvbmVudEZhY3Rvcnk6IENvbXBvbmVudEZhY3Rvcnk8YW55PiA9XG4gICAgICAgICAgICAgIGNvbXBvbmVudEZhY3RvcnlSZXNvbHZlci5yZXNvbHZlQ29tcG9uZW50RmFjdG9yeShpbmZvLmNvbXBvbmVudCkhO1xuXG4gICAgICAgICAgaWYgKCFjb21wb25lbnRGYWN0b3J5KSB7XG4gICAgICAgICAgICB0aHJvdyBuZXcgRXJyb3IoYEV4cGVjdGluZyBDb21wb25lbnRGYWN0b3J5IGZvcjogJHtnZXRUeXBlTmFtZShpbmZvLmNvbXBvbmVudCl9YCk7XG4gICAgICAgICAgfVxuXG4gICAgICAgICAgY29uc3QgaW5qZWN0b3JQcm9taXNlID0gbmV3IFBhcmVudEluamVjdG9yUHJvbWlzZShlbGVtZW50KTtcbiAgICAgICAgICBjb25zdCBmYWNhZGUgPSBuZXcgRG93bmdyYWRlQ29tcG9uZW50QWRhcHRlcihcbiAgICAgICAgICAgICAgZWxlbWVudCwgYXR0cnMsIHNjb3BlLCBuZ01vZGVsLCBpbmplY3RvciwgJGNvbXBpbGUsICRwYXJzZSwgY29tcG9uZW50RmFjdG9yeSxcbiAgICAgICAgICAgICAgd3JhcENhbGxiYWNrKTtcblxuICAgICAgICAgIGNvbnN0IHByb2plY3RhYmxlTm9kZXMgPSBmYWNhZGUuY29tcGlsZUNvbnRlbnRzKCk7XG4gICAgICAgICAgZmFjYWRlLmNyZWF0ZUNvbXBvbmVudChwcm9qZWN0YWJsZU5vZGVzKTtcbiAgICAgICAgICBmYWNhZGUuc2V0dXBJbnB1dHMoaXNOZ1VwZ3JhZGVMaXRlLCBpbmZvLnByb3BhZ2F0ZURpZ2VzdCk7XG4gICAgICAgICAgZmFjYWRlLnNldHVwT3V0cHV0cygpO1xuICAgICAgICAgIGZhY2FkZS5yZWdpc3RlckNsZWFudXAoKTtcblxuICAgICAgICAgIGluamVjdG9yUHJvbWlzZS5yZXNvbHZlKGZhY2FkZS5nZXRJbmplY3RvcigpKTtcblxuICAgICAgICAgIGlmIChyYW5Bc3luYykge1xuICAgICAgICAgICAgLy8gSWYgdGhpcyBpcyBydW4gYXN5bmMsIGl0IGlzIHBvc3NpYmxlIHRoYXQgaXQgaXMgbm90IHJ1biBpbnNpZGUgYVxuICAgICAgICAgICAgLy8gZGlnZXN0IGFuZCBpbml0aWFsIGlucHV0IHZhbHVlcyB3aWxsIG5vdCBiZSBkZXRlY3RlZC5cbiAgICAgICAgICAgIHNjb3BlLiRldmFsQXN5bmMoKCkgPT4ge30pO1xuICAgICAgICAgIH1cbiAgICAgICAgfTtcblxuICAgICAgICBjb25zdCBkb3duZ3JhZGVGbiA9XG4gICAgICAgICAgICAhaXNOZ1VwZ3JhZGVMaXRlID8gZG9Eb3duZ3JhZGUgOiAocEluamVjdG9yOiBJbmplY3RvciwgbUluamVjdG9yOiBJbmplY3RvcikgPT4ge1xuICAgICAgICAgICAgICBpZiAoIW5nWm9uZSkge1xuICAgICAgICAgICAgICAgIG5nWm9uZSA9IHBJbmplY3Rvci5nZXQoTmdab25lKTtcbiAgICAgICAgICAgICAgfVxuXG4gICAgICAgICAgICAgIHdyYXBDYWxsYmFjaygoKSA9PiBkb0Rvd25ncmFkZShwSW5qZWN0b3IsIG1JbmplY3RvcikpKCk7XG4gICAgICAgICAgICB9O1xuXG4gICAgICAgIC8vIE5PVEU6XG4gICAgICAgIC8vIE5vdCB1c2luZyBgUGFyZW50SW5qZWN0b3JQcm9taXNlLmFsbCgpYCAod2hpY2ggaXMgaW5oZXJpdGVkIGZyb20gYFN5bmNQcm9taXNlYCksIGJlY2F1c2VcbiAgICAgICAgLy8gQ2xvc3VyZSBDb21waWxlciAob3Igc29tZSByZWxhdGVkIHRvb2wpIGNvbXBsYWluczpcbiAgICAgICAgLy8gYFR5cGVFcnJvcjogLi4uJHNyYyRkb3duZ3JhZGVfY29tcG9uZW50X1BhcmVudEluamVjdG9yUHJvbWlzZS5hbGwgaXMgbm90IGEgZnVuY3Rpb25gXG4gICAgICAgIFN5bmNQcm9taXNlLmFsbChbZmluYWxQYXJlbnRJbmplY3RvciwgZmluYWxNb2R1bGVJbmplY3Rvcl0pXG4gICAgICAgICAgICAudGhlbigoW3BJbmplY3RvciwgbUluamVjdG9yXSkgPT4gZG93bmdyYWRlRm4ocEluamVjdG9yLCBtSW5qZWN0b3IpKTtcblxuICAgICAgICByYW5Bc3luYyA9IHRydWU7XG4gICAgICB9XG4gICAgfTtcbiAgfTtcblxuICAvLyBicmFja2V0LW5vdGF0aW9uIGJlY2F1c2Ugb2YgY2xvc3VyZSAtIHNlZSAjMTQ0NDFcbiAgZGlyZWN0aXZlRmFjdG9yeVsnJGluamVjdCddID0gWyRDT01QSUxFLCAkSU5KRUNUT1IsICRQQVJTRV07XG4gIHJldHVybiBkaXJlY3RpdmVGYWN0b3J5O1xufVxuXG4vKipcbiAqIFN5bmNocm9ub3VzIHByb21pc2UtbGlrZSBvYmplY3QgdG8gd3JhcCBwYXJlbnQgaW5qZWN0b3JzLFxuICogdG8gcHJlc2VydmUgdGhlIHN5bmNocm9ub3VzIG5hdHVyZSBvZiBBbmd1bGFySlMncyBgJGNvbXBpbGVgLlxuICovXG5jbGFzcyBQYXJlbnRJbmplY3RvclByb21pc2UgZXh0ZW5kcyBTeW5jUHJvbWlzZTxJbmplY3Rvcj4ge1xuICBwcml2YXRlIGluamVjdG9yS2V5OiBzdHJpbmcgPSBjb250cm9sbGVyS2V5KElOSkVDVE9SX0tFWSk7XG5cbiAgY29uc3RydWN0b3IocHJpdmF0ZSBlbGVtZW50OiBJQXVnbWVudGVkSlF1ZXJ5KSB7XG4gICAgc3VwZXIoKTtcblxuICAgIC8vIFN0b3JlIHRoZSBwcm9taXNlIG9uIHRoZSBlbGVtZW50LlxuICAgIGVsZW1lbnQuZGF0YSEodGhpcy5pbmplY3RvcktleSwgdGhpcyk7XG4gIH1cblxuICByZXNvbHZlKGluamVjdG9yOiBJbmplY3Rvcik6IHZvaWQge1xuICAgIC8vIFN0b3JlIHRoZSByZWFsIGluamVjdG9yIG9uIHRoZSBlbGVtZW50LlxuICAgIHRoaXMuZWxlbWVudC5kYXRhISh0aGlzLmluamVjdG9yS2V5LCBpbmplY3Rvcik7XG5cbiAgICAvLyBSZWxlYXNlIHRoZSBlbGVtZW50IHRvIHByZXZlbnQgbWVtb3J5IGxlYWtzLlxuICAgIHRoaXMuZWxlbWVudCA9IG51bGwhO1xuXG4gICAgLy8gUmVzb2x2ZSB0aGUgcHJvbWlzZS5cbiAgICBzdXBlci5yZXNvbHZlKGluamVjdG9yKTtcbiAgfVxufVxuIl19