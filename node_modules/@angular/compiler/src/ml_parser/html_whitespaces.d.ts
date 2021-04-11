/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
import * as html from './ast';
import { ParseTreeResult } from './parser';
export declare const PRESERVE_WS_ATTR_NAME = "ngPreserveWhitespaces";
/**
 * Angular Dart introduced &ngsp; as a placeholder for non-removable space, see:
 * https://github.com/dart-lang/angular/blob/0bb611387d29d65b5af7f9d2515ab571fd3fbee4/_tests/test/compiler/preserve_whitespace_test.dart#L25-L32
 * In Angular Dart &ngsp; is converted to the 0xE500 PUA (Private Use Areas) unicode character
 * and later on replaced by a space. We are re-implementing the same idea here.
 */
export declare function replaceNgsp(value: string): string;
/**
 * This visitor can walk HTML parse tree and remove / trim text nodes using the following rules:
 * - consider spaces, tabs and new lines as whitespace characters;
 * - drop text nodes consisting of whitespace characters only;
 * - for all other text nodes replace consecutive whitespace characters with one space;
 * - convert &ngsp; pseudo-entity to a single space;
 *
 * Removal and trimming of whitespaces have positive performance impact (less code to generate
 * while compiling templates, faster view creation). At the same time it can be "destructive"
 * in some cases (whitespaces can influence layout). Because of the potential of breaking layout
 * this visitor is not activated by default in Angular 5 and people need to explicitly opt-in for
 * whitespace removal. The default option for whitespace removal will be revisited in Angular 6
 * and might be changed to "on" by default.
 */
export declare class WhitespaceVisitor implements html.Visitor {
    visitElement(element: html.Element, context: any): any;
    visitAttribute(attribute: html.Attribute, context: any): any;
    visitText(text: html.Text, context: SiblingVisitorContext | null): any;
    visitComment(comment: html.Comment, context: any): any;
    visitExpansion(expansion: html.Expansion, context: any): any;
    visitExpansionCase(expansionCase: html.ExpansionCase, context: any): any;
}
export declare function removeWhitespaces(htmlAstWithErrors: ParseTreeResult): ParseTreeResult;
interface SiblingVisitorContext {
    prev: html.Node | undefined;
    next: html.Node | undefined;
}
export {};
