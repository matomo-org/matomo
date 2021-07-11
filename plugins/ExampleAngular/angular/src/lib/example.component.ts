declare var angular: angular.IAngularStatic;

import {Component, OnInit, Type} from '@angular/core';
import {downgradeComponent, UpgradeModule} from '@angular/upgrade/static';

@Component({
    selector: 'example-angular-component',
    template: `
    <p>
      this is a simple example angular component
    </p>
  `,
    styles: [
    ]
})
export class ExampleAngularComponent implements OnInit {

    constructor() { }

    ngOnInit(): void {

    }

}

