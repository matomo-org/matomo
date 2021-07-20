import {
    AfterContentChecked,
    AfterContentInit,
    Component,
    ContentChild,
    ElementRef,
    EventEmitter,
    Input,
    OnInit,
    Output
} from "@angular/core";

declare var piwik: any;
declare var $: any;

// NOTE: here iconsBar uses [hidden] instead of *ngIf, because rate-feature needs to exist when the user clicks on the modal.
// using ngIf would destroy the component, and so remove the event handler that listens to the dialog button 'Yes'.

@Component({
    selector: 'enriched-headline',
    template: `
    <div
        class="enrichedHeadline"
        (mouseenter)="showIcons = true"
        (mouseleave)="showIcons = false"
    >
        <div *ngIf="!editUrl" class="title" tabindex="6">
            <ng-container *ngTemplateOutlet="titleTemplate"></ng-container>
        </div>
        <a *ngIf="editUrl" class="title" [attr.href]="editUrl" title="{{'CoreHome_ClickToEditX'|translate:featureName}}">
            <ng-container *ngTemplateOutlet="titleTemplate"></ng-container>
        </a>
        
        <span [hidden]="!showIcons && !showInlineHelp" class="iconsBar">
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
            
            <rate-feature class="ratingIcons" [title]="featureName"></rate-feature>
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

        <ng-template #titleTemplate><ng-content></ng-content></ng-template>
    </div>
    `,
})
export class EnrichedHeadlineComponent implements AfterContentChecked {
    constructor(private componentElement: ElementRef) {}

    @Input() helpUrl: string = '';
    @Input() editUrl: string = '';
    @Input() reportGenerated?: string;
    @Input() featureName: string = '';
    @Input() inlineHelp?: string = '';
    @Input() showReportGenerated: boolean = false;

    tooltipAdded: boolean = false;
    showIcons: boolean = false;
    showInlineHelp: boolean = false;

    ngAfterContentChecked() {
        this.findFeatureNameInContentIfRequired();
        this.addReportGeneratedTooltip();
        this.findInlineHelpInContentIfRequired();
    }

    private addReportGeneratedTooltip() {
        if (this.tooltipAdded) {
            return;
        }

        if (!this.reportGenerated) {
            return;
        }

        if (!piwik.periods.parse(piwik.period, piwik.currentDateString).coinsToday()) {
            return;
        }

        const reportGeneratedElement = this.componentElement.nativeElement.querySelector('.report-generated');
        $(reportGeneratedElement).tooltip({
            track: true,
            content: this.reportGenerated,
            items: 'div',
            show: false,
            hide: false,
        });

        this.showReportGenerated = true;
        this.tooltipAdded = true;
    }

    private findFeatureNameInContentIfRequired() {
        if (this.featureName) {
            return;
        }

        this.featureName = $.trim($(this.componentElement.nativeElement).find('.title').first().text());
    }

    private findInlineHelpInContentIfRequired() {
        if (this.inlineHelp) {
            return;
        }

        // TODO: jquery should not be used in angular forever, it all must be replaced.
        const element = $(this.componentElement.nativeElement);
        let helpNode = $('.title .inlineHelp', element);

        if ((!helpNode || !helpNode.length) && element.next()) {
            // hack for reports :(
            helpNode = element.next().find('.reportDocumentation');
        }
        if ((!helpNode || !helpNode.length) && element.parent().next()) { // executed when using the enriched-headline adapter
            // hack for reports (2) :(
            helpNode = element.parent().next().find('.reportDocumentation');
        }

        if (helpNode && helpNode.length) {
            if ($.trim(helpNode.text())) {
                this.inlineHelp = $.trim(helpNode.html());
            }
            helpNode.remove();
        }
    }
}
