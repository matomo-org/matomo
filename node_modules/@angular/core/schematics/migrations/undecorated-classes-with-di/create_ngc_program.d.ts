/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
/// <amd-module name="@angular/core/schematics/migrations/undecorated-classes-with-di/create_ngc_program" />
import { AotCompiler } from '@angular/compiler';
import { CompilerHost } from '@angular/compiler-cli';
import * as ts from 'typescript';
/** Creates an NGC program that can be used to read and parse metadata for files. */
export declare function createNgcProgram(createHost: (options: ts.CompilerOptions) => CompilerHost, tsconfigPath: string): {
    host: CompilerHost;
    ngcProgram: import("@angular/compiler-cli").Program;
    program: ts.Program;
    compiler: AotCompiler;
};
