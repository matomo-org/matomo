/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
import { AST } from '../../../expression_parser/ast';
import * as i18n from '../../../i18n/i18n_ast';
import * as o from '../../../output/output_ast';
/**
 * I18nContext is a helper class which keeps track of all i18n-related aspects
 * (accumulates placeholders, bindings, etc) between i18nStart and i18nEnd instructions.
 *
 * When we enter a nested template, the top-level context is being passed down
 * to the nested component, which uses this context to generate a child instance
 * of I18nContext class (to handle nested template) and at the end, reconciles it back
 * with the parent context.
 *
 * @param index Instruction index of i18nStart, which initiates this context
 * @param ref Reference to a translation const that represents the content if thus context
 * @param level Nestng level defined for child contexts
 * @param templateIndex Instruction index of a template which this context belongs to
 * @param meta Meta information (id, meaning, description, etc) associated with this context
 */
export declare class I18nContext {
    readonly index: number;
    readonly ref: o.ReadVarExpr;
    readonly level: number;
    readonly templateIndex: number | null;
    readonly meta: i18n.I18nMeta;
    private registry?;
    readonly id: number;
    bindings: Set<AST>;
    placeholders: Map<string, any[]>;
    isEmitted: boolean;
    private _registry;
    private _unresolvedCtxCount;
    constructor(index: number, ref: o.ReadVarExpr, level: number, templateIndex: number | null, meta: i18n.I18nMeta, registry?: any);
    private appendTag;
    get icus(): any;
    get isRoot(): boolean;
    get isResolved(): boolean;
    getSerializedPlaceholders(): Map<string, any[]>;
    appendBinding(binding: AST): void;
    appendIcu(name: string, ref: o.Expression): void;
    appendBoundText(node: i18n.I18nMeta): void;
    appendTemplate(node: i18n.I18nMeta, index: number): void;
    appendElement(node: i18n.I18nMeta, index: number, closed?: boolean): void;
    appendProjection(node: i18n.I18nMeta, index: number): void;
    /**
     * Generates an instance of a child context based on the root one,
     * when we enter a nested template within I18n section.
     *
     * @param index Instruction index of corresponding i18nStart, which initiates this context
     * @param templateIndex Instruction index of a template which this context belongs to
     * @param meta Meta information (id, meaning, description, etc) associated with this context
     *
     * @returns I18nContext instance
     */
    forkChildContext(index: number, templateIndex: number, meta: i18n.I18nMeta): I18nContext;
    /**
     * Reconciles child context into parent one once the end of the i18n block is reached (i18nEnd).
     *
     * @param context Child I18nContext instance to be reconciled with parent context.
     */
    reconcileChildContext(context: I18nContext): void;
}
