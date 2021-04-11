/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
/// <amd-module name="@angular/core/schematics/utils/parse_html" />
import { Node } from '@angular/compiler/src/render3/r3_ast';
/**
 * Parses the given HTML content using the Angular compiler. In case the parsing
 * fails, null is being returned.
 */
export declare function parseHtmlGracefully(htmlContent: string, filePath: string): Node[] | null;
