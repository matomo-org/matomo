import * as i0 from '@angular/core';
import { Injectable, Component, NgModule } from '@angular/core';

class LibraryService {
    constructor() { }
}
LibraryService.ɵfac = i0.ɵɵngDeclareFactory({ minVersion: "12.0.0", version: "12.1.0", ngImport: i0, type: LibraryService, deps: [], target: i0.ɵɵFactoryTarget.Injectable });
LibraryService.ɵprov = i0.ɵɵngDeclareInjectable({ minVersion: "12.0.0", version: "12.1.0", ngImport: i0, type: LibraryService, providedIn: 'root' });
i0.ɵɵngDeclareClassMetadata({ minVersion: "12.0.0", version: "12.1.0", ngImport: i0, type: LibraryService, decorators: [{
            type: Injectable,
            args: [{
                    providedIn: 'root'
                }]
        }], ctorParameters: function () { return []; } });

class LibraryComponent {
    constructor() { }
    ngOnInit() {
    }
}
LibraryComponent.ɵfac = i0.ɵɵngDeclareFactory({ minVersion: "12.0.0", version: "12.1.0", ngImport: i0, type: LibraryComponent, deps: [], target: i0.ɵɵFactoryTarget.Component });
LibraryComponent.ɵcmp = i0.ɵɵngDeclareComponent({ minVersion: "12.0.0", version: "12.1.0", type: LibraryComponent, selector: "lib-library", ngImport: i0, template: `
    <p>
      library works!
    </p>
  `, isInline: true });
i0.ɵɵngDeclareClassMetadata({ minVersion: "12.0.0", version: "12.1.0", ngImport: i0, type: LibraryComponent, decorators: [{
            type: Component,
            args: [{
                    selector: 'lib-library',
                    template: `
    <p>
      library works!
    </p>
  `,
                    styles: []
                }]
        }], ctorParameters: function () { return []; } });

class LibraryModule {
}
LibraryModule.ɵfac = i0.ɵɵngDeclareFactory({ minVersion: "12.0.0", version: "12.1.0", ngImport: i0, type: LibraryModule, deps: [], target: i0.ɵɵFactoryTarget.NgModule });
LibraryModule.ɵmod = i0.ɵɵngDeclareNgModule({ minVersion: "12.0.0", version: "12.1.0", ngImport: i0, type: LibraryModule, declarations: [LibraryComponent], exports: [LibraryComponent] });
LibraryModule.ɵinj = i0.ɵɵngDeclareInjector({ minVersion: "12.0.0", version: "12.1.0", ngImport: i0, type: LibraryModule, imports: [[]] });
i0.ɵɵngDeclareClassMetadata({ minVersion: "12.0.0", version: "12.1.0", ngImport: i0, type: LibraryModule, decorators: [{
            type: NgModule,
            args: [{
                    declarations: [
                        LibraryComponent
                    ],
                    imports: [],
                    exports: [
                        LibraryComponent
                    ]
                }]
        }] });

/*
 * Public API Surface of library
 */

/**
 * Generated bundle index. Do not edit.
 */

export { LibraryComponent, LibraryModule, LibraryService };
//# sourceMappingURL=plugins-example-angular.js.map
