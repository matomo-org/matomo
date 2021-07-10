declare var angular: angular.IAngularStatic;

import {Compiler, Component, NgModule, OnInit, Type} from '@angular/core';
import { BrowserModule } from '@angular/platform-browser';
import {downgradeComponent, UpgradeModule} from '@angular/upgrade/static';

const windowAny = window as any;
const pluginList = windowAny.piwik.pluginsLoadedAndActivated as string[];

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
  imports: [
      BrowserModule,
      UpgradeModule,
  ],
})
export class AppModule {
  constructor(private upgrade: UpgradeModule, private compiler: Compiler) { }
  ngDoBootstrap() {
    this.findAndDowngradeComponents();

    angular.module('piwikApp').directive('testComponent', downgradeComponent({ component: TestComponent }));
    angular.module('piwikApp').directive('libLibrary', downgradeComponent({ component: windowAny.matomo['example-angular'].LibraryComponent as Type<any> }));

    this.upgrade.bootstrap(document.body, ['piwikApp'], { strictDi: false });
  }

  findAndDowngradeComponents() {
      for (let plugin of pluginList) {
          const kebabCaseName = toKebabCase(plugin);
          const moduleObject = windowAny.matomo[kebabCaseName];
          if (!moduleObject) {
              continue;
          }

          for (let [exportName, exportValue] of Object.entries(moduleObject)) {
              if (!exportName.endsWith('Component')) {
                  continue;
              }

              const selector = findSelectorInRuntimeComponent(exportValue);
              if (!selector) {
                  continue;
              }

              const selectorCamelCase = toDirectiveCamelCase(selector);
              if (!selectorCamelCase) {
                  // TODO: log here
                  continue;
              }

//              angular.module('piwikApp').directive(selectorCamelCase, downgradeComponent({ component: exportValue as Type<any> }));
          }
      }
  }
}

function findSelectorInRuntimeComponent(component: any): string|null {
    if (!component.decorators) {
        return null;
    }

    for (let decorator of component.decorators) {
        if (decorator?.args?.[0]?.selector) {
            return decorator.args[0].selector;
        }
    }
    return null;
}

function toKebabCase(s: string) {
    return s.substring(0, 1).toLowerCase() + s.substring(1).replace(/[A-Z]/g, letter => `-${letter.toLowerCase()}`);
}

function toDirectiveCamelCase(s: string|undefined) {
    if (!s) {
        return;
    }

    return s.substring(0,1) + s.substring(1).replace(/-[a-z]/g, prefix => prefix.substring(1).toUpperCase());
}
