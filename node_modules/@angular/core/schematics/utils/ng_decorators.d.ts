/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
/// <amd-module name="@angular/core/schematics/utils/ng_decorators" />
import * as ts from 'typescript';
export declare type CallExpressionDecorator = ts.Decorator & {
    expression: ts.CallExpression;
};
export interface NgDecorator {
    name: string;
    moduleName: string;
    node: CallExpressionDecorator;
    importNode: ts.ImportDeclaration;
}
/**
 * Gets all decorators which are imported from an Angular package (e.g. "@angular/core")
 * from a list of decorators.
 */
export declare function getAngularDecorators(typeChecker: ts.TypeChecker, decorators: ReadonlyArray<ts.Decorator>): NgDecorator[];
