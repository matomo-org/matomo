/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
import { ParseSourceSpan } from '../parse_util';
import * as o from './output_ast';
import { SourceMapGenerator } from './source_map';
export declare const CATCH_ERROR_VAR: o.ReadVarExpr;
export declare const CATCH_STACK_VAR: o.ReadVarExpr;
export interface OutputEmitter {
    emitStatements(genFilePath: string, stmts: o.Statement[], preamble?: string | null): string;
}
export declare class EmitterVisitorContext {
    private _indent;
    static createRoot(): EmitterVisitorContext;
    private _lines;
    private _classes;
    private _preambleLineCount;
    constructor(_indent: number);
    println(from?: {
        sourceSpan: ParseSourceSpan | null;
    } | null, lastPart?: string): void;
    lineIsEmpty(): boolean;
    lineLength(): number;
    print(from: {
        sourceSpan: ParseSourceSpan | null;
    } | null, part: string, newLine?: boolean): void;
    removeEmptyLastLine(): void;
    incIndent(): void;
    decIndent(): void;
    pushClass(clazz: o.ClassStmt): void;
    popClass(): o.ClassStmt;
    get currentClass(): o.ClassStmt | null;
    toSource(): string;
    toSourceMapGenerator(genFilePath: string, startsAtLine?: number): SourceMapGenerator;
    setPreambleLineCount(count: number): number;
    spanOf(line: number, column: number): ParseSourceSpan | null;
}
export declare abstract class AbstractEmitterVisitor implements o.StatementVisitor, o.ExpressionVisitor {
    private _escapeDollarInStrings;
    constructor(_escapeDollarInStrings: boolean);
    protected printLeadingComments(stmt: o.Statement, ctx: EmitterVisitorContext): void;
    visitExpressionStmt(stmt: o.ExpressionStatement, ctx: EmitterVisitorContext): any;
    visitReturnStmt(stmt: o.ReturnStatement, ctx: EmitterVisitorContext): any;
    abstract visitCastExpr(ast: o.CastExpr, context: any): any;
    abstract visitDeclareClassStmt(stmt: o.ClassStmt, ctx: EmitterVisitorContext): any;
    visitIfStmt(stmt: o.IfStmt, ctx: EmitterVisitorContext): any;
    abstract visitTryCatchStmt(stmt: o.TryCatchStmt, ctx: EmitterVisitorContext): any;
    visitThrowStmt(stmt: o.ThrowStmt, ctx: EmitterVisitorContext): any;
    abstract visitDeclareVarStmt(stmt: o.DeclareVarStmt, ctx: EmitterVisitorContext): any;
    visitWriteVarExpr(expr: o.WriteVarExpr, ctx: EmitterVisitorContext): any;
    visitWriteKeyExpr(expr: o.WriteKeyExpr, ctx: EmitterVisitorContext): any;
    visitWritePropExpr(expr: o.WritePropExpr, ctx: EmitterVisitorContext): any;
    visitInvokeMethodExpr(expr: o.InvokeMethodExpr, ctx: EmitterVisitorContext): any;
    abstract getBuiltinMethodName(method: o.BuiltinMethod): string;
    visitInvokeFunctionExpr(expr: o.InvokeFunctionExpr, ctx: EmitterVisitorContext): any;
    visitTaggedTemplateExpr(expr: o.TaggedTemplateExpr, ctx: EmitterVisitorContext): any;
    visitWrappedNodeExpr(ast: o.WrappedNodeExpr<any>, ctx: EmitterVisitorContext): any;
    visitTypeofExpr(expr: o.TypeofExpr, ctx: EmitterVisitorContext): any;
    visitReadVarExpr(ast: o.ReadVarExpr, ctx: EmitterVisitorContext): any;
    visitInstantiateExpr(ast: o.InstantiateExpr, ctx: EmitterVisitorContext): any;
    visitLiteralExpr(ast: o.LiteralExpr, ctx: EmitterVisitorContext): any;
    visitLocalizedString(ast: o.LocalizedString, ctx: EmitterVisitorContext): any;
    abstract visitExternalExpr(ast: o.ExternalExpr, ctx: EmitterVisitorContext): any;
    visitConditionalExpr(ast: o.ConditionalExpr, ctx: EmitterVisitorContext): any;
    visitNotExpr(ast: o.NotExpr, ctx: EmitterVisitorContext): any;
    visitAssertNotNullExpr(ast: o.AssertNotNull, ctx: EmitterVisitorContext): any;
    abstract visitFunctionExpr(ast: o.FunctionExpr, ctx: EmitterVisitorContext): any;
    abstract visitDeclareFunctionStmt(stmt: o.DeclareFunctionStmt, context: any): any;
    visitUnaryOperatorExpr(ast: o.UnaryOperatorExpr, ctx: EmitterVisitorContext): any;
    visitBinaryOperatorExpr(ast: o.BinaryOperatorExpr, ctx: EmitterVisitorContext): any;
    visitReadPropExpr(ast: o.ReadPropExpr, ctx: EmitterVisitorContext): any;
    visitReadKeyExpr(ast: o.ReadKeyExpr, ctx: EmitterVisitorContext): any;
    visitLiteralArrayExpr(ast: o.LiteralArrayExpr, ctx: EmitterVisitorContext): any;
    visitLiteralMapExpr(ast: o.LiteralMapExpr, ctx: EmitterVisitorContext): any;
    visitCommaExpr(ast: o.CommaExpr, ctx: EmitterVisitorContext): any;
    visitAllExpressions(expressions: o.Expression[], ctx: EmitterVisitorContext, separator: string): void;
    visitAllObjects<T>(handler: (t: T) => void, expressions: T[], ctx: EmitterVisitorContext, separator: string): void;
    visitAllStatements(statements: o.Statement[], ctx: EmitterVisitorContext): void;
}
export declare function escapeIdentifier(input: string, escapeDollar: boolean, alwaysQuote?: boolean): any;
