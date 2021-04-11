/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
import { CompileNgModuleMetadata, CompileProviderMetadata } from './compile_metadata';
import { CompileReflector } from './compile_reflector';
import { OutputContext } from './util';
export declare class NgModuleCompileResult {
    ngModuleFactoryVar: string;
    constructor(ngModuleFactoryVar: string);
}
export declare class NgModuleCompiler {
    private reflector;
    constructor(reflector: CompileReflector);
    compile(ctx: OutputContext, ngModuleMeta: CompileNgModuleMetadata, extraProviders: CompileProviderMetadata[]): NgModuleCompileResult;
    createStub(ctx: OutputContext, ngModuleReference: any): void;
    private _createNgModuleFactory;
}
