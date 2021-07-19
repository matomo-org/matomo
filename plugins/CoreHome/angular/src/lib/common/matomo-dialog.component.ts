import {
    Directive,
    ElementRef,
    EventEmitter,
    Input,
    OnChanges,
    OnInit,
    Output,
} from "@angular/core";

declare var piwik: any;

@Directive({
    selector: '[matomoDialog]',
})
export class MatomoDialogDirective implements OnInit, OnChanges {
    constructor(private componentElement:ElementRef) {}

    @Input() showModal: boolean = false; // TODO: better to handle showing/closing programmatically
    @Output() onYesClick: EventEmitter<void> = new EventEmitter<void>();
    @Output() onNoClick: EventEmitter<void> = new EventEmitter<void>();
    @Output() onClose: EventEmitter<void> = new EventEmitter<void>();

    ngOnInit() {
        this.componentElement.nativeElement.style.display = 'none';
    }

    ngOnChanges() {
        if (this.showModal) {
            this.doShowModal();
        }
    }

    private doShowModal() {
        piwik.helper.modalConfirm(this.componentElement.nativeElement, {
            yes: () => {
                this.onYesClick.emit();
            },
            no: () => {
                this.onNoClick.emit();
            },
        }, {
            onCloseEnd: () => {
                this.onClose.emit();
            },
        });
    }
}
