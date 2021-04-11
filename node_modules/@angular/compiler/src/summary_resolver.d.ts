/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
import { CompileTypeSummary } from './compile_metadata';
import { Type } from './core';
export interface Summary<T> {
    symbol: T;
    metadata: any;
    type?: CompileTypeSummary;
}
export declare abstract class SummaryResolver<T> {
    abstract isLibraryFile(fileName: string): boolean;
    abstract toSummaryFileName(fileName: string, referringSrcFileName: string): string;
    abstract fromSummaryFileName(fileName: string, referringLibFileName: string): string;
    abstract resolveSummary(reference: T): Summary<T> | null;
    abstract getSymbolsOf(filePath: string): T[] | null;
    abstract getImportAs(reference: T): T;
    abstract getKnownModuleName(fileName: string): string | null;
    abstract addSummary(summary: Summary<T>): void;
}
export declare class JitSummaryResolver implements SummaryResolver<Type> {
    private _summaries;
    isLibraryFile(): boolean;
    toSummaryFileName(fileName: string): string;
    fromSummaryFileName(fileName: string): string;
    resolveSummary(reference: Type): Summary<Type> | null;
    getSymbolsOf(): Type[];
    getImportAs(reference: Type): Type;
    getKnownModuleName(fileName: string): null;
    addSummary(summary: Summary<Type>): void;
}
