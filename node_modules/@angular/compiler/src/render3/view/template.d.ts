/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
import { LocalResolver } from '../../compiler_util/expression_converter';
import { ConstantPool } from '../../constant_pool';
import * as core from '../../core';
import { AST, AstMemoryEfficientTransformer, BindingPipe, LiteralArray, LiteralMap } from '../../expression_parser/ast';
import * as i18n from '../../i18n/i18n_ast';
import { InterpolationConfig } from '../../ml_parser/interpolation_config';
import { LexerRange } from '../../ml_parser/lexer';
import * as o from '../../output/output_ast';
import { ParseError } from '../../parse_util';
import { CssSelector, SelectorMatcher } from '../../selector';
import { BindingParser } from '../../template_parser/binding_parser';
import * as t from '../r3_ast';
import { I18nContext } from './i18n/context';
import { invalid } from './util';
export declare const LEADING_TRIVIA_CHARS: string[];
export declare function renderFlagCheckIfStmt(flags: core.RenderFlags, statements: o.Statement[]): o.IfStmt;
export declare function prepareEventListenerParameters(eventAst: t.BoundEvent, handlerName?: string | null, scope?: BindingScope | null): o.Expression[];
export interface ComponentDefConsts {
    /**
     * When a constant requires some pre-processing (e.g. i18n translation block that includes
     * goog.getMsg and $localize calls), the `prepareStatements` section contains corresponding
     * statements.
     */
    prepareStatements: o.Statement[];
    /**
     * Actual expressions that represent constants.
     */
    constExpressions: o.Expression[];
    /**
     * Cache to avoid generating duplicated i18n translation blocks.
     */
    i18nVarRefsCache: Map<i18n.I18nMeta, o.ReadVarExpr>;
}
export declare class TemplateDefinitionBuilder implements t.Visitor<void>, LocalResolver {
    private constantPool;
    private level;
    private contextName;
    private i18nContext;
    private templateIndex;
    private templateName;
    private directiveMatcher;
    private directives;
    private pipeTypeByName;
    private pipes;
    private _namespace;
    private i18nUseExternalIds;
    private _constants;
    private _dataIndex;
    private _bindingContext;
    private _prefixCode;
    /**
     * List of callbacks to generate creation mode instructions. We store them here as we process
     * the template so bindings in listeners are resolved only once all nodes have been visited.
     * This ensures all local refs and context variables are available for matching.
     */
    private _creationCodeFns;
    /**
     * List of callbacks to generate update mode instructions. We store them here as we process
     * the template so bindings are resolved only once all nodes have been visited. This ensures
     * all local refs and context variables are available for matching.
     */
    private _updateCodeFns;
    /** Index of the currently-selected node. */
    private _currentIndex;
    /** Temporary variable declarations generated from visiting pipes, literals, etc. */
    private _tempVariables;
    /**
     * List of callbacks to build nested templates. Nested templates must not be visited until
     * after the parent template has finished visiting all of its nodes. This ensures that all
     * local ref bindings in nested templates are able to find local ref values if the refs
     * are defined after the template declaration.
     */
    private _nestedTemplateFns;
    /**
     * This scope contains local variables declared in the update mode block of the template.
     * (e.g. refs and context vars in bindings)
     */
    private _bindingScope;
    private _valueConverter;
    private _unsupported;
    private i18n;
    private _pureFunctionSlots;
    private _bindingSlots;
    private fileBasedI18nSuffix;
    private _ngContentReservedSlots;
    private _ngContentSelectorsOffset;
    private _implicitReceiverExpr;
    constructor(constantPool: ConstantPool, parentBindingScope: BindingScope, level: number, contextName: string | null, i18nContext: I18nContext | null, templateIndex: number | null, templateName: string | null, directiveMatcher: SelectorMatcher | null, directives: Set<o.Expression>, pipeTypeByName: Map<string, o.Expression>, pipes: Set<o.Expression>, _namespace: o.ExternalReference, relativeContextFilePath: string, i18nUseExternalIds: boolean, _constants?: ComponentDefConsts);
    buildTemplateFunction(nodes: t.Node[], variables: t.Variable[], ngContentSelectorsOffset?: number, i18n?: i18n.I18nMeta): o.FunctionExpr;
    getLocal(name: string): o.Expression | null;
    notifyImplicitReceiverUse(): void;
    private i18nTranslate;
    private registerContextVariables;
    private i18nAppendBindings;
    private i18nBindProps;
    private i18nGenerateMainBlockVar;
    private i18nGenerateClosureVar;
    private i18nUpdateRef;
    private i18nStart;
    private i18nEnd;
    private i18nAttributesInstruction;
    private getNamespaceInstruction;
    private addNamespaceInstruction;
    /**
     * Adds an update instruction for an interpolated property or attribute, such as
     * `prop="{{value}}"` or `attr.title="{{value}}"`
     */
    private interpolatedUpdateInstruction;
    visitContent(ngContent: t.Content): void;
    visitElement(element: t.Element): void;
    visitTemplate(template: t.Template): void;
    readonly visitReference: typeof invalid;
    readonly visitVariable: typeof invalid;
    readonly visitTextAttribute: typeof invalid;
    readonly visitBoundAttribute: typeof invalid;
    readonly visitBoundEvent: typeof invalid;
    visitBoundText(text: t.BoundText): void;
    visitText(text: t.Text): void;
    visitIcu(icu: t.Icu): null;
    private allocateDataSlot;
    getConstCount(): number;
    getVarCount(): number;
    getConsts(): ComponentDefConsts;
    getNgContentSelectors(): o.Expression | null;
    private bindingContext;
    private templatePropertyBindings;
    private instructionFn;
    private processStylingUpdateInstruction;
    private creationInstruction;
    private creationInstructionChain;
    private updateInstructionWithAdvance;
    private updateInstruction;
    private updateInstructionChain;
    private updateInstructionChainWithAdvance;
    private addAdvanceInstructionIfNecessary;
    private allocatePureFunctionSlots;
    private allocateBindingSlots;
    /**
     * Gets an expression that refers to the implicit receiver. The implicit
     * receiver is always the root level context.
     */
    private getImplicitReceiverExpr;
    private convertPropertyBinding;
    /**
     * Gets a list of argument expressions to pass to an update instruction expression. Also updates
     * the temp variables state with temp variables that were identified as needing to be created
     * while visiting the arguments.
     * @param value The original expression we will be resolving an arguments list from.
     */
    private getUpdateInstructionArguments;
    private matchDirectives;
    /**
     * Prepares all attribute expression values for the `TAttributes` array.
     *
     * The purpose of this function is to properly construct an attributes array that
     * is passed into the `elementStart` (or just `element`) functions. Because there
     * are many different types of attributes, the array needs to be constructed in a
     * special way so that `elementStart` can properly evaluate them.
     *
     * The format looks like this:
     *
     * ```
     * attrs = [prop, value, prop2, value2,
     *   PROJECT_AS, selector,
     *   CLASSES, class1, class2,
     *   STYLES, style1, value1, style2, value2,
     *   BINDINGS, name1, name2, name3,
     *   TEMPLATE, name4, name5, name6,
     *   I18N, name7, name8, ...]
     * ```
     *
     * Note that this function will fully ignore all synthetic (@foo) attribute values
     * because those values are intended to always be generated as property instructions.
     */
    private getAttributeExpressions;
    private addToConsts;
    private addAttrsToConsts;
    private prepareRefsArray;
    private prepareListenerParameter;
}
export declare class ValueConverter extends AstMemoryEfficientTransformer {
    private constantPool;
    private allocateSlot;
    private allocatePureFunctionSlots;
    private definePipe;
    private _pipeBindExprs;
    constructor(constantPool: ConstantPool, allocateSlot: () => number, allocatePureFunctionSlots: (numSlots: number) => number, definePipe: (name: string, localName: string, slot: number, value: o.Expression) => void);
    visitPipe(pipe: BindingPipe, context: any): AST;
    updatePipeSlotOffsets(bindingSlots: number): void;
    visitLiteralArray(array: LiteralArray, context: any): AST;
    visitLiteralMap(map: LiteralMap, context: any): AST;
}
/**
 * Function which is executed whenever a variable is referenced for the first time in a given
 * scope.
 *
 * It is expected that the function creates the `const localName = expression`; statement.
 */
