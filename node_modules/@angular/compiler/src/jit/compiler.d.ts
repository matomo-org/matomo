/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
import { CompileProviderMetadata } from '../compile_metadata';
import { CompileReflector } from '../compile_reflector';
import { CompilerConfig } from '../config';
import { Type } from '../core';
import { CompileMetadataResolver } from '../metadata_resolver';
import { NgModuleCompiler } from '../ng_module_compiler';
import { JitEvaluator } from '../output/output_jit';
import { StyleCompiler } from '../style_compiler';
import { SummaryResolver } from '../summary_resolver';
import { TemplateParser } from '../template_parser/template_parser';
import { Console } from '../util';
import { ViewCompiler } from '../view_compiler/view_compiler';
export interface ModuleWithComponentFactories {
    ngModuleFactory: object;
    componentFactories: object[];
}
/**
 * An internal module of the Angular compiler that begins with component types,
 * extracts templates, and eventually produces a compiled version of the component
 * ready for linking into an application.
 *
 * @security  When compiling templates at runtime, you must ensure that the entire template comes
 * from a trusted source. Attacker-controlled data introduced by a template could expose your
 * application to XSS risks.  For more detail, see the [Security Guide](https://g.co/ng/security).
 */
export declare class JitCompiler {
    private _metadataResolver;
    private _templateParser;
    private _styleCompiler;
    private _viewCompiler;
    private _ngModuleCompiler;
    private _summaryResolver;
    private _reflector;
    private _jitEvaluator;
    private _compilerConfig;
    private _console;
    private getExtraNgModuleProviders;
    private _compiledTemplateCache;
    private _compiledHostTemplateCache;
    private _compiledDirectiveWrapperCache;
    private _compiledNgModuleCache;
    private _sharedStylesheetCount;
    private _addedAotSummaries;
    constructor(_metadataResolver: CompileMetadataResolver, _templateParser: TemplateParser, _styleCompiler: StyleCompiler, _viewCompiler: ViewCompiler, _ngModuleCompiler: NgModuleCompiler, _summaryResolver: SummaryResolver<Type>, _reflector: CompileReflector, _jitEvaluator: JitEvaluator, _compilerConfig: CompilerConfig, _console: Console, getExtraNgModuleProviders: (ngModule: any) => CompileProviderMetadata[]);
    compileModuleSync(moduleType: Type): object;
    compileModuleAsync(moduleType: Type): Promise<object>;
    compileModuleAndAllComponentsSync(moduleType: Type): ModuleWithComponentFactories;
    compileModuleAndAllComponentsAsync(moduleType: Type): Promise<ModuleWithComponentFactories>;
    getComponentFactory(component: Type): object;
    loadAotSummaries(summaries: () => any[]): void;
    private _addAotSummaries;
    hasAotSummary(ref: Type): boolean;
    private _filterJitIdentifiers;
    private _compileModuleAndComponents;
    private _compileModuleAndAllComponents;
    private _loadModules;
    private _compileModule;
    clearCacheFor(type: Type): void;
    clearCache(): void;
    private _createCompiledHostTemplate;
    private _createCompiledTemplate;
    private _compileTemplate;
    private _parseTemplate;
    private _resolveStylesCompileResult;
    private _resolveAndEvalStylesCompileResult;
    private _interpretOrJit;
}
