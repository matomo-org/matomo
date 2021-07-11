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

  var ExampleComponent = /** @class */ (function () {
      function ExampleComponent() {
      }
      ExampleComponent.prototype.ngOnInit = function () {
      };
      return ExampleComponent;
  }());
  ExampleComponent.ɵfac = i0__namespace.ɵɵngDeclareFactory({ minVersion: "12.0.0", version: "12.1.0", ngImport: i0__namespace, type: ExampleComponent, deps: [], target: i0__namespace.ɵɵFactoryTarget.Component });
  ExampleComponent.ɵcmp = i0__namespace.ɵɵngDeclareComponent({ minVersion: "12.0.0", version: "12.1.0", type: ExampleComponent, selector: "example-component", ngImport: i0__namespace, template: "\n    <p>\n      this is a simple example angular component\n    </p>\n  ", isInline: true });
  i0__namespace.ɵɵngDeclareClassMetadata({ minVersion: "12.0.0", version: "12.1.0", ngImport: i0__namespace, type: ExampleComponent, decorators: [{
              type: i0.Component,
              args: [{
                      selector: 'example-component',
                      template: "\n    <p>\n      this is a simple example angular component\n    </p>\n  ",
                      styles: []
                  }]
          }], ctorParameters: function () { return []; } });

  var ExampleAngularModule = /** @class */ (function () {
      function ExampleAngularModule() {
      }
      ExampleAngularModule.prototype.ngDoBootstrap = function () {
      };
      return ExampleAngularModule;
  }());
  ExampleAngularModule.ɵfac = i0__namespace.ɵɵngDeclareFactory({ minVersion: "12.0.0", version: "12.1.0", ngImport: i0__namespace, type: ExampleAngularModule, deps: [], target: i0__namespace.ɵɵFactoryTarget.NgModule });
  ExampleAngularModule.ɵmod = i0__namespace.ɵɵngDeclareNgModule({ minVersion: "12.0.0", version: "12.1.0", ngImport: i0__namespace, type: ExampleAngularModule, declarations: [ExampleComponent], imports: [_static.UpgradeModule,
          platformBrowser.BrowserModule], exports: [ExampleComponent] });
  ExampleAngularModule.ɵinj = i0__namespace.ɵɵngDeclareInjector({ minVersion: "12.0.0", version: "12.1.0", ngImport: i0__namespace, type: ExampleAngularModule, imports: [[
              _static.UpgradeModule,
              platformBrowser.BrowserModule,
          ]] });
  i0__namespace.ɵɵngDeclareClassMetadata({ minVersion: "12.0.0", version: "12.1.0", ngImport: i0__namespace, type: ExampleAngularModule, decorators: [{
              type: i0.NgModule,
              args: [{
                      declarations: [
                          ExampleComponent
                      ],
                      imports: [
                          _static.UpgradeModule,
                          platformBrowser.BrowserModule,
                      ],
                      entryComponents: [
                          ExampleComponent
                      ],
                      exports: [
                          ExampleComponent,
                      ],
                  }]
          }] });
  var ng2BootstrapFn = function (extraProviders) { return platformBrowserDynamic.platformBrowserDynamic(extraProviders).bootstrapModule(ExampleAngularModule); };
  var angularModuleName = _static.downgradeModule(ng2BootstrapFn);
  angular.module(angularModuleName).directive('libLibrary', _static.downgradeComponent({ component: ExampleComponent, downgradedModule: angularModuleName }));

  /*
   * Public API Surface of library
   */

  /**
   * Generated bundle index. Do not edit.
   */

  exports.ExampleAngularModule = ExampleAngularModule;
  exports.ExampleComponent = ExampleComponent;
  exports.angularModuleName = angularModuleName;

  Object.defineProperty(exports, '__esModule', { value: true });

})));
//# sourceMappingURL=matomo-example-angular.umd.js.map
