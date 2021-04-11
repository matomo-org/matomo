/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
import { StaticSymbol } from './aot/static_symbol';
import { ChangeDetectionStrategy, SchemaMetadata, Type, ViewEncapsulation } from './core';
import { LifecycleHooks } from './lifecycle_reflector';
import { ParseTreeResult as HtmlParseTreeResult } from './ml_parser/parser';
export declare function sanitizeIdentifier(name: string): string;
export declare function identifierName(compileIdentifier: CompileIdentifierMetadata | null | undefined): string | null;
export declare function identifierModuleUrl(compileIdentifier: CompileIdentifierMetadata): string;
export declare function viewClassName(compType: any, embeddedTemplateIndex: number): string;
export declare function rendererTypeName(compType: any): string;
export declare function hostViewClassName(compType: any): string;
export declare function componentFactoryName(compType: any): string;
export interface ProxyClass {
    setDelegate(delegate: any): void;
}
export interface CompileIdentifierMetadata {
    reference: any;
}
export declare enum CompileSummaryKind {
    Pipe = 0,
    Directive = 1,
    NgModule = 2,
    Injectable = 3
}
/**
 * A CompileSummary is the data needed to use a directive / pipe / module
 * in other modules / components. However, this data is not enough to compile
 * the directive / module itself.
 */
export interface CompileTypeSummary {
    summaryKind: CompileSummaryKind | null;
    type: CompileTypeMetadata;
}
export interface CompileDiDependencyMetadata {
    isAttribute?: boolean;
    isSelf?: boolean;
    isHost?: boolean;
    isSkipSelf?: boolean;
    isOptional?: boolean;
    isValue?: boolean;
    token?: CompileTokenMetadata;
    value?: any;
}
export interface CompileProviderMetadata {
    token: CompileTokenMetadata;
    useClass?: CompileTypeMetadata;
    useValue?: any;
    useExisting?: CompileTokenMetadata;
    useFactory?: CompileFactoryMetadata;
    deps?: CompileDiDependencyMetadata[];
    multi?: boolean;
}
export interface CompileFactoryMetadata extends CompileIdentifierMetadata {
    diDeps: CompileDiDependencyMetadata[];
    reference: any;
}
export declare function tokenName(token: CompileTokenMetadata): string | null;
export declare function tokenReference(token: CompileTokenMetadata): any;
export interface CompileTokenMetadata {
    value?: any;
    identifier?: CompileIdentifierMetadata | CompileTypeMetadata;
}
export interface CompileInjectableMetadata {
    symbol: StaticSymbol;
    type: CompileTypeMetadata;
    providedIn?: StaticSymbol;
    useValue?: any;
    useClass?: StaticSymbol;
    useExisting?: StaticSymbol;
    useFactory?: StaticSymbol;
    deps?: any[];
}
/**
 * Metadata regarding compilation of a type.
 */
export interface CompileTypeMetadata extends CompileIdentifierMetadata {
    diDeps: CompileDiDependencyMetadata[];
    lifecycleHooks: LifecycleHooks[];
    reference: any;
}
export interface CompileQueryMetadata {
    selectors: Array<CompileTokenMetadata>;
    descendants: boolean;
    first: boolean;
    propertyName: string;
    read: CompileTokenMetadata;
    static?: boolean;
    emitDistinctChangesOnly?: boolean;
}
/**
 * Metadata about a stylesheet
 */
export declare class CompileStylesheetMetadata {
    moduleUrl: string | null;
    styles: string[];
    styleUrls: string[];
    constructor({ moduleUrl, styles, styleUrls }?: {
        moduleUrl?: string;
        styles?: string[];
        styleUrls?: string[];
    });
}
/**
 * Summary Metadata regarding compilation of a template.
 */
export interface CompileTemplateSummary {
    ngContentSelectors: string[];
    encapsulation: ViewEncapsulation | null;
    styles: string[];
    animations: any[] | null;
}
/**
 * Metadata regarding compilation of a template.
 */
