/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
/// <amd-module name="@angular/core/schematics/utils/schematics_prompt" />
declare type Inquirer = typeof import('inquirer');
/** Whether prompts are currently supported. */
export declare function supportsPrompt(): boolean;
/**
 * Gets the resolved instance of "inquirer" which can be used to programmatically
 * create prompts.
 */
export declare function getInquirer(): Inquirer;
export {};
