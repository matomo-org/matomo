import {
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
export class EnrichedHeadlineComponent implements AfterContentInit {
    constructor(private componentElement: ElementRef) {}

    @Input() helpUrl: string = '';
    @Input() editUrl: string = '';
    @Input() reportGenerated?: string;
    @Input() featureName: string = '';
    @Input() inlineHelp?: string = '';
    @Input() showReportGenerated: boolean = false;

    showIcons: boolean = false;
    showInlineHelp: boolean = false;

    ngAfterContentInit(): void {
        this.findInlineHelpInContentIfRequired();
        this.findFeatureNameInContentIfRequired();
        this.addReportGeneratedTooltip();
    }

    private addReportGeneratedTooltip() {
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
        let helpNode = $('.title .inlineHelp', this.componentElement.nativeElement);

        if ((!helpNode || !helpNode.length) && element.next()) {
            // hack for reports :(
            helpNode = element.next().find('.reportDocumentation');
        }

        if (helpNode && helpNode.length) {
            // hackish solution to get binded html of p tag within the help node
            // at this point the ng-bind-html is not yet converted into html when report is not
            // initially loaded. Using $compile doesn't work. So get and set it manually
            const helpParagraph = $('p[ng-bind-html]', helpNode); // TODO: this will eventually not work as more components are converted
            console.log(helpParagraph.html());
/*
            if (helpParagraph.length) {
                helpParagraph.html($parse(helpParagraph.attr('ng-bind-html')));
            }

            if ($.trim(helpNode.text())) {
                scope.inlineHelp = $.trim(helpNode.html());
            }
            helpNode.remove();
            */
        }
    }
}
