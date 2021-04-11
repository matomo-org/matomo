/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
import { AstPath } from '../ast_path';
import { CompileDirectiveSummary, CompileProviderMetadata, CompileTokenMetadata } from '../compile_metadata';
import { SecurityContext } from '../core';
import { ASTWithSource, BoundElementProperty, ParsedEvent, ParsedVariable } from '../expression_parser/ast';
import { LifecycleHooks } from '../lifecycle_reflector';
import { ParseSourceSpan } from '../parse_util';
/**
 * An Abstract Syntax Tree node representing part of a parsed Angular template.
 */
export interface TemplateAst {
    /**
     * The source span from which this node was parsed.
     */
    sourceSpan: ParseSourceSpan;
    /**
     * Visit this node and possibly transform it.
     */
    visit(visitor: TemplateAstVisitor, context: any): any;
}
/**
 * A segment of text within the template.
 */
export declare class TextAst implements TemplateAst {
    value: string;
    ngContentIndex: number;
    sourceSpan: ParseSourceSpan;
    constructor(value: string, ngContentIndex: number, sourceSpan: ParseSourceSpan);
    visit(visitor: TemplateAstVisitor, context: any): any;
}
/**
 * A bound expression within the text of a template.
 */
export declare class BoundTextAst implements TemplateAst {
    value: ASTWithSource;
    ngContentIndex: number;
    sourceSpan: ParseSourceSpan;
    constructor(value: ASTWithSource, ngContentIndex: number, sourceSpan: ParseSourceSpan);
    visit(visitor: TemplateAstVisitor, context: any): any;
}
/**
 * A plain attribute on an element.
 */
export declare class AttrAst implements TemplateAst {
    name: string;
    value: string;
    sourceSpan: ParseSourceSpan;
    constructor(name: string, value: string, sourceSpan: ParseSourceSpan);
    visit(visitor: TemplateAstVisitor, context: any): any;
}
export declare const enum PropertyBindingType {
    Property = 0,
    Attribute = 1,
    Class = 2,
    Style = 3,
    Animation = 4
}
/**
 * A binding for an element property (e.g. `[property]="expression"`) or an animation trigger (e.g.
 * `[@trigger]="stateExp"`)
 */
export declare class BoundElementPropertyAst implements TemplateAst {
    name: string;
    type: PropertyBindingType;
    securityContext: SecurityContext;
    value: ASTWithSource;
    unit: string | null;
    sourceSpan: ParseSourceSpan;
    readonly isAnimation: boolean;
    constructor(name: string, type: PropertyBindingType, securityContext: SecurityContext, value: ASTWithSource, unit: string | null, sourceSpan: ParseSourceSpan);
    static fromBoundProperty(prop: BoundElementProperty): BoundElementPropertyAst;
    visit(visitor: TemplateAstVisitor, context: any): any;
}
/**
 * A binding for an element event (e.g. `(event)="handler()"`) or an animation trigger event (e.g.
 * `(@trigger.phase)="callback($event)"`).
 */
export declare class BoundEventAst implements TemplateAst {
    name: string;
    target: string | null;
    phase: string | null;
    handler: ASTWithSource;
    sourceSpan: ParseSourceSpan;
    handlerSpan: ParseSourceSpan;
    readonly fullName: string;
    readonly isAnimation: boolean;
    constructor(name: string, target: string | null, phase: string | null, handler: ASTWithSource, sourceSpan: ParseSourceSpan, handlerSpan: ParseSourceSpan);
    static calcFullName(name: string, target: string | null, phase: string | null): string;
    static fromParsedEvent(event: ParsedEvent): BoundEventAst;
    visit(visitor: TemplateAstVisitor, context: any): any;
}
/**
 * A reference declaration on an element (e.g. `let someName="expression"`).
 */
export declare class ReferenceAst implements TemplateAst {
    name: string;
    value: CompileTokenMetadata;
    originalValue: string;
    sourceSpan: ParseSourceSpan;
    constructor(name: string, value: CompileTokenMetadata, originalValue: string, sourceSpan: ParseSourceSpan);
    visit(visitor: TemplateAstVisitor, context: any): any;
}
/**
 * A variable declaration on a <ng-template> (e.g. `var-someName="someLocalName"`).
 */
