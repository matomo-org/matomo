/// <amd-module name="@angular/core/schematics/utils/typescript/compiler_host" />
/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
import { Tree } from '@angular-devkit/schematics';
import * as ts from 'typescript';
export declare type FakeReadFileFn = (fileName: string) => string | null;
/**
 * Creates a TypeScript program instance for a TypeScript project within
 * the virtual file system tree.
 * @param tree Virtual file system tree that contains the source files.
 * @param tsconfigPath Virtual file system path that resolves to the TypeScript project.
 * @param basePath Base path for the virtual file system tree.
 * @param fakeFileRead Optional file reader function. Can be used to overwrite files in
 *   the TypeScript program, or to add in-memory files (e.g. to add global types).
 * @param additionalFiles Additional file paths that should be added to the program.
 */
export declare function createMigrationProgram(tree: Tree, tsconfigPath: string, basePath: string, fakeFileRead?: FakeReadFileFn, additionalFiles?: string[]): {
    parsed: ts.ParsedCommandLine;
    host: ts.CompilerHost;
    program: ts.Program;
};
export declare function createMigrationCompilerHost(tree: Tree, options: ts.CompilerOptions, basePath: string, fakeRead?: FakeReadFileFn): ts.CompilerHost;
/**
 * Checks whether a file can be migrate by our automated migrations.
 * @param basePath Absolute path to the project.
 * @param sourceFile File being checked.
 * @param program Program that includes the source file.
 */
export declare function canMigrateFile(basePath: string, sourceFile: ts.SourceFile, program: ts.Program): boolean;
