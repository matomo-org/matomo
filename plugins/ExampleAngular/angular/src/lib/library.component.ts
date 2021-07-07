declare var angular: angular.IAngularStatic;

import { Component, OnInit } from '@angular/core';
import { downgradeComponent } from '@angular/upgrade/static';

@Component({
  selector: 'lib-library',
  template: `
    <p>
      library works!
    </p>
  `,
  styles: [
  ]
})
export class LibraryComponent implements OnInit {

  constructor() { }

  ngOnInit(): void {
  }

}

angular.module('piwikApp').directive('libLibrary', downgradeComponent({ component: LibraryComponent }));
