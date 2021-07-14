import {Directive, ElementRef, EventEmitter, HostListener, Output} from '@angular/core';

@Directive({
    selector: '[focusAnywhereButHere]'
})
export class FocusAnywhereButHereDirective {
    private isMouseDown: boolean = false;
    private hasScrolled: boolean = false;

    @Output() onLoseFocus = new EventEmitter<void>();

    constructor(private el: ElementRef) {}

    @HostListener('document:keyup', ['$event'])
    onEscapeHandler(event: KeyboardEvent) {
        if (event.key.charCodeAt(0) === 27) {// TODO: test
            this.isMouseDown = false;
            this.hasScrolled = false;
            this.onLoseFocus.emit();
        }
    }

    @HostListener('document:mouseup', ['$event'])
    onClickOutsideElement(event: Event) {
        const hadUsedScrollbar = this.isMouseDown && this.hasScrolled;
        this.isMouseDown = false;
        this.hasScrolled = false;

        if (hadUsedScrollbar) {
            return;
        }

        if (!this.el.nativeElement.contains(event.target)) {
            this.onLoseFocus.emit();
        }
    }

    @HostListener('document:scroll', ['$event'])
    onScroll() {
        this.hasScrolled = true;
    }

    @HostListener('document:mousedown', ['$event'])
    onMouseDown() {
        this.isMouseDown = true;
        this.hasScrolled = false;
    }
}
