/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
import { AstPath } from '../ast_path';
import { I18nMeta } from '../i18n/i18n_ast';
import { ParseSourceSpan } from '../parse_util';
export interface Node {
    sourceSpan: ParseSourceSpan;
    visit(visitor: Visitor, context: any): any;
}
export declare abstract class NodeWithI18n implements Node {
    sourceSpan: ParseSourceSpan;
    i18n?: import("@angular/compiler/src/i18n/i18n_ast").Message | import("@angular/compiler/src/i18n/i18n_ast").Node | undefined;
    constructor(sourceSpan: ParseSourceSpan, i18n?: import("@angular/compiler/src/i18n/i18n_ast").Message | import("@angular/compiler/src/i18n/i18n_ast").Node | undefined);
    abstract visit(visitor: Visitor, context: any): any;
}
export declare class Text extends NodeWithI18n {
    value: string;
    constructor(value: string, sourceSpan: ParseSourceSpan, i18n?: I18nMeta);
    visit(visitor: Visitor, context: any): any;
}
export declare class Expansion extends NodeWithI18n {
    switchValue: string;
    type: string;
    cases: ExpansionCase[];
    switchValueSourceSpan: ParseSourceSpan;
    constructor(switchValue: string, type: string, cases: ExpansionCase[], sourceSpan: ParseSourceSpan, switchValueSourceSpan: ParseSourceSpan, i18n?: I18nMeta);
    visit(visitor: Visitor, context: any): any;
}
export declare class ExpansionCase implements Node {
    value: string;
    expression: Node[];
    sourceSpan: ParseSourceSpan;
    valueSourceSpan: ParseSourceSpan;
    expSourceSpan: ParseSourceSpan;
    constructor(value: string, expression: Node[], sourceSpan: ParseSourceSpan, valueSourceSpan: ParseSourceSpan, expSourceSpan: ParseSourceSpan);
    visit(visitor: Visitor, context: any): any;
}
export declare class Attribute extends NodeWithI18n {
    name: string;
    value: string;
    readonly keySpan: ParseSourceSpan | undefined;
    valueSpan?: ParseSourceSpan | undefined;
    constructor(name: string, value: string, sourceSpan: ParseSourceSpan, keySpan: ParseSourceSpan | undefined, valueSpan?: ParseSourceSpan | undefined, i18n?: I18nMeta);
    visit(visitor: Visitor, context: any): any;
}
export declare class Element extends NodeWithI18n {
    name: string;
    attrs: Attribute[];
    children: Node[];
    startSourceSpan: ParseSourceSpan;
    endSourceSpan: ParseSourceSpan | null;
    constructor(name: string, attrs: Attribute[], children: Node[], sourceSpan: ParseSourceSpan, startSourceSpan: ParseSourceSpan, endSourceSpan?: ParseSourceSpan | null, i18n?: I18nMeta);
    visit(visitor: Visitor, context: any): any;
}
export declare class Comment implements Node {
    value: string | null;
    sourceSpan: ParseSourceSpan;
    constructor(value: string | null, sourceSpan: ParseSourceSpan);
    visit(visitor: Visitor, context: any): any;
}
export interface Visitor {
    visit?(node: Node, context: any): any;
    visitElement(element: Element, context: any): any;
    visitAttribute(attribute: Attribute, context: any): any;
    visitText(text: Text, context: any): any;
    visitComment(comment: Comment, context: any): any;
    visitExpansion(expansion: Expansion, context: any): any;
    visitExpansionCase(expansionCase: ExpansionCase, context: any): any;
}
export declare function visitAll(visitor: Visitor, nodes: Node[], context?: any): any[];
export declare class RecursiveVisitor implements Visitor {
    constructor();
    visitElement(ast: Element, context: any): any;
    visitAttribute(ast: Attribute, context: any): any;
    visitText(ast: Text, context: any): any;
    visitComment(ast: Comment, context: any): any;
    visitExpansion(ast: Expansion, context: any): any;
    visitExpansionCase(ast: ExpansionCase, context: any): any;
    private visitChildren;
}
export declare type HtmlAstPath = AstPath<Node>;
export declare function findNode(nodes: Node[], position: number): HtmlAstPath;