export declare class VariableAst implements TemplateAst {
    readonly name: string;
    readonly value: string;
    readonly sourceSpan: ParseSourceSpan;
    readonly valueSpan?: ParseSourceSpan | undefined;
    constructor(name: string, value: string, sourceSpan: ParseSourceSpan, valueSpan?: ParseSourceSpan | undefined);
    static fromParsedVariable(v: ParsedVariable): VariableAst;
    visit(visitor: TemplateAstVisitor, context: any): any;
}
/**
 * An element declaration in a template.
 */
export declare class ElementAst implements TemplateAst {
    name: string;
    attrs: AttrAst[];
    inputs: BoundElementPropertyAst[];
    outputs: BoundEventAst[];
    references: ReferenceAst[];
    directives: DirectiveAst[];
    providers: ProviderAst[];
    hasViewContainer: boolean;
    queryMatches: QueryMatch[];
    children: TemplateAst[];
    ngContentIndex: number | null;
    sourceSpan: ParseSourceSpan;
    endSourceSpan: ParseSourceSpan | null;
    constructor(name: string, attrs: AttrAst[], inputs: BoundElementPropertyAst[], outputs: BoundEventAst[], references: ReferenceAst[], directives: DirectiveAst[], providers: ProviderAst[], hasViewContainer: boolean, queryMatches: QueryMatch[], children: TemplateAst[], ngContentIndex: number | null, sourceSpan: ParseSourceSpan, endSourceSpan: ParseSourceSpan | null);
    visit(visitor: TemplateAstVisitor, context: any): any;
}
/**
 * A `<ng-template>` element included in an Angular template.
 */
export declare class EmbeddedTemplateAst implements TemplateAst {
    attrs: AttrAst[];
    outputs: BoundEventAst[];
    references: ReferenceAst[];
    variables: VariableAst[];
    directives: DirectiveAst[];
    providers: ProviderAst[];
    hasViewContainer: boolean;
    queryMatches: QueryMatch[];
    children: TemplateAst[];
    ngContentIndex: number;
    sourceSpan: ParseSourceSpan;
    constructor(attrs: AttrAst[], outputs: BoundEventAst[], references: ReferenceAst[], variables: VariableAst[], directives: DirectiveAst[], providers: ProviderAst[], hasViewContainer: boolean, queryMatches: QueryMatch[], children: TemplateAst[], ngContentIndex: number, sourceSpan: ParseSourceSpan);
    visit(visitor: TemplateAstVisitor, context: any): any;
}
/**
 * A directive property with a bound value (e.g. `*ngIf="condition").
 */
export declare class BoundDirectivePropertyAst implements TemplateAst {
    directiveName: string;
    templateName: string;
    value: ASTWithSource;
    sourceSpan: ParseSourceSpan;
    constructor(directiveName: string, templateName: string, value: ASTWithSource, sourceSpan: ParseSourceSpan);
    visit(visitor: TemplateAstVisitor, context: any): any;
}
/**
 * A directive declared on an element.
 */
export declare class DirectiveAst implements TemplateAst {
    directive: CompileDirectiveSummary;
    inputs: BoundDirectivePropertyAst[];
    hostProperties: BoundElementPropertyAst[];
    hostEvents: BoundEventAst[];
    contentQueryStartId: number;
    sourceSpan: ParseSourceSpan;
    constructor(directive: CompileDirectiveSummary, inputs: BoundDirectivePropertyAst[], hostProperties: BoundElementPropertyAst[], hostEvents: BoundEventAst[], contentQueryStartId: number, sourceSpan: ParseSourceSpan);
    visit(visitor: TemplateAstVisitor, context: any): any;
}
/**
 * A provider declared on an element
 */
