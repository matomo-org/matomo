/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
import { PlatformRef } from '@angular/core';
import { platformBrowser } from '@angular/platform-browser';
import { module_ as angularModule } from '../../src/common/src/angular1';
import { $INJECTOR, $PROVIDE, DOWNGRADED_MODULE_COUNT_KEY, INJECTOR_KEY, LAZY_MODULE_REF, UPGRADE_APP_TYPE_KEY, UPGRADE_MODULE_NAME } from '../../src/common/src/constants';
import { destroyApp, getDowngradedModuleCount, isFunction } from '../../src/common/src/util';
import { angular1Providers, setTempInjectorRef } from './angular1_providers';
import { NgAdapterInjector } from './util';
let moduleUid = 0;
/**
 * @description
 *
 * A helper function for creating an AngularJS module that can bootstrap an Angular module
 * "on-demand" (possibly lazily) when a {@link downgradeComponent downgraded component} needs to be
 * instantiated.
 *
 * *Part of the [upgrade/static](api?query=upgrade/static) library for hybrid upgrade apps that
 * support AOT compilation.*
 *
 * It allows loading/bootstrapping the Angular part of a hybrid application lazily and not having to
 * pay the cost up-front. For example, you can have an AngularJS application that uses Angular for
 * specific routes and only instantiate the Angular modules if/when the user visits one of these
 * routes.
 *
 * The Angular module will be bootstrapped once (when requested for the first time) and the same
 * reference will be used from that point onwards.
 *
 * `downgradeModule()` requires either an `NgModuleFactory` or a function:
 * - `NgModuleFactory`: If you pass an `NgModuleFactory`, it will be used to instantiate a module
 *   using `platformBrowser`'s {@link PlatformRef#bootstrapModuleFactory bootstrapModuleFactory()}.
 * - `Function`: If you pass a function, it is expected to return a promise resolving to an
 *   `NgModuleRef`. The function is called with an array of extra {@link StaticProvider Providers}
 *   that are expected to be available from the returned `NgModuleRef`'s `Injector`.
 *
 * `downgradeModule()` returns the name of the created AngularJS wrapper module. You can use it to
 * declare a dependency in your main AngularJS module.
 *
 * {@example upgrade/static/ts/lite/module.ts region="basic-how-to"}
 *
 * For more details on how to use `downgradeModule()` see
 * [Upgrading for Performance](guide/upgrade-performance).
 *
 * @usageNotes
 *
 * Apart from `UpgradeModule`, you can use the rest of the `upgrade/static` helpers as usual to
 * build a hybrid application. Note that the Angular pieces (e.g. downgraded services) will not be
 * available until the downgraded module has been bootstrapped, i.e. by instantiating a downgraded
 * component.
 *
 * <div class="alert is-important">
 *
 *   You cannot use `downgradeModule()` and `UpgradeModule` in the same hybrid application.<br />
 *   Use one or the other.
 *
 * </div>
 *
 * ### Differences with `UpgradeModule`
 *
 * Besides their different API, there are two important internal differences between
 * `downgradeModule()` and `UpgradeModule` that affect the behavior of hybrid applications:
 *
 * 1. Unlike `UpgradeModule`, `downgradeModule()` does not bootstrap the main AngularJS module
 *    inside the {@link NgZone Angular zone}.
 * 2. Unlike `UpgradeModule`, `downgradeModule()` does not automatically run a
 *    [$digest()](https://docs.angularjs.org/api/ng/type/$rootScope.Scope#$digest) when changes are
 *    detected in the Angular part of the application.
 *
 * What this means is that applications using `UpgradeModule` will run change detection more
 * frequently in order to ensure that both frameworks are properly notified about possible changes.
 * This will inevitably result in more change detection runs than necessary.
 *
 * `downgradeModule()`, on the other side, does not try to tie the two change detection systems as
 * tightly, restricting the explicit change detection runs only to cases where it knows it is
 * necessary (e.g. when the inputs of a downgraded component change). This improves performance,
 * especially in change-detection-heavy applications, but leaves it up to the developer to manually
 * notify each framework as needed.
 *
 * For a more detailed discussion of the differences and their implications, see
 * [Upgrading for Performance](guide/upgrade-performance).
 *
 * <div class="alert is-helpful">
 *
 *   You can manually trigger a change detection run in AngularJS using
 *   [scope.$apply(...)](https://docs.angularjs.org/api/ng/type/$rootScope.Scope#$apply) or
 *   [$rootScope.$digest()](https://docs.angularjs.org/api/ng/type/$rootScope.Scope#$digest).
 *
 *   You can manually trigger a change detection run in Angular using {@link NgZone#run
 *   ngZone.run(...)}.
 *
 * </div>
 *
 * ### Downgrading multiple modules
 *
 * It is possible to downgrade multiple modules and include them in an AngularJS application. In
 * that case, each downgraded module will be bootstrapped when an associated downgraded component or
 * injectable needs to be instantiated.
 *
 * Things to keep in mind, when downgrading multiple modules:
 *
 * - Each downgraded component/injectable needs to be explicitly associated with a downgraded
 *   module. See `downgradeComponent()` and `downgradeInjectable()` for more details.
 *
 * - If you want some injectables to be shared among all downgraded modules, you can provide them as
 *   `StaticProvider`s, when creating the `PlatformRef` (e.g. via `platformBrowser` or
 *   `platformBrowserDynamic`).
 *
 * - When using {@link PlatformRef#bootstrapmodule `bootstrapModule()`} or
 *   {@link PlatformRef#bootstrapmodulefactory `bootstrapModuleFactory()`} to bootstrap the
 *   downgraded modules, each one is considered a "root" module. As a consequence, a new instance
 *   will be created for every injectable provided in `"root"` (via
 *   {@link Injectable#providedIn `providedIn`}).
 *   If this is not your intention, you can have a shared module (that will act as act as the "root"
 *   module) and create all downgraded modules using that module's injector:
 *
 *   {@example upgrade/static/ts/lite-multi-shared/module.ts region="shared-root-module"}
 *
 * @publicApi
 */
