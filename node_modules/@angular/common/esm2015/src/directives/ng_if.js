/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
import { Directive, Input, TemplateRef, ViewContainerRef, ɵstringify as stringify } from '@angular/core';
/**
 * A structural directive that conditionally includes a template based on the value of
 * an expression coerced to Boolean.
 * When the expression evaluates to true, Angular renders the template
 * provided in a `then` clause, and when  false or null,
 * Angular renders the template provided in an optional `else` clause. The default
 * template for the `else` clause is blank.
 *
 * A [shorthand form](guide/structural-directives#asterisk) of the directive,
 * `*ngIf="condition"`, is generally used, provided
 * as an attribute of the anchor element for the inserted template.
 * Angular expands this into a more explicit version, in which the anchor element
 * is contained in an `<ng-template>` element.
 *
 * Simple form with shorthand syntax:
 *
 * ```
 * <div *ngIf="condition">Content to render when condition is true.</div>
 * ```
 *
 * Simple form with expanded syntax:
 *
 * ```
 * <ng-template [ngIf]="condition"><div>Content to render when condition is
 * true.</div></ng-template>
 * ```
 *
 * Form with an "else" block:
 *
 * ```
 * <div *ngIf="condition; else elseBlock">Content to render when condition is true.</div>
 * <ng-template #elseBlock>Content to render when condition is false.</ng-template>
 * ```
 *
 * Shorthand form with "then" and "else" blocks:
 *
 * ```
 * <div *ngIf="condition; then thenBlock else elseBlock"></div>
 * <ng-template #thenBlock>Content to render when condition is true.</ng-template>
 * <ng-template #elseBlock>Content to render when condition is false.</ng-template>
 * ```
 *
 * Form with storing the value locally:
 *
 * ```
 * <div *ngIf="condition as value; else elseBlock">{{value}}</div>
 * <ng-template #elseBlock>Content to render when value is null.</ng-template>
 * ```
 *
 * @usageNotes
 *
 * The `*ngIf` directive is most commonly used to conditionally show an inline template,
 * as seen in the following  example.
 * The default `else` template is blank.
 *
 * {@example common/ngIf/ts/module.ts region='NgIfSimple'}
 *
 * ### Showing an alternative template using `else`
 *
 * To display a template when `expression` evaluates to false, use an `else` template
 * binding as shown in the following example.
 * The `else` binding points to an `<ng-template>`  element labeled `#elseBlock`.
 * The template can be defined anywhere in the component view, but is typically placed right after
 * `ngIf` for readability.
 *
 * {@example common/ngIf/ts/module.ts region='NgIfElse'}
 *
 * ### Using an external `then` template
 *
 * In the previous example, the then-clause template is specified inline, as the content of the
 * tag that contains the `ngIf` directive. You can also specify a template that is defined
 * externally, by referencing a labeled `<ng-template>` element. When you do this, you can
 * change which template to use at runtime, as shown in the following example.
 *
 * {@example common/ngIf/ts/module.ts region='NgIfThenElse'}
 *
 * ### Storing a conditional result in a variable
 *
 * You might want to show a set of properties from the same object. If you are waiting
 * for asynchronous data, the object can be undefined.
 * In this case, you can use `ngIf` and store the result of the condition in a local
 * variable as shown in the following example.
 *
 * {@example common/ngIf/ts/module.ts region='NgIfAs'}
 *
 * This code uses only one `AsyncPipe`, so only one subscription is created.
 * The conditional statement stores the result of `userStream|async` in the local variable `user`.
 * You can then bind the local `user` repeatedly.
 *
 * The conditional displays the data only if `userStream` returns a value,
 * so you don't need to use the
 * safe-navigation-operator (`?.`)
 * to guard against null values when accessing properties.
 * You can display an alternative template while waiting for the data.
 *
 * ### Shorthand syntax
 *
 * The shorthand syntax `*ngIf` expands into two separate template specifications
 * for the "then" and "else" clauses. For example, consider the following shorthand statement,
 * that is meant to show a loading page while waiting for data to be loaded.
 *
 * ```
 * <div class="hero-list" *ngIf="heroes else loading">
 *  ...
 * </div>
 *
 * <ng-template #loading>
 *  <div>Loading...</div>
 * </ng-template>
 * ```
 *
 * You can see that the "else" clause references the `<ng-template>`
 * with the `#loading` label, and the template for the "then" clause
 * is provided as the content of the anchor element.
 *
 * However, when Angular expands the shorthand syntax, it creates
 * another `<ng-template>` tag, with `ngIf` and `ngIfElse` directives.
 * The anchor element containing the template for the "then" clause becomes
 * the content of this unlabeled `<ng-template>` tag.
 *
 * ```
 * <ng-template [ngIf]="heroes" [ngIfElse]="loading">
 *  <div class="hero-list">
 *   ...
 *  </div>
 * </ng-template>
 *
 * <ng-template #loading>
 *  <div>Loading...</div>
 * </ng-template>
 * ```
 *
 * The presence of the implicit template object has implications for the nesting of
 * structural directives. For more on this subject, see
 * [Structural Directives](https://angular.io/guide/built-in-directives#one-per-element).
 *
 * @ngModule CommonModule
 * @publicApi
 */
