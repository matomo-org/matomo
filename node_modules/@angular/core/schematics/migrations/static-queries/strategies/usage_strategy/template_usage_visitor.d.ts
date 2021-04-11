/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
/// <amd-module name="@angular/core/schematics/migrations/static-queries/strategies/usage_strategy/template_usage_visitor" />
import { BoundAttribute, BoundEvent, BoundText, Element, Node, NullVisitor, Template } from '@angular/compiler/src/render3/r3_ast';
/**
 * AST visitor that traverses the Render3 HTML AST in order to check if the given
 * query property is accessed statically in the template.
 */
export declare class TemplateUsageVisitor extends NullVisitor {
    queryPropertyName: string;
    private hasQueryTemplateReference;
    private expressionAstVisitor;
    constructor(queryPropertyName: string);
    /** Checks whether the given query is statically accessed within the specified HTML nodes. */
    isQueryUsedStatically(htmlNodes: Node[]): boolean;
    visitElement(element: Element): void;
    visitTemplate(template: Template): void;
    visitBoundAttribute(attribute: BoundAttribute): void;
    visitBoundText(text: BoundText): void;
    visitBoundEvent(node: BoundEvent): void;
}
