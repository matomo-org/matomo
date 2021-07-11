declare var angular: angular.IAngularStatic;

import {Component, OnInit, Type} from '@angular/core';
import {downgradeComponent, UpgradeModule} from '@angular/upgrade/static';

@Component({
    selector: 'example-component',
    template: `
    <p>
      this is a simple example angular component
    </p>
  `,
    styles: [
    ]
})
export class ExampleComponent implements OnInit {

    constructor() { }

    ngOnInit(): void {

    }

}

