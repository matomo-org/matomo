import {Component, EventEmitter, Input, Output} from "@angular/core";

// TODO: enriched headline

@Component({
    selector: 'content-block',
    template: `
    <div class="card">
        <div class="card-content">
            <h2 *ngIf="contentTitle && !feature && !helpUrl && !helpText" class="card-title">{{contentTitle}}</h2>
            <h2
                *ngIf="contentTitle && (feature || helpUrl || helpText)" class="card-title"
            >
                {{contentTitle}}
            </h2>
            <div>
                <ng-content></ng-content>
            </div>
        </div>
    </div>
    `,
})
export class ContentBlockComponent {
    @Input() contentTitle: string = '';
    @Input() feature: string = '';
    @Input() helpUrl: string = '';
    @Input() helpText: string = '';
    @Input() anchor?: string;


}

/*
<div class="card">
    <div class="card-content">
        <h2 ng-if="contentTitle && !feature && !helpUrl && !helpText" class="card-title">{{contentTitle}}</h2>
        <h2 ng-if="contentTitle && (feature || helpUrl || helpText)" class="card-title"
              piwik-enriched-headline feature-name="{{feature}}" help-url="{{helpUrl}}" inline-help="{{ helpText }}">
            {{contentTitle}}</h2>
        <div ng-transclude>
        </div>
    </div>
</div>
 */