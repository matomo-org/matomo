/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
import { StaticSymbol, StaticSymbolCache } from './aot/static_symbol';
import * as cpl from './compile_metadata';
import { CompileReflector } from './compile_reflector';
import { CompilerConfig } from './config';
import { Directive, Type } from './core';
import { DirectiveNormalizer } from './directive_normalizer';
import { DirectiveResolver } from './directive_resolver';
import { HtmlParser } from './ml_parser/html_parser';
import { NgModuleResolver } from './ng_module_resolver';
import { PipeResolver } from './pipe_resolver';
import { ElementSchemaRegistry } from './schema/element_schema_registry';
import { SummaryResolver } from './summary_resolver';
import { Console, SyncAsync } from './util';
export declare type ErrorCollector = (error: any, type?: any) => void;
export declare const ERROR_COMPONENT_TYPE = "ngComponentType";
export declare class CompileMetadataResolver {
    private _config;
    private _htmlParser;
    private _ngModuleResolver;
    private _directiveResolver;
    private _pipeResolver;
    private _summaryResolver;
    private _schemaRegistry;
    private _directiveNormalizer;
    private _console;
    private _staticSymbolCache;
    private _reflector;
    private _errorCollector?;
    private _nonNormalizedDirectiveCache;
    private _directiveCache;
    private _summaryCache;
    private _pipeCache;
    private _ngModuleCache;
    private _ngModuleOfTypes;
    private _shallowModuleCache;
    constructor(_config: CompilerConfig, _htmlParser: HtmlParser, _ngModuleResolver: NgModuleResolver, _directiveResolver: DirectiveResolver, _pipeResolver: PipeResolver, _summaryResolver: SummaryResolver<any>, _schemaRegistry: ElementSchemaRegistry, _directiveNormalizer: DirectiveNormalizer, _console: Console, _staticSymbolCache: StaticSymbolCache, _reflector: CompileReflector, _errorCollector?: ErrorCollector | undefined);
    getReflector(): CompileReflector;
    clearCacheFor(type: Type): void;
    clearCache(): void;
    private _createProxyClass;
    private getGeneratedClass;
    private getComponentViewClass;
    getHostComponentViewClass(dirType: any): StaticSymbol | cpl.ProxyClass;
    getHostComponentType(dirType: any): StaticSymbol | cpl.ProxyClass;
    private getRendererType;
    private getComponentFactory;
    private initComponentFactory;
    private _loadSummary;
    getHostComponentMetadata(compMeta: cpl.CompileDirectiveMetadata, hostViewType?: StaticSymbol | cpl.ProxyClass): cpl.CompileDirectiveMetadata;
    loadDirectiveMetadata(ngModuleType: any, directiveType: any, isSync: boolean): SyncAsync<null>;
    getNonNormalizedDirectiveMetadata(directiveType: any): {
        annotation: Directive;
        metadata: cpl.CompileDirectiveMetadata;
    } | null;
    /**
     * Gets the metadata for the given directive.
     * This assumes `loadNgModuleDirectiveAndPipeMetadata` has been called first.
     */
    getDirectiveMetadata(directiveType: any): cpl.CompileDirectiveMetadata;
    getDirectiveSummary(dirType: any): cpl.CompileDirectiveSummary;
    isDirective(type: any): boolean;
    isAbstractDirective(type: any): boolean;
    isPipe(type: any): boolean;
    isNgModule(type: any): boolean;
    getNgModuleSummary(moduleType: any, alreadyCollecting?: Set<any> | null): cpl.CompileNgModuleSummary | null;
    /**
     * Loads the declared directives and pipes of an NgModule.
     */
    loadNgModuleDirectiveAndPipeMetadata(moduleType: any, isSync: boolean, throwIfNotFound?: boolean): Promise<any>;
    getShallowModuleMetadata(moduleType: any): cpl.CompileShallowModuleMetadata | null;
    getNgModuleMetadata(moduleType: any, throwIfNotFound?: boolean, alreadyCollecting?: Set<any> | null): cpl.CompileNgModuleMetadata | null;
    private _checkSelfImport;
    private _getTypeDescriptor;
    private _addTypeToModule;
    private _getTransitiveNgModuleMetadata;
    private _getIdentifierMetadata;
    isInjectable(type: any): boolean;
    getInjectableSummary(type: any): cpl.CompileTypeSummary;
    getInjectableMetadata(type: any, dependencies?: any[] | null, throwOnUnknownDeps?: boolean): cpl.CompileInjectableMetadata | null;
    private _getTypeMetadata;
    private _getFactoryMetadata;
    /**
     * Gets the metadata for the given pipe.
     * This assumes `loadNgModuleDirectiveAndPipeMetadata` has been called first.
     */
    getPipeMetadata(pipeType: any): cpl.CompilePipeMetadata | null;
    getPipeSummary(pipeType: any): cpl.CompilePipeSummary;
    getOrLoadPipeMetadata(pipeType: any): cpl.CompilePipeMetadata;
    private _loadPipeMetadata;
    private _getDependenciesMetadata;
    private _getTokenMetadata;
    private _getProvidersMetadata;
    private _validateProvider;
    private _getEntryComponentsFromProvider;
    private _getEntryComponentMetadata;
    private _getInjectableTypeMetadata;
    getProviderMetadata(provider: cpl.ProviderMeta): cpl.CompileProviderMetadata;
    private _getQueriesMetadata;
    private _queryVarBindings;
    private _getQueryMetadata;
    private _reportError;
}
