import { Component, Input, NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule } from '@angular/router';

class ActivityIndicatorComponent {
    constructor() {
        this.loading = false;
        this.loadingMessage = '';
    }
    ngOnInit() {
        if (!this.loadingMessage) {
            this.loadingMessage = 'Loading data...';
        }
    }
}
ActivityIndicatorComponent.decorators = [
    { type: Component, args: [{
                selector: 'piwik-activity-indicator',
                template: "<div *ngIf=\"loading\" class=\"loadingPiwik\">\n    <!-- Below image will be uncommented while integrating in matomo project  -->\n    <!-- <img src=\"plugins/Morpheus/images/loading-blue.gif\" alt=\"\" />  -->\n    <span>{{ loadingMessage }}</span>\n</div>",
                styles: [".loadingPiwik{font-size:1.1em;color:#444;padding:.5em}.loadingPiwik img{margin-right:5px}.loadingSegment{color:#999;font-size:13px;margin-left:28px;display:none}#root>#loadingError{margin-left:20px;margin-right:20px}#loadingError{font-size:15px;padding:8px 0;display:none;color:#5793d4;font-weight:400}"]
            },] }
];
ActivityIndicatorComponent.ctorParameters = () => [];
ActivityIndicatorComponent.propDecorators = {
    loading: [{ type: Input }],
    loadingMessage: [{ type: Input }]
};

class ActivityIndicatorModule {
}
ActivityIndicatorModule.decorators = [
    { type: NgModule, args: [{
                declarations: [ActivityIndicatorComponent],
                imports: [
                    CommonModule
                ],
                exports: [ActivityIndicatorComponent]
            },] }
];

class CoreHomeModule {
}
CoreHomeModule.decorators = [
    { type: NgModule, args: [{
                declarations: [],
                imports: [
                    CommonModule,
                    RouterModule,
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

export { ActivityIndicatorComponent, CoreHomeModule, ActivityIndicatorModule as ɵa };
//# sourceMappingURL=core-home.js.map
