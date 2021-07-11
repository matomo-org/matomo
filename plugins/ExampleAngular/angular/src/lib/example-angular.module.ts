declare var angular: angular.IAngularStatic;

import {NgModule, StaticProvider} from '@angular/core';
import {downgradeComponent, downgradeModule, UpgradeModule} from '@angular/upgrade/static';
import { platformBrowserDynamic } from '@angular/platform-browser-dynamic';
import {BrowserModule} from "@angular/platform-browser";
import { ExampleComponent } from './example.component';

@NgModule({
  declarations: [
    ExampleComponent
  ],
  imports: [
    UpgradeModule,
    BrowserModule,
  ],
  entryComponents: [
    ExampleComponent
  ],
  exports: [
      ExampleComponent,
  ],
})
export class ExampleAngularModule {
  ngDoBootstrap() {

  }
}

const ng2BootstrapFn = (extraProviders: StaticProvider[]) =>
    platformBrowserDynamic(extraProviders).bootstrapModule(ExampleAngularModule);

export const angularModuleName = downgradeModule(ng2BootstrapFn);

angular.module(angularModuleName).directive('libLibrary', downgradeComponent({ component: ExampleComponent, downgradedModule: angularModuleName }));