export declare class ProviderAst implements TemplateAst {
    token: CompileTokenMetadata;
    multiProvider: boolean;
    eager: boolean;
    providers: CompileProviderMetadata[];
    providerType: ProviderAstType;
    lifecycleHooks: LifecycleHooks[];
    sourceSpan: ParseSourceSpan;
    readonly isModule: boolean;
    constructor(token: CompileTokenMetadata, multiProvider: boolean, eager: boolean, providers: CompileProviderMetadata[], providerType: ProviderAstType, lifecycleHooks: LifecycleHooks[], sourceSpan: ParseSourceSpan, isModule: boolean);
    visit(visitor: TemplateAstVisitor, context: any): any;
}
export declare enum ProviderAstType {
    PublicService = 0,
    PrivateService = 1,
    Component = 2,
    Directive = 3,
    Builtin = 4
}
/**
 * Position where content is to be projected (instance of `<ng-content>` in a template).
 */
export declare class NgContentAst implements TemplateAst {
    index: number;
    ngContentIndex: number;
    sourceSpan: ParseSourceSpan;
    constructor(index: number, ngContentIndex: number, sourceSpan: ParseSourceSpan);
    visit(visitor: TemplateAstVisitor, context: any): any;
}
export interface QueryMatch {
    queryId: number;
    value: CompileTokenMetadata;
}
/**
 * A visitor for {@link TemplateAst} trees that will process each node.
 */
export interface TemplateAstVisitor {
    visit?(ast: TemplateAst, context: any): any;
    visitNgContent(ast: NgContentAst, context: any): any;
    visitEmbeddedTemplate(ast: EmbeddedTemplateAst, context: any): any;
    visitElement(ast: ElementAst, context: any): any;
    visitReference(ast: ReferenceAst, context: any): any;
    visitVariable(ast: VariableAst, context: any): any;
    visitEvent(ast: BoundEventAst, context: any): any;
    visitElementProperty(ast: BoundElementPropertyAst, context: any): any;
    visitAttr(ast: AttrAst, context: any): any;
    visitBoundText(ast: BoundTextAst, context: any): any;
    visitText(ast: TextAst, context: any): any;
    visitDirective(ast: DirectiveAst, context: any): any;
    visitDirectiveProperty(ast: BoundDirectivePropertyAst, context: any): any;
}
/**
 * A visitor that accepts each node but doesn't do anything. It is intended to be used
 * as the base class for a visitor that is only interested in a subset of the node types.
 */
export declare class NullTemplateVisitor implements TemplateAstVisitor {
    visitNgContent(ast: NgContentAst, context: any): void;
    visitEmbeddedTemplate(ast: EmbeddedTemplateAst, context: any): void;
    visitElement(ast: ElementAst, context: any): void;
    visitReference(ast: ReferenceAst, context: any): void;
    visitVariable(ast: VariableAst, context: any): void;
    visitEvent(ast: BoundEventAst, context: any): void;
    visitElementProperty(ast: BoundElementPropertyAst, context: any): void;
    visitAttr(ast: AttrAst, context: any): void;
    visitBoundText(ast: BoundTextAst, context: any): void;
    visitText(ast: TextAst, context: any): void;
    visitDirective(ast: DirectiveAst, context: any): void;
    visitDirectiveProperty(ast: BoundDirectivePropertyAst, context: any): void;
}
/**
 * Base class that can be used to build a visitor that visits each node
 * in an template ast recursively.
 */
export declare class RecursiveTemplateAstVisitor extends NullTemplateVisitor implements TemplateAstVisitor {
    constructor();
    visitEmbeddedTemplate(ast: EmbeddedTemplateAst, context: any): any;
    visitElement(ast: ElementAst, context: any): any;
    visitDirective(ast: DirectiveAst, context: any): any;
    protected visitChildren(context: any, cb: (visit: (<V extends TemplateAst>(children: V[] | undefined) => void)) => void): any[];
}
/**
 * Visit every node in a list of {@link TemplateAst}s with the given {@link TemplateAstVisitor}.
 */
export declare function templateVisitAll(visitor: TemplateAstVisitor, asts: TemplateAst[], context?: any): any[];
export declare type TemplateAstPath = AstPath<TemplateAst>;
