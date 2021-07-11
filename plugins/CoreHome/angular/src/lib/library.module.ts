declare var angular: angular.IAngularStatic;

import {Component, NgModule, OnInit, StaticProvider} from '@angular/core';
import {downgradeComponent, downgradeModule, UpgradeModule} from '@angular/upgrade/static';
import { platformBrowserDynamic } from '@angular/platform-browser-dynamic';
import {BrowserModule} from "@angular/platform-browser";
@Component({
  selector: 'test-component',
  template: `
    <p>
      tc works!
    </p>
  `,
  styles: [
  ]
})
export class TestComponent implements OnInit {
  constructor() { }

  ngOnInit(): void {
  }
}

@NgModule({
  declarations: [
    TestComponent,
  ],
  entryComponents: [
    TestComponent
  ],
  imports: [
    UpgradeModule,
    BrowserModule,
  ],
})
export class CoreHomeModule {
  ngDoBootstrap() {

  }
}

const ng2BootstrapFn = (extraProviders: StaticProvider[]) =>
    platformBrowserDynamic(extraProviders).bootstrapModule(CoreHomeModule);

export const angularModuleName = downgradeModule(ng2BootstrapFn);

angular.module(angularModuleName).directive('testComponent', downgradeComponent({ component: TestComponent, downgradedModule: angularModuleName }));
