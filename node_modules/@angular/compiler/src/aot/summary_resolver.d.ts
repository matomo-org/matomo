/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
import { Summary, SummaryResolver } from '../summary_resolver';
import { StaticSymbol, StaticSymbolCache } from './static_symbol';
export interface AotSummaryResolverHost {
    /**
     * Loads an NgModule/Directive/Pipe summary file
     */
    loadSummary(filePath: string): string | null;
    /**
     * Returns whether a file is a source file or not.
     */
    isSourceFile(sourceFilePath: string): boolean;
    /**
     * Converts a file name into a representation that should be stored in a summary file.
     * This has to include changing the suffix as well.
     * E.g.
     * `some_file.ts` -> `some_file.d.ts`
     *
     * @param referringSrcFileName the soure file that refers to fileName
     */
    toSummaryFileName(fileName: string, referringSrcFileName: string): string;
    /**
     * Converts a fileName that was processed by `toSummaryFileName` back into a real fileName
     * given the fileName of the library that is referrig to it.
     */
    fromSummaryFileName(fileName: string, referringLibFileName: string): string;
}
export declare class AotSummaryResolver implements SummaryResolver<StaticSymbol> {
    private host;
    private staticSymbolCache;
    private summaryCache;
    private loadedFilePaths;
    private importAs;
    private knownFileNameToModuleNames;
    constructor(host: AotSummaryResolverHost, staticSymbolCache: StaticSymbolCache);
    isLibraryFile(filePath: string): boolean;
    toSummaryFileName(filePath: string, referringSrcFileName: string): string;
    fromSummaryFileName(fileName: string, referringLibFileName: string): string;
    resolveSummary(staticSymbol: StaticSymbol): Summary<StaticSymbol> | null;
    getSymbolsOf(filePath: string): StaticSymbol[] | null;
    getImportAs(staticSymbol: StaticSymbol): StaticSymbol;
    /**
     * Converts a file path to a module name that can be used as an `import`.
     */
    getKnownModuleName(importedFilePath: string): string | null;
    addSummary(summary: Summary<StaticSymbol>): void;
    private _loadSummaryFile;
}
