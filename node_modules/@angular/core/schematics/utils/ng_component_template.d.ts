/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
/// <amd-module name="@angular/core/schematics/utils/ng_component_template" />
import * as ts from 'typescript';
export interface ResolvedTemplate {
    /** Class declaration that contains this template. */
    container: ts.ClassDeclaration;
    /** File content of the given template. */
    content: string;
    /** Start offset of the template content (e.g. in the inline source file) */
    start: number;
    /** Whether the given template is inline or not. */
    inline: boolean;
    /** Path to the file that contains this template. */
    filePath: string;
    /**
     * Gets the character and line of a given position index in the template.
     * If the template is declared inline within a TypeScript source file, the line and
     * character are based on the full source file content.
     */
    getCharacterAndLineOfPosition: (pos: number) => {
        character: number;
        line: number;
    };
}
/**
 * Visitor that can be used to determine Angular templates referenced within given
 * TypeScript source files (inline templates or external referenced templates)
 */
export declare class NgComponentTemplateVisitor {
    typeChecker: ts.TypeChecker;
    resolvedTemplates: ResolvedTemplate[];
    constructor(typeChecker: ts.TypeChecker);
    visitNode(node: ts.Node): void;
    private visitClassDeclaration;
}