export declare type DeclareLocalVarCallback = (scope: BindingScope, relativeLevel: number) => o.Statement[];
/**
 * This is used when one refers to variable such as: 'let abc = nextContext(2).$implicit`.
 * - key to the map is the string literal `"abc"`.
 * - value `retrievalLevel` is the level from which this value can be retrieved, which is 2 levels
 * up in example.
 * - value `lhs` is the left hand side which is an AST representing `abc`.
 * - value `declareLocalCallback` is a callback that is invoked when declaring the local.
 * - value `declare` is true if this value needs to be declared.
 * - value `localRef` is true if we are storing a local reference
 * - value `priority` dictates the sorting priority of this var declaration compared
 * to other var declarations on the same retrieval level. For example, if there is a
 * context variable and a local ref accessing the same parent view, the context var
 * declaration should always come before the local ref declaration.
 */
declare type BindingData = {
    retrievalLevel: number;
    lhs: o.Expression;
    declareLocalCallback?: DeclareLocalVarCallback;
    declare: boolean;
    priority: number;
    localRef: boolean;
};
export declare class BindingScope implements LocalResolver {
    bindingLevel: number;
    private parent;
    globals?: Set<string> | undefined;
    /** Keeps a map from local variables to their BindingData. */
    private map;
    private referenceNameIndex;
    private restoreViewVariable;
    static createRootScope(): BindingScope;
    private constructor();
    get(name: string): o.Expression | null;
    /**
     * Create a local variable for later reference.
     *
     * @param retrievalLevel The level from which this value can be retrieved
     * @param name Name of the variable.
     * @param lhs AST representing the left hand side of the `let lhs = rhs;`.
     * @param priority The sorting priority of this var
     * @param declareLocalCallback The callback to invoke when declaring this local var
     * @param localRef Whether or not this is a local ref
     */
    set(retrievalLevel: number, name: string, lhs: o.Expression, priority?: number, declareLocalCallback?: DeclareLocalVarCallback, localRef?: true): BindingScope;
    getLocal(name: string): (o.Expression | null);
    notifyImplicitReceiverUse(): void;
    nestedScope(level: number, globals?: Set<string>): BindingScope;
    /**
     * Gets or creates a shared context variable and returns its expression. Note that
     * this does not mean that the shared variable will be declared. Variables in the
     * binding scope will be only declared if they are used.
     */
    getOrCreateSharedContextVar(retrievalLevel: number): o.ReadVarExpr;
    getSharedContextName(retrievalLevel: number): o.ReadVarExpr | null;
    maybeGenerateSharedContextVar(value: BindingData): void;
    generateSharedContextVar(retrievalLevel: number): void;
    getComponentProperty(name: string): o.Expression;
    maybeRestoreView(retrievalLevel: number, localRefLookup: boolean): void;
    restoreViewStatement(): o.Statement[];
    viewSnapshotStatements(): o.Statement[];
    isListenerScope(): boolean | null;
    variableDeclarations(): o.Statement[];
    freshReferenceName(): string;
}
/**
 * Creates a `CssSelector` given a tag name and a map of attributes
 */
