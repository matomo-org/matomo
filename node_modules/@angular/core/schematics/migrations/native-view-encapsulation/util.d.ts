/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
/// <amd-module name="@angular/core/schematics/migrations/native-view-encapsulation/util" />
import * as ts from 'typescript';
/** Finds all the Identifier nodes in a file that refer to `Native` view encapsulation. */
export declare function findNativeEncapsulationNodes(typeChecker: ts.TypeChecker, sourceFile: ts.SourceFile): Set<ts.Identifier>;
