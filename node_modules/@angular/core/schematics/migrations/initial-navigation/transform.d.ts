/// <amd-module name="@angular/core/schematics/migrations/initial-navigation/transform" />
/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
import * as ts from 'typescript';
import { UpdateRecorder } from './update_recorder';
export declare class InitialNavigationTransform {
    private getUpdateRecorder;
    private printer;
    constructor(getUpdateRecorder: (sf: ts.SourceFile) => UpdateRecorder);
    /** Migrate the ExtraOptions#InitialNavigation property assignments. */
    migrateInitialNavigationAssignments(literals: ts.PropertyAssignment[]): void;
    /** Migrate an ExtraOptions#InitialNavigation expression to use the new options format. */
    migrateAssignment(assignment: ts.PropertyAssignment): void;
    private _updateNode;
}
