/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
import { Injector } from '@angular/core';
import { TestBed } from '@angular/core/testing';
import * as ng from '../../../src/common/src/angular1';
import { $INJECTOR, INJECTOR_KEY, UPGRADE_APP_TYPE_KEY } from '../../../src/common/src/constants';
/**
 * A helper function to use when unit testing AngularJS services that depend upon downgraded Angular
 * services.
 *
 * This function returns an AngularJS module that is configured to wire up the AngularJS and Angular
 * injectors without the need to actually bootstrap a hybrid application.
 * This makes it simpler and faster to unit test services.
 *
 * Use the returned AngularJS module in a call to
 * [`angular.mocks.module`](https://docs.angularjs.org/api/ngMock/function/angular.mock.module) to
 * include this module in the unit test injector.
 *
 * In the following code snippet, we are configuring the `$injector` with two modules:
 * The AngularJS `ng1AppModule`, which is the AngularJS part of our hybrid application and the
 * `Ng2AppModule`, which is the Angular part.
 *
 * <code-example path="upgrade/static/ts/full/module.spec.ts"
 * region="angularjs-setup"></code-example>
 *
 * Once this is done we can get hold of services via the AngularJS `$injector` as normal.
 * Services that are (or have dependencies on) a downgraded Angular service, will be instantiated as
 * needed by the Angular root `Injector`.
 *
 * In the following code snippet, `heroesService` is a downgraded Angular service that we are
 * accessing from AngularJS.
 *
 * <code-example path="upgrade/static/ts/full/module.spec.ts"
 * region="angularjs-spec"></code-example>
 *
 * <div class="alert is-important">
 *
 * This helper is for testing services not components.
 * For Component testing you must still bootstrap a hybrid app. See `UpgradeModule` or
 * `downgradeModule` for more information.
 *
 * </div>
 *
 * <div class="alert is-important">
 *
 * The resulting configuration does not wire up AngularJS digests to Zone hooks. It is the
 * responsibility of the test writer to call `$rootScope.$apply`, as necessary, to trigger
 * AngularJS handlers of async events from Angular.
 *
 * </div>
 *
 * <div class="alert is-important">
 *
 * The helper sets up global variables to hold the shared Angular and AngularJS injectors.
 *
 * * Only call this helper once per spec.
 * * Do not use `createAngularJSTestingModule` in the same spec as `createAngularTestingModule`.
 *
 * </div>
 *
 * Here is the example application and its unit tests that use `createAngularTestingModule`
 * and `createAngularJSTestingModule`.
 *
 * <code-tabs>
 *  <code-pane header="module.spec.ts" path="upgrade/static/ts/full/module.spec.ts"></code-pane>
 *  <code-pane header="module.ts" path="upgrade/static/ts/full/module.ts"></code-pane>
 * </code-tabs>
 *
 *
 * @param angularModules a collection of Angular modules to include in the configuration.
 *
 * @publicApi
 */