export class NgIf {
    constructor(_viewContainer, templateRef) {
        this._viewContainer = _viewContainer;
        this._context = new NgIfContext();
        this._thenTemplateRef = null;
        this._elseTemplateRef = null;
        this._thenViewRef = null;
        this._elseViewRef = null;
        this._thenTemplateRef = templateRef;
    }
    /**
     * The Boolean expression to evaluate as the condition for showing a template.
     */
    set ngIf(condition) {
        this._context.$implicit = this._context.ngIf = condition;
        this._updateView();
    }
    /**
     * A template to show if the condition expression evaluates to true.
     */
    set ngIfThen(templateRef) {
        assertTemplate('ngIfThen', templateRef);
        this._thenTemplateRef = templateRef;
        this._thenViewRef = null; // clear previous view if any.
        this._updateView();
    }
    /**
     * A template to show if the condition expression evaluates to false.
     */
    set ngIfElse(templateRef) {
        assertTemplate('ngIfElse', templateRef);
        this._elseTemplateRef = templateRef;
        this._elseViewRef = null; // clear previous view if any.
        this._updateView();
    }
    _updateView() {
        if (this._context.$implicit) {
            if (!this._thenViewRef) {
                this._viewContainer.clear();
                this._elseViewRef = null;
                if (this._thenTemplateRef) {
                    this._thenViewRef =
                        this._viewContainer.createEmbeddedView(this._thenTemplateRef, this._context);
                }
            }
        }
        else {
            if (!this._elseViewRef) {
                this._viewContainer.clear();
                this._thenViewRef = null;
                if (this._elseTemplateRef) {
                    this._elseViewRef =
                        this._viewContainer.createEmbeddedView(this._elseTemplateRef, this._context);
                }
            }
        }
    }
    /**
     * Asserts the correct type of the context for the template that `NgIf` will render.
     *
     * The presence of this method is a signal to the Ivy template type-check compiler that the
     * `NgIf` structural directive renders its template with a specific context type.
     */
    static ngTemplateContextGuard(dir, ctx) {
        return true;
    }
}
NgIf.decorators = [
    { type: Directive, args: [{ selector: '[ngIf]' },] }
];
NgIf.ctorParameters = () => [
    { type: ViewContainerRef },
    { type: TemplateRef }
];
NgIf.propDecorators = {
    ngIf: [{ type: Input }],
    ngIfThen: [{ type: Input }],
    ngIfElse: [{ type: Input }]
};
/**
 * @publicApi
 */
