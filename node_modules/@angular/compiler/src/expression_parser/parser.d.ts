/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
import { InterpolationConfig } from '../ml_parser/interpolation_config';
import { AbsoluteSourceSpan, AST, AstVisitor, ASTWithSource, Binary, BindingPipe, Chain, Conditional, FunctionCall, ImplicitReceiver, Interpolation, KeyedRead, KeyedWrite, LiteralArray, LiteralMap, LiteralPrimitive, MethodCall, NonNullAssert, ParserError, ParseSpan, PrefixNot, PropertyRead, PropertyWrite, Quote, RecursiveAstVisitor, SafeMethodCall, SafePropertyRead, TemplateBinding, TemplateBindingIdentifier, ThisReceiver, Unary } from './ast';
import { Lexer, Token } from './lexer';
export interface InterpolationPiece {
    text: string;
    start: number;
    end: number;
}
export declare class SplitInterpolation {
    strings: InterpolationPiece[];
    expressions: InterpolationPiece[];
    offsets: number[];
    constructor(strings: InterpolationPiece[], expressions: InterpolationPiece[], offsets: number[]);
}
export declare class TemplateBindingParseResult {
    templateBindings: TemplateBinding[];
    warnings: string[];
    errors: ParserError[];
    constructor(templateBindings: TemplateBinding[], warnings: string[], errors: ParserError[]);
}
export declare class Parser {
    private _lexer;
    private errors;
    constructor(_lexer: Lexer);
    simpleExpressionChecker: typeof SimpleExpressionChecker;
    parseAction(input: string, location: string, absoluteOffset: number, interpolationConfig?: InterpolationConfig): ASTWithSource;
    parseBinding(input: string, location: string, absoluteOffset: number, interpolationConfig?: InterpolationConfig): ASTWithSource;
    private checkSimpleExpression;
    parseSimpleBinding(input: string, location: string, absoluteOffset: number, interpolationConfig?: InterpolationConfig): ASTWithSource;
    private _reportError;
    private _parseBindingAst;
    private _parseQuote;
    /**
     * Parse microsyntax template expression and return a list of bindings or
     * parsing errors in case the given expression is invalid.
     *
     * For example,
     * ```
     *   <div *ngFor="let item of items">
     *         ^      ^ absoluteValueOffset for `templateValue`
     *         absoluteKeyOffset for `templateKey`
     * ```
     * contains three bindings:
     * 1. ngFor -> null
     * 2. item -> NgForOfContext.$implicit
     * 3. ngForOf -> items
     *
     * This is apparent from the de-sugared template:
     * ```
     *   <ng-template ngFor let-item [ngForOf]="items">
     * ```
     *
     * @param templateKey name of directive, without the * prefix. For example: ngIf, ngFor
     * @param templateValue RHS of the microsyntax attribute
     * @param templateUrl template filename if it's external, component filename if it's inline
     * @param absoluteKeyOffset start of the `templateKey`
     * @param absoluteValueOffset start of the `templateValue`
     */
    parseTemplateBindings(templateKey: string, templateValue: string, templateUrl: string, absoluteKeyOffset: number, absoluteValueOffset: number): TemplateBindingParseResult;
    parseInterpolation(input: string, location: string, absoluteOffset: number, interpolationConfig?: InterpolationConfig): ASTWithSource | null;
    /**
     * Similar to `parseInterpolation`, but treats the provided string as a single expression
     * element that would normally appear within the interpolation prefix and suffix (`{{` and `}}`).
     * This is used for parsing the switch expression in ICUs.
     */
    parseInterpolationExpression(expression: string, location: string, absoluteOffset: number): ASTWithSource;
    private createInterpolationAst;
    /**
     * Splits a string of text into "raw" text segments and expressions present in interpolations in
     * the string.
     * Returns `null` if there are no interpolations, otherwise a
     * `SplitInterpolation` with splits that look like
     *   <raw text> <expression> <raw text> ... <raw text> <expression> <raw text>
     */
    splitInterpolation(input: string, location: string, interpolationConfig?: InterpolationConfig): SplitInterpolation;
    wrapLiteralPrimitive(input: string | null, location: string, absoluteOffset: number): ASTWithSource;
    private _stripComments;
    private _commentStart;
    private _checkNoInterpolation;
    /**
     * Finds the index of the end of an interpolation expression
     * while ignoring comments and quoted content.
     */
    private _getInterpolationEndIndex;
    /**
     * Generator used to iterate over the character indexes of a string that are outside of quotes.
     * @param input String to loop through.
     * @param start Index within the string at which to start.
     */
    private _forEachUnquotedChar;
}
export declare class IvyParser extends Parser {
    simpleExpressionChecker: typeof IvySimpleExpressionChecker;
}
export declare class _ParseAST {
    input: string;
    location: string;
    absoluteOffset: number;
    tokens: Token[];
    inputLength: number;
    parseAction: boolean;
    private errors;
    private offset;
    private rparensExpected;
    private rbracketsExpected;
    private rbracesExpected;
    private context;
    private sourceSpanCache;
    index: number;
    constructor(input: string, location: string, absoluteOffset: number, tokens: Token[], inputLength: number, parseAction: boolean, errors: ParserError[], offset: number);
    peek(offset: number): Token;
    get next(): Token;
    /** Whether all the parser input has been processed. */
    get atEOF(): boolean;
    /**
     * Index of the next token to be processed, or the end of the last token if all have been
     * processed.
     */
    get inputIndex(): number;
    /**
     * End index of the last processed token, or the start of the first token if none have been
     * processed.
     */
    get currentEndIndex(): number;
    /**
     * Returns the absolute offset of the start of the current token.
     */
    get currentAbsoluteOffset(): number;
    /**
     * Retrieve a `ParseSpan` from `start` to the current position (or to `artificialEndIndex` if
     * provided).
     *
     * @param start Position from which the `ParseSpan` will start.
     * @param artificialEndIndex Optional ending index to be used if provided (and if greater than the
     *     natural ending index)
     */
    span(start: number, artificialEndIndex?: number): ParseSpan;
    sourceSpan(start: number, artificialEndIndex?: number): AbsoluteSourceSpan;
    advance(): void;
    /**
     * Executes a callback in the provided context.
     */
    private withContext;
    consumeOptionalCharacter(code: number): boolean;
    peekKeywordLet(): boolean;
    peekKeywordAs(): boolean;
    /**
     * Consumes an expected character, otherwise emits an error about the missing expected character
     * and skips over the token stream until reaching a recoverable point.
     *
     * See `this.error` and `this.skip` for more details.
     */
    expectCharacter(code: number): void;
    consumeOptionalOperator(op: string): boolean;
    expectOperator(operator: string): void;
    prettyPrintToken(tok: Token): string;
    expectIdentifierOrKeyword(): string | null;
    expectIdentifierOrKeywordOrString(): string;
    parseChain(): AST;
    parsePipe(): AST;
    parseExpression(): AST;
    parseConditional(): AST;
    parseLogicalOr(): AST;
    parseLogicalAnd(): AST;
    parseEquality(): AST;
    parseRelational(): AST;
    parseAdditive(): AST;
    parseMultiplicative(): AST;
    parsePrefix(): AST;
    parseCallChain(): AST;
    parsePrimary(): AST;
    parseExpressionList(terminator: number): AST[];
    parseLiteralMap(): LiteralMap;
    parseAccessMemberOrMethodCall(receiver: AST, start: number, isSafe?: boolean): AST;
    parseCallArguments(): BindingPipe[];
    /**
     * Parses an identifier, a keyword, a string with an optional `-` in between,
     * and returns the string along with its absolute source span.
     */
    expectTemplateBindingKey(): TemplateBindingIdentifier;
    /**
     * Parse microsyntax template expression and return a list of bindings or
     * parsing errors in case the given expression is invalid.
     *
     * For example,
     * ```
     *   <div *ngFor="let item of items; index as i; trackBy: func">
     * ```
     * contains five bindings:
     * 1. ngFor -> null
     * 2. item -> NgForOfContext.$implicit
     * 3. ngForOf -> items
     * 4. i -> NgForOfContext.index
     * 5. ngForTrackBy -> func
     *
     * For a full description of the microsyntax grammar, see
     * https://gist.github.com/mhevery/d3530294cff2e4a1b3fe15ff75d08855
     *
     * @param templateKey name of the microsyntax directive, like ngIf, ngFor,
     * without the *, along with its absolute span.
     */
    parseTemplateBindings(templateKey: TemplateBindingIdentifier): TemplateBindingParseResult;
    /**
     * Parse a directive keyword, followed by a mandatory expression.
     * For example, "of items", "trackBy: func".
     * The bindings are: ngForOf -> items, ngForTrackBy -> func
     * There could be an optional "as" binding that follows the expression.
     * For example,
     * ```
     *   *ngFor="let item of items | slice:0:1 as collection".
     *                    ^^ ^^^^^^^^^^^^^^^^^ ^^^^^^^^^^^^^
     *               keyword    bound target   optional 'as' binding
     * ```
     *
     * @param key binding key, for example, ngFor, ngIf, ngForOf, along with its
     * absolute span.
     */
    private parseDirectiveKeywordBindings;
    /**
     * Return the expression AST for the bound target of a directive keyword
     * binding. For example,
     * ```
     *   *ngIf="condition | pipe"
     *          ^^^^^^^^^^^^^^^^ bound target for "ngIf"
     *   *ngFor="let item of items"
     *                       ^^^^^ bound target for "ngForOf"
     * ```
     */
    private getDirectiveBoundTarget;
    /**
     * Return the binding for a variable declared using `as`. Note that the order
     * of the key-value pair in this declaration is reversed. For example,
     * ```
     *   *ngFor="let item of items; index as i"
     *                              ^^^^^    ^
     *                              value    key
     * ```
     *
     * @param value name of the value in the declaration, "ngIf" in the example
     * above, along with its absolute span.
     */
    private parseAsBinding;
    /**
     * Return the binding for a variable declared using `let`. For example,
     * ```
     *   *ngFor="let item of items; let i=index;"
     *           ^^^^^^^^           ^^^^^^^^^^^
     * ```
     * In the first binding, `item` is bound to `NgForOfContext.$implicit`.
     * In the second binding, `i` is bound to `NgForOfContext.index`.
     */
    private parseLetBinding;
    /**
     * Consume the optional statement terminator: semicolon or comma.
     */
    private consumeStatementTerminator;
    /**
     * Records an error and skips over the token stream until reaching a recoverable point. See
     * `this.skip` for more details on token skipping.
     */
    error(message: string, index?: number | null): void;
    private locationText;
    /**
     * Error recovery should skip tokens until it encounters a recovery point.
     *
     * The following are treated as unconditional recovery points:
     *   - end of input
     *   - ';' (parseChain() is always the root production, and it expects a ';')
     *   - '|' (since pipes may be chained and each pipe expression may be treated independently)
     *
     * The following are conditional recovery points:
     *   - ')', '}', ']' if one of calling productions is expecting one of these symbols
     *     - This allows skip() to recover from errors such as '(a.) + 1' allowing more of the AST to
     *       be retained (it doesn't skip any tokens as the ')' is retained because of the '(' begins
     *       an '(' <expr> ')' production).
     *       The recovery points of grouping symbols must be conditional as they must be skipped if
     *       none of the calling productions are not expecting the closing token else we will never
     *       make progress in the case of an extraneous group closing symbol (such as a stray ')').
     *       That is, we skip a closing symbol if we are not in a grouping production.
     *   - '=' in a `Writable` context
     *     - In this context, we are able to recover after seeing the `=` operator, which
     *       signals the presence of an independent rvalue expression following the `=` operator.
     *
     * If a production expects one of these token it increments the corresponding nesting count,
     * and then decrements it just prior to checking if the token is in the input.
     */
    private skip;
}
declare class SimpleExpressionChecker implements AstVisitor {
    errors: string[];
    visitImplicitReceiver(ast: ImplicitReceiver, context: any): void;
    visitThisReceiver(ast: ThisReceiver, context: any): void;
    visitInterpolation(ast: Interpolation, context: any): void;
    visitLiteralPrimitive(ast: LiteralPrimitive, context: any): void;
    visitPropertyRead(ast: PropertyRead, context: any): void;
    visitPropertyWrite(ast: PropertyWrite, context: any): void;
    visitSafePropertyRead(ast: SafePropertyRead, context: any): void;
    visitMethodCall(ast: MethodCall, context: any): void;
    visitSafeMethodCall(ast: SafeMethodCall, context: any): void;
    visitFunctionCall(ast: FunctionCall, context: any): void;
    visitLiteralArray(ast: LiteralArray, context: any): void;
    visitLiteralMap(ast: LiteralMap, context: any): void;
    visitUnary(ast: Unary, context: any): void;
    visitBinary(ast: Binary, context: any): void;
    visitPrefixNot(ast: PrefixNot, context: any): void;
    visitNonNullAssert(ast: NonNullAssert, context: any): void;
    visitConditional(ast: Conditional, context: any): void;
    visitPipe(ast: BindingPipe, context: any): void;
    visitKeyedRead(ast: KeyedRead, context: any): void;
    visitKeyedWrite(ast: KeyedWrite, context: any): void;
    visitAll(asts: any[], context: any): any[];
    visitChain(ast: Chain, context: any): void;
    visitQuote(ast: Quote, context: any): void;
}
/**
 * This class implements SimpleExpressionChecker used in View Engine and performs more strict checks
 * to make sure host bindings do not contain pipes. In View Engine, having pipes in host bindings is
 * not supported as well, but in some cases (like `!(value | async)`) the error is not triggered at
 * compile time. In order to preserve View Engine behavior, more strict checks are introduced for
 * Ivy mode only.
 */
declare class IvySimpleExpressionChecker extends RecursiveAstVisitor implements SimpleExpressionChecker {
    errors: string[];
    visitPipe(): void;
}
export {};
