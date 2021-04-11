/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
/// <amd-module name="@angular/core/schematics/migrations/template-var-assignment/angular/html_variable_assignment_visitor" />
import { PropertyWrite } from '@angular/compiler';
import { BoundEvent, Element, NullVisitor, Template } from '@angular/compiler/src/render3/r3_ast';
export interface TemplateVariableAssignment {
    start: number;
    end: number;
    node: PropertyWrite;
}
/**
 * HTML AST visitor that traverses the Render3 HTML AST in order to find all
 * expressions that write to local template variables within bound events.
 */
export declare class HtmlVariableAssignmentVisitor extends NullVisitor {
    variableAssignments: TemplateVariableAssignment[];
    private currentVariables;
    private expressionAstVisitor;
    visitElement(element: Element): void;
    visitTemplate(template: Template): void;
    visitBoundEvent(node: BoundEvent): void;
}
