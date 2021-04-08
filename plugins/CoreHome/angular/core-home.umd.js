(function (global, factory) {
    typeof exports === 'object' && typeof module !== 'undefined' ? factory(exports, require('@angular/core'), require('@angular/common'), require('@angular/router'), require('@angular/upgrade/static'), require('@angular/platform-browser')) :
    typeof define === 'function' && define.amd ? define('core-home', ['exports', '@angular/core', '@angular/common', '@angular/router', '@angular/upgrade/static', '@angular/platform-browser'], factory) :
    (global = typeof globalThis !== 'undefined' ? globalThis : global || self, factory(global['core-home'] = {}, global.ng.core, global.ng.common, global.ng.router, global.ng.upgrade.static, global.ng.platformBrowser));
}(this, (function (exports, core, common, router, _static, platformBrowser) { 'use strict';

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
                    template: "<div *ngIf=\"loading\" class=\"loadingPiwik\">\n    <!-- Below image will be uncommented while integrating in matomo project  -->\n    <!-- <img src=\"plugins/Morpheus/images/loading-blue.gif\" alt=\"\" />  -->\n    <span>{{ loadingMessage }}</span>\n</div>",
                    styles: [".loadingPiwik{font-size:1.1em;color:#444;padding:.5em}.loadingPiwik img{margin-right:5px}.loadingSegment{color:#999;font-size:13px;margin-left:28px;display:none}#root>#loadingError{margin-left:20px;margin-right:20px}#loadingError{font-size:15px;padding:8px 0;display:none;color:#5793d4;font-weight:400}"]
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
    exports.ɵa = ActivityIndicatorModule;

    Object.defineProperty(exports, '__esModule', { value: true });

})));
//# sourceMappingURL=core-home.umd.js.map
