import {
    AfterContentChecked,
    AfterContentInit,
    Component,
    ElementRef,
    EventEmitter,
    Input,
    OnInit,
    Output,
    ViewChild
} from "@angular/core";

let adminContent: Element|null;

declare var $: any;

@Component({
    selector: 'content-block',
    template: `
    <div class="card">
        <a *ngIf="anchor" [attr.id]="anchor"></a>
        <div class="card-content">
            <h2 *ngIf="contentTitle && !feature && !helpUrl && !helpText" class="card-title">{{contentTitle}}</h2>
            <h2
                *ngIf="contentTitle && (feature || helpUrl || helpText)" class="card-title"
            >
                {{contentTitle}}
            </h2>
            <div class="contentContainer" #contentContainer>
                <ng-content></ng-content>
            </div>
        </div>
    </div>
    `,
    styles: [
        '.contentContainer .contentHelp { display: none }',
    ],
})
export class ContentBlockComponent implements AfterContentInit, AfterContentChecked {
    constructor(private componentElement: ElementRef) {}

    @Input() contentTitle: string = '';
    @Input() feature: boolean = false;
    @Input() helpUrl: string = '';
    @Input() helpText: string = '';
    @Input() anchor?: string;

    @ViewChild('contentContainer') contentContainer?: ElementRef;

    ngAfterContentInit() {
        if (!adminContent) {
            // cache admin node for further content blocks
            // TODO: it will effectively get cleaned up because we do a pageload in admin pages, but we shouldn't
            // really be doing this.
            adminContent = document.querySelector('#content.admin');
        }

        let contentTopPosition: number|null = null;
        if (adminContent) {
            contentTopPosition = $(adminContent).offset().top;
        }

        if (contentTopPosition || contentTopPosition === 0) {
            const parents = $(this.componentElement.nativeElement).parentsUntil('.col', '[piwik-widget-loader]');
            let topThis;
            if (parents.length) {
                // when shown within the widget loader, we need to get the offset of that element
                // as the widget loader might be still shown. Would otherwise not position correctly
                // the widgets on the admin home page
                topThis = parents.offset().top;
            } else {
                topThis = $(this.componentElement.nativeElement).offset().top;
            }

            if ((topThis - contentTopPosition) < 17) {
                // we make sure to display the first card with no margin-top to have it on same as line as
                // navigation
                $(this.componentElement.nativeElement).css('marginTop', '0');
            }
        }
    }

    ngAfterContentChecked(): void {
        if (this.helpText) {
            return;
        }

        const inlineHelp = this.contentContainer?.nativeElement.querySelector('.contentHelp');
        if (inlineHelp) {
            this.helpText = inlineHelp.innerHTML;
        }
    }
}