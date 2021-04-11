/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
/// <amd-module name="@angular/core/schematics/migrations/undecorated-classes-with-di/decorator_rewrite/convert_directive_metadata" />
import { StaticSymbol } from '@angular/compiler';
import * as ts from 'typescript';
/** Error that will be thrown if an unexpected value needs to be converted. */
export declare class UnexpectedMetadataValueError extends Error {
}
/**
 * Converts a directive metadata object into a TypeScript expression. Throws
 * if metadata cannot be cleanly converted.
 */
export declare function convertDirectiveMetadataToExpression(metadata: any, resolveSymbolImport: (symbol: StaticSymbol) => string | null, createImport: (moduleName: string, name: string) => ts.Expression, convertProperty?: (key: string, value: any) => ts.Expression | null): ts.Expression;
