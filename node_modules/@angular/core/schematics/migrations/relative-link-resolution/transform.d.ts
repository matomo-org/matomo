/// <amd-module name="@angular/core/schematics/migrations/relative-link-resolution/transform" />
/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
import * as ts from 'typescript';
import { UpdateRecorder } from './update_recorder';
export declare class RelativeLinkResolutionTransform {
    private getUpdateRecorder;
    private printer;
    constructor(getUpdateRecorder: (sf: ts.SourceFile) => UpdateRecorder);
    /** Migrate the ExtraOptions#RelativeLinkResolution property assignments. */
    migrateRouterModuleForRootCalls(calls: ts.CallExpression[]): void;
    migrateObjectLiterals(vars: ts.ObjectLiteralExpression[]): void;
    private _updateCallExpressionWithoutExtraOptions;
    private _getMigratedLiteralExpression;
    private _maybeUpdateLiteral;
    private _updateNode;
}
