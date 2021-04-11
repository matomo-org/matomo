/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
export const FormErrorExamples = {
    formControlName: `
    <div [formGroup]="myGroup">
      <input formControlName="firstName">
    </div>

    In your class:

    this.myGroup = new FormGroup({
       firstName: new FormControl()
    });`,
    formGroupName: `
    <div [formGroup]="myGroup">
       <div formGroupName="person">
          <input formControlName="firstName">
       </div>
    </div>

    In your class:

    this.myGroup = new FormGroup({
       person: new FormGroup({ firstName: new FormControl() })
    });`,
    formArrayName: `
    <div [formGroup]="myGroup">
      <div formArrayName="cities">
        <div *ngFor="let city of cityArray.controls; index as i">
          <input [formControlName]="i">
        </div>
      </div>
    </div>

    In your class:

    this.cityArray = new FormArray([new FormControl('SF')]);
    this.myGroup = new FormGroup({
      cities: this.cityArray
    });`,
    ngModelGroup: `
    <form>
       <div ngModelGroup="person">
          <input [(ngModel)]="person.name" name="firstName">
       </div>
    </form>`,
    ngModelWithFormGroup: `
    <div [formGroup]="myGroup">
       <input formControlName="firstName">
       <input [(ngModel)]="showMoreControls" [ngModelOptions]="{standalone: true}">
    </div>
  `
};
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiZXJyb3JfZXhhbXBsZXMuanMiLCJzb3VyY2VSb290IjoiIiwic291cmNlcyI6WyIuLi8uLi8uLi8uLi8uLi8uLi8uLi9wYWNrYWdlcy9mb3Jtcy9zcmMvZGlyZWN0aXZlcy9lcnJvcl9leGFtcGxlcy50cyJdLCJuYW1lcyI6W10sIm1hcHBpbmdzIjoiQUFBQTs7Ozs7O0dBTUc7QUFFSCxNQUFNLENBQUMsTUFBTSxpQkFBaUIsR0FBRztJQUMvQixlQUFlLEVBQUU7Ozs7Ozs7OztRQVNYO0lBRU4sYUFBYSxFQUFFOzs7Ozs7Ozs7OztRQVdUO0lBRU4sYUFBYSxFQUFFOzs7Ozs7Ozs7Ozs7OztRQWNUO0lBRU4sWUFBWSxFQUFFOzs7OztZQUtKO0lBRVYsb0JBQW9CLEVBQUU7Ozs7O0dBS3JCO0NBQ0YsQ0FBQyIsInNvdXJjZXNDb250ZW50IjpbIi8qKlxuICogQGxpY2Vuc2VcbiAqIENvcHlyaWdodCBHb29nbGUgTExDIEFsbCBSaWdodHMgUmVzZXJ2ZWQuXG4gKlxuICogVXNlIG9mIHRoaXMgc291cmNlIGNvZGUgaXMgZ292ZXJuZWQgYnkgYW4gTUlULXN0eWxlIGxpY2Vuc2UgdGhhdCBjYW4gYmVcbiAqIGZvdW5kIGluIHRoZSBMSUNFTlNFIGZpbGUgYXQgaHR0cHM6Ly9hbmd1bGFyLmlvL2xpY2Vuc2VcbiAqL1xuXG5leHBvcnQgY29uc3QgRm9ybUVycm9yRXhhbXBsZXMgPSB7XG4gIGZvcm1Db250cm9sTmFtZTogYFxuICAgIDxkaXYgW2Zvcm1Hcm91cF09XCJteUdyb3VwXCI+XG4gICAgICA8aW5wdXQgZm9ybUNvbnRyb2xOYW1lPVwiZmlyc3ROYW1lXCI+XG4gICAgPC9kaXY+XG5cbiAgICBJbiB5b3VyIGNsYXNzOlxuXG4gICAgdGhpcy5teUdyb3VwID0gbmV3IEZvcm1Hcm91cCh7XG4gICAgICAgZmlyc3ROYW1lOiBuZXcgRm9ybUNvbnRyb2woKVxuICAgIH0pO2AsXG5cbiAgZm9ybUdyb3VwTmFtZTogYFxuICAgIDxkaXYgW2Zvcm1Hcm91cF09XCJteUdyb3VwXCI+XG4gICAgICAgPGRpdiBmb3JtR3JvdXBOYW1lPVwicGVyc29uXCI+XG4gICAgICAgICAgPGlucHV0IGZvcm1Db250cm9sTmFtZT1cImZpcnN0TmFtZVwiPlxuICAgICAgIDwvZGl2PlxuICAgIDwvZGl2PlxuXG4gICAgSW4geW91ciBjbGFzczpcblxuICAgIHRoaXMubXlHcm91cCA9IG5ldyBGb3JtR3JvdXAoe1xuICAgICAgIHBlcnNvbjogbmV3IEZvcm1Hcm91cCh7IGZpcnN0TmFtZTogbmV3IEZvcm1Db250cm9sKCkgfSlcbiAgICB9KTtgLFxuXG4gIGZvcm1BcnJheU5hbWU6IGBcbiAgICA8ZGl2IFtmb3JtR3JvdXBdPVwibXlHcm91cFwiPlxuICAgICAgPGRpdiBmb3JtQXJyYXlOYW1lPVwiY2l0aWVzXCI+XG4gICAgICAgIDxkaXYgKm5nRm9yPVwibGV0IGNpdHkgb2YgY2l0eUFycmF5LmNvbnRyb2xzOyBpbmRleCBhcyBpXCI+XG4gICAgICAgICAgPGlucHV0IFtmb3JtQ29udHJvbE5hbWVdPVwiaVwiPlxuICAgICAgICA8L2Rpdj5cbiAgICAgIDwvZGl2PlxuICAgIDwvZGl2PlxuXG4gICAgSW4geW91ciBjbGFzczpcblxuICAgIHRoaXMuY2l0eUFycmF5ID0gbmV3IEZvcm1BcnJheShbbmV3IEZvcm1Db250cm9sKCdTRicpXSk7XG4gICAgdGhpcy5teUdyb3VwID0gbmV3IEZvcm1Hcm91cCh7XG4gICAgICBjaXRpZXM6IHRoaXMuY2l0eUFycmF5XG4gICAgfSk7YCxcblxuICBuZ01vZGVsR3JvdXA6IGBcbiAgICA8Zm9ybT5cbiAgICAgICA8ZGl2IG5nTW9kZWxHcm91cD1cInBlcnNvblwiPlxuICAgICAgICAgIDxpbnB1dCBbKG5nTW9kZWwpXT1cInBlcnNvbi5uYW1lXCIgbmFtZT1cImZpcnN0TmFtZVwiPlxuICAgICAgIDwvZGl2PlxuICAgIDwvZm9ybT5gLFxuXG4gIG5nTW9kZWxXaXRoRm9ybUdyb3VwOiBgXG4gICAgPGRpdiBbZm9ybUdyb3VwXT1cIm15R3JvdXBcIj5cbiAgICAgICA8aW5wdXQgZm9ybUNvbnRyb2xOYW1lPVwiZmlyc3ROYW1lXCI+XG4gICAgICAgPGlucHV0IFsobmdNb2RlbCldPVwic2hvd01vcmVDb250cm9sc1wiIFtuZ01vZGVsT3B0aW9uc109XCJ7c3RhbmRhbG9uZTogdHJ1ZX1cIj5cbiAgICA8L2Rpdj5cbiAgYFxufTtcbiJdfQ==