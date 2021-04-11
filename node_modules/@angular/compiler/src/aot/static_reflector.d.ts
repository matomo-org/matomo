/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
import { CompileReflector } from '../compile_reflector';
import * as o from '../output/output_ast';
import { SummaryResolver } from '../summary_resolver';
import { StaticSymbol } from './static_symbol';
import { StaticSymbolResolver } from './static_symbol_resolver';
/**
 * A static reflector implements enough of the Reflector API that is necessary to compile
 * templates statically.
 */
export declare class StaticReflector implements CompileReflector {
    private summaryResolver;
    private symbolResolver;
    private errorRecorder?;
    private annotationCache;
    private shallowAnnotationCache;
    private propertyCache;
    private parameterCache;
    private methodCache;
    private staticCache;
    private conversionMap;
    private resolvedExternalReferences;
    private injectionToken;
    private opaqueToken;
    ROUTES: StaticSymbol;
    private ANALYZE_FOR_ENTRY_COMPONENTS;
    private annotationForParentClassWithSummaryKind;
    constructor(summaryResolver: SummaryResolver<StaticSymbol>, symbolResolver: StaticSymbolResolver, knownMetadataClasses?: {
        name: string;
        filePath: string;
        ctor: any;
    }[], knownMetadataFunctions?: {
        name: string;
        filePath: string;
        fn: any;
    }[], errorRecorder?: ((error: any, fileName?: string | undefined) => void) | undefined);
    componentModuleUrl(typeOrFunc: StaticSymbol): string;
    /**
     * Invalidate the specified `symbols` on program change.
     * @param symbols
     */
    invalidateSymbols(symbols: StaticSymbol[]): void;
    resolveExternalReference(ref: o.ExternalReference, containingFile?: string): StaticSymbol;
    findDeclaration(moduleUrl: string, name: string, containingFile?: string): StaticSymbol;
    tryFindDeclaration(moduleUrl: string, name: string, containingFile?: string): StaticSymbol;
    findSymbolDeclaration(symbol: StaticSymbol): StaticSymbol;
    tryAnnotations(type: StaticSymbol): any[];
    annotations(type: StaticSymbol): any[];
    shallowAnnotations(type: StaticSymbol): any[];
    private _annotations;
    propMetadata(type: StaticSymbol): {
        [key: string]: any[];
    };
    parameters(type: StaticSymbol): any[];
    private _methodNames;
    private _staticMembers;
    private findParentType;
    hasLifecycleHook(type: any, lcProperty: string): boolean;
    guards(type: any): {
        [key: string]: StaticSymbol;
    };
    private _registerDecoratorOrConstructor;
    private _registerFunction;
    private initializeConversionMap;
    /**
     * getStaticSymbol produces a Type whose metadata is known but whose implementation is not loaded.
     * All types passed to the StaticResolver should be pseudo-types returned by this method.
     *
     * @param declarationFile the absolute path of the file where the symbol is declared
     * @param name the name of the type.
     */
    getStaticSymbol(declarationFile: string, name: string, members?: string[]): StaticSymbol;
    /**
     * Simplify but discard any errors
     */
    private trySimplify;
    private getTypeMetadata;
    private reportError;
    private error;
}
