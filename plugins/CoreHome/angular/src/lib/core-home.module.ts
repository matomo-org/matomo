import {NgModule, StaticProvider} from '@angular/core';
import {downgradeModule, UpgradeModule} from '@angular/upgrade/static';
import { platformBrowserDynamic } from '@angular/platform-browser-dynamic';
import {BrowserModule} from "@angular/platform-browser";
import { SiteSelectorComponent } from './siteselector/siteselector.component';
import {SitesService} from "./site-store/sites.service";
import {MatomoApiService} from "./matomo-api/matomo-api.service";
import {HttpClientModule} from "@angular/common/http";

export * from './site-store/sites.service';
export * from './site-store/site';

@NgModule({
  declarations: [
    SiteSelectorComponent,
  ],
  imports: [
    BrowserModule,
    UpgradeModule,
    HttpClientModule,
  ],
  exports: [
    SiteSelectorComponent,
  ],
  providers: [
    MatomoApiService,
    SitesService,
  ]
})
export class CoreHomeModule {
  ngDoBootstrap() {
    // empty
  }
}

const ng2BootstrapFn = (extraProviders: StaticProvider[]) =>
    platformBrowserDynamic(extraProviders).bootstrapModule(CoreHomeModule);

export const angularModuleName = downgradeModule(ng2BootstrapFn);
