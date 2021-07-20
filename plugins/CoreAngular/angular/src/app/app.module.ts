import {NgModule} from '@angular/core';
import { BrowserModule } from '@angular/platform-browser';
import {UpgradeModule} from '@angular/upgrade/static';

@NgModule({
  imports: [
      BrowserModule,
      UpgradeModule,
  ],
})
export class AppModule {
  constructor(private upgrade: UpgradeModule) { }

  ngDoBootstrap() {
    try {
      this.upgrade.bootstrap(document.body, ['piwikApp'], {strictDi: false});
    } catch (e) {
      console.log(`failed to bootstrap app: ${e.stack || e.message || e}`);
    }
  }
}
