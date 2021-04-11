/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
import { CompileDirectiveMetadata, CompileStylesheetMetadata } from './compile_metadata';
import { UrlResolver } from './url_resolver';
import { OutputContext } from './util';
export declare const HOST_ATTR: string;
export declare const CONTENT_ATTR: string;
export declare class StylesCompileDependency {
    name: string;
    moduleUrl: string;
    setValue: (value: any) => void;
    constructor(name: string, moduleUrl: string, setValue: (value: any) => void);
}
export declare class CompiledStylesheet {
    outputCtx: OutputContext;
    stylesVar: string;
    dependencies: StylesCompileDependency[];
    isShimmed: boolean;
    meta: CompileStylesheetMetadata;
    constructor(outputCtx: OutputContext, stylesVar: string, dependencies: StylesCompileDependency[], isShimmed: boolean, meta: CompileStylesheetMetadata);
}
export declare class StyleCompiler {
    private _urlResolver;
    private _shadowCss;
    constructor(_urlResolver: UrlResolver);
    compileComponent(outputCtx: OutputContext, comp: CompileDirectiveMetadata): CompiledStylesheet;
    compileStyles(outputCtx: OutputContext, comp: CompileDirectiveMetadata, stylesheet: CompileStylesheetMetadata, shim?: boolean): CompiledStylesheet;
    needsStyleShim(comp: CompileDirectiveMetadata): boolean;
    private _compileStyles;
    private _shimIfNeeded;
}
