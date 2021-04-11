/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
/// <amd-module name="@angular/core/schematics/migrations/renderer-to-renderer2/helpers" />
import * as ts from 'typescript';
/** Names of the helper functions that are supported for this migration. */
export declare const enum HelperFunction {
    any = "AnyDuringRendererMigration",
    createElement = "__ngRendererCreateElementHelper",
    createText = "__ngRendererCreateTextHelper",
    createTemplateAnchor = "__ngRendererCreateTemplateAnchorHelper",
    projectNodes = "__ngRendererProjectNodesHelper",
    animate = "__ngRendererAnimateHelper",
    destroyView = "__ngRendererDestroyViewHelper",
    detachView = "__ngRendererDetachViewHelper",
    attachViewAfter = "__ngRendererAttachViewAfterHelper",
    splitNamespace = "__ngRendererSplitNamespaceHelper",
    setElementAttribute = "__ngRendererSetElementAttributeHelper"
}
/** Gets the string representation of a helper function. */
export declare function getHelper(name: HelperFunction, sourceFile: ts.SourceFile, printer: ts.Printer): string;
