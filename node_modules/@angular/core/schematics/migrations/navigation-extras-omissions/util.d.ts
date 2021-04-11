/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
/// <amd-module name="@angular/core/schematics/migrations/navigation-extras-omissions/util" />
import * as ts from 'typescript';
export declare function migrateLiteral(methodName: string, node: ts.ObjectLiteralExpression): ts.ObjectLiteralExpression;
export declare function findLiteralsToMigrate(sourceFile: ts.SourceFile, typeChecker: ts.TypeChecker): Map<string, Set<ts.ObjectLiteralExpression>>;
