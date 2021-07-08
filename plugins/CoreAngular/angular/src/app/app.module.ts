import { NgModule } from '@angular/core';
import { BrowserModule } from '@angular/platform-browser';
import { UpgradeModule } from '@angular/upgrade/static';
import { LibraryModule } from '@matomo/ExampleAngular'; // TODO: make dynamic

@NgModule({
  imports: [
    BrowserModule,
    UpgradeModule,
    LibraryModule,
  ],
})
export class AppModule {
  constructor(private upgrade: UpgradeModule) { }
  ngDoBootstrap() {
    this.upgrade.bootstrap(document.body, ['piwikApp'], { strictDi: false });
  }
}
