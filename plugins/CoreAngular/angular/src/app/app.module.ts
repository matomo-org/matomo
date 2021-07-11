import {platformBrowserDynamic} from "@angular/platform-browser-dynamic";

declare var angular: angular.IAngularStatic;

import {Compiler, Component, Injector, NgModule, OnInit, StaticProvider} from '@angular/core';
import { BrowserModule } from '@angular/platform-browser';
import {downgradeComponent, downgradeModule, UpgradeModule} from '@angular/upgrade/static';

const windowAny = window as any;
const pluginList = windowAny.piwik.pluginsLoadedAndActivated as string[];

@NgModule({
  imports: [
      BrowserModule,
      UpgradeModule,
  ],
})
export class AppModule {
  constructor(private upgrade: UpgradeModule, private compiler: Compiler, private parentInjector: Injector) { }

  ngDoBootstrap() {
    this.upgrade.bootstrap(document.body, ['piwikApp'], { strictDi: false });
  }
}