export declare function createCssSelector(elementName: string, attributes: {
    [name: string]: string;
}): CssSelector;
/**
 * Options that can be used to modify how a template is parsed by `parseTemplate()`.
 */
export interface ParseTemplateOptions {
    /**
     * Include whitespace nodes in the parsed output.
     */
    preserveWhitespaces?: boolean;
    /**
     * Preserve original line endings instead of normalizing '\r\n' endings to '\n'.
     */
    preserveLineEndings?: boolean;
    /**
     * How to parse interpolation markers.
     */
    interpolationConfig?: InterpolationConfig;
    /**
     * The start and end point of the text to parse within the `source` string.
     * The entire `source` string is parsed if this is not provided.
     * */
    range?: LexerRange;
    /**
     * If this text is stored in a JavaScript string, then we have to deal with escape sequences.
     *
     * **Example 1:**
     *
     * ```
     * "abc\"def\nghi"
     * ```
     *
     * - The `\"` must be converted to `"`.
     * - The `\n` must be converted to a new line character in a token,
     *   but it should not increment the current line for source mapping.
     *
     * **Example 2:**
     *
     * ```
     * "abc\
     *  def"
     * ```
     *
     * The line continuation (`\` followed by a newline) should be removed from a token
     * but the new line should increment the current line for source mapping.
     */
    escapedString?: boolean;
    /**
     * An array of characters that should be considered as leading trivia.
     * Leading trivia are characters that are not important to the developer, and so should not be
     * included in source-map segments.  A common example is whitespace.
     */
    leadingTriviaChars?: string[];
    /**
     * Render `$localize` message ids with additional legacy message ids.
     *
     * This option defaults to `true` but in the future the defaul will be flipped.
     *
     * For now set this option to false if you have migrated the translation files to use the new
     * `$localize` message id format and you are not using compile time translation merging.
     */
    enableI18nLegacyMessageIdFormat?: boolean;
    /**
     * If this text is stored in an external template (e.g. via `templateUrl`) then we need to decide
     * whether or not to normalize the line-endings (from `\r\n` to `\n`) when processing ICU
     * expressions.
     *
     * If `true` then we will normalize ICU expression line endings.
     * The default is `false`, but this will be switched in a future major release.
     */
    i18nNormalizeLineEndingsInICUs?: boolean;
    /**
     * Whether the template was inline.
     */
    isInline?: boolean;
    /**
     * Whether to always attempt to convert the parsed HTML AST to an R3 AST, despite HTML or i18n
     * Meta parse errors.
     *
     *
     * This option is useful in the context of the language service, where we want to get as much
     * information as possible, despite any errors in the HTML. As an example, a user may be adding
     * a new tag and expecting autocomplete on that tag. In this scenario, the HTML is in an errored
     * state, as there is an incomplete open tag. However, we're still able to convert the HTML AST
     * nodes to R3 AST nodes in order to provide information for the language service.
     *
     * Note that even when `true` the HTML parse and i18n errors are still appended to the errors
     * output, but this is done after converting the HTML AST to R3 AST.
     */
    alwaysAttemptHtmlToR3AstConversion?: boolean;
}
/**
 * Parse a template into render3 `Node`s and additional metadata, with no other dependencies.
 *
 * @param template text of the template to parse
 * @param templateUrl URL to use for source mapping of the parsed template
 * @param options options to modify how the template is parsed
 */