export declare class CompileTemplateMetadata {
    encapsulation: ViewEncapsulation | null;
    template: string | null;
    templateUrl: string | null;
    htmlAst: HtmlParseTreeResult | null;
    isInline: boolean;
    styles: string[];
    styleUrls: string[];
    externalStylesheets: CompileStylesheetMetadata[];
    animations: any[];
    ngContentSelectors: string[];
    interpolation: [string, string] | null;
    preserveWhitespaces: boolean;
    constructor({ encapsulation, template, templateUrl, htmlAst, styles, styleUrls, externalStylesheets, animations, ngContentSelectors, interpolation, isInline, preserveWhitespaces }: {
        encapsulation: ViewEncapsulation | null;
        template: string | null;
        templateUrl: string | null;
        htmlAst: HtmlParseTreeResult | null;
        styles: string[];
        styleUrls: string[];
        externalStylesheets: CompileStylesheetMetadata[];
        ngContentSelectors: string[];
        animations: any[];
        interpolation: [string, string] | null;
        isInline: boolean;
        preserveWhitespaces: boolean;
    });
    toSummary(): CompileTemplateSummary;
}
export interface CompileEntryComponentMetadata {
    componentType: any;
    componentFactory: StaticSymbol | object;
}
export interface CompileDirectiveSummary extends CompileTypeSummary {
    type: CompileTypeMetadata;
    isComponent: boolean;
    selector: string | null;
    exportAs: string | null;
    inputs: {
        [key: string]: string;
    };
    outputs: {
        [key: string]: string;
    };
    hostListeners: {
        [key: string]: string;
    };
    hostProperties: {
        [key: string]: string;
    };
    hostAttributes: {
        [key: string]: string;
    };
    providers: CompileProviderMetadata[];
    viewProviders: CompileProviderMetadata[];
    queries: CompileQueryMetadata[];
    guards: {
        [key: string]: any;
    };
    viewQueries: CompileQueryMetadata[];
    entryComponents: CompileEntryComponentMetadata[];
    changeDetection: ChangeDetectionStrategy | null;
    template: CompileTemplateSummary | null;
    componentViewType: StaticSymbol | ProxyClass | null;
    rendererType: StaticSymbol | object | null;
    componentFactory: StaticSymbol | object | null;
}
/**
 * Metadata regarding compilation of a directive.
 */
export declare class CompileDirectiveMetadata {
    static create({ isHost, type, isComponent, selector, exportAs, changeDetection, inputs, outputs, host, providers, viewProviders, queries, guards, viewQueries, entryComponents, template, componentViewType, rendererType, componentFactory }: {
        isHost: boolean;
        type: CompileTypeMetadata;
        isComponent: boolean;
        selector: string | null;
        exportAs: string | null;
        changeDetection: ChangeDetectionStrategy | null;
        inputs: string[];
        outputs: string[];
        host: {
            [key: string]: string;
        };
        providers: CompileProviderMetadata[];
        viewProviders: CompileProviderMetadata[];
        queries: CompileQueryMetadata[];
        guards: {
            [key: string]: any;
        };
        viewQueries: CompileQueryMetadata[];
        entryComponents: CompileEntryComponentMetadata[];
        template: CompileTemplateMetadata;
        componentViewType: StaticSymbol | ProxyClass | null;
        rendererType: StaticSymbol | object | null;
        componentFactory: StaticSymbol | object | null;
    }): CompileDirectiveMetadata;
    isHost: boolean;
    type: CompileTypeMetadata;
    isComponent: boolean;
    selector: string | null;
    exportAs: string | null;
    changeDetection: ChangeDetectionStrategy | null;
    inputs: {
        [key: string]: string;
    };
    outputs: {
        [key: string]: string;
    };
    hostListeners: {
        [key: string]: string;
    };
    hostProperties: {
        [key: string]: string;
    };
    hostAttributes: {
        [key: string]: string;
    };
    providers: CompileProviderMetadata[];
    viewProviders: CompileProviderMetadata[];
    queries: CompileQueryMetadata[];
    guards: {
        [key: string]: any;
    };
    viewQueries: CompileQueryMetadata[];
    entryComponents: CompileEntryComponentMetadata[];
    template: CompileTemplateMetadata | null;
    componentViewType: StaticSymbol | ProxyClass | null;
    rendererType: StaticSymbol | object | null;
    componentFactory: StaticSymbol | object | null;
    constructor({ isHost, type, isComponent, selector, exportAs, changeDetection, inputs, outputs, hostListeners, hostProperties, hostAttributes, providers, viewProviders, queries, guards, viewQueries, entryComponents, template, componentViewType, rendererType, componentFactory }: {
        isHost: boolean;
        type: CompileTypeMetadata;
        isComponent: boolean;
        selector: string | null;
        exportAs: string | null;
        changeDetection: ChangeDetectionStrategy | null;
        inputs: {
            [key: string]: string;
        };
        outputs: {
            [key: string]: string;
        };
        hostListeners: {
            [key: string]: string;
        };
        hostProperties: {
            [key: string]: string;
        };
        hostAttributes: {
            [key: string]: string;
        };
        providers: CompileProviderMetadata[];
        viewProviders: CompileProviderMetadata[];
        queries: CompileQueryMetadata[];
        guards: {
            [key: string]: any;
        };
        viewQueries: CompileQueryMetadata[];
        entryComponents: CompileEntryComponentMetadata[];
        template: CompileTemplateMetadata | null;
        componentViewType: StaticSymbol | ProxyClass | null;
        rendererType: StaticSymbol | object | null;
        componentFactory: StaticSymbol | object | null;
    });
    toSummary(): CompileDirectiveSummary;
}
export interface CompilePipeSummary extends CompileTypeSummary {
    type: CompileTypeMetadata;
    name: string;
    pure: boolean;
}
export declare class CompilePipeMetadata {
    type: CompileTypeMetadata;
    name: string;
    pure: boolean;
    constructor({ type, name, pure }: {
        type: CompileTypeMetadata;
        name: string;
        pure: boolean;
    });
    toSummary(): CompilePipeSummary;
}
export interface CompileNgModuleSummary extends CompileTypeSummary {
    type: CompileTypeMetadata;
    exportedDirectives: CompileIdentifierMetadata[];
    exportedPipes: CompileIdentifierMetadata[];
    entryComponents: CompileEntryComponentMetadata[];
    providers: {
        provider: CompileProviderMetadata;
        module: CompileIdentifierMetadata;
    }[];
    modules: CompileTypeMetadata[];
}
export declare class CompileShallowModuleMetadata {
    type: CompileTypeMetadata;
    rawExports: any;
    rawImports: any;
    rawProviders: any;
}
/**
 * Metadata regarding compilation of a module.
 */
