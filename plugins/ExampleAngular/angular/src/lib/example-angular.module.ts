declare var angular: angular.IAngularStatic;

import {NgModule, StaticProvider} from '@angular/core';
import {downgradeComponent, downgradeModule, UpgradeModule} from '@angular/upgrade/static';
import { platformBrowserDynamic } from '@angular/platform-browser-dynamic';
import {BrowserModule} from "@angular/platform-browser";
import { ExampleAngularComponent } from './example.component';

@NgModule({
  declarations: [
    ExampleAngularComponent
  ],
  imports: [
    UpgradeModule,
    BrowserModule,
  ],
  entryComponents: [
    ExampleAngularComponent
  ],
  exports: [
    ExampleAngularComponent,
  ],
})
export class ExampleAngularModule {
  ngDoBootstrap() {

  }
}

const ng2BootstrapFn = (extraProviders: StaticProvider[]) =>
    platformBrowserDynamic(extraProviders).bootstrapModule(ExampleAngularModule);

export const angularModuleName = downgradeModule(ng2BootstrapFn);

angular.module(angularModuleName).directive('exampleAngularComponent', downgradeComponent({ component: ExampleAngularComponent, downgradedModule: angularModuleName }));