export declare function parseTemplate(template: string, templateUrl: string, options?: ParseTemplateOptions): ParsedTemplate;
/**
 * Construct a `BindingParser` with a default configuration.
 */
export declare function makeBindingParser(interpolationConfig?: InterpolationConfig): BindingParser;
export declare function resolveSanitizationFn(context: core.SecurityContext, isAttribute?: boolean): o.ExternalExpr | null;
/**
 * Generate statements that define a given translation message.
 *
 * ```
 * var I18N_1;
 * if (typeof ngI18nClosureMode !== undefined && ngI18nClosureMode) {
 *     var MSG_EXTERNAL_XXX = goog.getMsg(
 *          "Some message with {$interpolation}!",
 *          { "interpolation": "\uFFFD0\uFFFD" }
 *     );
 *     I18N_1 = MSG_EXTERNAL_XXX;
 * }
 * else {
 *     I18N_1 = $localize`Some message with ${'\uFFFD0\uFFFD'}!`;
 * }
 * ```
 *
 * @param message The original i18n AST message node
 * @param variable The variable that will be assigned the translation, e.g. `I18N_1`.
 * @param closureVar The variable for Closure `goog.getMsg` calls, e.g. `MSG_EXTERNAL_XXX`.
 * @param params Object mapping placeholder names to their values (e.g.
 * `{ "interpolation": "\uFFFD0\uFFFD" }`).
 * @param transformFn Optional transformation function that will be applied to the translation (e.g.
 * post-processing).
 * @returns An array of statements that defined a given translation.
 */
export declare function getTranslationDeclStmts(message: i18n.Message, variable: o.ReadVarExpr, closureVar: o.ReadVarExpr, params?: {
    [name: string]: o.Expression;
}, transformFn?: (raw: o.ReadVarExpr) => o.Expression): o.Statement[];
/**
 * Information about the template which was extracted during parsing.
 *
 * This contains the actual parsed template as well as any metadata collected during its parsing,
 * some of which might be useful for re-parsing the template with different options.
 */
export interface ParsedTemplate {
    /**
     * Include whitespace nodes in the parsed output.
     */
    preserveWhitespaces?: boolean;
    /**
     * How to parse interpolation markers.
     */
    interpolationConfig?: InterpolationConfig;
    /**
     * The string contents of the template, or an expression that represents the string/template
     * literal as it occurs in the source.
     *
     * This is the "logical" template string, after expansion of any escaped characters (for inline
     * templates). This may differ from the actual template bytes as they appear in the .ts file.
     */
    template: string | o.Expression;
    /**
     * A full path to the file which contains the template.
     *
     * This can be either the original .ts file if the template is inline, or the .html file if an
     * external file was used.
     */
    templateUrl: string;
    /**
     * Whether the template was inline (using `template`) or external (using `templateUrl`).
     */
    isInline: boolean;
    /**
     * Any errors from parsing the template the first time.
     *
     * `null` if there are no errors. Otherwise, the array of errors is guaranteed to be non-empty.
     */
    errors: ParseError[] | null;
    /**
     * The template AST, parsed from the template.
     */
    nodes: t.Node[];
    /**
     * Any styleUrls extracted from the metadata.
     */
    styleUrls: string[];
    /**
     * Any inline styles extracted from the metadata.
     */
    styles: string[];
    /**
     * Any ng-content selectors extracted from the template.
     */
    ngContentSelectors: string[];
}
export {};
