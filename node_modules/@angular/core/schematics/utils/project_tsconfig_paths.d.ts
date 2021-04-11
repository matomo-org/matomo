/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
/// <amd-module name="@angular/core/schematics/utils/project_tsconfig_paths" />
import { Tree } from '@angular-devkit/schematics';
/**
 * Gets all tsconfig paths from a CLI project by reading the workspace configuration
 * and looking for common tsconfig locations.
 */
export declare function getProjectTsConfigPaths(tree: Tree): {
    buildPaths: string[];
    testPaths: string[];
};
