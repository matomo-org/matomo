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

    var windowAny = window;
    var pluginList = windowAny.piwik.pluginsLoadedAndActivated;
    var AppModule = /** @class */ (function () {
        function AppModule(upgrade, compiler, parentInjector) {
            this.upgrade = upgrade;
            this.compiler = compiler;
            this.parentInjector = parentInjector;
        }
        AppModule.prototype.ngDoBootstrap = function () {
            this.upgrade.bootstrap(document.body, ['piwikApp'], { strictDi: false });
        };
        return AppModule;
    }());
    AppModule.ɵfac = i0__namespace.ɵɵngDeclareFactory({ minVersion: "12.0.0", version: "12.1.0", ngImport: i0__namespace, type: AppModule, deps: [{ token: i1__namespace.UpgradeModule }, { token: i0__namespace.Compiler }, { token: i0__namespace.Injector }], target: i0__namespace.ɵɵFactoryTarget.NgModule });
    AppModule.ɵmod = i0__namespace.ɵɵngDeclareNgModule({ minVersion: "12.0.0", version: "12.1.0", ngImport: i0__namespace, type: AppModule, imports: [platformBrowser.BrowserModule,
            i1.UpgradeModule] });
    AppModule.ɵinj = i0__namespace.ɵɵngDeclareInjector({ minVersion: "12.0.0", version: "12.1.0", ngImport: i0__namespace, type: AppModule, imports: [[
                platformBrowser.BrowserModule,
                i1.UpgradeModule,
            ]] });
    i0__namespace.ɵɵngDeclareClassMetadata({ minVersion: "12.0.0", version: "12.1.0", ngImport: i0__namespace, type: AppModule, decorators: [{
                type: i0.NgModule,
                args: [{
                        imports: [
                            platformBrowser.BrowserModule,
                            i1.UpgradeModule,
                        ],
                    }]
            }], ctorParameters: function () { return [{ type: i1__namespace.UpgradeModule }, { type: i0__namespace.Compiler }, { type: i0__namespace.Injector }]; } });

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