export declare class CompileNgModuleMetadata {
    type: CompileTypeMetadata;
    declaredDirectives: CompileIdentifierMetadata[];
    exportedDirectives: CompileIdentifierMetadata[];
    declaredPipes: CompileIdentifierMetadata[];
    exportedPipes: CompileIdentifierMetadata[];
    entryComponents: CompileEntryComponentMetadata[];
    bootstrapComponents: CompileIdentifierMetadata[];
    providers: CompileProviderMetadata[];
    importedModules: CompileNgModuleSummary[];
    exportedModules: CompileNgModuleSummary[];
    schemas: SchemaMetadata[];
    id: string | null;
    transitiveModule: TransitiveCompileNgModuleMetadata;
    constructor({ type, providers, declaredDirectives, exportedDirectives, declaredPipes, exportedPipes, entryComponents, bootstrapComponents, importedModules, exportedModules, schemas, transitiveModule, id }: {
        type: CompileTypeMetadata;
        providers: CompileProviderMetadata[];
        declaredDirectives: CompileIdentifierMetadata[];
        exportedDirectives: CompileIdentifierMetadata[];
        declaredPipes: CompileIdentifierMetadata[];
        exportedPipes: CompileIdentifierMetadata[];
        entryComponents: CompileEntryComponentMetadata[];
        bootstrapComponents: CompileIdentifierMetadata[];
        importedModules: CompileNgModuleSummary[];
        exportedModules: CompileNgModuleSummary[];
        transitiveModule: TransitiveCompileNgModuleMetadata;
        schemas: SchemaMetadata[];
        id: string | null;
    });
    toSummary(): CompileNgModuleSummary;
}
export declare class TransitiveCompileNgModuleMetadata {
    directivesSet: Set<any>;
    directives: CompileIdentifierMetadata[];
    exportedDirectivesSet: Set<any>;
    exportedDirectives: CompileIdentifierMetadata[];
    pipesSet: Set<any>;
    pipes: CompileIdentifierMetadata[];
    exportedPipesSet: Set<any>;
    exportedPipes: CompileIdentifierMetadata[];
    modulesSet: Set<any>;
    modules: CompileTypeMetadata[];
    entryComponentsSet: Set<any>;
    entryComponents: CompileEntryComponentMetadata[];
    providers: {
        provider: CompileProviderMetadata;
        module: CompileIdentifierMetadata;
    }[];
    addProvider(provider: CompileProviderMetadata, module: CompileIdentifierMetadata): void;
    addDirective(id: CompileIdentifierMetadata): void;
    addExportedDirective(id: CompileIdentifierMetadata): void;
    addPipe(id: CompileIdentifierMetadata): void;
    addExportedPipe(id: CompileIdentifierMetadata): void;
    addModule(id: CompileTypeMetadata): void;
    addEntryComponent(ec: CompileEntryComponentMetadata): void;
}
export declare class ProviderMeta {
    token: any;
    useClass: Type | null;
    useValue: any;
    useExisting: any;
    useFactory: Function | null;
    dependencies: Object[] | null;
    multi: boolean;
    constructor(token: any, { useClass, useValue, useExisting, useFactory, deps, multi }: {
        useClass?: Type;
        useValue?: any;
        useExisting?: any;
        useFactory?: Function | null;
        deps?: Object[] | null;
        multi?: boolean;
    });
}
export declare function flatten<T>(list: Array<T | T[]>): T[];
export declare function templateSourceUrl(ngModuleType: CompileIdentifierMetadata, compMeta: {
    type: CompileIdentifierMetadata;
}, templateMeta: {
    isInline: boolean;
    templateUrl: string | null;
}): string;
export declare function sharedStylesheetJitUrl(meta: CompileStylesheetMetadata, id: number): string;
export declare function ngModuleJitUrl(moduleMeta: CompileNgModuleMetadata): string;
export declare function templateJitUrl(ngModuleType: CompileIdentifierMetadata, compMeta: CompileDirectiveMetadata): string;
