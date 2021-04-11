/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
/// <amd-module name="@angular/core/schematics/migrations/template-var-assignment/analyze_template" />
import { PropertyWrite } from '@angular/compiler';
import { ResolvedTemplate } from '../../utils/ng_component_template';
export interface TemplateVariableAssignment {
    node: PropertyWrite;
    start: number;
    end: number;
}
/**
 * Analyzes a given resolved template by looking for property assignments to local
 * template variables within bound events.
 */
export declare function analyzeResolvedTemplate(template: ResolvedTemplate): TemplateVariableAssignment[] | null;
