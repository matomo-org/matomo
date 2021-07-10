import {InjectionToken, ModuleWithProviders, NgModule} from '@angular/core';
import { BrowserModule } from '@angular/platform-browser';
import { UpgradeModule } from '@angular/upgrade/static';

type PluginList = string[];

export const ACTIVATED_PLUGINS = new InjectionToken<PluginList>('ACTIVATED_PLUGINS');

function providePluginModules(plugins: PluginList) {
    return [
        { provide: ACTIVATED_PLUGINS, multi: true, useValue: plugins },
    ];
}

@NgModule({})
export class PluginModuleProviderModule {
    static dynamicPluginModuleProvider(plugins: PluginList): ModuleWithProviders<PluginModuleProviderModule> {
        return { ngModule: PluginModuleProviderModule, providers: [providePluginModules(plugins)] };
    }
}
