/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
import { CompileDirectiveMetadata, CompileNgModuleMetadata, CompilePipeMetadata, CompileTypeMetadata, CompileTypeSummary } from '../compile_metadata';
import { Summary, SummaryResolver } from '../summary_resolver';
import { OutputContext } from '../util';
import { StaticSymbol, StaticSymbolCache } from './static_symbol';
import { ResolvedStaticSymbol, StaticSymbolResolver } from './static_symbol_resolver';
export declare function serializeSummaries(srcFileName: string, forJitCtx: OutputContext | null, summaryResolver: SummaryResolver<StaticSymbol>, symbolResolver: StaticSymbolResolver, symbols: ResolvedStaticSymbol[], types: {
    summary: CompileTypeSummary;
    metadata: CompileNgModuleMetadata | CompileDirectiveMetadata | CompilePipeMetadata | CompileTypeMetadata;
}[], createExternalSymbolReexports?: boolean): {
    json: string;
    exportAs: {
        symbol: StaticSymbol;
        exportAs: string;
    }[];
};
export declare function deserializeSummaries(symbolCache: StaticSymbolCache, summaryResolver: SummaryResolver<StaticSymbol>, libraryFileName: string, json: string): {
    moduleName: string | null;
    summaries: Summary<StaticSymbol>[];
    importAs: {
        symbol: StaticSymbol;
        importAs: StaticSymbol;
    }[];
};
export declare function createForJitStub(outputCtx: OutputContext, reference: StaticSymbol): void;
