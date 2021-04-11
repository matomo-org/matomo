/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
/// <amd-module name="@angular/core/schematics/utils/import_manager" />
import * as ts from 'typescript';
/** Update recorder for managing imports. */
export interface ImportManagerUpdateRecorder {
    addNewImport(start: number, importText: string): void;
    updateExistingImport(namedBindings: ts.NamedImports, newNamedBindings: string): void;
}
/**
 * Import manager that can be used to add TypeScript imports to given source
 * files. The manager ensures that multiple transformations are applied properly
 * without shifted offsets and that similar existing import declarations are re-used.
 */
export declare class ImportManager {
    private getUpdateRecorder;
    private printer;
    /** Map of import declarations that need to be updated to include the given symbols. */
    private updatedImports;
    /** Map of source-files and their previously used identifier names. */
    private usedIdentifierNames;
    /**
     * Array of previously resolved symbol imports. Cache can be re-used to return
     * the same identifier without checking the source-file again.
     */
    private importCache;
    constructor(getUpdateRecorder: (sf: ts.SourceFile) => ImportManagerUpdateRecorder, printer: ts.Printer);
    /**
     * Adds an import to the given source-file and returns the TypeScript
     * identifier that can be used to access the newly imported symbol.
     */
    addImportToSourceFile(sourceFile: ts.SourceFile, symbolName: string | null, moduleName: string, typeImport?: boolean): ts.Expression;
    /**
     * Stores the collected import changes within the appropriate update recorders. The
     * updated imports can only be updated *once* per source-file because previous updates
     * could otherwise shift the source-file offsets.
     */
    recordChanges(): void;
    /** Gets an unique identifier with a base name for the given source file. */
    private _getUniqueIdentifier;
    /**
     * Checks whether the specified identifier name is used within the given
     * source file.
     */
    private isUniqueIdentifierName;
    private _recordUsedIdentifier;
    /**
     * Determines the full end of a given node. By default the end position of a node is
     * before all trailing comments. This could mean that generated imports shift comments.
     */
    private _getEndPositionOfNode;
}
