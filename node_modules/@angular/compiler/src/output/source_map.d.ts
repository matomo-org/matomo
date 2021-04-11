/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
export declare type SourceMap = {
    version: number;
    file?: string;
    sourceRoot: string;
    sources: string[];
    sourcesContent: (string | null)[];
    mappings: string;
};
export declare class SourceMapGenerator {
    private file;
    private sourcesContent;
    private lines;
    private lastCol0;
    private hasMappings;
    constructor(file?: string | null);
    addSource(url: string, content?: string | null): this;
    addLine(): this;
    addMapping(col0: number, sourceUrl?: string, sourceLine0?: number, sourceCol0?: number): this;
    toJSON(): SourceMap | null;
    toJsComment(): string;
}
export declare function toBase64String(value: string): string;
