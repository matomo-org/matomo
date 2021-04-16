(function (global, factory) {
    typeof exports === 'object' && typeof module !== 'undefined' ? factory(exports, require('@angular/core'), require('@angular/common'), require('@angular/router')) :
    typeof define === 'function' && define.amd ? define('core-home', ['exports', '@angular/core', '@angular/common', '@angular/router'], factory) :
    (global = typeof globalThis !== 'undefined' ? globalThis : global || self, factory(global['core-home'] = {}, global.ng.core, global.ng.common, global.ng.router));
}(this, (function (exports, core, common, router) { 'use strict';

    var ActivityIndicatorComponent = /** @class */ (function () {
        function ActivityIndicatorComponent() {
            this.loading = false;
            this.loadingMessage = '';
        }
        ActivityIndicatorComponent.prototype.ngOnInit = function () {
            if (!this.loadingMessage) {
                this.loadingMessage = 'Loading data...';
            }
        };
        return ActivityIndicatorComponent;
    }());
    ActivityIndicatorComponent.decorators = [
        { type: core.Component, args: [{
                    selector: 'piwik-activity-indicator',
                    template: "<div *ngIf=\"loading\" class=\"loadingPiwik\">\n    <img src=\"plugins/Morpheus/images/loading-blue.gif\" alt=\"\" />\n    <span>{{ loadingMessage }}</span>\n</div>\n",
                    styles: [""]
                },] }
    ];
    ActivityIndicatorComponent.ctorParameters = function () { return []; };
    ActivityIndicatorComponent.propDecorators = {
        loading: [{ type: core.Input }],
        loadingMessage: [{ type: core.Input }]
    };

    var ActivityIndicatorModule = /** @class */ (function () {
        function ActivityIndicatorModule() {
        }
        return ActivityIndicatorModule;
    }());
    ActivityIndicatorModule.decorators = [
        { type: core.NgModule, args: [{
                    entryComponents: [ActivityIndicatorComponent],
                    declarations: [ActivityIndicatorComponent],
                    imports: [
                        common.CommonModule
                    ],
                    exports: [ActivityIndicatorComponent]
                },] }
    ];

    var CoreHomeModule = /** @class */ (function () {
        function CoreHomeModule() {
        }
        return CoreHomeModule;
    }());
    CoreHomeModule.decorators = [
        { type: core.NgModule, args: [{
                    declarations: [],
                    imports: [
                        common.CommonModule,
                        router.RouterModule,
                        ActivityIndicatorModule
                    ],
                    exports: [ActivityIndicatorComponent]
                },] }
    ];

    /*
     * Public API Surface of core-home
     */

    /**
     * Generated bundle index. Do not edit.
     */

    exports.ActivityIndicatorComponent = ActivityIndicatorComponent;
    exports.CoreHomeModule = CoreHomeModule;
    exports.ea = ActivityIndicatorModule;

    Object.defineProperty(exports, '__esModule', { value: true });

})));
//# sourceMappingURL=core-home.umd.js.map
