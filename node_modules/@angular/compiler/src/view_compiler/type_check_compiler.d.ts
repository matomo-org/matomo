/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
import { AotCompilerOptions } from '../aot/compiler_options';
import { StaticReflector } from '../aot/static_reflector';
import { StaticSymbol } from '../aot/static_symbol';
import { CompileDirectiveMetadata, CompilePipeSummary } from '../compile_metadata';
import * as o from '../output/output_ast';
import { TemplateAst } from '../template_parser/template_ast';
import { OutputContext } from '../util';
/**
 * Generates code that is used to type check templates.
 */
export declare class TypeCheckCompiler {
    private options;
    private reflector;
    constructor(options: AotCompilerOptions, reflector: StaticReflector);
    /**
     * Important notes:
     * - This must not produce new `import` statements, but only refer to types outside
     *   of the file via the variables provided via externalReferenceVars.
     *   This allows Typescript to reuse the old program's structure as no imports have changed.
     * - This must not produce any exports, as this would pollute the .d.ts file
     *   and also violate the point above.
     */
    compileComponent(componentId: string, component: CompileDirectiveMetadata, template: TemplateAst[], usedPipes: CompilePipeSummary[], externalReferenceVars: Map<StaticSymbol, string>, ctx: OutputContext): o.Statement[];
}
