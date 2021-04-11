/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
(function (factory) {
    if (typeof module === "object" && typeof module.exports === "object") {
        var v = factory(require, exports);
        if (v !== undefined) module.exports = v;
    }
    else if (typeof define === "function" && define.amd) {
        define("@angular/core/schematics/migrations/static-queries/strategies/usage_strategy/template_usage_visitor", ["require", "exports", "@angular/compiler", "@angular/compiler/src/render3/r3_ast"], factory);
    }
})(function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.TemplateUsageVisitor = void 0;
    const compiler_1 = require("@angular/compiler");
    const r3_ast_1 = require("@angular/compiler/src/render3/r3_ast");
    /**
     * AST visitor that traverses the Render3 HTML AST in order to check if the given
     * query property is accessed statically in the template.
     */
    class TemplateUsageVisitor extends r3_ast_1.NullVisitor {
        constructor(queryPropertyName) {
            super();
            this.queryPropertyName = queryPropertyName;
            this.hasQueryTemplateReference = false;
            this.expressionAstVisitor = new ExpressionAstVisitor(this.queryPropertyName);
        }
        /** Checks whether the given query is statically accessed within the specified HTML nodes. */
        isQueryUsedStatically(htmlNodes) {
            this.hasQueryTemplateReference = false;
            this.expressionAstVisitor.hasQueryPropertyRead = false;
            // Visit all AST nodes and check if the query property is used statically.
            r3_ast_1.visitAll(this, htmlNodes);
            return !this.hasQueryTemplateReference && this.expressionAstVisitor.hasQueryPropertyRead;
        }
        visitElement(element) {
            // In case there is a template references variable that matches the query property
            // name, we can finish this visitor as such a template variable can be used in the
            // entire template and the query therefore can't be accessed from the template.
            if (element.references.some(r => r.name === this.queryPropertyName)) {
                this.hasQueryTemplateReference = true;
                return;
            }
            r3_ast_1.visitAll(this, element.attributes);
            r3_ast_1.visitAll(this, element.inputs);
            r3_ast_1.visitAll(this, element.outputs);
            r3_ast_1.visitAll(this, element.children);
        }
        visitTemplate(template) {
            r3_ast_1.visitAll(this, template.attributes);
            r3_ast_1.visitAll(this, template.inputs);
            r3_ast_1.visitAll(this, template.outputs);
            // We don't want to visit any children of the template as these never can't
            // access a query statically. The templates can be rendered in the ngAfterViewInit"
            // lifecycle hook at the earliest.
        }
        visitBoundAttribute(attribute) {
            attribute.value.visit(this.expressionAstVisitor, attribute.sourceSpan);
        }
        visitBoundText(text) {
            text.value.visit(this.expressionAstVisitor, text.sourceSpan);
        }
        visitBoundEvent(node) {
            node.handler.visit(this.expressionAstVisitor, node.handlerSpan);
        }
    }
    exports.TemplateUsageVisitor = TemplateUsageVisitor;
    /**
     * AST visitor that checks if the given expression contains property reads that
     * refer to the specified query property name.
     */
    class ExpressionAstVisitor extends compiler_1.RecursiveAstVisitor {
        constructor(queryPropertyName) {
            super();
            this.queryPropertyName = queryPropertyName;
            this.hasQueryPropertyRead = false;
        }
        visitPropertyRead(node, span) {
            // The receiver of the property read needs to be "implicit" as queries are accessed
            // from the component instance and not from other objects.
            if (node.receiver instanceof compiler_1.ImplicitReceiver && node.name === this.queryPropertyName) {
                this.hasQueryPropertyRead = true;
                return;
            }
            super.visitPropertyRead(node, span);
        }
    }
});
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoidGVtcGxhdGVfdXNhZ2VfdmlzaXRvci5qcyIsInNvdXJjZVJvb3QiOiIiLCJzb3VyY2VzIjpbIi4uLy4uLy4uLy4uLy4uLy4uLy4uLy4uLy4uLy4uL3BhY2thZ2VzL2NvcmUvc2NoZW1hdGljcy9taWdyYXRpb25zL3N0YXRpYy1xdWVyaWVzL3N0cmF0ZWdpZXMvdXNhZ2Vfc3RyYXRlZ3kvdGVtcGxhdGVfdXNhZ2VfdmlzaXRvci50cyJdLCJuYW1lcyI6W10sIm1hcHBpbmdzIjoiQUFBQTs7Ozs7O0dBTUc7Ozs7Ozs7Ozs7Ozs7SUFFSCxnREFBdUc7SUFDdkcsaUVBQTJJO0lBRTNJOzs7T0FHRztJQUNILE1BQWEsb0JBQXFCLFNBQVEsb0JBQVc7UUFJbkQsWUFBbUIsaUJBQXlCO1lBQzFDLEtBQUssRUFBRSxDQUFDO1lBRFMsc0JBQWlCLEdBQWpCLGlCQUFpQixDQUFRO1lBSHBDLDhCQUF5QixHQUFHLEtBQUssQ0FBQztZQUNsQyx5QkFBb0IsR0FBRyxJQUFJLG9CQUFvQixDQUFDLElBQUksQ0FBQyxpQkFBaUIsQ0FBQyxDQUFDO1FBSWhGLENBQUM7UUFFRCw2RkFBNkY7UUFDN0YscUJBQXFCLENBQUMsU0FBaUI7WUFDckMsSUFBSSxDQUFDLHlCQUF5QixHQUFHLEtBQUssQ0FBQztZQUN2QyxJQUFJLENBQUMsb0JBQW9CLENBQUMsb0JBQW9CLEdBQUcsS0FBSyxDQUFDO1lBRXZELDBFQUEwRTtZQUMxRSxpQkFBUSxDQUFDLElBQUksRUFBRSxTQUFTLENBQUMsQ0FBQztZQUUxQixPQUFPLENBQUMsSUFBSSxDQUFDLHlCQUF5QixJQUFJLElBQUksQ0FBQyxvQkFBb0IsQ0FBQyxvQkFBb0IsQ0FBQztRQUMzRixDQUFDO1FBRUQsWUFBWSxDQUFDLE9BQWdCO1lBQzNCLGtGQUFrRjtZQUNsRixrRkFBa0Y7WUFDbEYsK0VBQStFO1lBQy9FLElBQUksT0FBTyxDQUFDLFVBQVUsQ0FBQyxJQUFJLENBQUMsQ0FBQyxDQUFDLEVBQUUsQ0FBQyxDQUFDLENBQUMsSUFBSSxLQUFLLElBQUksQ0FBQyxpQkFBaUIsQ0FBQyxFQUFFO2dCQUNuRSxJQUFJLENBQUMseUJBQXlCLEdBQUcsSUFBSSxDQUFDO2dCQUN0QyxPQUFPO2FBQ1I7WUFFRCxpQkFBUSxDQUFDLElBQUksRUFBRSxPQUFPLENBQUMsVUFBVSxDQUFDLENBQUM7WUFDbkMsaUJBQVEsQ0FBQyxJQUFJLEVBQUUsT0FBTyxDQUFDLE1BQU0sQ0FBQyxDQUFDO1lBQy9CLGlCQUFRLENBQUMsSUFBSSxFQUFFLE9BQU8sQ0FBQyxPQUFPLENBQUMsQ0FBQztZQUNoQyxpQkFBUSxDQUFDLElBQUksRUFBRSxPQUFPLENBQUMsUUFBUSxDQUFDLENBQUM7UUFDbkMsQ0FBQztRQUVELGFBQWEsQ0FBQyxRQUFrQjtZQUM5QixpQkFBUSxDQUFDLElBQUksRUFBRSxRQUFRLENBQUMsVUFBVSxDQUFDLENBQUM7WUFDcEMsaUJBQVEsQ0FBQyxJQUFJLEVBQUUsUUFBUSxDQUFDLE1BQU0sQ0FBQyxDQUFDO1lBQ2hDLGlCQUFRLENBQUMsSUFBSSxFQUFFLFFBQVEsQ0FBQyxPQUFPLENBQUMsQ0FBQztZQUVqQywyRUFBMkU7WUFDM0UsbUZBQW1GO1lBQ25GLGtDQUFrQztRQUNwQyxDQUFDO1FBRUQsbUJBQW1CLENBQUMsU0FBeUI7WUFDM0MsU0FBUyxDQUFDLEtBQUssQ0FBQyxLQUFLLENBQUMsSUFBSSxDQUFDLG9CQUFvQixFQUFFLFNBQVMsQ0FBQyxVQUFVLENBQUMsQ0FBQztRQUN6RSxDQUFDO1FBRUQsY0FBYyxDQUFDLElBQWU7WUFDNUIsSUFBSSxDQUFDLEtBQUssQ0FBQyxLQUFLLENBQUMsSUFBSSxDQUFDLG9CQUFvQixFQUFFLElBQUksQ0FBQyxVQUFVLENBQUMsQ0FBQztRQUMvRCxDQUFDO1FBRUQsZUFBZSxDQUFDLElBQWdCO1lBQzlCLElBQUksQ0FBQyxPQUFPLENBQUMsS0FBSyxDQUFDLElBQUksQ0FBQyxvQkFBb0IsRUFBRSxJQUFJLENBQUMsV0FBVyxDQUFDLENBQUM7UUFDbEUsQ0FBQztLQUNGO0lBdkRELG9EQXVEQztJQUVEOzs7T0FHRztJQUNILE1BQU0sb0JBQXFCLFNBQVEsOEJBQW1CO1FBR3BELFlBQW9CLGlCQUF5QjtZQUMzQyxLQUFLLEVBQUUsQ0FBQztZQURVLHNCQUFpQixHQUFqQixpQkFBaUIsQ0FBUTtZQUY3Qyx5QkFBb0IsR0FBRyxLQUFLLENBQUM7UUFJN0IsQ0FBQztRQUVELGlCQUFpQixDQUFDLElBQWtCLEVBQUUsSUFBcUI7WUFDekQsbUZBQW1GO1lBQ25GLDBEQUEwRDtZQUMxRCxJQUFJLElBQUksQ0FBQyxRQUFRLFlBQVksMkJBQWdCLElBQUksSUFBSSxDQUFDLElBQUksS0FBSyxJQUFJLENBQUMsaUJBQWlCLEVBQUU7Z0JBQ3JGLElBQUksQ0FBQyxvQkFBb0IsR0FBRyxJQUFJLENBQUM7Z0JBQ2pDLE9BQU87YUFDUjtZQUVELEtBQUssQ0FBQyxpQkFBaUIsQ0FBQyxJQUFJLEVBQUUsSUFBSSxDQUFDLENBQUM7UUFDdEMsQ0FBQztLQUNGIiwic291cmNlc0NvbnRlbnQiOlsiLyoqXG4gKiBAbGljZW5zZVxuICogQ29weXJpZ2h0IEdvb2dsZSBMTEMgQWxsIFJpZ2h0cyBSZXNlcnZlZC5cbiAqXG4gKiBVc2Ugb2YgdGhpcyBzb3VyY2UgY29kZSBpcyBnb3Zlcm5lZCBieSBhbiBNSVQtc3R5bGUgbGljZW5zZSB0aGF0IGNhbiBiZVxuICogZm91bmQgaW4gdGhlIExJQ0VOU0UgZmlsZSBhdCBodHRwczovL2FuZ3VsYXIuaW8vbGljZW5zZVxuICovXG5cbmltcG9ydCB7SW1wbGljaXRSZWNlaXZlciwgUGFyc2VTb3VyY2VTcGFuLCBQcm9wZXJ0eVJlYWQsIFJlY3Vyc2l2ZUFzdFZpc2l0b3J9IGZyb20gJ0Bhbmd1bGFyL2NvbXBpbGVyJztcbmltcG9ydCB7Qm91bmRBdHRyaWJ1dGUsIEJvdW5kRXZlbnQsIEJvdW5kVGV4dCwgRWxlbWVudCwgTm9kZSwgTnVsbFZpc2l0b3IsIFRlbXBsYXRlLCB2aXNpdEFsbH0gZnJvbSAnQGFuZ3VsYXIvY29tcGlsZXIvc3JjL3JlbmRlcjMvcjNfYXN0JztcblxuLyoqXG4gKiBBU1QgdmlzaXRvciB0aGF0IHRyYXZlcnNlcyB0aGUgUmVuZGVyMyBIVE1MIEFTVCBpbiBvcmRlciB0byBjaGVjayBpZiB0aGUgZ2l2ZW5cbiAqIHF1ZXJ5IHByb3BlcnR5IGlzIGFjY2Vzc2VkIHN0YXRpY2FsbHkgaW4gdGhlIHRlbXBsYXRlLlxuICovXG5leHBvcnQgY2xhc3MgVGVtcGxhdGVVc2FnZVZpc2l0b3IgZXh0ZW5kcyBOdWxsVmlzaXRvciB7XG4gIHByaXZhdGUgaGFzUXVlcnlUZW1wbGF0ZVJlZmVyZW5jZSA9IGZhbHNlO1xuICBwcml2YXRlIGV4cHJlc3Npb25Bc3RWaXNpdG9yID0gbmV3IEV4cHJlc3Npb25Bc3RWaXNpdG9yKHRoaXMucXVlcnlQcm9wZXJ0eU5hbWUpO1xuXG4gIGNvbnN0cnVjdG9yKHB1YmxpYyBxdWVyeVByb3BlcnR5TmFtZTogc3RyaW5nKSB7XG4gICAgc3VwZXIoKTtcbiAgfVxuXG4gIC8qKiBDaGVja3Mgd2hldGhlciB0aGUgZ2l2ZW4gcXVlcnkgaXMgc3RhdGljYWxseSBhY2Nlc3NlZCB3aXRoaW4gdGhlIHNwZWNpZmllZCBIVE1MIG5vZGVzLiAqL1xuICBpc1F1ZXJ5VXNlZFN0YXRpY2FsbHkoaHRtbE5vZGVzOiBOb2RlW10pOiBib29sZWFuIHtcbiAgICB0aGlzLmhhc1F1ZXJ5VGVtcGxhdGVSZWZlcmVuY2UgPSBmYWxzZTtcbiAgICB0aGlzLmV4cHJlc3Npb25Bc3RWaXNpdG9yLmhhc1F1ZXJ5UHJvcGVydHlSZWFkID0gZmFsc2U7XG5cbiAgICAvLyBWaXNpdCBhbGwgQVNUIG5vZGVzIGFuZCBjaGVjayBpZiB0aGUgcXVlcnkgcHJvcGVydHkgaXMgdXNlZCBzdGF0aWNhbGx5LlxuICAgIHZpc2l0QWxsKHRoaXMsIGh0bWxOb2Rlcyk7XG5cbiAgICByZXR1cm4gIXRoaXMuaGFzUXVlcnlUZW1wbGF0ZVJlZmVyZW5jZSAmJiB0aGlzLmV4cHJlc3Npb25Bc3RWaXNpdG9yLmhhc1F1ZXJ5UHJvcGVydHlSZWFkO1xuICB9XG5cbiAgdmlzaXRFbGVtZW50KGVsZW1lbnQ6IEVsZW1lbnQpOiB2b2lkIHtcbiAgICAvLyBJbiBjYXNlIHRoZXJlIGlzIGEgdGVtcGxhdGUgcmVmZXJlbmNlcyB2YXJpYWJsZSB0aGF0IG1hdGNoZXMgdGhlIHF1ZXJ5IHByb3BlcnR5XG4gICAgLy8gbmFtZSwgd2UgY2FuIGZpbmlzaCB0aGlzIHZpc2l0b3IgYXMgc3VjaCBhIHRlbXBsYXRlIHZhcmlhYmxlIGNhbiBiZSB1c2VkIGluIHRoZVxuICAgIC8vIGVudGlyZSB0ZW1wbGF0ZSBhbmQgdGhlIHF1ZXJ5IHRoZXJlZm9yZSBjYW4ndCBiZSBhY2Nlc3NlZCBmcm9tIHRoZSB0ZW1wbGF0ZS5cbiAgICBpZiAoZWxlbWVudC5yZWZlcmVuY2VzLnNvbWUociA9PiByLm5hbWUgPT09IHRoaXMucXVlcnlQcm9wZXJ0eU5hbWUpKSB7XG4gICAgICB0aGlzLmhhc1F1ZXJ5VGVtcGxhdGVSZWZlcmVuY2UgPSB0cnVlO1xuICAgICAgcmV0dXJuO1xuICAgIH1cblxuICAgIHZpc2l0QWxsKHRoaXMsIGVsZW1lbnQuYXR0cmlidXRlcyk7XG4gICAgdmlzaXRBbGwodGhpcywgZWxlbWVudC5pbnB1dHMpO1xuICAgIHZpc2l0QWxsKHRoaXMsIGVsZW1lbnQub3V0cHV0cyk7XG4gICAgdmlzaXRBbGwodGhpcywgZWxlbWVudC5jaGlsZHJlbik7XG4gIH1cblxuICB2aXNpdFRlbXBsYXRlKHRlbXBsYXRlOiBUZW1wbGF0ZSk6IHZvaWQge1xuICAgIHZpc2l0QWxsKHRoaXMsIHRlbXBsYXRlLmF0dHJpYnV0ZXMpO1xuICAgIHZpc2l0QWxsKHRoaXMsIHRlbXBsYXRlLmlucHV0cyk7XG4gICAgdmlzaXRBbGwodGhpcywgdGVtcGxhdGUub3V0cHV0cyk7XG5cbiAgICAvLyBXZSBkb24ndCB3YW50IHRvIHZpc2l0IGFueSBjaGlsZHJlbiBvZiB0aGUgdGVtcGxhdGUgYXMgdGhlc2UgbmV2ZXIgY2FuJ3RcbiAgICAvLyBhY2Nlc3MgYSBxdWVyeSBzdGF0aWNhbGx5LiBUaGUgdGVtcGxhdGVzIGNhbiBiZSByZW5kZXJlZCBpbiB0aGUgbmdBZnRlclZpZXdJbml0XCJcbiAgICAvLyBsaWZlY3ljbGUgaG9vayBhdCB0aGUgZWFybGllc3QuXG4gIH1cblxuICB2aXNpdEJvdW5kQXR0cmlidXRlKGF0dHJpYnV0ZTogQm91bmRBdHRyaWJ1dGUpIHtcbiAgICBhdHRyaWJ1dGUudmFsdWUudmlzaXQodGhpcy5leHByZXNzaW9uQXN0VmlzaXRvciwgYXR0cmlidXRlLnNvdXJjZVNwYW4pO1xuICB9XG5cbiAgdmlzaXRCb3VuZFRleHQodGV4dDogQm91bmRUZXh0KSB7XG4gICAgdGV4dC52YWx1ZS52aXNpdCh0aGlzLmV4cHJlc3Npb25Bc3RWaXNpdG9yLCB0ZXh0LnNvdXJjZVNwYW4pO1xuICB9XG5cbiAgdmlzaXRCb3VuZEV2ZW50KG5vZGU6IEJvdW5kRXZlbnQpIHtcbiAgICBub2RlLmhhbmRsZXIudmlzaXQodGhpcy5leHByZXNzaW9uQXN0VmlzaXRvciwgbm9kZS5oYW5kbGVyU3Bhbik7XG4gIH1cbn1cblxuLyoqXG4gKiBBU1QgdmlzaXRvciB0aGF0IGNoZWNrcyBpZiB0aGUgZ2l2ZW4gZXhwcmVzc2lvbiBjb250YWlucyBwcm9wZXJ0eSByZWFkcyB0aGF0XG4gKiByZWZlciB0byB0aGUgc3BlY2lmaWVkIHF1ZXJ5IHByb3BlcnR5IG5hbWUuXG4gKi9cbmNsYXNzIEV4cHJlc3Npb25Bc3RWaXNpdG9yIGV4dGVuZHMgUmVjdXJzaXZlQXN0VmlzaXRvciB7XG4gIGhhc1F1ZXJ5UHJvcGVydHlSZWFkID0gZmFsc2U7XG5cbiAgY29uc3RydWN0b3IocHJpdmF0ZSBxdWVyeVByb3BlcnR5TmFtZTogc3RyaW5nKSB7XG4gICAgc3VwZXIoKTtcbiAgfVxuXG4gIHZpc2l0UHJvcGVydHlSZWFkKG5vZGU6IFByb3BlcnR5UmVhZCwgc3BhbjogUGFyc2VTb3VyY2VTcGFuKTogYW55IHtcbiAgICAvLyBUaGUgcmVjZWl2ZXIgb2YgdGhlIHByb3BlcnR5IHJlYWQgbmVlZHMgdG8gYmUgXCJpbXBsaWNpdFwiIGFzIHF1ZXJpZXMgYXJlIGFjY2Vzc2VkXG4gICAgLy8gZnJvbSB0aGUgY29tcG9uZW50IGluc3RhbmNlIGFuZCBub3QgZnJvbSBvdGhlciBvYmplY3RzLlxuICAgIGlmIChub2RlLnJlY2VpdmVyIGluc3RhbmNlb2YgSW1wbGljaXRSZWNlaXZlciAmJiBub2RlLm5hbWUgPT09IHRoaXMucXVlcnlQcm9wZXJ0eU5hbWUpIHtcbiAgICAgIHRoaXMuaGFzUXVlcnlQcm9wZXJ0eVJlYWQgPSB0cnVlO1xuICAgICAgcmV0dXJuO1xuICAgIH1cblxuICAgIHN1cGVyLnZpc2l0UHJvcGVydHlSZWFkKG5vZGUsIHNwYW4pO1xuICB9XG59XG4iXX0=