export class NgIfContext {
    constructor() {
        this.$implicit = null;
        this.ngIf = null;
    }
}
function assertTemplate(property, templateRef) {
    const isTemplateRefOrNull = !!(!templateRef || templateRef.createEmbeddedView);
    if (!isTemplateRefOrNull) {
        throw new Error(`${property} must be a TemplateRef, but received '${stringify(templateRef)}'.`);
    }
}
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoibmdfaWYuanMiLCJzb3VyY2VSb290IjoiIiwic291cmNlcyI6WyIuLi8uLi8uLi8uLi8uLi8uLi8uLi9wYWNrYWdlcy9jb21tb24vc3JjL2RpcmVjdGl2ZXMvbmdfaWYudHMiXSwibmFtZXMiOltdLCJtYXBwaW5ncyI6IkFBQUE7Ozs7OztHQU1HO0FBRUgsT0FBTyxFQUFDLFNBQVMsRUFBbUIsS0FBSyxFQUFFLFdBQVcsRUFBRSxnQkFBZ0IsRUFBRSxVQUFVLElBQUksU0FBUyxFQUFDLE1BQU0sZUFBZSxDQUFDO0FBR3hIOzs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7R0EwSUc7QUFFSCxNQUFNLE9BQU8sSUFBSTtJQU9mLFlBQW9CLGNBQWdDLEVBQUUsV0FBd0M7UUFBMUUsbUJBQWMsR0FBZCxjQUFjLENBQWtCO1FBTjVDLGFBQVEsR0FBbUIsSUFBSSxXQUFXLEVBQUssQ0FBQztRQUNoRCxxQkFBZ0IsR0FBcUMsSUFBSSxDQUFDO1FBQzFELHFCQUFnQixHQUFxQyxJQUFJLENBQUM7UUFDMUQsaUJBQVksR0FBeUMsSUFBSSxDQUFDO1FBQzFELGlCQUFZLEdBQXlDLElBQUksQ0FBQztRQUdoRSxJQUFJLENBQUMsZ0JBQWdCLEdBQUcsV0FBVyxDQUFDO0lBQ3RDLENBQUM7SUFFRDs7T0FFRztJQUNILElBQ0ksSUFBSSxDQUFDLFNBQVk7UUFDbkIsSUFBSSxDQUFDLFFBQVEsQ0FBQyxTQUFTLEdBQUcsSUFBSSxDQUFDLFFBQVEsQ0FBQyxJQUFJLEdBQUcsU0FBUyxDQUFDO1FBQ3pELElBQUksQ0FBQyxXQUFXLEVBQUUsQ0FBQztJQUNyQixDQUFDO0lBRUQ7O09BRUc7SUFDSCxJQUNJLFFBQVEsQ0FBQyxXQUE2QztRQUN4RCxjQUFjLENBQUMsVUFBVSxFQUFFLFdBQVcsQ0FBQyxDQUFDO1FBQ3hDLElBQUksQ0FBQyxnQkFBZ0IsR0FBRyxXQUFXLENBQUM7UUFDcEMsSUFBSSxDQUFDLFlBQVksR0FBRyxJQUFJLENBQUMsQ0FBRSw4QkFBOEI7UUFDekQsSUFBSSxDQUFDLFdBQVcsRUFBRSxDQUFDO0lBQ3JCLENBQUM7SUFFRDs7T0FFRztJQUNILElBQ0ksUUFBUSxDQUFDLFdBQTZDO1FBQ3hELGNBQWMsQ0FBQyxVQUFVLEVBQUUsV0FBVyxDQUFDLENBQUM7UUFDeEMsSUFBSSxDQUFDLGdCQUFnQixHQUFHLFdBQVcsQ0FBQztRQUNwQyxJQUFJLENBQUMsWUFBWSxHQUFHLElBQUksQ0FBQyxDQUFFLDhCQUE4QjtRQUN6RCxJQUFJLENBQUMsV0FBVyxFQUFFLENBQUM7SUFDckIsQ0FBQztJQUVPLFdBQVc7UUFDakIsSUFBSSxJQUFJLENBQUMsUUFBUSxDQUFDLFNBQVMsRUFBRTtZQUMzQixJQUFJLENBQUMsSUFBSSxDQUFDLFlBQVksRUFBRTtnQkFDdEIsSUFBSSxDQUFDLGNBQWMsQ0FBQyxLQUFLLEVBQUUsQ0FBQztnQkFDNUIsSUFBSSxDQUFDLFlBQVksR0FBRyxJQUFJLENBQUM7Z0JBQ3pCLElBQUksSUFBSSxDQUFDLGdCQUFnQixFQUFFO29CQUN6QixJQUFJLENBQUMsWUFBWTt3QkFDYixJQUFJLENBQUMsY0FBYyxDQUFDLGtCQUFrQixDQUFDLElBQUksQ0FBQyxnQkFBZ0IsRUFBRSxJQUFJLENBQUMsUUFBUSxDQUFDLENBQUM7aUJBQ2xGO2FBQ0Y7U0FDRjthQUFNO1lBQ0wsSUFBSSxDQUFDLElBQUksQ0FBQyxZQUFZLEVBQUU7Z0JBQ3RCLElBQUksQ0FBQyxjQUFjLENBQUMsS0FBSyxFQUFFLENBQUM7Z0JBQzVCLElBQUksQ0FBQyxZQUFZLEdBQUcsSUFBSSxDQUFDO2dCQUN6QixJQUFJLElBQUksQ0FBQyxnQkFBZ0IsRUFBRTtvQkFDekIsSUFBSSxDQUFDLFlBQVk7d0JBQ2IsSUFBSSxDQUFDLGNBQWMsQ0FBQyxrQkFBa0IsQ0FBQyxJQUFJLENBQUMsZ0JBQWdCLEVBQUUsSUFBSSxDQUFDLFFBQVEsQ0FBQyxDQUFDO2lCQUNsRjthQUNGO1NBQ0Y7SUFDSCxDQUFDO0lBZUQ7Ozs7O09BS0c7SUFDSCxNQUFNLENBQUMsc0JBQXNCLENBQUksR0FBWSxFQUFFLEdBQVE7UUFFckQsT0FBTyxJQUFJLENBQUM7SUFDZCxDQUFDOzs7WUF2RkYsU0FBUyxTQUFDLEVBQUMsUUFBUSxFQUFFLFFBQVEsRUFBQzs7O1lBOUl5QixnQkFBZ0I7WUFBN0IsV0FBVzs7O21CQTZKbkQsS0FBSzt1QkFTTCxLQUFLO3VCQVdMLEtBQUs7O0FBdURSOztHQUVHO0FBQ0gsTUFBTSxPQUFPLFdBQVc7SUFBeEI7UUFDUyxjQUFTLEdBQU0sSUFBSyxDQUFDO1FBQ3JCLFNBQUksR0FBTSxJQUFLLENBQUM7SUFDekIsQ0FBQztDQUFBO0FBRUQsU0FBUyxjQUFjLENBQUMsUUFBZ0IsRUFBRSxXQUFrQztJQUMxRSxNQUFNLG1CQUFtQixHQUFHLENBQUMsQ0FBQyxDQUFDLENBQUMsV0FBVyxJQUFJLFdBQVcsQ0FBQyxrQkFBa0IsQ0FBQyxDQUFDO0lBQy9FLElBQUksQ0FBQyxtQkFBbUIsRUFBRTtRQUN4QixNQUFNLElBQUksS0FBSyxDQUFDLEdBQUcsUUFBUSx5Q0FBeUMsU0FBUyxDQUFDLFdBQVcsQ0FBQyxJQUFJLENBQUMsQ0FBQztLQUNqRztBQUNILENBQUMiLCJzb3VyY2VzQ29udGVudCI6WyIvKipcbiAqIEBsaWNlbnNlXG4gKiBDb3B5cmlnaHQgR29vZ2xlIExMQyBBbGwgUmlnaHRzIFJlc2VydmVkLlxuICpcbiAqIFVzZSBvZiB0aGlzIHNvdXJjZSBjb2RlIGlzIGdvdmVybmVkIGJ5IGFuIE1JVC1zdHlsZSBsaWNlbnNlIHRoYXQgY2FuIGJlXG4gKiBmb3VuZCBpbiB0aGUgTElDRU5TRSBmaWxlIGF0IGh0dHBzOi8vYW5ndWxhci5pby9saWNlbnNlXG4gKi9cblxuaW1wb3J0IHtEaXJlY3RpdmUsIEVtYmVkZGVkVmlld1JlZiwgSW5wdXQsIFRlbXBsYXRlUmVmLCBWaWV3Q29udGFpbmVyUmVmLCDJtXN0cmluZ2lmeSBhcyBzdHJpbmdpZnl9IGZyb20gJ0Bhbmd1bGFyL2NvcmUnO1xuXG5cbi8qKlxuICogQSBzdHJ1Y3R1cmFsIGRpcmVjdGl2ZSB0aGF0IGNvbmRpdGlvbmFsbHkgaW5jbHVkZXMgYSB0ZW1wbGF0ZSBiYXNlZCBvbiB0aGUgdmFsdWUgb2ZcbiAqIGFuIGV4cHJlc3Npb24gY29lcmNlZCB0byBCb29sZWFuLlxuICogV2hlbiB0aGUgZXhwcmVzc2lvbiBldmFsdWF0ZXMgdG8gdHJ1ZSwgQW5ndWxhciByZW5kZXJzIHRoZSB0ZW1wbGF0ZVxuICogcHJvdmlkZWQgaW4gYSBgdGhlbmAgY2xhdXNlLCBhbmQgd2hlbiAgZmFsc2Ugb3IgbnVsbCxcbiAqIEFuZ3VsYXIgcmVuZGVycyB0aGUgdGVtcGxhdGUgcHJvdmlkZWQgaW4gYW4gb3B0aW9uYWwgYGVsc2VgIGNsYXVzZS4gVGhlIGRlZmF1bHRcbiAqIHRlbXBsYXRlIGZvciB0aGUgYGVsc2VgIGNsYXVzZSBpcyBibGFuay5cbiAqXG4gKiBBIFtzaG9ydGhhbmQgZm9ybV0oZ3VpZGUvc3RydWN0dXJhbC1kaXJlY3RpdmVzI2FzdGVyaXNrKSBvZiB0aGUgZGlyZWN0aXZlLFxuICogYCpuZ0lmPVwiY29uZGl0aW9uXCJgLCBpcyBnZW5lcmFsbHkgdXNlZCwgcHJvdmlkZWRcbiAqIGFzIGFuIGF0dHJpYnV0ZSBvZiB0aGUgYW5jaG9yIGVsZW1lbnQgZm9yIHRoZSBpbnNlcnRlZCB0ZW1wbGF0ZS5cbiAqIEFuZ3VsYXIgZXhwYW5kcyB0aGlzIGludG8gYSBtb3JlIGV4cGxpY2l0IHZlcnNpb24sIGluIHdoaWNoIHRoZSBhbmNob3IgZWxlbWVudFxuICogaXMgY29udGFpbmVkIGluIGFuIGA8bmctdGVtcGxhdGU+YCBlbGVtZW50LlxuICpcbiAqIFNpbXBsZSBmb3JtIHdpdGggc2hvcnRoYW5kIHN5bnRheDpcbiAqXG4gKiBgYGBcbiAqIDxkaXYgKm5nSWY9XCJjb25kaXRpb25cIj5Db250ZW50IHRvIHJlbmRlciB3aGVuIGNvbmRpdGlvbiBpcyB0cnVlLjwvZGl2PlxuICogYGBgXG4gKlxuICogU2ltcGxlIGZvcm0gd2l0aCBleHBhbmRlZCBzeW50YXg6XG4gKlxuICogYGBgXG4gKiA8bmctdGVtcGxhdGUgW25nSWZdPVwiY29uZGl0aW9uXCI+PGRpdj5Db250ZW50IHRvIHJlbmRlciB3aGVuIGNvbmRpdGlvbiBpc1xuICogdHJ1ZS48L2Rpdj48L25nLXRlbXBsYXRlPlxuICogYGBgXG4gKlxuICogRm9ybSB3aXRoIGFuIFwiZWxzZVwiIGJsb2NrOlxuICpcbiAqIGBgYFxuICogPGRpdiAqbmdJZj1cImNvbmRpdGlvbjsgZWxzZSBlbHNlQmxvY2tcIj5Db250ZW50IHRvIHJlbmRlciB3aGVuIGNvbmRpdGlvbiBpcyB0cnVlLjwvZGl2PlxuICogPG5nLXRlbXBsYXRlICNlbHNlQmxvY2s+Q29udGVudCB0byByZW5kZXIgd2hlbiBjb25kaXRpb24gaXMgZmFsc2UuPC9uZy10ZW1wbGF0ZT5cbiAqIGBgYFxuICpcbiAqIFNob3J0aGFuZCBmb3JtIHdpdGggXCJ0aGVuXCIgYW5kIFwiZWxzZVwiIGJsb2NrczpcbiAqXG4gKiBgYGBcbiAqIDxkaXYgKm5nSWY9XCJjb25kaXRpb247IHRoZW4gdGhlbkJsb2NrIGVsc2UgZWxzZUJsb2NrXCI+PC9kaXY+XG4gKiA8bmctdGVtcGxhdGUgI3RoZW5CbG9jaz5Db250ZW50IHRvIHJlbmRlciB3aGVuIGNvbmRpdGlvbiBpcyB0cnVlLjwvbmctdGVtcGxhdGU+XG4gKiA8bmctdGVtcGxhdGUgI2Vsc2VCbG9jaz5Db250ZW50IHRvIHJlbmRlciB3aGVuIGNvbmRpdGlvbiBpcyBmYWxzZS48L25nLXRlbXBsYXRlPlxuICogYGBgXG4gKlxuICogRm9ybSB3aXRoIHN0b3JpbmcgdGhlIHZhbHVlIGxvY2FsbHk6XG4gKlxuICogYGBgXG4gKiA8ZGl2ICpuZ0lmPVwiY29uZGl0aW9uIGFzIHZhbHVlOyBlbHNlIGVsc2VCbG9ja1wiPnt7dmFsdWV9fTwvZGl2PlxuICogPG5nLXRlbXBsYXRlICNlbHNlQmxvY2s+Q29udGVudCB0byByZW5kZXIgd2hlbiB2YWx1ZSBpcyBudWxsLjwvbmctdGVtcGxhdGU+XG4gKiBgYGBcbiAqXG4gKiBAdXNhZ2VOb3Rlc1xuICpcbiAqIFRoZSBgKm5nSWZgIGRpcmVjdGl2ZSBpcyBtb3N0IGNvbW1vbmx5IHVzZWQgdG8gY29uZGl0aW9uYWxseSBzaG93IGFuIGlubGluZSB0ZW1wbGF0ZSxcbiAqIGFzIHNlZW4gaW4gdGhlIGZvbGxvd2luZyAgZXhhbXBsZS5cbiAqIFRoZSBkZWZhdWx0IGBlbHNlYCB0ZW1wbGF0ZSBpcyBibGFuay5cbiAqXG4gKiB7QGV4YW1wbGUgY29tbW9uL25nSWYvdHMvbW9kdWxlLnRzIHJlZ2lvbj0nTmdJZlNpbXBsZSd9XG4gKlxuICogIyMjIFNob3dpbmcgYW4gYWx0ZXJuYXRpdmUgdGVtcGxhdGUgdXNpbmcgYGVsc2VgXG4gKlxuICogVG8gZGlzcGxheSBhIHRlbXBsYXRlIHdoZW4gYGV4cHJlc3Npb25gIGV2YWx1YXRlcyB0byBmYWxzZSwgdXNlIGFuIGBlbHNlYCB0ZW1wbGF0ZVxuICogYmluZGluZyBhcyBzaG93biBpbiB0aGUgZm9sbG93aW5nIGV4YW1wbGUuXG4gKiBUaGUgYGVsc2VgIGJpbmRpbmcgcG9pbnRzIHRvIGFuIGA8bmctdGVtcGxhdGU+YCAgZWxlbWVudCBsYWJlbGVkIGAjZWxzZUJsb2NrYC5cbiAqIFRoZSB0ZW1wbGF0ZSBjYW4gYmUgZGVmaW5lZCBhbnl3aGVyZSBpbiB0aGUgY29tcG9uZW50IHZpZXcsIGJ1dCBpcyB0eXBpY2FsbHkgcGxhY2VkIHJpZ2h0IGFmdGVyXG4gKiBgbmdJZmAgZm9yIHJlYWRhYmlsaXR5LlxuICpcbiAqIHtAZXhhbXBsZSBjb21tb24vbmdJZi90cy9tb2R1bGUudHMgcmVnaW9uPSdOZ0lmRWxzZSd9XG4gKlxuICogIyMjIFVzaW5nIGFuIGV4dGVybmFsIGB0aGVuYCB0ZW1wbGF0ZVxuICpcbiAqIEluIHRoZSBwcmV2aW91cyBleGFtcGxlLCB0aGUgdGhlbi1jbGF1c2UgdGVtcGxhdGUgaXMgc3BlY2lmaWVkIGlubGluZSwgYXMgdGhlIGNvbnRlbnQgb2YgdGhlXG4gKiB0YWcgdGhhdCBjb250YWlucyB0aGUgYG5nSWZgIGRpcmVjdGl2ZS4gWW91IGNhbiBhbHNvIHNwZWNpZnkgYSB0ZW1wbGF0ZSB0aGF0IGlzIGRlZmluZWRcbiAqIGV4dGVybmFsbHksIGJ5IHJlZmVyZW5jaW5nIGEgbGFiZWxlZCBgPG5nLXRlbXBsYXRlPmAgZWxlbWVudC4gV2hlbiB5b3UgZG8gdGhpcywgeW91IGNhblxuICogY2hhbmdlIHdoaWNoIHRlbXBsYXRlIHRvIHVzZSBhdCBydW50aW1lLCBhcyBzaG93biBpbiB0aGUgZm9sbG93aW5nIGV4YW1wbGUuXG4gKlxuICoge0BleGFtcGxlIGNvbW1vbi9uZ0lmL3RzL21vZHVsZS50cyByZWdpb249J05nSWZUaGVuRWxzZSd9XG4gKlxuICogIyMjIFN0b3JpbmcgYSBjb25kaXRpb25hbCByZXN1bHQgaW4gYSB2YXJpYWJsZVxuICpcbiAqIFlvdSBtaWdodCB3YW50IHRvIHNob3cgYSBzZXQgb2YgcHJvcGVydGllcyBmcm9tIHRoZSBzYW1lIG9iamVjdC4gSWYgeW91IGFyZSB3YWl0aW5nXG4gKiBmb3IgYXN5bmNocm9ub3VzIGRhdGEsIHRoZSBvYmplY3QgY2FuIGJlIHVuZGVmaW5lZC5cbiAqIEluIHRoaXMgY2FzZSwgeW91IGNhbiB1c2UgYG5nSWZgIGFuZCBzdG9yZSB0aGUgcmVzdWx0IG9mIHRoZSBjb25kaXRpb24gaW4gYSBsb2NhbFxuICogdmFyaWFibGUgYXMgc2hvd24gaW4gdGhlIGZvbGxvd2luZyBleGFtcGxlLlxuICpcbiAqIHtAZXhhbXBsZSBjb21tb24vbmdJZi90cy9tb2R1bGUudHMgcmVnaW9uPSdOZ0lmQXMnfVxuICpcbiAqIFRoaXMgY29kZSB1c2VzIG9ubHkgb25lIGBBc3luY1BpcGVgLCBzbyBvbmx5IG9uZSBzdWJzY3JpcHRpb24gaXMgY3JlYXRlZC5cbiAqIFRoZSBjb25kaXRpb25hbCBzdGF0ZW1lbnQgc3RvcmVzIHRoZSByZXN1bHQgb2YgYHVzZXJTdHJlYW18YXN5bmNgIGluIHRoZSBsb2NhbCB2YXJpYWJsZSBgdXNlcmAuXG4gKiBZb3UgY2FuIHRoZW4gYmluZCB0aGUgbG9jYWwgYHVzZXJgIHJlcGVhdGVkbHkuXG4gKlxuICogVGhlIGNvbmRpdGlvbmFsIGRpc3BsYXlzIHRoZSBkYXRhIG9ubHkgaWYgYHVzZXJTdHJlYW1gIHJldHVybnMgYSB2YWx1ZSxcbiAqIHNvIHlvdSBkb24ndCBuZWVkIHRvIHVzZSB0aGVcbiAqIHNhZmUtbmF2aWdhdGlvbi1vcGVyYXRvciAoYD8uYClcbiAqIHRvIGd1YXJkIGFnYWluc3QgbnVsbCB2YWx1ZXMgd2hlbiBhY2Nlc3NpbmcgcHJvcGVydGllcy5cbiAqIFlvdSBjYW4gZGlzcGxheSBhbiBhbHRlcm5hdGl2ZSB0ZW1wbGF0ZSB3aGlsZSB3YWl0aW5nIGZvciB0aGUgZGF0YS5cbiAqXG4gKiAjIyMgU2hvcnRoYW5kIHN5bnRheFxuICpcbiAqIFRoZSBzaG9ydGhhbmQgc3ludGF4IGAqbmdJZmAgZXhwYW5kcyBpbnRvIHR3byBzZXBhcmF0ZSB0ZW1wbGF0ZSBzcGVjaWZpY2F0aW9uc1xuICogZm9yIHRoZSBcInRoZW5cIiBhbmQgXCJlbHNlXCIgY2xhdXNlcy4gRm9yIGV4YW1wbGUsIGNvbnNpZGVyIHRoZSBmb2xsb3dpbmcgc2hvcnRoYW5kIHN0YXRlbWVudCxcbiAqIHRoYXQgaXMgbWVhbnQgdG8gc2hvdyBhIGxvYWRpbmcgcGFnZSB3aGlsZSB3YWl0aW5nIGZvciBkYXRhIHRvIGJlIGxvYWRlZC5cbiAqXG4gKiBgYGBcbiAqIDxkaXYgY2xhc3M9XCJoZXJvLWxpc3RcIiAqbmdJZj1cImhlcm9lcyBlbHNlIGxvYWRpbmdcIj5cbiAqICAuLi5cbiAqIDwvZGl2PlxuICpcbiAqIDxuZy10ZW1wbGF0ZSAjbG9hZGluZz5cbiAqICA8ZGl2PkxvYWRpbmcuLi48L2Rpdj5cbiAqIDwvbmctdGVtcGxhdGU+XG4gKiBgYGBcbiAqXG4gKiBZb3UgY2FuIHNlZSB0aGF0IHRoZSBcImVsc2VcIiBjbGF1c2UgcmVmZXJlbmNlcyB0aGUgYDxuZy10ZW1wbGF0ZT5gXG4gKiB3aXRoIHRoZSBgI2xvYWRpbmdgIGxhYmVsLCBhbmQgdGhlIHRlbXBsYXRlIGZvciB0aGUgXCJ0aGVuXCIgY2xhdXNlXG4gKiBpcyBwcm92aWRlZCBhcyB0aGUgY29udGVudCBvZiB0aGUgYW5jaG9yIGVsZW1lbnQuXG4gKlxuICogSG93ZXZlciwgd2hlbiBBbmd1bGFyIGV4cGFuZHMgdGhlIHNob3J0aGFuZCBzeW50YXgsIGl0IGNyZWF0ZXNcbiAqIGFub3RoZXIgYDxuZy10ZW1wbGF0ZT5gIHRhZywgd2l0aCBgbmdJZmAgYW5kIGBuZ0lmRWxzZWAgZGlyZWN0aXZlcy5cbiAqIFRoZSBhbmNob3IgZWxlbWVudCBjb250YWluaW5nIHRoZSB0ZW1wbGF0ZSBmb3IgdGhlIFwidGhlblwiIGNsYXVzZSBiZWNvbWVzXG4gKiB0aGUgY29udGVudCBvZiB0aGlzIHVubGFiZWxlZCBgPG5nLXRlbXBsYXRlPmAgdGFnLlxuICpcbiAqIGBgYFxuICogPG5nLXRlbXBsYXRlIFtuZ0lmXT1cImhlcm9lc1wiIFtuZ0lmRWxzZV09XCJsb2FkaW5nXCI+XG4gKiAgPGRpdiBjbGFzcz1cImhlcm8tbGlzdFwiPlxuICogICAuLi5cbiAqICA8L2Rpdj5cbiAqIDwvbmctdGVtcGxhdGU+XG4gKlxuICogPG5nLXRlbXBsYXRlICNsb2FkaW5nPlxuICogIDxkaXY+TG9hZGluZy4uLjwvZGl2PlxuICogPC9uZy10ZW1wbGF0ZT5cbiAqIGBgYFxuICpcbiAqIFRoZSBwcmVzZW5jZSBvZiB0aGUgaW1wbGljaXQgdGVtcGxhdGUgb2JqZWN0IGhhcyBpbXBsaWNhdGlvbnMgZm9yIHRoZSBuZXN0aW5nIG9mXG4gKiBzdHJ1Y3R1cmFsIGRpcmVjdGl2ZXMuIEZvciBtb3JlIG9uIHRoaXMgc3ViamVjdCwgc2VlXG4gKiBbU3RydWN0dXJhbCBEaXJlY3RpdmVzXShodHRwczovL2FuZ3VsYXIuaW8vZ3VpZGUvYnVpbHQtaW4tZGlyZWN0aXZlcyNvbmUtcGVyLWVsZW1lbnQpLlxuICpcbiAqIEBuZ01vZHVsZSBDb21tb25Nb2R1bGVcbiAqIEBwdWJsaWNBcGlcbiAqL1xuQERpcmVjdGl2ZSh7c2VsZWN0b3I6ICdbbmdJZl0nfSlcbmV4cG9ydCBjbGFzcyBOZ0lmPFQgPSB1bmtub3duPiB7XG4gIHByaXZhdGUgX2NvbnRleHQ6IE5nSWZDb250ZXh0PFQ+ID0gbmV3IE5nSWZDb250ZXh0PFQ+KCk7XG4gIHByaXZhdGUgX3RoZW5UZW1wbGF0ZVJlZjogVGVtcGxhdGVSZWY8TmdJZkNvbnRleHQ8VD4+fG51bGwgPSBudWxsO1xuICBwcml2YXRlIF9lbHNlVGVtcGxhdGVSZWY6IFRlbXBsYXRlUmVmPE5nSWZDb250ZXh0PFQ+PnxudWxsID0gbnVsbDtcbiAgcHJpdmF0ZSBfdGhlblZpZXdSZWY6IEVtYmVkZGVkVmlld1JlZjxOZ0lmQ29udGV4dDxUPj58bnVsbCA9IG51bGw7XG4gIHByaXZhdGUgX2Vsc2VWaWV3UmVmOiBFbWJlZGRlZFZpZXdSZWY8TmdJZkNvbnRleHQ8VD4+fG51bGwgPSBudWxsO1xuXG4gIGNvbnN0cnVjdG9yKHByaXZhdGUgX3ZpZXdDb250YWluZXI6IFZpZXdDb250YWluZXJSZWYsIHRlbXBsYXRlUmVmOiBUZW1wbGF0ZVJlZjxOZ0lmQ29udGV4dDxUPj4pIHtcbiAgICB0aGlzLl90aGVuVGVtcGxhdGVSZWYgPSB0ZW1wbGF0ZVJlZjtcbiAgfVxuXG4gIC8qKlxuICAgKiBUaGUgQm9vbGVhbiBleHByZXNzaW9uIHRvIGV2YWx1YXRlIGFzIHRoZSBjb25kaXRpb24gZm9yIHNob3dpbmcgYSB0ZW1wbGF0ZS5cbiAgICovXG4gIEBJbnB1dCgpXG4gIHNldCBuZ0lmKGNvbmRpdGlvbjogVCkge1xuICAgIHRoaXMuX2NvbnRleHQuJGltcGxpY2l0ID0gdGhpcy5fY29udGV4dC5uZ0lmID0gY29uZGl0aW9uO1xuICAgIHRoaXMuX3VwZGF0ZVZpZXcoKTtcbiAgfVxuXG4gIC8qKlxuICAgKiBBIHRlbXBsYXRlIHRvIHNob3cgaWYgdGhlIGNvbmRpdGlvbiBleHByZXNzaW9uIGV2YWx1YXRlcyB0byB0cnVlLlxuICAgKi9cbiAgQElucHV0KClcbiAgc2V0IG5nSWZUaGVuKHRlbXBsYXRlUmVmOiBUZW1wbGF0ZVJlZjxOZ0lmQ29udGV4dDxUPj58bnVsbCkge1xuICAgIGFzc2VydFRlbXBsYXRlKCduZ0lmVGhlbicsIHRlbXBsYXRlUmVmKTtcbiAgICB0aGlzLl90aGVuVGVtcGxhdGVSZWYgPSB0ZW1wbGF0ZVJlZjtcbiAgICB0aGlzLl90aGVuVmlld1JlZiA9IG51bGw7ICAvLyBjbGVhciBwcmV2aW91cyB2aWV3IGlmIGFueS5cbiAgICB0aGlzLl91cGRhdGVWaWV3KCk7XG4gIH1cblxuICAvKipcbiAgICogQSB0ZW1wbGF0ZSB0byBzaG93IGlmIHRoZSBjb25kaXRpb24gZXhwcmVzc2lvbiBldmFsdWF0ZXMgdG8gZmFsc2UuXG4gICAqL1xuICBASW5wdXQoKVxuICBzZXQgbmdJZkVsc2UodGVtcGxhdGVSZWY6IFRlbXBsYXRlUmVmPE5nSWZDb250ZXh0PFQ+PnxudWxsKSB7XG4gICAgYXNzZXJ0VGVtcGxhdGUoJ25nSWZFbHNlJywgdGVtcGxhdGVSZWYpO1xuICAgIHRoaXMuX2Vsc2VUZW1wbGF0ZVJlZiA9IHRlbXBsYXRlUmVmO1xuICAgIHRoaXMuX2Vsc2VWaWV3UmVmID0gbnVsbDsgIC8vIGNsZWFyIHByZXZpb3VzIHZpZXcgaWYgYW55LlxuICAgIHRoaXMuX3VwZGF0ZVZpZXcoKTtcbiAgfVxuXG4gIHByaXZhdGUgX3VwZGF0ZVZpZXcoKSB7XG4gICAgaWYgKHRoaXMuX2NvbnRleHQuJGltcGxpY2l0KSB7XG4gICAgICBpZiAoIXRoaXMuX3RoZW5WaWV3UmVmKSB7XG4gICAgICAgIHRoaXMuX3ZpZXdDb250YWluZXIuY2xlYXIoKTtcbiAgICAgICAgdGhpcy5fZWxzZVZpZXdSZWYgPSBudWxsO1xuICAgICAgICBpZiAodGhpcy5fdGhlblRlbXBsYXRlUmVmKSB7XG4gICAgICAgICAgdGhpcy5fdGhlblZpZXdSZWYgPVxuICAgICAgICAgICAgICB0aGlzLl92aWV3Q29udGFpbmVyLmNyZWF0ZUVtYmVkZGVkVmlldyh0aGlzLl90aGVuVGVtcGxhdGVSZWYsIHRoaXMuX2NvbnRleHQpO1xuICAgICAgICB9XG4gICAgICB9XG4gICAgfSBlbHNlIHtcbiAgICAgIGlmICghdGhpcy5fZWxzZVZpZXdSZWYpIHtcbiAgICAgICAgdGhpcy5fdmlld0NvbnRhaW5lci5jbGVhcigpO1xuICAgICAgICB0aGlzLl90aGVuVmlld1JlZiA9IG51bGw7XG4gICAgICAgIGlmICh0aGlzLl9lbHNlVGVtcGxhdGVSZWYpIHtcbiAgICAgICAgICB0aGlzLl9lbHNlVmlld1JlZiA9XG4gICAgICAgICAgICAgIHRoaXMuX3ZpZXdDb250YWluZXIuY3JlYXRlRW1iZWRkZWRWaWV3KHRoaXMuX2Vsc2VUZW1wbGF0ZVJlZiwgdGhpcy5fY29udGV4dCk7XG4gICAgICAgIH1cbiAgICAgIH1cbiAgICB9XG4gIH1cblxuICAvKiogQGludGVybmFsICovXG4gIHB1YmxpYyBzdGF0aWMgbmdJZlVzZUlmVHlwZUd1YXJkOiB2b2lkO1xuXG4gIC8qKlxuICAgKiBBc3NlcnQgdGhlIGNvcnJlY3QgdHlwZSBvZiB0aGUgZXhwcmVzc2lvbiBib3VuZCB0byB0aGUgYG5nSWZgIGlucHV0IHdpdGhpbiB0aGUgdGVtcGxhdGUuXG4gICAqXG4gICAqIFRoZSBwcmVzZW5jZSBvZiB0aGlzIHN0YXRpYyBmaWVsZCBpcyBhIHNpZ25hbCB0byB0aGUgSXZ5IHRlbXBsYXRlIHR5cGUgY2hlY2sgY29tcGlsZXIgdGhhdFxuICAgKiB3aGVuIHRoZSBgTmdJZmAgc3RydWN0dXJhbCBkaXJlY3RpdmUgcmVuZGVycyBpdHMgdGVtcGxhdGUsIHRoZSB0eXBlIG9mIHRoZSBleHByZXNzaW9uIGJvdW5kXG4gICAqIHRvIGBuZ0lmYCBzaG91bGQgYmUgbmFycm93ZWQgaW4gc29tZSB3YXkuIEZvciBgTmdJZmAsIHRoZSBiaW5kaW5nIGV4cHJlc3Npb24gaXRzZWxmIGlzIHVzZWQgdG9cbiAgICogbmFycm93IGl0cyB0eXBlLCB3aGljaCBhbGxvd3MgdGhlIHN0cmljdE51bGxDaGVja3MgZmVhdHVyZSBvZiBUeXBlU2NyaXB0IHRvIHdvcmsgd2l0aCBgTmdJZmAuXG4gICAqL1xuICBzdGF0aWMgbmdUZW1wbGF0ZUd1YXJkX25nSWY6ICdiaW5kaW5nJztcblxuICAvKipcbiAgICogQXNzZXJ0cyB0aGUgY29ycmVjdCB0eXBlIG9mIHRoZSBjb250ZXh0IGZvciB0aGUgdGVtcGxhdGUgdGhhdCBgTmdJZmAgd2lsbCByZW5kZXIuXG4gICAqXG4gICAqIFRoZSBwcmVzZW5jZSBvZiB0aGlzIG1ldGhvZCBpcyBhIHNpZ25hbCB0byB0aGUgSXZ5IHRlbXBsYXRlIHR5cGUtY2hlY2sgY29tcGlsZXIgdGhhdCB0aGVcbiAgICogYE5nSWZgIHN0cnVjdHVyYWwgZGlyZWN0aXZlIHJlbmRlcnMgaXRzIHRlbXBsYXRlIHdpdGggYSBzcGVjaWZpYyBjb250ZXh0IHR5cGUuXG4gICAqL1xuICBzdGF0aWMgbmdUZW1wbGF0ZUNvbnRleHRHdWFyZDxUPihkaXI6IE5nSWY8VD4sIGN0eDogYW55KTpcbiAgICAgIGN0eCBpcyBOZ0lmQ29udGV4dDxFeGNsdWRlPFQsIGZhbHNlfDB8Jyd8bnVsbHx1bmRlZmluZWQ+PiB7XG4gICAgcmV0dXJuIHRydWU7XG4gIH1cbn1cblxuLyoqXG4gKiBAcHVibGljQXBpXG4gKi9cbmV4cG9ydCBjbGFzcyBOZ0lmQ29udGV4dDxUID0gdW5rbm93bj4ge1xuICBwdWJsaWMgJGltcGxpY2l0OiBUID0gbnVsbCE7XG4gIHB1YmxpYyBuZ0lmOiBUID0gbnVsbCE7XG59XG5cbmZ1bmN0aW9uIGFzc2VydFRlbXBsYXRlKHByb3BlcnR5OiBzdHJpbmcsIHRlbXBsYXRlUmVmOiBUZW1wbGF0ZVJlZjxhbnk+fG51bGwpOiB2b2lkIHtcbiAgY29uc3QgaXNUZW1wbGF0ZVJlZk9yTnVsbCA9ICEhKCF0ZW1wbGF0ZVJlZiB8fCB0ZW1wbGF0ZVJlZi5jcmVhdGVFbWJlZGRlZFZpZXcpO1xuICBpZiAoIWlzVGVtcGxhdGVSZWZPck51bGwpIHtcbiAgICB0aHJvdyBuZXcgRXJyb3IoYCR7cHJvcGVydHl9IG11c3QgYmUgYSBUZW1wbGF0ZVJlZiwgYnV0IHJlY2VpdmVkICcke3N0cmluZ2lmeSh0ZW1wbGF0ZVJlZil9Jy5gKTtcbiAgfVxufVxuIl19