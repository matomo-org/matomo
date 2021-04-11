/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
/// <amd-module name="@angular/core/schematics/migrations/can-activate-with-redirect-to/util" />
import * as ts from 'typescript';
export declare function migrateLiteral(node: ts.ObjectLiteralExpression): ts.ObjectLiteralExpression;
export declare function findLiteralsToMigrate(sourceFile: ts.SourceFile): Set<ts.ObjectLiteralExpression>;
