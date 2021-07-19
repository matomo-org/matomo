import {Component, EventEmitter, Input, Output} from "@angular/core";

// TODO: enriched headline

@Component({
    selector: 'enriched-headline',
    template: `
    <div
        class="enrichedHeadline"
        (mouseenter)="showIcons = true"
        (mouseleave)="showIcons = false"
    >
        <div *ngIf="!editUrl" class="title" tabindex="6">
            <ng-content></ng-content>
        </div>
        <a *ngIf="editUrl" class="title" [attr.href]="editUrl" title="{{'CoreHome_ClickToEditX'|translate:featureName}}">
            <ng-content></ng-content>
        </a>
        
        <span *ngIf="showIcons || showInlineHelp" class="iconsBar">
            <a
                *ngIf="helpUrl && !inlineHelp"
                rel="noreferrer noopener"
                target="_blank"
                [attr.href]="helpUrl"
                title="{{ 'CoreHome_ExternalHelp'|translate }}"
                class="helpIcon"
            >
                
            </a>
        </span>
    </div>
    `,
})
export class EnrichedHeadlineComponent {
    @Input() helpUrl: string = '';
    @Input() editUrl: string = '';
    @Input() reportGenerated?: string;
    @Input() featureName: string = '';
    @Input() inlineHelp?: string = '';
    @Input() showReportGenerated: boolean = false;

    showIcons: boolean = false;
    showInlineHelp: boolean = false;
}

/*
        <a class="helpIcon"><span class="icon-help"></span></a>

        <a ng-if="inlineHelp"
           title="{{ 'General_Help'|translate }}"
           ng-click="view.showInlineHelp=!view.showInlineHelp"
           class="helpIcon" ng-class="{ 'active': view.showInlineHelp }"><span class="icon-help"></span></a>

        <div class="ratingIcons"
             piwik-rate-feature
             title="{{ featureName }}"></div>
    </span>

    <div ng-show="showReportGenerated" class="icon-clock report-generated"></div>

    <div class="inlineHelp" ng-show="view.showInlineHelp">
        <div ng-bind-html="inlineHelp"></div>
        <a ng-if="helpUrl"
           rel="noreferrer noopener"
           target="_blank"
           href="{{ helpUrl }}"
           class="readMore">{{ 'General_MoreDetails'|translate }}</a>
    </div>
</div>

 */