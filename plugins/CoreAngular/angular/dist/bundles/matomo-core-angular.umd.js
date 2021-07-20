(function (global, factory) {
    typeof exports === 'object' && typeof module !== 'undefined' ? factory(exports, require('@angular/platform-browser-dynamic'), require('@angular/core'), require('@angular/platform-browser'), require('@angular/upgrade/static')) :
    typeof define === 'function' && define.amd ? define('@matomo/core-angular', ['exports', '@angular/platform-browser-dynamic', '@angular/core', '@angular/platform-browser', '@angular/upgrade/static'], factory) :
    (global = typeof globalThis !== 'undefined' ? globalThis : global || self, factory((global.matomo = global.matomo || {}, global.matomo['core-angular'] = {}), global.ng.platformBrowserDynamic, global.ng.core, global.ng.platformBrowser, global.ng.upgrade.static));
}(this, (function (exports, platformBrowserDynamic, i0, platformBrowser, i1) { 'use strict';

    function _interopNamespace(e) {
        if (e && e.__esModule) return e;
        var n = Object.create(null);
        if (e) {
            Object.keys(e).forEach(function (k) {
                if (k !== 'default') {
                    var d = Object.getOwnPropertyDescriptor(e, k);
                    Object.defineProperty(n, k, d.get ? d : {
                        enumerable: true,
                        get: function () {
                            return e[k];
                        }
                    });
                }
            });
        }
        n['default'] = e;
        return Object.freeze(n);
    }

    var i0__namespace = /*#__PURE__*/_interopNamespace(i0);
    var i1__namespace = /*#__PURE__*/_interopNamespace(i1);

    var AppModule = /** @class */ (function () {
        function AppModule(upgrade) {
            this.upgrade = upgrade;
        }
        AppModule.prototype.ngDoBootstrap = function () {
            try {
                this.upgrade.bootstrap(document.body, ['piwikApp'], { strictDi: false });
            }
            catch (e) {
                console.log("failed to bootstrap app: " + (e.stack || e.message || e));
            }
        };
        return AppModule;
    }());
    AppModule.ɵfac = function AppModule_Factory(t) { return new (t || AppModule)(i0__namespace.ɵɵinject(i1__namespace.UpgradeModule)); };
    AppModule.ɵmod = /*@__PURE__*/ i0__namespace.ɵɵdefineNgModule({ type: AppModule });
    AppModule.ɵinj = /*@__PURE__*/ i0__namespace.ɵɵdefineInjector({ imports: [[
                platformBrowser.BrowserModule,
                i1.UpgradeModule,
            ]] });
    (function () {
        (typeof ngDevMode === "undefined" || ngDevMode) && i0__namespace.ɵsetClassMetadata(AppModule, [{
                type: i0.NgModule,
                args: [{
                        imports: [
                            platformBrowser.BrowserModule,
                            i1.UpgradeModule,
                        ],
                    }]
            }], function () { return [{ type: i1__namespace.UpgradeModule }]; }, null);
    })();
    (function () {
        (typeof ngJitMode === "undefined" || ngJitMode) && i0__namespace.ɵɵsetNgModuleScope(AppModule, { imports: [platformBrowser.BrowserModule,
                i1.UpgradeModule] });
    })();

    /*
     * Public API Surface of CoreAngular
     */
    //if (environment.production) {
    //    enableProdMode();
    //}
    platformBrowserDynamic.platformBrowserDynamic().bootstrapModule(AppModule)
        .catch(function (err) { return console.error(err); });

    /**
     * Generated bundle index. Do not edit.
     */

    exports.AppModule = AppModule;

    Object.defineProperty(exports, '__esModule', { value: true });

})));
//# sourceMappingURL=matomo-core-angular.umd.js.map
