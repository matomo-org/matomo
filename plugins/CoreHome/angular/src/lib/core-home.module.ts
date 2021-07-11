import {NgModule, StaticProvider} from '@angular/core';
import {downgradeModule, UpgradeModule} from '@angular/upgrade/static';
import { platformBrowserDynamic } from '@angular/platform-browser-dynamic';
import {BrowserModule} from "@angular/platform-browser";
import { SiteSelectorComponent } from './siteselector/siteselector.component';

@NgModule({
  declarations: [
    SiteSelectorComponent,
  ],
  imports: [
    UpgradeModule,
    BrowserModule,
  ],
  exports: [
    SiteSelectorComponent,
  ],
})
export class CoreHomeModule {
  ngDoBootstrap() {
    // empty
  }
}

const ng2BootstrapFn = (extraProviders: StaticProvider[]) =>
    platformBrowserDynamic(extraProviders).bootstrapModule(CoreHomeModule);

export const angularModuleName = downgradeModule(ng2BootstrapFn);
