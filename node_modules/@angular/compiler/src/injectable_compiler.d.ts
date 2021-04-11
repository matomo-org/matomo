/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
import { CompileInjectableMetadata } from './compile_metadata';
import { CompileReflector } from './compile_reflector';
import * as o from './output/output_ast';
import { OutputContext } from './util';
export declare class InjectableCompiler {
    private reflector;
    private alwaysGenerateDef;
    private tokenInjector;
    constructor(reflector: CompileReflector, alwaysGenerateDef: boolean);
    private depsArray;
    factoryFor(injectable: CompileInjectableMetadata, ctx: OutputContext): o.Expression;
    injectableDef(injectable: CompileInjectableMetadata, ctx: OutputContext): o.Expression;
    compile(injectable: CompileInjectableMetadata, ctx: OutputContext): void;
}
