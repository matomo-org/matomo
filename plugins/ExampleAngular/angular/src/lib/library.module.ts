import { NgModule } from '@angular/core';
import { LibraryComponent } from './library.component';
import {downgradeComponent, UpgradeModule} from '@angular/upgrade/static';

@NgModule({
  declarations: [
    LibraryComponent
  ],
  imports: [
    UpgradeModule,
  ],
  exports: [
    LibraryComponent
  ],
  entryComponents: [
    LibraryComponent
  ],
})
export class LibraryModule { }
