/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
import { CompileDirectiveMetadata, CompileDirectiveSummary, CompilePipeSummary, CompileTypeMetadata } from '../compile_metadata';
import { CompileReflector } from '../compile_reflector';
import { CompilerConfig } from '../config';
import { SchemaMetadata } from '../core';
import { AST } from '../expression_parser/ast';
import { Parser } from '../expression_parser/parser';
import { HtmlParser, ParseTreeResult } from '../ml_parser/html_parser';
import { InterpolationConfig } from '../ml_parser/interpolation_config';
import { ParseError, ParseErrorLevel, ParseSourceSpan } from '../parse_util';
import { ElementSchemaRegistry } from '../schema/element_schema_registry';
import { CssSelector } from '../selector';
import { Console } from '../util';
import * as t from './template_ast';
export declare class TemplateParseError extends ParseError {
    constructor(message: string, span: ParseSourceSpan, level: ParseErrorLevel);
}
export declare class TemplateParseResult {
    templateAst?: t.TemplateAst[] | undefined;
    usedPipes?: CompilePipeSummary[] | undefined;
    errors?: ParseError[] | undefined;
    constructor(templateAst?: t.TemplateAst[] | undefined, usedPipes?: CompilePipeSummary[] | undefined, errors?: ParseError[] | undefined);
}
export declare class TemplateParser {
    private _config;
    private _reflector;
    private _exprParser;
    private _schemaRegistry;
    private _htmlParser;
    private _console;
    transforms: t.TemplateAstVisitor[];
    constructor(_config: CompilerConfig, _reflector: CompileReflector, _exprParser: Parser, _schemaRegistry: ElementSchemaRegistry, _htmlParser: HtmlParser, _console: Console | null, transforms: t.TemplateAstVisitor[]);
    get expressionParser(): Parser;
    parse(component: CompileDirectiveMetadata, template: string | ParseTreeResult, directives: CompileDirectiveSummary[], pipes: CompilePipeSummary[], schemas: SchemaMetadata[], templateUrl: string, preserveWhitespaces: boolean): {
        template: t.TemplateAst[];
        pipes: CompilePipeSummary[];
    };
    tryParse(component: CompileDirectiveMetadata, template: string | ParseTreeResult, directives: CompileDirectiveSummary[], pipes: CompilePipeSummary[], schemas: SchemaMetadata[], templateUrl: string, preserveWhitespaces: boolean): TemplateParseResult;
    tryParseHtml(htmlAstWithErrors: ParseTreeResult, component: CompileDirectiveMetadata, directives: CompileDirectiveSummary[], pipes: CompilePipeSummary[], schemas: SchemaMetadata[]): TemplateParseResult;
    expandHtml(htmlAstWithErrors: ParseTreeResult, forced?: boolean): ParseTreeResult;
    getInterpolationConfig(component: CompileDirectiveMetadata): InterpolationConfig | undefined;
}
export declare function splitClasses(classAttrValue: string): string[];
export declare function createElementCssSelector(elementName: string, attributes: [string, string][]): CssSelector;
export declare function removeSummaryDuplicates<T extends {
    type: CompileTypeMetadata;
}>(items: T[]): T[];
export declare function isEmptyExpression(ast: AST): boolean;
