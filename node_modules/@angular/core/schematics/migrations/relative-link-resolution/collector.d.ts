/// <amd-module name="@angular/core/schematics/migrations/relative-link-resolution/collector" />
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
 * found ExtraOptions#RelativeLinkResolution assignments.
 */
export declare class RelativeLinkResolutionCollector {
    private readonly typeChecker;
    readonly forRootCalls: ts.CallExpression[];
    readonly extraOptionsLiterals: ts.ObjectLiteralExpression[];
    constructor(typeChecker: ts.TypeChecker);
    visitNode(node: ts.Node): void;
    private getLiteralNeedingMigrationFromIdentifier;
    private getLiteralNeedingMigration;
}
