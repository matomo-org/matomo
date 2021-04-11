/// <amd-module name="@angular/core/schematics/migrations/initial-navigation/collector" />
/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
import * as ts from 'typescript';
/**
 * Visitor that walks through specified TypeScript nodes and collects all
 * found ExtraOptions#InitialNavigation assignments.
 */
export declare class InitialNavigationCollector {
    private readonly typeChecker;
    assignments: Set<ts.PropertyAssignment>;
    constructor(typeChecker: ts.TypeChecker);
    visitNode(node: ts.Node): void;
    visitExtraOptionsLiteral(extraOptionsLiteral: ts.ObjectLiteralExpression): void;
    private getLiteralNeedingMigrationFromIdentifier;
    private getLiteralNeedingMigration;
}
