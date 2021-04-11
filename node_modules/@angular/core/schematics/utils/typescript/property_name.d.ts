/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
/// <amd-module name="@angular/core/schematics/utils/typescript/property_name" />
import * as ts from 'typescript';
/** Type that describes a property name with an obtainable text. */
declare type PropertyNameWithText = Exclude<ts.PropertyName, ts.ComputedPropertyName>;
/**
 * Gets the text of the given property name. Returns null if the property
 * name couldn't be determined statically.
 */
export declare function getPropertyNameText(node: ts.PropertyName): string | null;
/** Checks whether the given property name has a text. */
export declare function hasPropertyNameText(node: ts.PropertyName): node is PropertyNameWithText;
export {};
