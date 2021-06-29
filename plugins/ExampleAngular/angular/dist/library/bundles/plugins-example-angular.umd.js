(function (global, factory) {
    typeof exports === 'object' && typeof module !== 'undefined' ? factory(exports, require('@angular/core')) :
    typeof define === 'function' && define.amd ? define('@plugins/example-angular', ['exports', '@angular/core'], factory) :
    (global = typeof globalThis !== 'undefined' ? globalThis : global || self, factory((global.plugins = global.plugins || {}, global.plugins['example-angular'] = {}), global.ng.core));
}(this, (function (exports, i0) { 'use strict';

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

    var LibraryService = /** @class */ (function () {
        function LibraryService() {
        }
        return LibraryService;
    }());
    LibraryService.ɵfac = i0__namespace.ɵɵngDeclareFactory({ minVersion: "12.0.0", version: "12.1.0", ngImport: i0__namespace, type: LibraryService, deps: [], target: i0__namespace.ɵɵFactoryTarget.Injectable });
    LibraryService.ɵprov = i0__namespace.ɵɵngDeclareInjectable({ minVersion: "12.0.0", version: "12.1.0", ngImport: i0__namespace, type: LibraryService, providedIn: 'root' });
    i0__namespace.ɵɵngDeclareClassMetadata({ minVersion: "12.0.0", version: "12.1.0", ngImport: i0__namespace, type: LibraryService, decorators: [{
                type: i0.Injectable,
                args: [{
                        providedIn: 'root'
                    }]
            }], ctorParameters: function () { return []; } });

    var LibraryComponent = /** @class */ (function () {
        function LibraryComponent() {
        }
        LibraryComponent.prototype.ngOnInit = function () {
        };
        return LibraryComponent;
    }());
    LibraryComponent.ɵfac = i0__namespace.ɵɵngDeclareFactory({ minVersion: "12.0.0", version: "12.1.0", ngImport: i0__namespace, type: LibraryComponent, deps: [], target: i0__namespace.ɵɵFactoryTarget.Component });
    LibraryComponent.ɵcmp = i0__namespace.ɵɵngDeclareComponent({ minVersion: "12.0.0", version: "12.1.0", type: LibraryComponent, selector: "lib-library", ngImport: i0__namespace, template: "\n    <p>\n      library works!\n    </p>\n  ", isInline: true });
    i0__namespace.ɵɵngDeclareClassMetadata({ minVersion: "12.0.0", version: "12.1.0", ngImport: i0__namespace, type: LibraryComponent, decorators: [{
                type: i0.Component,
                args: [{
                        selector: 'lib-library',
                        template: "\n    <p>\n      library works!\n    </p>\n  ",
                        styles: []
                    }]
            }], ctorParameters: function () { return []; } });

    var LibraryModule = /** @class */ (function () {
        function LibraryModule() {
        }
        return LibraryModule;
    }());
    LibraryModule.ɵfac = i0__namespace.ɵɵngDeclareFactory({ minVersion: "12.0.0", version: "12.1.0", ngImport: i0__namespace, type: LibraryModule, deps: [], target: i0__namespace.ɵɵFactoryTarget.NgModule });
    LibraryModule.ɵmod = i0__namespace.ɵɵngDeclareNgModule({ minVersion: "12.0.0", version: "12.1.0", ngImport: i0__namespace, type: LibraryModule, declarations: [LibraryComponent], exports: [LibraryComponent] });
    LibraryModule.ɵinj = i0__namespace.ɵɵngDeclareInjector({ minVersion: "12.0.0", version: "12.1.0", ngImport: i0__namespace, type: LibraryModule, imports: [[]] });
    i0__namespace.ɵɵngDeclareClassMetadata({ minVersion: "12.0.0", version: "12.1.0", ngImport: i0__namespace, type: LibraryModule, decorators: [{
                type: i0.NgModule,
                args: [{
                        declarations: [
                            LibraryComponent
                        ],
                        imports: [],
                        exports: [
                            LibraryComponent
                        ]
                    }]
            }] });

    /*
     * Public API Surface of library
     */

    /**
     * Generated bundle index. Do not edit.
     */

    exports.LibraryComponent = LibraryComponent;
    exports.LibraryModule = LibraryModule;
    exports.LibraryService = LibraryService;

    Object.defineProperty(exports, '__esModule', { value: true });

})));
//# sourceMappingURL=plugins-example-angular.umd.js.map
