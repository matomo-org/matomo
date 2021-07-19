import {Component, EventEmitter, Input, Output} from "@angular/core";

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
                <span class="icon-help"></span>
            </a>
            
            <a
                *ngIf="inlineHelp"
                title="{{ 'General_Help'|translate }}"
                (click)="showInlineHelp=!showInlineHelp"
                class="helpIcon"
                [class.active]="showInlineHelp"
            >
                <span class="icon-help"></span>
            </a>
            
            <rate-feature class="ratingIcons" title="{{ featureName }}"></rate-feature>
        </span>

        <div *ngIf="showReportGenerated" class="icon-clock report-generated"></div>

        <div class="inlineHelp" *ngIf="showInlineHelp">
            <div [innerHTML]="inlineHelp"></div>
            <a
                    *ngIf="helpUrl"
                    rel="noreferrer noopener"
                    target="_blank"
                    [attr.href]="helpUrl"
                    class="readMore"
            >
                {{ 'General_MoreDetails'|translate }}
            </a>
        </div>
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
