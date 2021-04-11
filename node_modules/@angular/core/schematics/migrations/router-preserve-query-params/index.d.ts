/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
/// <amd-module name="@angular/core/schematics/migrations/router-preserve-query-params" />
import { Rule } from '@angular-devkit/schematics';
/**
 * Migration that switches `NavigationExtras.preserveQueryParams` to set the coresponding value via
 * `NavigationExtras`'s `queryParamsHandling` attribute.
 */
export default function (): Rule;