export function createAngularJSTestingModule(angularModules) {
    return ng.module_('$$angularJSTestingModule', [])
        .constant(UPGRADE_APP_TYPE_KEY, 2 /* Static */)
        .factory(INJECTOR_KEY, [
        $INJECTOR,
        ($injector) => {
            TestBed.configureTestingModule({
                imports: angularModules,
                providers: [{ provide: $INJECTOR, useValue: $injector }]
            });
            return TestBed.inject(Injector);
        }
    ])
        .name;
}
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiY3JlYXRlX2FuZ3VsYXJqc190ZXN0aW5nX21vZHVsZS5qcyIsInNvdXJjZVJvb3QiOiIiLCJzb3VyY2VzIjpbIi4uLy4uLy4uLy4uLy4uLy4uLy4uLy4uL3BhY2thZ2VzL3VwZ3JhZGUvc3RhdGljL3Rlc3Rpbmcvc3JjL2NyZWF0ZV9hbmd1bGFyanNfdGVzdGluZ19tb2R1bGUudHMiXSwibmFtZXMiOltdLCJtYXBwaW5ncyI6IkFBQUE7Ozs7OztHQU1HO0FBRUgsT0FBTyxFQUFDLFFBQVEsRUFBQyxNQUFNLGVBQWUsQ0FBQztBQUN2QyxPQUFPLEVBQUMsT0FBTyxFQUFDLE1BQU0sdUJBQXVCLENBQUM7QUFFOUMsT0FBTyxLQUFLLEVBQUUsTUFBTSxrQ0FBa0MsQ0FBQztBQUN2RCxPQUFPLEVBQUMsU0FBUyxFQUFFLFlBQVksRUFBRSxvQkFBb0IsRUFBQyxNQUFNLG1DQUFtQyxDQUFDO0FBSWhHOzs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7R0FrRUc7QUFDSCxNQUFNLFVBQVUsNEJBQTRCLENBQUMsY0FBcUI7SUFDaEUsT0FBTyxFQUFFLENBQUMsT0FBTyxDQUFDLDBCQUEwQixFQUFFLEVBQUUsQ0FBQztTQUM1QyxRQUFRLENBQUMsb0JBQW9CLGlCQUF3QjtTQUNyRCxPQUFPLENBQ0osWUFBWSxFQUNaO1FBQ0UsU0FBUztRQUNULENBQUMsU0FBOEIsRUFBRSxFQUFFO1lBQ2pDLE9BQU8sQ0FBQyxzQkFBc0IsQ0FBQztnQkFDN0IsT0FBTyxFQUFFLGNBQWM7Z0JBQ3ZCLFNBQVMsRUFBRSxDQUFDLEVBQUMsT0FBTyxFQUFFLFNBQVMsRUFBRSxRQUFRLEVBQUUsU0FBUyxFQUFDLENBQUM7YUFDdkQsQ0FBQyxDQUFDO1lBQ0gsT0FBTyxPQUFPLENBQUMsTUFBTSxDQUFDLFFBQVEsQ0FBQyxDQUFDO1FBQ2xDLENBQUM7S0FDRixDQUFDO1NBQ0wsSUFBSSxDQUFDO0FBQ1osQ0FBQyIsInNvdXJjZXNDb250ZW50IjpbIi8qKlxuICogQGxpY2Vuc2VcbiAqIENvcHlyaWdodCBHb29nbGUgTExDIEFsbCBSaWdodHMgUmVzZXJ2ZWQuXG4gKlxuICogVXNlIG9mIHRoaXMgc291cmNlIGNvZGUgaXMgZ292ZXJuZWQgYnkgYW4gTUlULXN0eWxlIGxpY2Vuc2UgdGhhdCBjYW4gYmVcbiAqIGZvdW5kIGluIHRoZSBMSUNFTlNFIGZpbGUgYXQgaHR0cHM6Ly9hbmd1bGFyLmlvL2xpY2Vuc2VcbiAqL1xuXG5pbXBvcnQge0luamVjdG9yfSBmcm9tICdAYW5ndWxhci9jb3JlJztcbmltcG9ydCB7VGVzdEJlZH0gZnJvbSAnQGFuZ3VsYXIvY29yZS90ZXN0aW5nJztcblxuaW1wb3J0ICogYXMgbmcgZnJvbSAnLi4vLi4vLi4vc3JjL2NvbW1vbi9zcmMvYW5ndWxhcjEnO1xuaW1wb3J0IHskSU5KRUNUT1IsIElOSkVDVE9SX0tFWSwgVVBHUkFERV9BUFBfVFlQRV9LRVl9IGZyb20gJy4uLy4uLy4uL3NyYy9jb21tb24vc3JjL2NvbnN0YW50cyc7XG5pbXBvcnQge1VwZ3JhZGVBcHBUeXBlfSBmcm9tICcuLi8uLi8uLi9zcmMvY29tbW9uL3NyYy91dGlsJztcblxuXG4vKipcbiAqIEEgaGVscGVyIGZ1bmN0aW9uIHRvIHVzZSB3aGVuIHVuaXQgdGVzdGluZyBBbmd1bGFySlMgc2VydmljZXMgdGhhdCBkZXBlbmQgdXBvbiBkb3duZ3JhZGVkIEFuZ3VsYXJcbiAqIHNlcnZpY2VzLlxuICpcbiAqIFRoaXMgZnVuY3Rpb24gcmV0dXJucyBhbiBBbmd1bGFySlMgbW9kdWxlIHRoYXQgaXMgY29uZmlndXJlZCB0byB3aXJlIHVwIHRoZSBBbmd1bGFySlMgYW5kIEFuZ3VsYXJcbiAqIGluamVjdG9ycyB3aXRob3V0IHRoZSBuZWVkIHRvIGFjdHVhbGx5IGJvb3RzdHJhcCBhIGh5YnJpZCBhcHBsaWNhdGlvbi5cbiAqIFRoaXMgbWFrZXMgaXQgc2ltcGxlciBhbmQgZmFzdGVyIHRvIHVuaXQgdGVzdCBzZXJ2aWNlcy5cbiAqXG4gKiBVc2UgdGhlIHJldHVybmVkIEFuZ3VsYXJKUyBtb2R1bGUgaW4gYSBjYWxsIHRvXG4gKiBbYGFuZ3VsYXIubW9ja3MubW9kdWxlYF0oaHR0cHM6Ly9kb2NzLmFuZ3VsYXJqcy5vcmcvYXBpL25nTW9jay9mdW5jdGlvbi9hbmd1bGFyLm1vY2subW9kdWxlKSB0b1xuICogaW5jbHVkZSB0aGlzIG1vZHVsZSBpbiB0aGUgdW5pdCB0ZXN0IGluamVjdG9yLlxuICpcbiAqIEluIHRoZSBmb2xsb3dpbmcgY29kZSBzbmlwcGV0LCB3ZSBhcmUgY29uZmlndXJpbmcgdGhlIGAkaW5qZWN0b3JgIHdpdGggdHdvIG1vZHVsZXM6XG4gKiBUaGUgQW5ndWxhckpTIGBuZzFBcHBNb2R1bGVgLCB3aGljaCBpcyB0aGUgQW5ndWxhckpTIHBhcnQgb2Ygb3VyIGh5YnJpZCBhcHBsaWNhdGlvbiBhbmQgdGhlXG4gKiBgTmcyQXBwTW9kdWxlYCwgd2hpY2ggaXMgdGhlIEFuZ3VsYXIgcGFydC5cbiAqXG4gKiA8Y29kZS1leGFtcGxlIHBhdGg9XCJ1cGdyYWRlL3N0YXRpYy90cy9mdWxsL21vZHVsZS5zcGVjLnRzXCJcbiAqIHJlZ2lvbj1cImFuZ3VsYXJqcy1zZXR1cFwiPjwvY29kZS1leGFtcGxlPlxuICpcbiAqIE9uY2UgdGhpcyBpcyBkb25lIHdlIGNhbiBnZXQgaG9sZCBvZiBzZXJ2aWNlcyB2aWEgdGhlIEFuZ3VsYXJKUyBgJGluamVjdG9yYCBhcyBub3JtYWwuXG4gKiBTZXJ2aWNlcyB0aGF0IGFyZSAob3IgaGF2ZSBkZXBlbmRlbmNpZXMgb24pIGEgZG93bmdyYWRlZCBBbmd1bGFyIHNlcnZpY2UsIHdpbGwgYmUgaW5zdGFudGlhdGVkIGFzXG4gKiBuZWVkZWQgYnkgdGhlIEFuZ3VsYXIgcm9vdCBgSW5qZWN0b3JgLlxuICpcbiAqIEluIHRoZSBmb2xsb3dpbmcgY29kZSBzbmlwcGV0LCBgaGVyb2VzU2VydmljZWAgaXMgYSBkb3duZ3JhZGVkIEFuZ3VsYXIgc2VydmljZSB0aGF0IHdlIGFyZVxuICogYWNjZXNzaW5nIGZyb20gQW5ndWxhckpTLlxuICpcbiAqIDxjb2RlLWV4YW1wbGUgcGF0aD1cInVwZ3JhZGUvc3RhdGljL3RzL2Z1bGwvbW9kdWxlLnNwZWMudHNcIlxuICogcmVnaW9uPVwiYW5ndWxhcmpzLXNwZWNcIj48L2NvZGUtZXhhbXBsZT5cbiAqXG4gKiA8ZGl2IGNsYXNzPVwiYWxlcnQgaXMtaW1wb3J0YW50XCI+XG4gKlxuICogVGhpcyBoZWxwZXIgaXMgZm9yIHRlc3Rpbmcgc2VydmljZXMgbm90IGNvbXBvbmVudHMuXG4gKiBGb3IgQ29tcG9uZW50IHRlc3RpbmcgeW91IG11c3Qgc3RpbGwgYm9vdHN0cmFwIGEgaHlicmlkIGFwcC4gU2VlIGBVcGdyYWRlTW9kdWxlYCBvclxuICogYGRvd25ncmFkZU1vZHVsZWAgZm9yIG1vcmUgaW5mb3JtYXRpb24uXG4gKlxuICogPC9kaXY+XG4gKlxuICogPGRpdiBjbGFzcz1cImFsZXJ0IGlzLWltcG9ydGFudFwiPlxuICpcbiAqIFRoZSByZXN1bHRpbmcgY29uZmlndXJhdGlvbiBkb2VzIG5vdCB3aXJlIHVwIEFuZ3VsYXJKUyBkaWdlc3RzIHRvIFpvbmUgaG9va3MuIEl0IGlzIHRoZVxuICogcmVzcG9uc2liaWxpdHkgb2YgdGhlIHRlc3Qgd3JpdGVyIHRvIGNhbGwgYCRyb290U2NvcGUuJGFwcGx5YCwgYXMgbmVjZXNzYXJ5LCB0byB0cmlnZ2VyXG4gKiBBbmd1bGFySlMgaGFuZGxlcnMgb2YgYXN5bmMgZXZlbnRzIGZyb20gQW5ndWxhci5cbiAqXG4gKiA8L2Rpdj5cbiAqXG4gKiA8ZGl2IGNsYXNzPVwiYWxlcnQgaXMtaW1wb3J0YW50XCI+XG4gKlxuICogVGhlIGhlbHBlciBzZXRzIHVwIGdsb2JhbCB2YXJpYWJsZXMgdG8gaG9sZCB0aGUgc2hhcmVkIEFuZ3VsYXIgYW5kIEFuZ3VsYXJKUyBpbmplY3RvcnMuXG4gKlxuICogKiBPbmx5IGNhbGwgdGhpcyBoZWxwZXIgb25jZSBwZXIgc3BlYy5cbiAqICogRG8gbm90IHVzZSBgY3JlYXRlQW5ndWxhckpTVGVzdGluZ01vZHVsZWAgaW4gdGhlIHNhbWUgc3BlYyBhcyBgY3JlYXRlQW5ndWxhclRlc3RpbmdNb2R1bGVgLlxuICpcbiAqIDwvZGl2PlxuICpcbiAqIEhlcmUgaXMgdGhlIGV4YW1wbGUgYXBwbGljYXRpb24gYW5kIGl0cyB1bml0IHRlc3RzIHRoYXQgdXNlIGBjcmVhdGVBbmd1bGFyVGVzdGluZ01vZHVsZWBcbiAqIGFuZCBgY3JlYXRlQW5ndWxhckpTVGVzdGluZ01vZHVsZWAuXG4gKlxuICogPGNvZGUtdGFicz5cbiAqICA8Y29kZS1wYW5lIGhlYWRlcj1cIm1vZHVsZS5zcGVjLnRzXCIgcGF0aD1cInVwZ3JhZGUvc3RhdGljL3RzL2Z1bGwvbW9kdWxlLnNwZWMudHNcIj48L2NvZGUtcGFuZT5cbiAqICA8Y29kZS1wYW5lIGhlYWRlcj1cIm1vZHVsZS50c1wiIHBhdGg9XCJ1cGdyYWRlL3N0YXRpYy90cy9mdWxsL21vZHVsZS50c1wiPjwvY29kZS1wYW5lPlxuICogPC9jb2RlLXRhYnM+XG4gKlxuICpcbiAqIEBwYXJhbSBhbmd1bGFyTW9kdWxlcyBhIGNvbGxlY3Rpb24gb2YgQW5ndWxhciBtb2R1bGVzIHRvIGluY2x1ZGUgaW4gdGhlIGNvbmZpZ3VyYXRpb24uXG4gKlxuICogQHB1YmxpY0FwaVxuICovXG5leHBvcnQgZnVuY3Rpb24gY3JlYXRlQW5ndWxhckpTVGVzdGluZ01vZHVsZShhbmd1bGFyTW9kdWxlczogYW55W10pOiBzdHJpbmcge1xuICByZXR1cm4gbmcubW9kdWxlXygnJCRhbmd1bGFySlNUZXN0aW5nTW9kdWxlJywgW10pXG4gICAgICAuY29uc3RhbnQoVVBHUkFERV9BUFBfVFlQRV9LRVksIFVwZ3JhZGVBcHBUeXBlLlN0YXRpYylcbiAgICAgIC5mYWN0b3J5KFxuICAgICAgICAgIElOSkVDVE9SX0tFWSxcbiAgICAgICAgICBbXG4gICAgICAgICAgICAkSU5KRUNUT1IsXG4gICAgICAgICAgICAoJGluamVjdG9yOiBuZy5JSW5qZWN0b3JTZXJ2aWNlKSA9PiB7XG4gICAgICAgICAgICAgIFRlc3RCZWQuY29uZmlndXJlVGVzdGluZ01vZHVsZSh7XG4gICAgICAgICAgICAgICAgaW1wb3J0czogYW5ndWxhck1vZHVsZXMsXG4gICAgICAgICAgICAgICAgcHJvdmlkZXJzOiBbe3Byb3ZpZGU6ICRJTkpFQ1RPUiwgdXNlVmFsdWU6ICRpbmplY3Rvcn1dXG4gICAgICAgICAgICAgIH0pO1xuICAgICAgICAgICAgICByZXR1cm4gVGVzdEJlZC5pbmplY3QoSW5qZWN0b3IpO1xuICAgICAgICAgICAgfVxuICAgICAgICAgIF0pXG4gICAgICAubmFtZTtcbn1cbiJdfQ==