export function downgradeModule(moduleFactoryOrBootstrapFn) {
    const lazyModuleName = `${UPGRADE_MODULE_NAME}.lazy${++moduleUid}`;
    const lazyModuleRefKey = `${LAZY_MODULE_REF}${lazyModuleName}`;
    const lazyInjectorKey = `${INJECTOR_KEY}${lazyModuleName}`;
    const bootstrapFn = isFunction(moduleFactoryOrBootstrapFn) ?
        moduleFactoryOrBootstrapFn :
        (extraProviders) => platformBrowser(extraProviders).bootstrapModuleFactory(moduleFactoryOrBootstrapFn);
    let injector;
    // Create an ng1 module to bootstrap.
    angularModule(lazyModuleName, [])
        .constant(UPGRADE_APP_TYPE_KEY, 3 /* Lite */)
        .factory(INJECTOR_KEY, [lazyInjectorKey, identity])
        .factory(lazyInjectorKey, () => {
        if (!injector) {
            throw new Error('Trying to get the Angular injector before bootstrapping the corresponding ' +
                'Angular module.');
        }
        return injector;
    })
        .factory(LAZY_MODULE_REF, [lazyModuleRefKey, identity])
        .factory(lazyModuleRefKey, [
        $INJECTOR,
        ($injector) => {
            setTempInjectorRef($injector);
            const result = {
                promise: bootstrapFn(angular1Providers).then(ref => {
                    injector = result.injector = new NgAdapterInjector(ref.injector);
                    injector.get($INJECTOR);
                    // Destroy the AngularJS app once the Angular `PlatformRef` is destroyed.
                    // This does not happen in a typical SPA scenario, but it might be useful for
                    // other use-cases where disposing of an Angular/AngularJS app is necessary
                    // (such as Hot Module Replacement (HMR)).
                    // See https://github.com/angular/angular/issues/39935.
                    injector.get(PlatformRef).onDestroy(() => destroyApp($injector));
                    return injector;
                })
            };
            return result;
        }
    ])
        .config([
        $INJECTOR, $PROVIDE,
        ($injector, $provide) => {
            $provide.constant(DOWNGRADED_MODULE_COUNT_KEY, getDowngradedModuleCount($injector) + 1);
        }
    ]);
    return lazyModuleName;
}
function identity(x) {
    return x;
}
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiZG93bmdyYWRlX21vZHVsZS5qcyIsInNvdXJjZVJvb3QiOiIiLCJzb3VyY2VzIjpbIi4uLy4uLy4uLy4uLy4uLy4uLy4uL3BhY2thZ2VzL3VwZ3JhZGUvc3RhdGljL3NyYy9kb3duZ3JhZGVfbW9kdWxlLnRzIl0sIm5hbWVzIjpbXSwibWFwcGluZ3MiOiJBQUFBOzs7Ozs7R0FNRztBQUVILE9BQU8sRUFBeUMsV0FBVyxFQUFpQixNQUFNLGVBQWUsQ0FBQztBQUNsRyxPQUFPLEVBQUMsZUFBZSxFQUFDLE1BQU0sMkJBQTJCLENBQUM7QUFFMUQsT0FBTyxFQUFvQyxPQUFPLElBQUksYUFBYSxFQUFDLE1BQU0sK0JBQStCLENBQUM7QUFDMUcsT0FBTyxFQUFDLFNBQVMsRUFBRSxRQUFRLEVBQUUsMkJBQTJCLEVBQUUsWUFBWSxFQUFFLGVBQWUsRUFBRSxvQkFBb0IsRUFBRSxtQkFBbUIsRUFBQyxNQUFNLGdDQUFnQyxDQUFDO0FBQzFLLE9BQU8sRUFBQyxVQUFVLEVBQUUsd0JBQXdCLEVBQUUsVUFBVSxFQUFnQyxNQUFNLDJCQUEyQixDQUFDO0FBRTFILE9BQU8sRUFBQyxpQkFBaUIsRUFBRSxrQkFBa0IsRUFBQyxNQUFNLHNCQUFzQixDQUFDO0FBQzNFLE9BQU8sRUFBQyxpQkFBaUIsRUFBQyxNQUFNLFFBQVEsQ0FBQztBQUd6QyxJQUFJLFNBQVMsR0FBRyxDQUFDLENBQUM7QUFFbEI7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7OztHQTRHRztBQUNILE1BQU0sVUFBVSxlQUFlLENBQUksMEJBQytCO0lBQ2hFLE1BQU0sY0FBYyxHQUFHLEdBQUcsbUJBQW1CLFFBQVEsRUFBRSxTQUFTLEVBQUUsQ0FBQztJQUNuRSxNQUFNLGdCQUFnQixHQUFHLEdBQUcsZUFBZSxHQUFHLGNBQWMsRUFBRSxDQUFDO0lBQy9ELE1BQU0sZUFBZSxHQUFHLEdBQUcsWUFBWSxHQUFHLGNBQWMsRUFBRSxDQUFDO0lBRTNELE1BQU0sV0FBVyxHQUFHLFVBQVUsQ0FBQywwQkFBMEIsQ0FBQyxDQUFDLENBQUM7UUFDeEQsMEJBQTBCLENBQUMsQ0FBQztRQUM1QixDQUFDLGNBQWdDLEVBQUUsRUFBRSxDQUNqQyxlQUFlLENBQUMsY0FBYyxDQUFDLENBQUMsc0JBQXNCLENBQUMsMEJBQTBCLENBQUMsQ0FBQztJQUUzRixJQUFJLFFBQWtCLENBQUM7SUFFdkIscUNBQXFDO0lBQ3JDLGFBQWEsQ0FBQyxjQUFjLEVBQUUsRUFBRSxDQUFDO1NBQzVCLFFBQVEsQ0FBQyxvQkFBb0IsZUFBc0I7U0FDbkQsT0FBTyxDQUFDLFlBQVksRUFBRSxDQUFDLGVBQWUsRUFBRSxRQUFRLENBQUMsQ0FBQztTQUNsRCxPQUFPLENBQ0osZUFBZSxFQUNmLEdBQUcsRUFBRTtRQUNILElBQUksQ0FBQyxRQUFRLEVBQUU7WUFDYixNQUFNLElBQUksS0FBSyxDQUNYLDRFQUE0RTtnQkFDNUUsaUJBQWlCLENBQUMsQ0FBQztTQUN4QjtRQUNELE9BQU8sUUFBUSxDQUFDO0lBQ2xCLENBQUMsQ0FBQztTQUNMLE9BQU8sQ0FBQyxlQUFlLEVBQUUsQ0FBQyxnQkFBZ0IsRUFBRSxRQUFRLENBQUMsQ0FBQztTQUN0RCxPQUFPLENBQ0osZ0JBQWdCLEVBQ2hCO1FBQ0UsU0FBUztRQUNULENBQUMsU0FBMkIsRUFBRSxFQUFFO1lBQzlCLGtCQUFrQixDQUFDLFNBQVMsQ0FBQyxDQUFDO1lBQzlCLE1BQU0sTUFBTSxHQUFrQjtnQkFDNUIsT0FBTyxFQUFFLFdBQVcsQ0FBQyxpQkFBaUIsQ0FBQyxDQUFDLElBQUksQ0FBQyxHQUFHLENBQUMsRUFBRTtvQkFDakQsUUFBUSxHQUFHLE1BQU0sQ0FBQyxRQUFRLEdBQUcsSUFBSSxpQkFBaUIsQ0FBQyxHQUFHLENBQUMsUUFBUSxDQUFDLENBQUM7b0JBQ2pFLFFBQVEsQ0FBQyxHQUFHLENBQUMsU0FBUyxDQUFDLENBQUM7b0JBRXhCLHlFQUF5RTtvQkFDekUsNkVBQTZFO29CQUM3RSwyRUFBMkU7b0JBQzNFLDBDQUEwQztvQkFDMUMsdURBQXVEO29CQUN2RCxRQUFRLENBQUMsR0FBRyxDQUFDLFdBQVcsQ0FBQyxDQUFDLFNBQVMsQ0FBQyxHQUFHLEVBQUUsQ0FBQyxVQUFVLENBQUMsU0FBUyxDQUFDLENBQUMsQ0FBQztvQkFFakUsT0FBTyxRQUFRLENBQUM7Z0JBQ2xCLENBQUMsQ0FBQzthQUNILENBQUM7WUFDRixPQUFPLE1BQU0sQ0FBQztRQUNoQixDQUFDO0tBQ0YsQ0FBQztTQUNMLE1BQU0sQ0FBQztRQUNOLFNBQVMsRUFBRSxRQUFRO1FBQ25CLENBQUMsU0FBMkIsRUFBRSxRQUF5QixFQUFFLEVBQUU7WUFDekQsUUFBUSxDQUFDLFFBQVEsQ0FBQywyQkFBMkIsRUFBRSx3QkFBd0IsQ0FBQyxTQUFTLENBQUMsR0FBRyxDQUFDLENBQUMsQ0FBQztRQUMxRixDQUFDO0tBQ0YsQ0FBQyxDQUFDO0lBRVAsT0FBTyxjQUFjLENBQUM7QUFDeEIsQ0FBQztBQUVELFNBQVMsUUFBUSxDQUFVLENBQUk7SUFDN0IsT0FBTyxDQUFDLENBQUM7QUFDWCxDQUFDIiwic291cmNlc0NvbnRlbnQiOlsiLyoqXG4gKiBAbGljZW5zZVxuICogQ29weXJpZ2h0IEdvb2dsZSBMTEMgQWxsIFJpZ2h0cyBSZXNlcnZlZC5cbiAqXG4gKiBVc2Ugb2YgdGhpcyBzb3VyY2UgY29kZSBpcyBnb3Zlcm5lZCBieSBhbiBNSVQtc3R5bGUgbGljZW5zZSB0aGF0IGNhbiBiZVxuICogZm91bmQgaW4gdGhlIExJQ0VOU0UgZmlsZSBhdCBodHRwczovL2FuZ3VsYXIuaW8vbGljZW5zZVxuICovXG5cbmltcG9ydCB7SW5qZWN0b3IsIE5nTW9kdWxlRmFjdG9yeSwgTmdNb2R1bGVSZWYsIFBsYXRmb3JtUmVmLCBTdGF0aWNQcm92aWRlcn0gZnJvbSAnQGFuZ3VsYXIvY29yZSc7XG5pbXBvcnQge3BsYXRmb3JtQnJvd3Nlcn0gZnJvbSAnQGFuZ3VsYXIvcGxhdGZvcm0tYnJvd3Nlcic7XG5cbmltcG9ydCB7SUluamVjdG9yU2VydmljZSwgSVByb3ZpZGVTZXJ2aWNlLCBtb2R1bGVfIGFzIGFuZ3VsYXJNb2R1bGV9IGZyb20gJy4uLy4uL3NyYy9jb21tb24vc3JjL2FuZ3VsYXIxJztcbmltcG9ydCB7JElOSkVDVE9SLCAkUFJPVklERSwgRE9XTkdSQURFRF9NT0RVTEVfQ09VTlRfS0VZLCBJTkpFQ1RPUl9LRVksIExBWllfTU9EVUxFX1JFRiwgVVBHUkFERV9BUFBfVFlQRV9LRVksIFVQR1JBREVfTU9EVUxFX05BTUV9IGZyb20gJy4uLy4uL3NyYy9jb21tb24vc3JjL2NvbnN0YW50cyc7XG5pbXBvcnQge2Rlc3Ryb3lBcHAsIGdldERvd25ncmFkZWRNb2R1bGVDb3VudCwgaXNGdW5jdGlvbiwgTGF6eU1vZHVsZVJlZiwgVXBncmFkZUFwcFR5cGV9IGZyb20gJy4uLy4uL3NyYy9jb21tb24vc3JjL3V0aWwnO1xuXG5pbXBvcnQge2FuZ3VsYXIxUHJvdmlkZXJzLCBzZXRUZW1wSW5qZWN0b3JSZWZ9IGZyb20gJy4vYW5ndWxhcjFfcHJvdmlkZXJzJztcbmltcG9ydCB7TmdBZGFwdGVySW5qZWN0b3J9IGZyb20gJy4vdXRpbCc7XG5cblxubGV0IG1vZHVsZVVpZCA9IDA7XG5cbi8qKlxuICogQGRlc2NyaXB0aW9uXG4gKlxuICogQSBoZWxwZXIgZnVuY3Rpb24gZm9yIGNyZWF0aW5nIGFuIEFuZ3VsYXJKUyBtb2R1bGUgdGhhdCBjYW4gYm9vdHN0cmFwIGFuIEFuZ3VsYXIgbW9kdWxlXG4gKiBcIm9uLWRlbWFuZFwiIChwb3NzaWJseSBsYXppbHkpIHdoZW4gYSB7QGxpbmsgZG93bmdyYWRlQ29tcG9uZW50IGRvd25ncmFkZWQgY29tcG9uZW50fSBuZWVkcyB0byBiZVxuICogaW5zdGFudGlhdGVkLlxuICpcbiAqICpQYXJ0IG9mIHRoZSBbdXBncmFkZS9zdGF0aWNdKGFwaT9xdWVyeT11cGdyYWRlL3N0YXRpYykgbGlicmFyeSBmb3IgaHlicmlkIHVwZ3JhZGUgYXBwcyB0aGF0XG4gKiBzdXBwb3J0IEFPVCBjb21waWxhdGlvbi4qXG4gKlxuICogSXQgYWxsb3dzIGxvYWRpbmcvYm9vdHN0cmFwcGluZyB0aGUgQW5ndWxhciBwYXJ0IG9mIGEgaHlicmlkIGFwcGxpY2F0aW9uIGxhemlseSBhbmQgbm90IGhhdmluZyB0b1xuICogcGF5IHRoZSBjb3N0IHVwLWZyb250LiBGb3IgZXhhbXBsZSwgeW91IGNhbiBoYXZlIGFuIEFuZ3VsYXJKUyBhcHBsaWNhdGlvbiB0aGF0IHVzZXMgQW5ndWxhciBmb3JcbiAqIHNwZWNpZmljIHJvdXRlcyBhbmQgb25seSBpbnN0YW50aWF0ZSB0aGUgQW5ndWxhciBtb2R1bGVzIGlmL3doZW4gdGhlIHVzZXIgdmlzaXRzIG9uZSBvZiB0aGVzZVxuICogcm91dGVzLlxuICpcbiAqIFRoZSBBbmd1bGFyIG1vZHVsZSB3aWxsIGJlIGJvb3RzdHJhcHBlZCBvbmNlICh3aGVuIHJlcXVlc3RlZCBmb3IgdGhlIGZpcnN0IHRpbWUpIGFuZCB0aGUgc2FtZVxuICogcmVmZXJlbmNlIHdpbGwgYmUgdXNlZCBmcm9tIHRoYXQgcG9pbnQgb253YXJkcy5cbiAqXG4gKiBgZG93bmdyYWRlTW9kdWxlKClgIHJlcXVpcmVzIGVpdGhlciBhbiBgTmdNb2R1bGVGYWN0b3J5YCBvciBhIGZ1bmN0aW9uOlxuICogLSBgTmdNb2R1bGVGYWN0b3J5YDogSWYgeW91IHBhc3MgYW4gYE5nTW9kdWxlRmFjdG9yeWAsIGl0IHdpbGwgYmUgdXNlZCB0byBpbnN0YW50aWF0ZSBhIG1vZHVsZVxuICogICB1c2luZyBgcGxhdGZvcm1Ccm93c2VyYCdzIHtAbGluayBQbGF0Zm9ybVJlZiNib290c3RyYXBNb2R1bGVGYWN0b3J5IGJvb3RzdHJhcE1vZHVsZUZhY3RvcnkoKX0uXG4gKiAtIGBGdW5jdGlvbmA6IElmIHlvdSBwYXNzIGEgZnVuY3Rpb24sIGl0IGlzIGV4cGVjdGVkIHRvIHJldHVybiBhIHByb21pc2UgcmVzb2x2aW5nIHRvIGFuXG4gKiAgIGBOZ01vZHVsZVJlZmAuIFRoZSBmdW5jdGlvbiBpcyBjYWxsZWQgd2l0aCBhbiBhcnJheSBvZiBleHRyYSB7QGxpbmsgU3RhdGljUHJvdmlkZXIgUHJvdmlkZXJzfVxuICogICB0aGF0IGFyZSBleHBlY3RlZCB0byBiZSBhdmFpbGFibGUgZnJvbSB0aGUgcmV0dXJuZWQgYE5nTW9kdWxlUmVmYCdzIGBJbmplY3RvcmAuXG4gKlxuICogYGRvd25ncmFkZU1vZHVsZSgpYCByZXR1cm5zIHRoZSBuYW1lIG9mIHRoZSBjcmVhdGVkIEFuZ3VsYXJKUyB3cmFwcGVyIG1vZHVsZS4gWW91IGNhbiB1c2UgaXQgdG9cbiAqIGRlY2xhcmUgYSBkZXBlbmRlbmN5IGluIHlvdXIgbWFpbiBBbmd1bGFySlMgbW9kdWxlLlxuICpcbiAqIHtAZXhhbXBsZSB1cGdyYWRlL3N0YXRpYy90cy9saXRlL21vZHVsZS50cyByZWdpb249XCJiYXNpYy1ob3ctdG9cIn1cbiAqXG4gKiBGb3IgbW9yZSBkZXRhaWxzIG9uIGhvdyB0byB1c2UgYGRvd25ncmFkZU1vZHVsZSgpYCBzZWVcbiAqIFtVcGdyYWRpbmcgZm9yIFBlcmZvcm1hbmNlXShndWlkZS91cGdyYWRlLXBlcmZvcm1hbmNlKS5cbiAqXG4gKiBAdXNhZ2VOb3Rlc1xuICpcbiAqIEFwYXJ0IGZyb20gYFVwZ3JhZGVNb2R1bGVgLCB5b3UgY2FuIHVzZSB0aGUgcmVzdCBvZiB0aGUgYHVwZ3JhZGUvc3RhdGljYCBoZWxwZXJzIGFzIHVzdWFsIHRvXG4gKiBidWlsZCBhIGh5YnJpZCBhcHBsaWNhdGlvbi4gTm90ZSB0aGF0IHRoZSBBbmd1bGFyIHBpZWNlcyAoZS5nLiBkb3duZ3JhZGVkIHNlcnZpY2VzKSB3aWxsIG5vdCBiZVxuICogYXZhaWxhYmxlIHVudGlsIHRoZSBkb3duZ3JhZGVkIG1vZHVsZSBoYXMgYmVlbiBib290c3RyYXBwZWQsIGkuZS4gYnkgaW5zdGFudGlhdGluZyBhIGRvd25ncmFkZWRcbiAqIGNvbXBvbmVudC5cbiAqXG4gKiA8ZGl2IGNsYXNzPVwiYWxlcnQgaXMtaW1wb3J0YW50XCI+XG4gKlxuICogICBZb3UgY2Fubm90IHVzZSBgZG93bmdyYWRlTW9kdWxlKClgIGFuZCBgVXBncmFkZU1vZHVsZWAgaW4gdGhlIHNhbWUgaHlicmlkIGFwcGxpY2F0aW9uLjxiciAvPlxuICogICBVc2Ugb25lIG9yIHRoZSBvdGhlci5cbiAqXG4gKiA8L2Rpdj5cbiAqXG4gKiAjIyMgRGlmZmVyZW5jZXMgd2l0aCBgVXBncmFkZU1vZHVsZWBcbiAqXG4gKiBCZXNpZGVzIHRoZWlyIGRpZmZlcmVudCBBUEksIHRoZXJlIGFyZSB0d28gaW1wb3J0YW50IGludGVybmFsIGRpZmZlcmVuY2VzIGJldHdlZW5cbiAqIGBkb3duZ3JhZGVNb2R1bGUoKWAgYW5kIGBVcGdyYWRlTW9kdWxlYCB0aGF0IGFmZmVjdCB0aGUgYmVoYXZpb3Igb2YgaHlicmlkIGFwcGxpY2F0aW9uczpcbiAqXG4gKiAxLiBVbmxpa2UgYFVwZ3JhZGVNb2R1bGVgLCBgZG93bmdyYWRlTW9kdWxlKClgIGRvZXMgbm90IGJvb3RzdHJhcCB0aGUgbWFpbiBBbmd1bGFySlMgbW9kdWxlXG4gKiAgICBpbnNpZGUgdGhlIHtAbGluayBOZ1pvbmUgQW5ndWxhciB6b25lfS5cbiAqIDIuIFVubGlrZSBgVXBncmFkZU1vZHVsZWAsIGBkb3duZ3JhZGVNb2R1bGUoKWAgZG9lcyBub3QgYXV0b21hdGljYWxseSBydW4gYVxuICogICAgWyRkaWdlc3QoKV0oaHR0cHM6Ly9kb2NzLmFuZ3VsYXJqcy5vcmcvYXBpL25nL3R5cGUvJHJvb3RTY29wZS5TY29wZSMkZGlnZXN0KSB3aGVuIGNoYW5nZXMgYXJlXG4gKiAgICBkZXRlY3RlZCBpbiB0aGUgQW5ndWxhciBwYXJ0IG9mIHRoZSBhcHBsaWNhdGlvbi5cbiAqXG4gKiBXaGF0IHRoaXMgbWVhbnMgaXMgdGhhdCBhcHBsaWNhdGlvbnMgdXNpbmcgYFVwZ3JhZGVNb2R1bGVgIHdpbGwgcnVuIGNoYW5nZSBkZXRlY3Rpb24gbW9yZVxuICogZnJlcXVlbnRseSBpbiBvcmRlciB0byBlbnN1cmUgdGhhdCBib3RoIGZyYW1ld29ya3MgYXJlIHByb3Blcmx5IG5vdGlmaWVkIGFib3V0IHBvc3NpYmxlIGNoYW5nZXMuXG4gKiBUaGlzIHdpbGwgaW5ldml0YWJseSByZXN1bHQgaW4gbW9yZSBjaGFuZ2UgZGV0ZWN0aW9uIHJ1bnMgdGhhbiBuZWNlc3NhcnkuXG4gKlxuICogYGRvd25ncmFkZU1vZHVsZSgpYCwgb24gdGhlIG90aGVyIHNpZGUsIGRvZXMgbm90IHRyeSB0byB0aWUgdGhlIHR3byBjaGFuZ2UgZGV0ZWN0aW9uIHN5c3RlbXMgYXNcbiAqIHRpZ2h0bHksIHJlc3RyaWN0aW5nIHRoZSBleHBsaWNpdCBjaGFuZ2UgZGV0ZWN0aW9uIHJ1bnMgb25seSB0byBjYXNlcyB3aGVyZSBpdCBrbm93cyBpdCBpc1xuICogbmVjZXNzYXJ5IChlLmcuIHdoZW4gdGhlIGlucHV0cyBvZiBhIGRvd25ncmFkZWQgY29tcG9uZW50IGNoYW5nZSkuIFRoaXMgaW1wcm92ZXMgcGVyZm9ybWFuY2UsXG4gKiBlc3BlY2lhbGx5IGluIGNoYW5nZS1kZXRlY3Rpb24taGVhdnkgYXBwbGljYXRpb25zLCBidXQgbGVhdmVzIGl0IHVwIHRvIHRoZSBkZXZlbG9wZXIgdG8gbWFudWFsbHlcbiAqIG5vdGlmeSBlYWNoIGZyYW1ld29yayBhcyBuZWVkZWQuXG4gKlxuICogRm9yIGEgbW9yZSBkZXRhaWxlZCBkaXNjdXNzaW9uIG9mIHRoZSBkaWZmZXJlbmNlcyBhbmQgdGhlaXIgaW1wbGljYXRpb25zLCBzZWVcbiAqIFtVcGdyYWRpbmcgZm9yIFBlcmZvcm1hbmNlXShndWlkZS91cGdyYWRlLXBlcmZvcm1hbmNlKS5cbiAqXG4gKiA8ZGl2IGNsYXNzPVwiYWxlcnQgaXMtaGVscGZ1bFwiPlxuICpcbiAqICAgWW91IGNhbiBtYW51YWxseSB0cmlnZ2VyIGEgY2hhbmdlIGRldGVjdGlvbiBydW4gaW4gQW5ndWxhckpTIHVzaW5nXG4gKiAgIFtzY29wZS4kYXBwbHkoLi4uKV0oaHR0cHM6Ly9kb2NzLmFuZ3VsYXJqcy5vcmcvYXBpL25nL3R5cGUvJHJvb3RTY29wZS5TY29wZSMkYXBwbHkpIG9yXG4gKiAgIFskcm9vdFNjb3BlLiRkaWdlc3QoKV0oaHR0cHM6Ly9kb2NzLmFuZ3VsYXJqcy5vcmcvYXBpL25nL3R5cGUvJHJvb3RTY29wZS5TY29wZSMkZGlnZXN0KS5cbiAqXG4gKiAgIFlvdSBjYW4gbWFudWFsbHkgdHJpZ2dlciBhIGNoYW5nZSBkZXRlY3Rpb24gcnVuIGluIEFuZ3VsYXIgdXNpbmcge0BsaW5rIE5nWm9uZSNydW5cbiAqICAgbmdab25lLnJ1biguLi4pfS5cbiAqXG4gKiA8L2Rpdj5cbiAqXG4gKiAjIyMgRG93bmdyYWRpbmcgbXVsdGlwbGUgbW9kdWxlc1xuICpcbiAqIEl0IGlzIHBvc3NpYmxlIHRvIGRvd25ncmFkZSBtdWx0aXBsZSBtb2R1bGVzIGFuZCBpbmNsdWRlIHRoZW0gaW4gYW4gQW5ndWxhckpTIGFwcGxpY2F0aW9uLiBJblxuICogdGhhdCBjYXNlLCBlYWNoIGRvd25ncmFkZWQgbW9kdWxlIHdpbGwgYmUgYm9vdHN0cmFwcGVkIHdoZW4gYW4gYXNzb2NpYXRlZCBkb3duZ3JhZGVkIGNvbXBvbmVudCBvclxuICogaW5qZWN0YWJsZSBuZWVkcyB0byBiZSBpbnN0YW50aWF0ZWQuXG4gKlxuICogVGhpbmdzIHRvIGtlZXAgaW4gbWluZCwgd2hlbiBkb3duZ3JhZGluZyBtdWx0aXBsZSBtb2R1bGVzOlxuICpcbiAqIC0gRWFjaCBkb3duZ3JhZGVkIGNvbXBvbmVudC9pbmplY3RhYmxlIG5lZWRzIHRvIGJlIGV4cGxpY2l0bHkgYXNzb2NpYXRlZCB3aXRoIGEgZG93bmdyYWRlZFxuICogICBtb2R1bGUuIFNlZSBgZG93bmdyYWRlQ29tcG9uZW50KClgIGFuZCBgZG93bmdyYWRlSW5qZWN0YWJsZSgpYCBmb3IgbW9yZSBkZXRhaWxzLlxuICpcbiAqIC0gSWYgeW91IHdhbnQgc29tZSBpbmplY3RhYmxlcyB0byBiZSBzaGFyZWQgYW1vbmcgYWxsIGRvd25ncmFkZWQgbW9kdWxlcywgeW91IGNhbiBwcm92aWRlIHRoZW0gYXNcbiAqICAgYFN0YXRpY1Byb3ZpZGVyYHMsIHdoZW4gY3JlYXRpbmcgdGhlIGBQbGF0Zm9ybVJlZmAgKGUuZy4gdmlhIGBwbGF0Zm9ybUJyb3dzZXJgIG9yXG4gKiAgIGBwbGF0Zm9ybUJyb3dzZXJEeW5hbWljYCkuXG4gKlxuICogLSBXaGVuIHVzaW5nIHtAbGluayBQbGF0Zm9ybVJlZiNib290c3RyYXBtb2R1bGUgYGJvb3RzdHJhcE1vZHVsZSgpYH0gb3JcbiAqICAge0BsaW5rIFBsYXRmb3JtUmVmI2Jvb3RzdHJhcG1vZHVsZWZhY3RvcnkgYGJvb3RzdHJhcE1vZHVsZUZhY3RvcnkoKWB9IHRvIGJvb3RzdHJhcCB0aGVcbiAqICAgZG93bmdyYWRlZCBtb2R1bGVzLCBlYWNoIG9uZSBpcyBjb25zaWRlcmVkIGEgXCJyb290XCIgbW9kdWxlLiBBcyBhIGNvbnNlcXVlbmNlLCBhIG5ldyBpbnN0YW5jZVxuICogICB3aWxsIGJlIGNyZWF0ZWQgZm9yIGV2ZXJ5IGluamVjdGFibGUgcHJvdmlkZWQgaW4gYFwicm9vdFwiYCAodmlhXG4gKiAgIHtAbGluayBJbmplY3RhYmxlI3Byb3ZpZGVkSW4gYHByb3ZpZGVkSW5gfSkuXG4gKiAgIElmIHRoaXMgaXMgbm90IHlvdXIgaW50ZW50aW9uLCB5b3UgY2FuIGhhdmUgYSBzaGFyZWQgbW9kdWxlICh0aGF0IHdpbGwgYWN0IGFzIGFjdCBhcyB0aGUgXCJyb290XCJcbiAqICAgbW9kdWxlKSBhbmQgY3JlYXRlIGFsbCBkb3duZ3JhZGVkIG1vZHVsZXMgdXNpbmcgdGhhdCBtb2R1bGUncyBpbmplY3RvcjpcbiAqXG4gKiAgIHtAZXhhbXBsZSB1cGdyYWRlL3N0YXRpYy90cy9saXRlLW11bHRpLXNoYXJlZC9tb2R1bGUudHMgcmVnaW9uPVwic2hhcmVkLXJvb3QtbW9kdWxlXCJ9XG4gKlxuICogQHB1YmxpY0FwaVxuICovXG5leHBvcnQgZnVuY3Rpb24gZG93bmdyYWRlTW9kdWxlPFQ+KG1vZHVsZUZhY3RvcnlPckJvb3RzdHJhcEZuOiBOZ01vZHVsZUZhY3Rvcnk8VD58KFxuICAgIChleHRyYVByb3ZpZGVyczogU3RhdGljUHJvdmlkZXJbXSkgPT4gUHJvbWlzZTxOZ01vZHVsZVJlZjxUPj4pKTogc3RyaW5nIHtcbiAgY29uc3QgbGF6eU1vZHVsZU5hbWUgPSBgJHtVUEdSQURFX01PRFVMRV9OQU1FfS5sYXp5JHsrK21vZHVsZVVpZH1gO1xuICBjb25zdCBsYXp5TW9kdWxlUmVmS2V5ID0gYCR7TEFaWV9NT0RVTEVfUkVGfSR7bGF6eU1vZHVsZU5hbWV9YDtcbiAgY29uc3QgbGF6eUluamVjdG9yS2V5ID0gYCR7SU5KRUNUT1JfS0VZfSR7bGF6eU1vZHVsZU5hbWV9YDtcblxuICBjb25zdCBib290c3RyYXBGbiA9IGlzRnVuY3Rpb24obW9kdWxlRmFjdG9yeU9yQm9vdHN0cmFwRm4pID9cbiAgICAgIG1vZHVsZUZhY3RvcnlPckJvb3RzdHJhcEZuIDpcbiAgICAgIChleHRyYVByb3ZpZGVyczogU3RhdGljUHJvdmlkZXJbXSkgPT5cbiAgICAgICAgICBwbGF0Zm9ybUJyb3dzZXIoZXh0cmFQcm92aWRlcnMpLmJvb3RzdHJhcE1vZHVsZUZhY3RvcnkobW9kdWxlRmFjdG9yeU9yQm9vdHN0cmFwRm4pO1xuXG4gIGxldCBpbmplY3RvcjogSW5qZWN0b3I7XG5cbiAgLy8gQ3JlYXRlIGFuIG5nMSBtb2R1bGUgdG8gYm9vdHN0cmFwLlxuICBhbmd1bGFyTW9kdWxlKGxhenlNb2R1bGVOYW1lLCBbXSlcbiAgICAgIC5jb25zdGFudChVUEdSQURFX0FQUF9UWVBFX0tFWSwgVXBncmFkZUFwcFR5cGUuTGl0ZSlcbiAgICAgIC5mYWN0b3J5KElOSkVDVE9SX0tFWSwgW2xhenlJbmplY3RvcktleSwgaWRlbnRpdHldKVxuICAgICAgLmZhY3RvcnkoXG4gICAgICAgICAgbGF6eUluamVjdG9yS2V5LFxuICAgICAgICAgICgpID0+IHtcbiAgICAgICAgICAgIGlmICghaW5qZWN0b3IpIHtcbiAgICAgICAgICAgICAgdGhyb3cgbmV3IEVycm9yKFxuICAgICAgICAgICAgICAgICAgJ1RyeWluZyB0byBnZXQgdGhlIEFuZ3VsYXIgaW5qZWN0b3IgYmVmb3JlIGJvb3RzdHJhcHBpbmcgdGhlIGNvcnJlc3BvbmRpbmcgJyArXG4gICAgICAgICAgICAgICAgICAnQW5ndWxhciBtb2R1bGUuJyk7XG4gICAgICAgICAgICB9XG4gICAgICAgICAgICByZXR1cm4gaW5qZWN0b3I7XG4gICAgICAgICAgfSlcbiAgICAgIC5mYWN0b3J5KExBWllfTU9EVUxFX1JFRiwgW2xhenlNb2R1bGVSZWZLZXksIGlkZW50aXR5XSlcbiAgICAgIC5mYWN0b3J5KFxuICAgICAgICAgIGxhenlNb2R1bGVSZWZLZXksXG4gICAgICAgICAgW1xuICAgICAgICAgICAgJElOSkVDVE9SLFxuICAgICAgICAgICAgKCRpbmplY3RvcjogSUluamVjdG9yU2VydmljZSkgPT4ge1xuICAgICAgICAgICAgICBzZXRUZW1wSW5qZWN0b3JSZWYoJGluamVjdG9yKTtcbiAgICAgICAgICAgICAgY29uc3QgcmVzdWx0OiBMYXp5TW9kdWxlUmVmID0ge1xuICAgICAgICAgICAgICAgIHByb21pc2U6IGJvb3RzdHJhcEZuKGFuZ3VsYXIxUHJvdmlkZXJzKS50aGVuKHJlZiA9PiB7XG4gICAgICAgICAgICAgICAgICBpbmplY3RvciA9IHJlc3VsdC5pbmplY3RvciA9IG5ldyBOZ0FkYXB0ZXJJbmplY3RvcihyZWYuaW5qZWN0b3IpO1xuICAgICAgICAgICAgICAgICAgaW5qZWN0b3IuZ2V0KCRJTkpFQ1RPUik7XG5cbiAgICAgICAgICAgICAgICAgIC8vIERlc3Ryb3kgdGhlIEFuZ3VsYXJKUyBhcHAgb25jZSB0aGUgQW5ndWxhciBgUGxhdGZvcm1SZWZgIGlzIGRlc3Ryb3llZC5cbiAgICAgICAgICAgICAgICAgIC8vIFRoaXMgZG9lcyBub3QgaGFwcGVuIGluIGEgdHlwaWNhbCBTUEEgc2NlbmFyaW8sIGJ1dCBpdCBtaWdodCBiZSB1c2VmdWwgZm9yXG4gICAgICAgICAgICAgICAgICAvLyBvdGhlciB1c2UtY2FzZXMgd2hlcmUgZGlzcG9zaW5nIG9mIGFuIEFuZ3VsYXIvQW5ndWxhckpTIGFwcCBpcyBuZWNlc3NhcnlcbiAgICAgICAgICAgICAgICAgIC8vIChzdWNoIGFzIEhvdCBNb2R1bGUgUmVwbGFjZW1lbnQgKEhNUikpLlxuICAgICAgICAgICAgICAgICAgLy8gU2VlIGh0dHBzOi8vZ2l0aHViLmNvbS9hbmd1bGFyL2FuZ3VsYXIvaXNzdWVzLzM5OTM1LlxuICAgICAgICAgICAgICAgICAgaW5qZWN0b3IuZ2V0KFBsYXRmb3JtUmVmKS5vbkRlc3Ryb3koKCkgPT4gZGVzdHJveUFwcCgkaW5qZWN0b3IpKTtcblxuICAgICAgICAgICAgICAgICAgcmV0dXJuIGluamVjdG9yO1xuICAgICAgICAgICAgICAgIH0pXG4gICAgICAgICAgICAgIH07XG4gICAgICAgICAgICAgIHJldHVybiByZXN1bHQ7XG4gICAgICAgICAgICB9XG4gICAgICAgICAgXSlcbiAgICAgIC5jb25maWcoW1xuICAgICAgICAkSU5KRUNUT1IsICRQUk9WSURFLFxuICAgICAgICAoJGluamVjdG9yOiBJSW5qZWN0b3JTZXJ2aWNlLCAkcHJvdmlkZTogSVByb3ZpZGVTZXJ2aWNlKSA9PiB7XG4gICAgICAgICAgJHByb3ZpZGUuY29uc3RhbnQoRE9XTkdSQURFRF9NT0RVTEVfQ09VTlRfS0VZLCBnZXREb3duZ3JhZGVkTW9kdWxlQ291bnQoJGluamVjdG9yKSArIDEpO1xuICAgICAgICB9XG4gICAgICBdKTtcblxuICByZXR1cm4gbGF6eU1vZHVsZU5hbWU7XG59XG5cbmZ1bmN0aW9uIGlkZW50aXR5PFQgPSBhbnk+KHg6IFQpOiBUIHtcbiAgcmV0dXJuIHg7XG59XG4iXX0=