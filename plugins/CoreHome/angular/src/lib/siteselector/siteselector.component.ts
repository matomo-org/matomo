import {Component, OnInit, Type} from '@angular/core';
import {downgradeComponent, UpgradeModule} from '@angular/upgrade/static';

@Component({
    selector: 'site-selector',
    template: `
    <p>
      this is a simple example angular component
    </p>
  `,
    styles: [
    ]
})
export class SiteSelectorComponent implements OnInit {

    constructor() { }

    ngOnInit(): void {

    }

}

