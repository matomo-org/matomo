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
        define("@angular/core/schematics/migrations/template-var-assignment/analyze_template", ["require", "exports", "@angular/compiler/src/render3/r3_ast", "@angular/core/schematics/utils/parse_html", "@angular/core/schematics/migrations/template-var-assignment/angular/html_variable_assignment_visitor"], factory);
    }
})(function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.analyzeResolvedTemplate = void 0;
    const r3_ast_1 = require("@angular/compiler/src/render3/r3_ast");
    const parse_html_1 = require("@angular/core/schematics/utils/parse_html");
    const html_variable_assignment_visitor_1 = require("@angular/core/schematics/migrations/template-var-assignment/angular/html_variable_assignment_visitor");
    /**
     * Analyzes a given resolved template by looking for property assignments to local
     * template variables within bound events.
     */
    function analyzeResolvedTemplate(template) {
        const templateNodes = parse_html_1.parseHtmlGracefully(template.content, template.filePath);
        if (!templateNodes) {
            return null;
        }
        const visitor = new html_variable_assignment_visitor_1.HtmlVariableAssignmentVisitor();
        // Analyze the Angular Render3 HTML AST and collect all template variable assignments.
        r3_ast_1.visitAll(visitor, templateNodes);
        return visitor.variableAssignments.map(({ node, start, end }) => ({ node, start: start + node.span.start, end }));
    }
    exports.analyzeResolvedTemplate = analyzeResolvedTemplate;
});
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiYW5hbHl6ZV90ZW1wbGF0ZS5qcyIsInNvdXJjZVJvb3QiOiIiLCJzb3VyY2VzIjpbIi4uLy4uLy4uLy4uLy4uLy4uLy4uLy4uL3BhY2thZ2VzL2NvcmUvc2NoZW1hdGljcy9taWdyYXRpb25zL3RlbXBsYXRlLXZhci1hc3NpZ25tZW50L2FuYWx5emVfdGVtcGxhdGUudHMiXSwibmFtZXMiOltdLCJtYXBwaW5ncyI6IkFBQUE7Ozs7OztHQU1HOzs7Ozs7Ozs7Ozs7O0lBR0gsaUVBQThEO0lBRTlELDBFQUEyRDtJQUMzRCwySkFBeUY7SUFRekY7OztPQUdHO0lBQ0gsU0FBZ0IsdUJBQXVCLENBQUMsUUFBMEI7UUFFaEUsTUFBTSxhQUFhLEdBQUcsZ0NBQW1CLENBQUMsUUFBUSxDQUFDLE9BQU8sRUFBRSxRQUFRLENBQUMsUUFBUSxDQUFDLENBQUM7UUFFL0UsSUFBSSxDQUFDLGFBQWEsRUFBRTtZQUNsQixPQUFPLElBQUksQ0FBQztTQUNiO1FBRUQsTUFBTSxPQUFPLEdBQUcsSUFBSSxnRUFBNkIsRUFBRSxDQUFDO1FBRXBELHNGQUFzRjtRQUN0RixpQkFBUSxDQUFDLE9BQU8sRUFBRSxhQUFhLENBQUMsQ0FBQztRQUVqQyxPQUFPLE9BQU8sQ0FBQyxtQkFBbUIsQ0FBQyxHQUFHLENBQ2xDLENBQUMsRUFBQyxJQUFJLEVBQUUsS0FBSyxFQUFFLEdBQUcsRUFBQyxFQUFFLEVBQUUsQ0FBQyxDQUFDLEVBQUMsSUFBSSxFQUFFLEtBQUssRUFBRSxLQUFLLEdBQUcsSUFBSSxDQUFDLElBQUksQ0FBQyxLQUFLLEVBQUUsR0FBRyxFQUFDLENBQUMsQ0FBQyxDQUFDO0lBQzdFLENBQUM7SUFmRCwwREFlQyIsInNvdXJjZXNDb250ZW50IjpbIi8qKlxuICogQGxpY2Vuc2VcbiAqIENvcHlyaWdodCBHb29nbGUgTExDIEFsbCBSaWdodHMgUmVzZXJ2ZWQuXG4gKlxuICogVXNlIG9mIHRoaXMgc291cmNlIGNvZGUgaXMgZ292ZXJuZWQgYnkgYW4gTUlULXN0eWxlIGxpY2Vuc2UgdGhhdCBjYW4gYmVcbiAqIGZvdW5kIGluIHRoZSBMSUNFTlNFIGZpbGUgYXQgaHR0cHM6Ly9hbmd1bGFyLmlvL2xpY2Vuc2VcbiAqL1xuXG5pbXBvcnQge1Byb3BlcnR5V3JpdGV9IGZyb20gJ0Bhbmd1bGFyL2NvbXBpbGVyJztcbmltcG9ydCB7dmlzaXRBbGx9IGZyb20gJ0Bhbmd1bGFyL2NvbXBpbGVyL3NyYy9yZW5kZXIzL3IzX2FzdCc7XG5pbXBvcnQge1Jlc29sdmVkVGVtcGxhdGV9IGZyb20gJy4uLy4uL3V0aWxzL25nX2NvbXBvbmVudF90ZW1wbGF0ZSc7XG5pbXBvcnQge3BhcnNlSHRtbEdyYWNlZnVsbHl9IGZyb20gJy4uLy4uL3V0aWxzL3BhcnNlX2h0bWwnO1xuaW1wb3J0IHtIdG1sVmFyaWFibGVBc3NpZ25tZW50VmlzaXRvcn0gZnJvbSAnLi9hbmd1bGFyL2h0bWxfdmFyaWFibGVfYXNzaWdubWVudF92aXNpdG9yJztcblxuZXhwb3J0IGludGVyZmFjZSBUZW1wbGF0ZVZhcmlhYmxlQXNzaWdubWVudCB7XG4gIG5vZGU6IFByb3BlcnR5V3JpdGU7XG4gIHN0YXJ0OiBudW1iZXI7XG4gIGVuZDogbnVtYmVyO1xufVxuXG4vKipcbiAqIEFuYWx5emVzIGEgZ2l2ZW4gcmVzb2x2ZWQgdGVtcGxhdGUgYnkgbG9va2luZyBmb3IgcHJvcGVydHkgYXNzaWdubWVudHMgdG8gbG9jYWxcbiAqIHRlbXBsYXRlIHZhcmlhYmxlcyB3aXRoaW4gYm91bmQgZXZlbnRzLlxuICovXG5leHBvcnQgZnVuY3Rpb24gYW5hbHl6ZVJlc29sdmVkVGVtcGxhdGUodGVtcGxhdGU6IFJlc29sdmVkVGVtcGxhdGUpOiBUZW1wbGF0ZVZhcmlhYmxlQXNzaWdubWVudFtdfFxuICAgIG51bGwge1xuICBjb25zdCB0ZW1wbGF0ZU5vZGVzID0gcGFyc2VIdG1sR3JhY2VmdWxseSh0ZW1wbGF0ZS5jb250ZW50LCB0ZW1wbGF0ZS5maWxlUGF0aCk7XG5cbiAgaWYgKCF0ZW1wbGF0ZU5vZGVzKSB7XG4gICAgcmV0dXJuIG51bGw7XG4gIH1cblxuICBjb25zdCB2aXNpdG9yID0gbmV3IEh0bWxWYXJpYWJsZUFzc2lnbm1lbnRWaXNpdG9yKCk7XG5cbiAgLy8gQW5hbHl6ZSB0aGUgQW5ndWxhciBSZW5kZXIzIEhUTUwgQVNUIGFuZCBjb2xsZWN0IGFsbCB0ZW1wbGF0ZSB2YXJpYWJsZSBhc3NpZ25tZW50cy5cbiAgdmlzaXRBbGwodmlzaXRvciwgdGVtcGxhdGVOb2Rlcyk7XG5cbiAgcmV0dXJuIHZpc2l0b3IudmFyaWFibGVBc3NpZ25tZW50cy5tYXAoXG4gICAgICAoe25vZGUsIHN0YXJ0LCBlbmR9KSA9PiAoe25vZGUsIHN0YXJ0OiBzdGFydCArIG5vZGUuc3Bhbi5zdGFydCwgZW5kfSkpO1xufVxuIl19