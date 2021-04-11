/**
 * @license Angular v11.2.7
 * (c) 2010-2021 Google LLC. https://angular.io/
 * License: MIT
 */

(function (global, factory) {
    typeof exports === 'object' && typeof module !== 'undefined' ? factory(exports, require('@angular/common'), require('@angular/core'), require('@angular/router'), require('@angular/upgrade/static')) :
    typeof define === 'function' && define.amd ? define('@angular/router/upgrade', ['exports', '@angular/common', '@angular/core', '@angular/router', '@angular/upgrade/static'], factory) :
    (global = global || self, factory((global.ng = global.ng || {}, global.ng.router = global.ng.router || {}, global.ng.router.upgrade = {}), global.ng.common, global.ng.core, global.ng.router, global.ng.upgrade.static));
}(this, (function (exports, common, core, router, _static) { 'use strict';

    /**
     * @license
     * Copyright Google LLC All Rights Reserved.
     *
     * Use of this source code is governed by an MIT-style license that can be
     * found in the LICENSE file at https://angular.io/license
     */
    var ɵ0 = locationSyncBootstrapListener;
    /**
     * Creates an initializer that sets up `ngRoute` integration
     * along with setting up the Angular router.
     *
     * @usageNotes
     *
     * <code-example language="typescript">
     * @NgModule({
     *  imports: [
     *   RouterModule.forRoot(SOME_ROUTES),
     *   UpgradeModule
     * ],
     * providers: [
     *   RouterUpgradeInitializer
     * ]
     * })
     * export class AppModule {
     *   ngDoBootstrap() {}
     * }
     * </code-example>
     *
     * @publicApi
     */
    var RouterUpgradeInitializer = {
        provide: core.APP_BOOTSTRAP_LISTENER,
        multi: true,
        useFactory: ɵ0,
        deps: [_static.UpgradeModule]
    };
    /**
     * @internal
     */
    function locationSyncBootstrapListener(ngUpgrade) {
        return function () {
            setUpLocationSync(ngUpgrade);
        };
    }
    /**
     * Sets up a location change listener to trigger `history.pushState`.
     * Works around the problem that `onPopState` does not trigger `history.pushState`.
     * Must be called *after* calling `UpgradeModule.bootstrap`.
     *
     * @param ngUpgrade The upgrade NgModule.
     * @param urlType The location strategy.
     * @see `HashLocationStrategy`
     * @see `PathLocationStrategy`
     *
     * @publicApi
     */
    function setUpLocationSync(ngUpgrade, urlType) {
        if (urlType === void 0) { urlType = 'path'; }
        if (!ngUpgrade.$injector) {
            throw new Error("\n        RouterUpgradeInitializer can be used only after UpgradeModule.bootstrap has been called.\n        Remove RouterUpgradeInitializer and call setUpLocationSync after UpgradeModule.bootstrap.\n      ");
        }
        var router$1 = ngUpgrade.injector.get(router.Router);
        var location = ngUpgrade.injector.get(common.Location);
        ngUpgrade.$injector.get('$rootScope')
            .$on('$locationChangeStart', function (_, next, __) {
            var url;
            if (urlType === 'path') {
                url = resolveUrl(next);
            }
            else if (urlType === 'hash') {
                // Remove the first hash from the URL
                var hashIdx = next.indexOf('#');
                url = resolveUrl(next.substring(0, hashIdx) + next.substring(hashIdx + 1));
            }
            else {
                throw 'Invalid URLType passed to setUpLocationSync: ' + urlType;
            }
            var path = location.normalize(url.pathname);
            router$1.navigateByUrl(path + url.search + url.hash);
        });
    }
    /**
     * Normalizes and parses a URL.
     *
     * - Normalizing means that a relative URL will be resolved into an absolute URL in the context of
     *   the application document.
     * - Parsing means that the anchor's `protocol`, `hostname`, `port`, `pathname` and related
     *   properties are all populated to reflect the normalized URL.
     *
     * While this approach has wide compatibility, it doesn't work as expected on IE. On IE, normalizing
     * happens similar to other browsers, but the parsed components will not be set. (E.g. if you assign
     * `a.href = 'foo'`, then `a.protocol`, `a.host`, etc. will not be correctly updated.)
     * We work around that by performing the parsing in a 2nd step by taking a previously normalized URL
     * and assigning it again. This correctly populates all properties.
     *
     * See
     * https://github.com/angular/angular.js/blob/2c7400e7d07b0f6cec1817dab40b9250ce8ebce6/src/ng/urlUtils.js#L26-L33
     * for more info.
     */
    var anchor;
    function resolveUrl(url) {
        if (!anchor) {
            anchor = document.createElement('a');
        }
        anchor.setAttribute('href', url);
        anchor.setAttribute('href', anchor.href);
        return {
            // IE does not start `pathname` with `/` like other browsers.
            pathname: "/" + anchor.pathname.replace(/^\//, ''),
            search: anchor.search,
            hash: anchor.hash
        };
    }

    /**
     * @license
     * Copyright Google LLC All Rights Reserved.
     *
     * Use of this source code is governed by an MIT-style license that can be
     * found in the LICENSE file at https://angular.io/license
     */
    // This file only reexports content of the `src` folder. Keep it that way.

    /**
     * @license
     * Copyright Google LLC All Rights Reserved.
     *
     * Use of this source code is governed by an MIT-style license that can be
     * found in the LICENSE file at https://angular.io/license
     */

    /**
     * Generated bundle index. Do not edit.
     */

    exports.RouterUpgradeInitializer = RouterUpgradeInitializer;
    exports.locationSyncBootstrapListener = locationSyncBootstrapListener;
    exports.setUpLocationSync = setUpLocationSync;
    exports.ɵ0 = ɵ0;

    Object.defineProperty(exports, '__esModule', { value: true });

})));
//# sourceMappingURL=router-upgrade.umd.js.map
