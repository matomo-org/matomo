/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
import { AST } from '../../expression_parser/ast';
import { SelectorMatcher } from '../../selector';
import { BoundAttribute, BoundEvent, Element, Reference, Template, TextAttribute, Variable } from '../r3_ast';
import { BoundTarget, DirectiveMeta, Target, TargetBinder } from './t2_api';
/**
 * Processes `Target`s with a given set of directives and performs a binding operation, which
 * returns an object similar to TypeScript's `ts.TypeChecker` that contains knowledge about the
 * target.
 */
export declare class R3TargetBinder<DirectiveT extends DirectiveMeta> implements TargetBinder<DirectiveT> {
    private directiveMatcher;
    constructor(directiveMatcher: SelectorMatcher<DirectiveT>);
    /**
     * Perform a binding operation on the given `Target` and return a `BoundTarget` which contains
     * metadata about the types referenced in the template.
     */
    bind(target: Target): BoundTarget<DirectiveT>;
}
/**
 * Metadata container for a `Target` that allows queries for specific bits of metadata.
 *
 * See `BoundTarget` for documentation on the individual methods.
 */
export declare class R3BoundTarget<DirectiveT extends DirectiveMeta> implements BoundTarget<DirectiveT> {
    readonly target: Target;
    private directives;
    private bindings;
    private references;
    private exprTargets;
    private symbols;
    private nestingLevel;
    private templateEntities;
    private usedPipes;
    constructor(target: Target, directives: Map<Element | Template, DirectiveT[]>, bindings: Map<BoundAttribute | BoundEvent | TextAttribute, DirectiveT | Element | Template>, references: Map<BoundAttribute | BoundEvent | Reference | TextAttribute, {
        directive: DirectiveT;
        node: Element | Template;
    } | Element | Template>, exprTargets: Map<AST, Reference | Variable>, symbols: Map<Reference | Variable, Template>, nestingLevel: Map<Template, number>, templateEntities: Map<Template | null, ReadonlySet<Reference | Variable>>, usedPipes: Set<string>);
    getEntitiesInTemplateScope(template: Template | null): ReadonlySet<Reference | Variable>;
    getDirectivesOfNode(node: Element | Template): DirectiveT[] | null;
    getReferenceTarget(ref: Reference): {
        directive: DirectiveT;
        node: Element | Template;
    } | Element | Template | null;
    getConsumerOfBinding(binding: BoundAttribute | BoundEvent | TextAttribute): DirectiveT | Element | Template | null;
    getExpressionTarget(expr: AST): Reference | Variable | null;
    getTemplateOfSymbol(symbol: Reference | Variable): Template | null;
    getNestingLevel(template: Template): number;
    getUsedDirectives(): DirectiveT[];
    getUsedPipes(): string[];
}
