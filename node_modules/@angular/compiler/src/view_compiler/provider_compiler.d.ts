/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
import { CompileDiDependencyMetadata, CompileEntryComponentMetadata } from '../compile_metadata';
import { CompileReflector } from '../compile_reflector';
import { NodeFlags } from '../core';
import { LifecycleHooks } from '../lifecycle_reflector';
import * as o from '../output/output_ast';
import { ProviderAst } from '../template_parser/template_ast';
import { OutputContext } from '../util';
export declare function providerDef(ctx: OutputContext, providerAst: ProviderAst): {
    providerExpr: o.Expression;
    flags: NodeFlags;
    depsExpr: o.Expression;
    tokenExpr: o.Expression;
};
export declare function depDef(ctx: OutputContext, dep: CompileDiDependencyMetadata): o.Expression;
export declare function lifecycleHookToNodeFlag(lifecycleHook: LifecycleHooks): NodeFlags;
export declare function componentFactoryResolverProviderDef(reflector: CompileReflector, ctx: OutputContext, flags: NodeFlags, entryComponents: CompileEntryComponentMetadata[]): {
    providerExpr: o.Expression;
    flags: NodeFlags;
    depsExpr: o.Expression;
    tokenExpr: o.Expression;
};
