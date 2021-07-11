(function (global, factory) {
  typeof exports === 'object' && typeof module !== 'undefined' ? factory(exports, require('@angular/core'), require('@angular/upgrade/static'), require('@angular/platform-browser-dynamic'), require('@angular/platform-browser')) :
  typeof define === 'function' && define.amd ? define('@matomo/example-angular', ['exports', '@angular/core', '@angular/upgrade/static', '@angular/platform-browser-dynamic', '@angular/platform-browser'], factory) :
  (global = typeof globalThis !== 'undefined' ? globalThis : global || self, factory((global.matomo = global.matomo || {}, global.matomo['example-angular'] = {}), global.ng.core, global.ng.upgrade.static, global.ng.platformBrowserDynamic, global.ng.platformBrowser));
}(this, (function (exports, i0, _static, platformBrowserDynamic, platformBrowser) { 'use strict';

  function _interopNamespace(e) {
    if (e && e.__esModule) return e;
    var n = Object.create(null);
    if (e) {
      Object.keys(e).forEach(function (k) {
        if (k !== 'default') {
          var d = Object.getOwnPropertyDescriptor(e, k);
          Object.defineProperty(n, k, d.get ? d : {
            enumerable: true,
            get: function () {
              return e[k];
            }
          });
        }
      });
    }
    n['default'] = e;
    return Object.freeze(n);
  }

  var i0__namespace = /*#__PURE__*/_interopNamespace(i0);

  var LibraryComponent = /** @class */ (function () {
      function LibraryComponent() {
      }
      LibraryComponent.prototype.ngOnInit = function () {
          console.log('no?');
      };
      return LibraryComponent;
  }());
  LibraryComponent.ɵfac = i0__namespace.ɵɵngDeclareFactory({ minVersion: "12.0.0", version: "12.1.0", ngImport: i0__namespace, type: LibraryComponent, deps: [], target: i0__namespace.ɵɵFactoryTarget.Component });
  LibraryComponent.ɵcmp = i0__namespace.ɵɵngDeclareComponent({ minVersion: "12.0.0", version: "12.1.0", type: LibraryComponent, selector: "lib-library", ngImport: i0__namespace, template: "\n    <p>\n      library works!\n    </p>\n  ", isInline: true });
  i0__namespace.ɵɵngDeclareClassMetadata({ minVersion: "12.0.0", version: "12.1.0", ngImport: i0__namespace, type: LibraryComponent, decorators: [{
              type: i0.Component,
              args: [{
                      selector: 'lib-library',
                      template: "\n    <p>\n      library works!\n    </p>\n  ",
                      styles: []
                  }]
          }], ctorParameters: function () { return []; } });
  var LibraryModule = /** @class */ (function () {
      function LibraryModule() {
      }
      LibraryModule.prototype.ngDoBootstrap = function () {
      };
      return LibraryModule;
  }());
  LibraryModule.ɵfac = i0__namespace.ɵɵngDeclareFactory({ minVersion: "12.0.0", version: "12.1.0", ngImport: i0__namespace, type: LibraryModule, deps: [], target: i0__namespace.ɵɵFactoryTarget.NgModule });
  LibraryModule.ɵmod = i0__namespace.ɵɵngDeclareNgModule({ minVersion: "12.0.0", version: "12.1.0", ngImport: i0__namespace, type: LibraryModule, declarations: [LibraryComponent], imports: [_static.UpgradeModule,
          platformBrowser.BrowserModule], exports: [LibraryComponent] });
  LibraryModule.ɵinj = i0__namespace.ɵɵngDeclareInjector({ minVersion: "12.0.0", version: "12.1.0", ngImport: i0__namespace, type: LibraryModule, imports: [[
              _static.UpgradeModule,
              platformBrowser.BrowserModule,
          ]] });
  i0__namespace.ɵɵngDeclareClassMetadata({ minVersion: "12.0.0", version: "12.1.0", ngImport: i0__namespace, type: LibraryModule, decorators: [{
              type: i0.NgModule,
              args: [{
                      declarations: [
                          LibraryComponent
                      ],
                      imports: [
                          _static.UpgradeModule,
                          platformBrowser.BrowserModule,
                      ],
                      entryComponents: [
                          LibraryComponent
                      ],
                      exports: [
                          LibraryComponent,
                      ],
                  }]
          }] });
  var ng2BootstrapFn = function (extraProviders) { return platformBrowserDynamic.platformBrowserDynamic(extraProviders).bootstrapModule(LibraryModule); };
  var angularModuleName = _static.downgradeModule(ng2BootstrapFn);
  angular.module(angularModuleName).directive('libLibrary', _static.downgradeComponent({ component: LibraryComponent, downgradedModule: angularModuleName }));

  /*
   * Public API Surface of library
   */

  /**
   * Generated bundle index. Do not edit.
   */

  exports.LibraryComponent = LibraryComponent;
  exports.LibraryModule = LibraryModule;
  exports.angularModuleName = angularModuleName;

  Object.defineProperty(exports, '__esModule', { value: true });

})));
//# sourceMappingURL=matomo-example-angular.umd.js.map
