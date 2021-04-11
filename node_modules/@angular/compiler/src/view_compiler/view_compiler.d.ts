/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
import { CompileDirectiveMetadata, CompilePipeSummary } from '../compile_metadata';
import { CompileReflector } from '../compile_reflector';
import * as o from '../output/output_ast';
import { TemplateAst } from '../template_parser/template_ast';
import { OutputContext } from '../util';
export declare class ViewCompileResult {
    viewClassVar: string;
    rendererTypeVar: string;
    constructor(viewClassVar: string, rendererTypeVar: string);
}
export declare class ViewCompiler {
    private _reflector;
    constructor(_reflector: CompileReflector);
    compileComponent(outputCtx: OutputContext, component: CompileDirectiveMetadata, template: TemplateAst[], styles: o.Expression, usedPipes: CompilePipeSummary[]): ViewCompileResult;
}
export declare function elementEventFullName(target: string | null, name: string): string;
