/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
(function (factory) {
    if (typeof module === "object" && typeof module.exports === "object") {
        var v = factory(require, exports);
        if (v !== undefined) module.exports = v;
    }
    else if (typeof define === "function" && define.amd) {
        define("@angular/core/schematics/utils/import_manager", ["require", "exports", "path", "typescript"], factory);
    }
})(function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.ImportManager = void 0;
    const path_1 = require("path");
    const ts = require("typescript");
    /**
     * Import manager that can be used to add TypeScript imports to given source
     * files. The manager ensures that multiple transformations are applied properly
     * without shifted offsets and that similar existing import declarations are re-used.
     */
    class ImportManager {
        constructor(getUpdateRecorder, printer) {
            this.getUpdateRecorder = getUpdateRecorder;
            this.printer = printer;
            /** Map of import declarations that need to be updated to include the given symbols. */
            this.updatedImports = new Map();
            /** Map of source-files and their previously used identifier names. */
            this.usedIdentifierNames = new Map();
            /**
             * Array of previously resolved symbol imports. Cache can be re-used to return
             * the same identifier without checking the source-file again.
             */
            this.importCache = [];
        }
        /**
         * Adds an import to the given source-file and returns the TypeScript
         * identifier that can be used to access the newly imported symbol.
         */
        addImportToSourceFile(sourceFile, symbolName, moduleName, typeImport = false) {
            const sourceDir = path_1.dirname(sourceFile.fileName);
            let importStartIndex = 0;
            let existingImport = null;
            // In case the given import has been already generated previously, we just return
            // the previous generated identifier in order to avoid duplicate generated imports.
            const cachedImport = this.importCache.find(c => c.sourceFile === sourceFile && c.symbolName === symbolName &&
                c.moduleName === moduleName);
            if (cachedImport) {
                return cachedImport.identifier;
            }
            // Walk through all source-file top-level statements and search for import declarations
            // that already match the specified "moduleName" and can be updated to import the
            // given symbol. If no matching import can be found, the last import in the source-file
            // will be used as starting point for a new import that will be generated.
            for (let i = sourceFile.statements.length - 1; i >= 0; i--) {
                const statement = sourceFile.statements[i];
                if (!ts.isImportDeclaration(statement) || !ts.isStringLiteral(statement.moduleSpecifier) ||
                    !statement.importClause) {
                    continue;
                }
                if (importStartIndex === 0) {
                    importStartIndex = this._getEndPositionOfNode(statement);
                }
                const moduleSpecifier = statement.moduleSpecifier.text;
                if (moduleSpecifier.startsWith('.') &&
                    path_1.resolve(sourceDir, moduleSpecifier) !== path_1.resolve(sourceDir, moduleName) ||
                    moduleSpecifier !== moduleName) {
                    continue;
                }
                if (statement.importClause.namedBindings) {
                    const namedBindings = statement.importClause.namedBindings;
                    // In case a "Type" symbol is imported, we can't use namespace imports
                    // because these only export symbols available at runtime (no types)
                    if (ts.isNamespaceImport(namedBindings) && !typeImport) {
                        return ts.createPropertyAccess(ts.createIdentifier(namedBindings.name.text), ts.createIdentifier(symbolName || 'default'));
                    }
                    else if (ts.isNamedImports(namedBindings) && symbolName) {
                        const existingElement = namedBindings.elements.find(e => e.propertyName ? e.propertyName.text === symbolName : e.name.text === symbolName);
                        if (existingElement) {
                            return ts.createIdentifier(existingElement.name.text);
                        }
                        // In case the symbol could not be found in an existing import, we
                        // keep track of the import declaration as it can be updated to include
                        // the specified symbol name without having to create a new import.
                        existingImport = statement;
                    }
                }
                else if (statement.importClause.name && !symbolName) {
                    return ts.createIdentifier(statement.importClause.name.text);
                }
            }
            if (existingImport) {
                const propertyIdentifier = ts.createIdentifier(symbolName);
                const generatedUniqueIdentifier = this._getUniqueIdentifier(sourceFile, symbolName);
                const needsGeneratedUniqueName = generatedUniqueIdentifier.text !== symbolName;
                const importName = needsGeneratedUniqueName ? generatedUniqueIdentifier : propertyIdentifier;
                // Since it can happen that multiple classes need to be imported within the
                // specified source file and we want to add the identifiers to the existing
                // import declaration, we need to keep track of the updated import declarations.
                // We can't directly update the import declaration for each identifier as this
                // would throw off the recorder offsets. We need to keep track of the new identifiers
                // for the import and perform the import transformation as batches per source-file.
                this.updatedImports.set(existingImport, (this.updatedImports.get(existingImport) || []).concat({
                    propertyName: needsGeneratedUniqueName ? propertyIdentifier : undefined,
                    importName: importName,
                }));
                // Keep track of all updated imports so that we don't generate duplicate
                // similar imports as these can't be statically analyzed in the source-file yet.
                this.importCache.push({ sourceFile, moduleName, symbolName, identifier: importName });
                return importName;
            }
            let identifier = null;
            let newImport = null;
            if (symbolName) {
                const propertyIdentifier = ts.createIdentifier(symbolName);
                const generatedUniqueIdentifier = this._getUniqueIdentifier(sourceFile, symbolName);
                const needsGeneratedUniqueName = generatedUniqueIdentifier.text !== symbolName;
                identifier = needsGeneratedUniqueName ? generatedUniqueIdentifier : propertyIdentifier;
                newImport = ts.createImportDeclaration(undefined, undefined, ts.createImportClause(undefined, ts.createNamedImports([ts.createImportSpecifier(needsGeneratedUniqueName ? propertyIdentifier : undefined, identifier)])), ts.createStringLiteral(moduleName));
            }
            else {
                identifier = this._getUniqueIdentifier(sourceFile, 'defaultExport');
                newImport = ts.createImportDeclaration(undefined, undefined, ts.createImportClause(identifier, undefined), ts.createStringLiteral(moduleName));
            }
            const newImportText = this.printer.printNode(ts.EmitHint.Unspecified, newImport, sourceFile);
            // If the import is generated at the start of the source file, we want to add
            // a new-line after the import. Otherwise if the import is generated after an
            // existing import, we need to prepend a new-line so that the import is not on
            // the same line as the existing import anchor.
            this.getUpdateRecorder(sourceFile)
                .addNewImport(importStartIndex, importStartIndex === 0 ? `${newImportText}\n` : `\n${newImportText}`);
            // Keep track of all generated imports so that we don't generate duplicate
            // similar imports as these can't be statically analyzed in the source-file yet.
            this.importCache.push({ sourceFile, symbolName, moduleName, identifier });
            return identifier;
        }
        /**
         * Stores the collected import changes within the appropriate update recorders. The
         * updated imports can only be updated *once* per source-file because previous updates
         * could otherwise shift the source-file offsets.
         */
        recordChanges() {
            this.updatedImports.forEach((expressions, importDecl) => {
                const sourceFile = importDecl.getSourceFile();
                const recorder = this.getUpdateRecorder(sourceFile);
                const namedBindings = importDecl.importClause.namedBindings;
                const newNamedBindings = ts.updateNamedImports(namedBindings, namedBindings.elements.concat(expressions.map(({ propertyName, importName }) => ts.createImportSpecifier(propertyName, importName))));
                const newNamedBindingsText = this.printer.printNode(ts.EmitHint.Unspecified, newNamedBindings, sourceFile);
                recorder.updateExistingImport(namedBindings, newNamedBindingsText);
            });
        }
        /** Gets an unique identifier with a base name for the given source file. */
        _getUniqueIdentifier(sourceFile, baseName) {
            if (this.isUniqueIdentifierName(sourceFile, baseName)) {
                this._recordUsedIdentifier(sourceFile, baseName);
                return ts.createIdentifier(baseName);
            }
            let name = null;
            let counter = 1;
            do {
                name = `${baseName}_${counter++}`;
            } while (!this.isUniqueIdentifierName(sourceFile, name));
            this._recordUsedIdentifier(sourceFile, name);
            return ts.createIdentifier(name);
        }
        /**
         * Checks whether the specified identifier name is used within the given
         * source file.
         */
        isUniqueIdentifierName(sourceFile, name) {
            if (this.usedIdentifierNames.has(sourceFile) &&
                this.usedIdentifierNames.get(sourceFile).indexOf(name) !== -1) {
                return false;
            }
            // Walk through the source file and search for an identifier matching
            // the given name. In that case, it's not guaranteed that this name
            // is unique in the given declaration scope and we just return false.
            const nodeQueue = [sourceFile];
            while (nodeQueue.length) {
                const node = nodeQueue.shift();
                if (ts.isIdentifier(node) && node.text === name) {
                    return false;
                }
                nodeQueue.push(...node.getChildren());
            }
            return true;
        }
        _recordUsedIdentifier(sourceFile, identifierName) {
            this.usedIdentifierNames.set(sourceFile, (this.usedIdentifierNames.get(sourceFile) || []).concat(identifierName));
        }
        /**
         * Determines the full end of a given node. By default the end position of a node is
         * before all trailing comments. This could mean that generated imports shift comments.
         */
        _getEndPositionOfNode(node) {
            const nodeEndPos = node.getEnd();
            const commentRanges = ts.getTrailingCommentRanges(node.getSourceFile().text, nodeEndPos);
            if (!commentRanges || !commentRanges.length) {
                return nodeEndPos;
            }
            return commentRanges[commentRanges.length - 1].end;
        }
    }
    exports.ImportManager = ImportManager;
});
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiaW1wb3J0X21hbmFnZXIuanMiLCJzb3VyY2VSb290IjoiIiwic291cmNlcyI6WyIuLi8uLi8uLi8uLi8uLi8uLi8uLi9wYWNrYWdlcy9jb3JlL3NjaGVtYXRpY3MvdXRpbHMvaW1wb3J0X21hbmFnZXIudHMiXSwibmFtZXMiOltdLCJtYXBwaW5ncyI6IkFBQUE7Ozs7OztHQU1HOzs7Ozs7Ozs7Ozs7O0lBRUgsK0JBQXNDO0lBQ3RDLGlDQUFpQztJQVFqQzs7OztPQUlHO0lBQ0gsTUFBYSxhQUFhO1FBaUJ4QixZQUNZLGlCQUFxRSxFQUNyRSxPQUFtQjtZQURuQixzQkFBaUIsR0FBakIsaUJBQWlCLENBQW9EO1lBQ3JFLFlBQU8sR0FBUCxPQUFPLENBQVk7WUFsQi9CLHVGQUF1RjtZQUMvRSxtQkFBYyxHQUNsQixJQUFJLEdBQUcsRUFBcUYsQ0FBQztZQUNqRyxzRUFBc0U7WUFDOUQsd0JBQW1CLEdBQUcsSUFBSSxHQUFHLEVBQTJCLENBQUM7WUFDakU7OztlQUdHO1lBQ0ssZ0JBQVcsR0FLYixFQUFFLENBQUM7UUFJeUIsQ0FBQztRQUVuQzs7O1dBR0c7UUFDSCxxQkFBcUIsQ0FDakIsVUFBeUIsRUFBRSxVQUF1QixFQUFFLFVBQWtCLEVBQ3RFLFVBQVUsR0FBRyxLQUFLO1lBQ3BCLE1BQU0sU0FBUyxHQUFHLGNBQU8sQ0FBQyxVQUFVLENBQUMsUUFBUSxDQUFDLENBQUM7WUFDL0MsSUFBSSxnQkFBZ0IsR0FBRyxDQUFDLENBQUM7WUFDekIsSUFBSSxjQUFjLEdBQThCLElBQUksQ0FBQztZQUVyRCxpRkFBaUY7WUFDakYsbUZBQW1GO1lBQ25GLE1BQU0sWUFBWSxHQUFHLElBQUksQ0FBQyxXQUFXLENBQUMsSUFBSSxDQUN0QyxDQUFDLENBQUMsRUFBRSxDQUFDLENBQUMsQ0FBQyxVQUFVLEtBQUssVUFBVSxJQUFJLENBQUMsQ0FBQyxVQUFVLEtBQUssVUFBVTtnQkFDM0QsQ0FBQyxDQUFDLFVBQVUsS0FBSyxVQUFVLENBQUMsQ0FBQztZQUNyQyxJQUFJLFlBQVks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Q0FBQzthQUN2QztZQUNELE9BQU8sSUFBSSxDQUFDO1FBQ2QsQ0FBQztRQUVPLHFCQUFxQixDQUFDLFVBQXlCLEVBQUUsY0FBc0I7WUFDN0UsSUFBSSxDQUFDLG1CQUFtQixDQUFDLEdBQUcsQ0FDeEIsVUFBVSxFQUFFLENBQUMsSUFBSSxDQUFDLG1CQUFtQixDQUFDLEdBQUcsQ0FBQyxVQUFVLENBQUMsSUFBSSxFQUFFLENBQUMsQ0FBQyxNQUFNLENBQUMsY0FBYyxDQUFDLENBQUMsQ0FBQztRQUMzRixDQUFDO1FBRUQ7OztXQUdHO1FBQ0sscUJBQXFCLENBQUMsSUFBYTtZQUN6QyxNQUFNLFVBQVUsR0FBRyxJQUFJLENBQUMsTUFBTSxFQUFFLENBQUM7WUFDakMsTUFBTSxhQUFhLEdBQUcsRUFBRSxDQUFDLHdCQUF3QixDQUFDLElBQUksQ0FBQyxhQUFhLEVBQUUsQ0FBQyxJQUFJLEVBQUUsVUFBVSxDQUFDLENBQUM7WUFDekYsSUFBSSxDQUFDLGFBQWEsSUFBSSxDQUFDLGFBQWEsQ0FBQyxNQUFNLEVBQUU7Z0JBQzNDLE9BQU8sVUFBVSxDQUFDO2FBQ25CO1lBQ0QsT0FBTyxhQUFhLENBQUMsYUFBYSxDQUFDLE1BQU0sR0FBRyxDQUFDLENBQUUsQ0FBQyxHQUFHLENBQUM7UUFDdEQsQ0FBQztLQUNGO0lBNU9ELHNDQTRPQyIsInNvdXJjZXNDb250ZW50IjpbIi8qKlxuICogQGxpY2Vuc2VcbiAqIENvcHlyaWdodCBHb29nbGUgTExDIEFsbCBSaWdodHMgUmVzZXJ2ZWQuXG4gKlxuICogVXNlIG9mIHRoaXMgc291cmNlIGNvZGUgaXMgZ292ZXJuZWQgYnkgYW4gTUlULXN0eWxlIGxpY2Vuc2UgdGhhdCBjYW4gYmVcbiAqIGZvdW5kIGluIHRoZSBMSUNFTlNFIGZpbGUgYXQgaHR0cHM6Ly9hbmd1bGFyLmlvL2xpY2Vuc2VcbiAqL1xuXG5pbXBvcnQge2Rpcm5hbWUsIHJlc29sdmV9IGZyb20gJ3BhdGgnO1xuaW1wb3J0ICogYXMgdHMgZnJvbSAndHlwZXNjcmlwdCc7XG5cbi8qKiBVcGRhdGUgcmVjb3JkZXIgZm9yIG1hbmFnaW5nIGltcG9ydHMuICovXG5leHBvcnQgaW50ZXJmYWNlIEltcG9ydE1hbmFnZXJVcGRhdGVSZWNvcmRlciB7XG4gIGFkZE5ld0ltcG9ydChzdGFydDogbnVtYmVyLCBpbXBvcnRUZXh0OiBzdHJpbmcpOiB2b2lkO1xuICB1cGRhdGVFeGlzdGluZ0ltcG9ydChuYW1lZEJpbmRpbmdzOiB0cy5OYW1lZEltcG9ydHMsIG5ld05hbWVkQmluZGluZ3M6IHN0cmluZyk6IHZvaWQ7XG59XG5cbi8qKlxuICogSW1wb3J0IG1hbmFnZXIgdGhhdCBjYW4gYmUgdXNlZCB0byBhZGQgVHlwZVNjcmlwdCBpbXBvcnRzIHRvIGdpdmVuIHNvdXJjZVxuICogZmlsZXMuIFRoZSBtYW5hZ2VyIGVuc3VyZXMgdGhhdCBtdWx0aXBsZSB0cmFuc2Zvcm1hdGlvbnMgYXJlIGFwcGxpZWQgcHJvcGVybHlcbiAqIHdpdGhvdXQgc2hpZnRlZCBvZmZzZXRzIGFuZCB0aGF0IHNpbWlsYXIgZXhpc3RpbmcgaW1wb3J0IGRlY2xhcmF0aW9ucyBhcmUgcmUtdXNlZC5cbiAqL1xuZXhwb3J0IGNsYXNzIEltcG9ydE1hbmFnZXIge1xuICAvKiogTWFwIG9mIGltcG9ydCBkZWNsYXJhdGlvbnMgdGhhdCBuZWVkIHRvIGJlIHVwZGF0ZWQgdG8gaW5jbHVkZSB0aGUgZ2l2ZW4gc3ltYm9scy4gKi9cbiAgcHJpdmF0ZSB1cGRhdGVkSW1wb3J0cyA9XG4gICAgICBuZXcgTWFwPHRzLkltcG9ydERlY2xhcmF0aW9uLCB7cHJvcGVydHlOYW1lPzogdHMuSWRlbnRpZmllciwgaW1wb3J0TmFtZTogdHMuSWRlbnRpZmllcn1bXT4oKTtcbiAgLyoqIE1hcCBvZiBzb3VyY2UtZmlsZXMgYW5kIHRoZWlyIHByZXZpb3VzbHkgdXNlZCBpZGVudGlmaWVyIG5hbWVzLiAqL1xuICBwcml2YXRlIHVzZWRJZGVudGlmaWVyTmFtZXMgPSBuZXcgTWFwPHRzLlNvdXJjZUZpbGUsIHN0cmluZ1tdPigpO1xuICAvKipcbiAgICogQXJyYXkgb2YgcHJldmlvdXNseSByZXNvbHZlZCBzeW1ib2wgaW1wb3J0cy4gQ2FjaGUgY2FuIGJlIHJlLXVzZWQgdG8gcmV0dXJuXG4gICAqIHRoZSBzYW1lIGlkZW50aWZpZXIgd2l0aG91dCBjaGVja2luZyB0aGUgc291cmNlLWZpbGUgYWdhaW4uXG4gICAqL1xuICBwcml2YXRlIGltcG9ydENhY2hlOiB7XG4gICAgc291cmNlRmlsZTogdHMuU291cmNlRmlsZSxcbiAgICBzeW1ib2xOYW1lOiBzdHJpbmd8bnVsbCxcbiAgICBtb2R1bGVOYW1lOiBzdHJpbmcsXG4gICAgaWRlbnRpZmllcjogdHMuSWRlbnRpZmllclxuICB9W10gPSBbXTtcblxuICBjb25zdHJ1Y3RvcihcbiAgICAgIHByaXZhdGUgZ2V0VXBkYXRlUmVjb3JkZXI6IChzZjogdHMuU291cmNlRmlsZSkgPT4gSW1wb3J0TWFuYWdlclVwZGF0ZVJlY29yZGVyLFxuICAgICAgcHJpdmF0ZSBwcmludGVyOiB0cy5QcmludGVyKSB7fVxuXG4gIC8qKlxuICAgKiBBZGRzIGFuIGltcG9ydCB0byB0aGUgZ2l2ZW4gc291cmNlLWZpbGUgYW5kIHJldHVybnMgdGhlIFR5cGVTY3JpcHRcbiAgICogaWRlbnRpZmllciB0aGF0IGNhbiBiZSB1c2VkIHRvIGFjY2VzcyB0aGUgbmV3bHkgaW1wb3J0ZWQgc3ltYm9sLlxuICAgKi9cbiAgYWRkSW1wb3J0VG9Tb3VyY2VGaWxlKFxuICAgICAgc291cmNlRmlsZTogdHMuU291cmNlRmlsZSwgc3ltYm9sTmFtZTogc3RyaW5nfG51bGwsIG1vZHVsZU5hbWU6IHN0cmluZyxcbiAgICAgIHR5cGVJbXBvcnQgPSBmYWxzZSk6IHRzLkV4cHJlc3Npb24ge1xuICAgIGNvbnN0IHNvdXJjZURpciA9IGRpcm5hbWUoc291cmNlRmlsZS5maWxlTmFtZSk7XG4gICAgbGV0IGltcG9ydFN0YXJ0SW5kZXggPSAwO1xuICAgIGxldCBleGlzdGluZ0ltcG9ydDogdHMuSW1wb3J0RGVjbGFyYXRpb258bnVsbCA9IG51bGw7XG5cbiAgICAvLyBJbiBjYXNlIHRoZSBnaXZlbiBpbXBvcnQgaGFzIGJlZW4gYWxyZWFkeSBnZW5lcmF0ZWQgcHJldmlvdXNseSwgd2UganVzdCByZXR1cm5cbiAgICAvLyB0aGUgcHJldmlvdXMgZ2VuZXJhdGVkIGlkZW50aWZpZXIgaW4gb3JkZXIgdG8gYXZvaWQgZHVwbGljYXRlIGdlbmVyYXRlZCBpbXBvcnRzLlxuICAgIGNvbnN0IGNhY2hlZEltcG9ydCA9IHRoaXMuaW1wb3J0Q2FjaGUuZmluZChcbiAgICAgICAgYyA9PiBjLnNvdXJjZUZpbGUgPT09IHNvdXJjZUZpbGUgJiYgYy5zeW1ib2xOYW1lID09PSBzeW1ib2xOYW1lICYmXG4gICAgICAgICAgICBjLm1vZHVsZU5hbWUgPT09IG1vZHVsZU5hbWUpO1xuICAgIGlmIChjYWNoZWRJbXBvcnQpIHtcbiAgICAgIHJldHVybiBjYWNoZWRJbXBvcnQuaWRlbnRpZmllcjtcbiAgICB9XG5cbiAgICAvLyBXYWxrIHRocm91Z2ggYWxsIHNvdXJjZS1maWxlIHRvcC1sZXZlbCBzdGF0ZW1lbnRzIGFuZCBzZWFyY2ggZm9yIGltcG9ydCBkZWNsYXJhdGlvbnNcbiAgICAvLyB0aGF0IGFscmVhZHkgbWF0Y2ggdGhlIHNwZWNpZmllZCBcIm1vZHVsZU5hbWVcIiBhbmQgY2FuIGJlIHVwZGF0ZWQgdG8gaW1wb3J0IHRoZVxuICAgIC8vIGdpdmVuIHN5bWJvbC4gSWYgbm8gbWF0Y2hpbmcgaW1wb3J0IGNhbiBiZSBmb3VuZCwgdGhlIGxhc3QgaW1wb3J0IGluIHRoZSBzb3VyY2UtZmlsZVxuICAgIC8vIHdpbGwgYmUgdXNlZCBhcyBzdGFydGluZyBwb2ludCBmb3IgYSBuZXcgaW1wb3J0IHRoYXQgd2lsbCBiZSBnZW5lcmF0ZWQuXG4gICAgZm9yIChsZXQgaSA9IHNvdXJjZUZpbGUuc3RhdGVtZW50cy5sZW5ndGggLSAxOyBpID49IDA7IGktLSkge1xuICAgICAgY29uc3Qgc3RhdGVtZW50ID0gc291cmNlRmlsZS5zdGF0ZW1lbnRzW2ldO1xuXG4gICAgICBpZiAoIXRzLmlzSW1wb3J0RGVjbGFyYXRpb24oc3RhdGVtZW50KSB8fCAhdHMuaXNTdHJpbmdMaXRlcmFsKHN0YXRlbWVudC5tb2R1bGVTcGVjaWZpZXIpIHx8XG4gICAgICAgICAgIXN0YXRlbWVudC5pbXBvcnRDbGF1c2UpIHtcbiAgICAgICAgY29udGludWU7XG4gICAgICB9XG5cbiAgICAgIGlmIChpbXBvcnRTdGFydEluZGV4ID09PSAwKSB7XG4gICAgICAgIGltcG9ydFN0YXJ0SW5kZXggPSB0aGlzLl9nZXRFbmRQb3NpdGlvbk9mTm9kZShzdGF0ZW1lbnQpO1xuICAgICAgfVxuXG4gICAgICBjb25zdCBtb2R1bGVTcGVjaWZpZXIgPSBzdGF0ZW1lbnQubW9kdWxlU3BlY2lmaWVyLnRleHQ7XG5cbiAgICAgIGlmIChtb2R1bGVTcGVjaWZpZXIuc3RhcnRzV2l0aCgnLicpICYmXG4gICAgICAgICAgICAgIHJlc29sdmUoc291cmNlRGlyLCBtb2R1bGVTcGVjaWZpZXIpICE9PSByZXNvbHZlKHNvdXJjZURpciwgbW9kdWxlTmFtZSkgfHxcbiAgICAgICAgICBtb2R1bGVTcGVjaWZpZXIgIT09IG1vZHVsZU5hbWUpIHtcbiAgICAgICAgY29udGludWU7XG4gICAgICB9XG5cbiAgICAgIGlmIChzdGF0ZW1lbnQuaW1wb3J0Q2xhdXNlLm5hbWVkQmluZGluZ3MpIHtcbiAgICAgICAgY29uc3QgbmFtZWRCaW5kaW5ncyA9IHN0YXRlbWVudC5pbXBvcnRDbGF1c2UubmFtZWRCaW5kaW5ncztcblxuICAgICAgICAvLyBJbiBjYXNlIGEgXCJUeXBlXCIgc3ltYm9sIGlzIGltcG9ydGVkLCB3ZSBjYW4ndCB1c2UgbmFtZXNwYWNlIGltcG9ydHNcbiAgICAgICAgLy8gYmVjYXVzZSB0aGVzZSBvbmx5IGV4cG9ydCBzeW1ib2xzIGF2YWlsYWJsZSBhdCBydW50aW1lIChubyB0eXBlcylcbiAgICAgICAgaWYgKHRzLmlzTmFtZXNwYWNlSW1wb3J0KG5hbWVkQmluZGluZ3MpICYmICF0eXBlSW1wb3J0KSB7XG4gICAgICAgICAgcmV0dXJuIHRzLmNyZWF0ZVByb3BlcnR5QWNjZXNzKFxuICAgICAgICAgICAgICB0cy5jcmVhdGVJZGVudGlmaWVyKG5hbWVkQmluZGluZ3MubmFtZS50ZXh0KSxcbiAgICAgICAgICAgICAgdHMuY3JlYXRlSWRlbnRpZmllcihzeW1ib2xOYW1lIHx8ICdkZWZhdWx0JykpO1xuICAgICAgICB9IGVsc2UgaWYgKHRzLmlzTmFtZWRJbXBvcnRzKG5hbWVkQmluZGluZ3MpICYmIHN5bWJvbE5hbWUpIHtcbiAgICAgICAgICBjb25zdCBleGlzdGluZ0VsZW1lbnQgPSBuYW1lZEJpbmRpbmdzLmVsZW1lbnRzLmZpbmQoXG4gICAgICAgICAgICAgIGUgPT5cbiAgICAgICAgICAgICAgICAgIGUucHJvcGVydHlOYW1lID8gZS5wcm9wZXJ0eU5hbWUudGV4dCA9PT0gc3ltYm9sTmFtZSA6IGUubmFtZS50ZXh0ID09PSBzeW1ib2xOYW1lKTtcblxuICAgICAgICAgIGlmIChleGlzdGluZ0VsZW1lbnQpIHtcbiAgICAgICAgICAgIHJldHVybiB0cy5jcmVhdGVJZGVudGlmaWVyKGV4aXN0aW5nRWxlbWVudC5uYW1lLnRleHQpO1xuICAgICAgICAgIH1cblxuICAgICAgICAgIC8vIEluIGNhc2UgdGhlIHN5bWJvbCBjb3VsZCBub3QgYmUgZm91bmQgaW4gYW4gZXhpc3RpbmcgaW1wb3J0LCB3ZVxuICAgICAgICAgIC8vIGtlZXAgdHJhY2sgb2YgdGhlIGltcG9ydCBkZWNsYXJhdGlvbiBhcyBpdCBjYW4gYmUgdXBkYXRlZCB0byBpbmNsdWRlXG4gICAgICAgICAgLy8gdGhlIHNwZWNpZmllZCBzeW1ib2wgbmFtZSB3aXRob3V0IGhhdmluZyB0byBjcmVhdGUgYSBuZXcgaW1wb3J0LlxuICAgICAgICAgIGV4aXN0aW5nSW1wb3J0ID0gc3RhdGVtZW50O1xuICAgICAgICB9XG4gICAgICB9IGVsc2UgaWYgKHN0YXRlbWVudC5pbXBvcnRDbGF1c2UubmFtZSAmJiAhc3ltYm9sTmFtZSkge1xuICAgICAgICByZXR1cm4gdHMuY3JlYXRlSWRlbnRpZmllcihzdGF0ZW1lbnQuaW1wb3J0Q2xhdXNlLm5hbWUudGV4dCk7XG4gICAgICB9XG4gICAgfVxuXG4gICAgaWYgKGV4aXN0aW5nSW1wb3J0KSB7XG4gICAgICBjb25zdCBwcm9wZXJ0eUlkZW50aWZpZXIgPSB0cy5jcmVhdGVJZGVudGlmaWVyKHN5bWJvbE5hbWUhKTtcbiAgICAgIGNvbnN0IGdlbmVyYXRlZFVuaXF1ZUlkZW50aWZpZXIgPSB0aGlzLl9nZXRVbmlxdWVJZGVudGlmaWVyKHNvdXJjZUZpbGUsIHN5bWJvbE5hbWUhKTtcbiAgICAgIGNvbnN0IG5lZWRzR2VuZXJhdGVkVW5pcXVlTmFtZSA9IGdlbmVyYXRlZFVuaXF1ZUlkZW50aWZpZXIudGV4dCAhPT0gc3ltYm9sTmFtZTtcbiAgICAgIGNvbnN0IGltcG9ydE5hbWUgPSBuZWVkc0dlbmVyYXRlZFVuaXF1ZU5hbWUgPyBnZW5lcmF0ZWRVbmlxdWVJZGVudGlmaWVyIDogcHJvcGVydHlJZGVudGlmaWVyO1xuXG4gICAgICAvLyBTaW5jZSBpdCBjYW4gaGFwcGVuIHRoYXQgbXVsdGlwbGUgY2xhc3NlcyBuZWVkIHRvIGJlIGltcG9ydGVkIHdpdGhpbiB0aGVcbiAgICAgIC8vIHNwZWNpZmllZCBzb3VyY2UgZmlsZSBhbmQgd2Ugd2FudCB0byBhZGQgdGhlIGlkZW50aWZpZXJzIHRvIHRoZSBleGlzdGluZ1xuICAgICAgLy8gaW1wb3J0IGRlY2xhcmF0aW9uLCB3ZSBuZWVkIHRvIGtlZXAgdHJhY2sgb2YgdGhlIHVwZGF0ZWQgaW1wb3J0IGRlY2xhcmF0aW9ucy5cbiAgICAgIC8vIFdlIGNhbid0IGRpcmVjdGx5IHVwZGF0ZSB0aGUgaW1wb3J0IGRlY2xhcmF0aW9uIGZvciBlYWNoIGlkZW50aWZpZXIgYXMgdGhpc1xuICAgICAgLy8gd291bGQgdGhyb3cgb2ZmIHRoZSByZWNvcmRlciBvZmZzZXRzLiBXZSBuZWVkIHRvIGtlZXAgdHJhY2sgb2YgdGhlIG5ldyBpZGVudGlmaWVyc1xuICAgICAgLy8gZm9yIHRoZSBpbXBvcnQgYW5kIHBlcmZvcm0gdGhlIGltcG9ydCB0cmFuc2Zvcm1hdGlvbiBhcyBiYXRjaGVzIHBlciBzb3VyY2UtZmlsZS5cbiAgICAgIHRoaXMudXBkYXRlZEltcG9ydHMuc2V0KFxuICAgICAgICAgIGV4aXN0aW5nSW1wb3J0LCAodGhpcy51cGRhdGVkSW1wb3J0cy5nZXQoZXhpc3RpbmdJbXBvcnQpIHx8IFtdKS5jb25jYXQoe1xuICAgICAgICAgICAgcHJvcGVydHlOYW1lOiBuZWVkc0dlbmVyYXRlZFVuaXF1ZU5hbWUgPyBwcm9wZXJ0eUlkZW50aWZpZXIgOiB1bmRlZmluZWQsXG4gICAgICAgICAgICBpbXBvcnROYW1lOiBpbXBvcnROYW1lLFxuICAgICAgICAgIH0pKTtcblxuICAgICAgLy8gS2VlcCB0cmFjayBvZiBhbGwgdXBkYXRlZCBpbXBvcnRzIHNvIHRoYXQgd2UgZG9uJ3QgZ2VuZXJhdGUgZHVwbGljYXRlXG4gICAgICAvLyBzaW1pbGFyIGltcG9ydHMgYXMgdGhlc2UgY2FuJ3QgYmUgc3RhdGljYWxseSBhbmFseXplZCBpbiB0aGUgc291cmNlLWZpbGUgeWV0LlxuICAgICAgdGhpcy5pbXBvcnRDYWNoZS5wdXNoKHtzb3VyY2VGaWxlLCBtb2R1bGVOYW1lLCBzeW1ib2xOYW1lLCBpZGVudGlmaWVyOiBpbXBvcnROYW1lfSk7XG5cbiAgICAgIHJldHVybiBpbXBvcnROYW1lO1xuICAgIH1cblxuICAgIGxldCBpZGVudGlmaWVyOiB0cy5JZGVudGlmaWVyfG51bGwgPSBudWxsO1xuICAgIGxldCBuZXdJbXBvcnQ6IHRzLkltcG9ydERlY2xhcmF0aW9ufG51bGwgPSBudWxsO1xuXG4gICAgaWYgKHN5bWJvbE5hbWUpIHtcbiAgICAgIGNvbnN0IHByb3BlcnR5SWRlbnRpZmllciA9IHRzLmNyZWF0ZUlkZW50aWZpZXIoc3ltYm9sTmFtZSk7XG4gICAgICBjb25zdCBnZW5lcmF0ZWRVbmlxdWVJZGVudGlmaWVyID0gdGhpcy5fZ2V0VW5pcXVlSWRlbnRpZmllcihzb3VyY2VGaWxlLCBzeW1ib2xOYW1lKTtcbiAgICAgIGNvbnN0IG5lZWRzR2VuZXJhdGVkVW5pcXVlTmFtZSA9IGdlbmVyYXRlZFVuaXF1ZUlkZW50aWZpZXIudGV4dCAhPT0gc3ltYm9sTmFtZTtcbiAgICAgIGlkZW50aWZpZXIgPSBuZWVkc0dlbmVyYXRlZFVuaXF1ZU5hbWUgPyBnZW5lcmF0ZWRVbmlxdWVJZGVudGlmaWVyIDogcHJvcGVydHlJZGVudGlmaWVyO1xuXG4gICAgICBuZXdJbXBvcnQgPSB0cy5jcmVhdGVJbXBvcnREZWNsYXJhdGlvbihcbiAgICAgICAgICB1bmRlZmluZWQsIHVuZGVmaW5lZCxcbiAgICAgICAgICB0cy5jcmVhdGVJbXBvcnRDbGF1c2UoXG4gICAgICAgICAgICAgIHVuZGVmaW5lZCxcbiAgICAgICAgICAgICAgdHMuY3JlYXRlTmFtZWRJbXBvcnRzKFt0cy5jcmVhdGVJbXBvcnRTcGVjaWZpZXIoXG4gICAgICAgICAgICAgICAgICBuZWVkc0dlbmVyYXRlZFVuaXF1ZU5hbWUgPyBwcm9wZXJ0eUlkZW50aWZpZXIgOiB1bmRlZmluZWQsIGlkZW50aWZpZXIpXSkpLFxuICAgICAgICAgIHRzLmNyZWF0ZVN0cmluZ0xpdGVyYWwobW9kdWxlTmFtZSkpO1xuICAgIH0gZWxzZSB7XG4gICAgICBpZGVudGlmaWVyID0gdGhpcy5fZ2V0VW5pcXVlSWRlbnRpZmllcihzb3VyY2VGaWxlLCAnZGVmYXVsdEV4cG9ydCcpO1xuICAgICAgbmV3SW1wb3J0ID0gdHMuY3JlYXRlSW1wb3J0RGVjbGFyYXRpb24oXG4gICAgICAgICAgdW5kZWZpbmVkLCB1bmRlZmluZWQsIHRzLmNyZWF0ZUltcG9ydENsYXVzZShpZGVudGlmaWVyLCB1bmRlZmluZWQpLFxuICAgICAgICAgIHRzLmNyZWF0ZVN0cmluZ0xpdGVyYWwobW9kdWxlTmFtZSkpO1xuICAgIH1cblxuICAgIGNvbnN0IG5ld0ltcG9ydFRleHQgPSB0aGlzLnByaW50ZXIucHJpbnROb2RlKHRzLkVtaXRIaW50LlVuc3BlY2lmaWVkLCBuZXdJbXBvcnQsIHNvdXJjZUZpbGUpO1xuICAgIC8vIElmIHRoZSBpbXBvcnQgaXMgZ2VuZXJhdGVkIGF0IHRoZSBzdGFydCBvZiB0aGUgc291cmNlIGZpbGUsIHdlIHdhbnQgdG8gYWRkXG4gICAgLy8gYSBuZXctbGluZSBhZnRlciB0aGUgaW1wb3J0LiBPdGhlcndpc2UgaWYgdGhlIGltcG9ydCBpcyBnZW5lcmF0ZWQgYWZ0ZXIgYW5cbiAgICAvLyBleGlzdGluZyBpbXBvcnQsIHdlIG5lZWQgdG8gcHJlcGVuZCBhIG5ldy1saW5lIHNvIHRoYXQgdGhlIGltcG9ydCBpcyBub3Qgb25cbiAgICAvLyB0aGUgc2FtZSBsaW5lIGFzIHRoZSBleGlzdGluZyBpbXBvcnQgYW5jaG9yLlxuICAgIHRoaXMuZ2V0VXBkYXRlUmVjb3JkZXIoc291cmNlRmlsZSlcbiAgICAgICAgLmFkZE5ld0ltcG9ydChcbiAgICAgICAgICAgIGltcG9ydFN0YXJ0SW5kZXgsIGltcG9ydFN0YXJ0SW5kZXggPT09IDAgPyBgJHtuZXdJbXBvcnRUZXh0fVxcbmAgOiBgXFxuJHtuZXdJbXBvcnRUZXh0fWApO1xuXG4gICAgLy8gS2VlcCB0cmFjayBvZiBhbGwgZ2VuZXJhdGVkIGltcG9ydHMgc28gdGhhdCB3ZSBkb24ndCBnZW5lcmF0ZSBkdXBsaWNhdGVcbiAgICAvLyBzaW1pbGFyIGltcG9ydHMgYXMgdGhlc2UgY2FuJ3QgYmUgc3RhdGljYWxseSBhbmFseXplZCBpbiB0aGUgc291cmNlLWZpbGUgeWV0LlxuICAgIHRoaXMuaW1wb3J0Q2FjaGUucHVzaCh7c291cmNlRmlsZSwgc3ltYm9sTmFtZSwgbW9kdWxlTmFtZSwgaWRlbnRpZmllcn0pO1xuXG4gICAgcmV0dXJuIGlkZW50aWZpZXI7XG4gIH1cblxuICAvKipcbiAgICogU3RvcmVzIHRoZSBjb2xsZWN0ZWQgaW1wb3J0IGNoYW5nZXMgd2l0aGluIHRoZSBhcHByb3ByaWF0ZSB1cGRhdGUgcmVjb3JkZXJzLiBUaGVcbiAgICogdXBkYXRlZCBpbXBvcnRzIGNhbiBvbmx5IGJlIHVwZGF0ZWQgKm9uY2UqIHBlciBzb3VyY2UtZmlsZSBiZWNhdXNlIHByZXZpb3VzIHVwZGF0ZXNcbiAgICogY291bGQgb3RoZXJ3aXNlIHNoaWZ0IHRoZSBzb3VyY2UtZmlsZSBvZmZzZXRzLlxuICAgKi9cbiAgcmVjb3JkQ2hhbmdlcygpIHtcbiAgICB0aGlzLnVwZGF0ZWRJbXBvcnRzLmZvckVhY2goKGV4cHJlc3Npb25zLCBpbXBvcnREZWNsKSA9PiB7XG4gICAgICBjb25zdCBzb3VyY2VGaWxlID0gaW1wb3J0RGVjbC5nZXRTb3VyY2VGaWxlKCk7XG4gICAgICBjb25zdCByZWNvcmRlciA9IHRoaXMuZ2V0VXBkYXRlUmVjb3JkZXIoc291cmNlRmlsZSk7XG4gICAgICBjb25zdCBuYW1lZEJpbmRpbmdzID0gaW1wb3J0RGVjbC5pbXBvcnRDbGF1c2UhLm5hbWVkQmluZGluZ3MgYXMgdHMuTmFtZWRJbXBvcnRzO1xuICAgICAgY29uc3QgbmV3TmFtZWRCaW5kaW5ncyA9IHRzLnVwZGF0ZU5hbWVkSW1wb3J0cyhcbiAgICAgICAgICBuYW1lZEJpbmRpbmdzLFxuICAgICAgICAgIG5hbWVkQmluZGluZ3MuZWxlbWVudHMuY29uY2F0KGV4cHJlc3Npb25zLm1hcChcbiAgICAgICAgICAgICAgKHtwcm9wZXJ0eU5hbWUsIGltcG9ydE5hbWV9KSA9PiB0cy5jcmVhdGVJbXBvcnRTcGVjaWZpZXIocHJvcGVydHlOYW1lLCBpbXBvcnROYW1lKSkpKTtcblxuICAgICAgY29uc3QgbmV3TmFtZWRCaW5kaW5nc1RleHQgPVxuICAgICAgICAgIHRoaXMucHJpbnRlci5wcmludE5vZGUodHMuRW1pdEhpbnQuVW5zcGVjaWZpZWQsIG5ld05hbWVkQmluZGluZ3MsIHNvdXJjZUZpbGUpO1xuICAgICAgcmVjb3JkZXIudXBkYXRlRXhpc3RpbmdJbXBvcnQobmFtZWRCaW5kaW5ncywgbmV3TmFtZWRCaW5kaW5nc1RleHQpO1xuICAgIH0pO1xuICB9XG5cbiAgLyoqIEdldHMgYW4gdW5pcXVlIGlkZW50aWZpZXIgd2l0aCBhIGJhc2UgbmFtZSBmb3IgdGhlIGdpdmVuIHNvdXJjZSBmaWxlLiAqL1xuICBwcml2YXRlIF9nZXRVbmlxdWVJZGVudGlmaWVyKHNvdXJjZUZpbGU6IHRzLlNvdXJjZUZpbGUsIGJhc2VOYW1lOiBzdHJpbmcpOiB0cy5JZGVudGlmaWVyIHtcbiAgICBpZiAodGhpcy5pc1VuaXF1ZUlkZW50aWZpZXJOYW1lKHNvdXJjZUZpbGUsIGJhc2VOYW1lKSkge1xuICAgICAgdGhpcy5fcmVjb3JkVXNlZElkZW50aWZpZXIoc291cmNlRmlsZSwgYmFzZU5hbWUpO1xuICAgICAgcmV0dXJuIHRzLmNyZWF0ZUlkZW50aWZpZXIoYmFzZU5hbWUpO1xuICAgIH1cblxuICAgIGxldCBuYW1lID0gbnVsbDtcbiAgICBsZXQgY291bnRlciA9IDE7XG4gICAgZG8ge1xuICAgICAgbmFtZSA9IGAke2Jhc2VOYW1lfV8ke2NvdW50ZXIrK31gO1xuICAgIH0gd2hpbGUgKCF0aGlzLmlzVW5pcXVlSWRlbnRpZmllck5hbWUoc291cmNlRmlsZSwgbmFtZSkpO1xuXG4gICAgdGhpcy5fcmVjb3JkVXNlZElkZW50aWZpZXIoc291cmNlRmlsZSwgbmFtZSEpO1xuICAgIHJldHVybiB0cy5jcmVhdGVJZGVudGlmaWVyKG5hbWUhKTtcbiAgfVxuXG4gIC8qKlxuICAgKiBDaGVja3Mgd2hldGhlciB0aGUgc3BlY2lmaWVkIGlkZW50aWZpZXIgbmFtZSBpcyB1c2VkIHdpdGhpbiB0aGUgZ2l2ZW5cbiAgICogc291cmNlIGZpbGUuXG4gICAqL1xuICBwcml2YXRlIGlzVW5pcXVlSWRlbnRpZmllck5hbWUoc291cmNlRmlsZTogdHMuU291cmNlRmlsZSwgbmFtZTogc3RyaW5nKSB7XG4gICAgaWYgKHRoaXMudXNlZElkZW50aWZpZXJOYW1lcy5oYXMoc291cmNlRmlsZSkgJiZcbiAgICAgICAgdGhpcy51c2VkSWRlbnRpZmllck5hbWVzLmdldChzb3VyY2VGaWxlKSEuaW5kZXhPZihuYW1lKSAhPT0gLTEpIHtcbiAgICAgIHJldHVybiBmYWxzZTtcbiAgICB9XG5cbiAgICAvLyBXYWxrIHRocm91Z2ggdGhlIHNvdXJjZSBmaWxlIGFuZCBzZWFyY2ggZm9yIGFuIGlkZW50aWZpZXIgbWF0Y2hpbmdcbiAgICAvLyB0aGUgZ2l2ZW4gbmFtZS4gSW4gdGhhdCBjYXNlLCBpdCdzIG5vdCBndWFyYW50ZWVkIHRoYXQgdGhpcyBuYW1lXG4gICAgLy8gaXMgdW5pcXVlIGluIHRoZSBnaXZlbiBkZWNsYXJhdGlvbiBzY29wZSBhbmQgd2UganVzdCByZXR1cm4gZmFsc2UuXG4gICAgY29uc3Qgbm9kZVF1ZXVlOiB0cy5Ob2RlW10gPSBbc291cmNlRmlsZV07XG4gICAgd2hpbGUgKG5vZGVRdWV1ZS5sZW5ndGgpIHtcbiAgICAgIGNvbnN0IG5vZGUgPSBub2RlUXVldWUuc2hpZnQoKSE7XG4gICAgICBpZiAodHMuaXNJZGVudGlmaWVyKG5vZGUpICYmIG5vZGUudGV4dCA9PT0gbmFtZSkge1xuICAgICAgICByZXR1cm4gZmFsc2U7XG4gICAgICB9XG4gICAgICBub2RlUXVldWUucHVzaCguLi5ub2RlLmdldENoaWxkcmVuKCkpO1xuICAgIH1cbiAgICByZXR1cm4gdHJ1ZTtcbiAgfVxuXG4gIHByaXZhdGUgX3JlY29yZFVzZWRJZGVudGlmaWVyKHNvdXJjZUZpbGU6IHRzLlNvdXJjZUZpbGUsIGlkZW50aWZpZXJOYW1lOiBzdHJpbmcpIHtcbiAgICB0aGlzLnVzZWRJZGVudGlmaWVyTmFtZXMuc2V0KFxuICAgICAgICBzb3VyY2VGaWxlLCAodGhpcy51c2VkSWRlbnRpZmllck5hbWVzLmdldChzb3VyY2VGaWxlKSB8fCBbXSkuY29uY2F0KGlkZW50aWZpZXJOYW1lKSk7XG4gIH1cblxuICAvKipcbiAgICogRGV0ZXJtaW5lcyB0aGUgZnVsbCBlbmQgb2YgYSBnaXZlbiBub2RlLiBCeSBkZWZhdWx0IHRoZSBlbmQgcG9zaXRpb24gb2YgYSBub2RlIGlzXG4gICAqIGJlZm9yZSBhbGwgdHJhaWxpbmcgY29tbWVudHMuIFRoaXMgY291bGQgbWVhbiB0aGF0IGdlbmVyYXRlZCBpbXBvcnRzIHNoaWZ0IGNvbW1lbnRzLlxuICAgKi9cbiAgcHJpdmF0ZSBfZ2V0RW5kUG9zaXRpb25PZk5vZGUobm9kZTogdHMuTm9kZSkge1xuICAgIGNvbnN0IG5vZGVFbmRQb3MgPSBub2RlLmdldEVuZCgpO1xuICAgIGNvbnN0IGNvbW1lbnRSYW5nZXMgPSB0cy5nZXRUcmFpbGluZ0NvbW1lbnRSYW5nZXMobm9kZS5nZXRTb3VyY2VGaWxlKCkudGV4dCwgbm9kZUVuZFBvcyk7XG4gICAgaWYgKCFjb21tZW50UmFuZ2VzIHx8ICFjb21tZW50UmFuZ2VzLmxlbmd0aCkge1xuICAgICAgcmV0dXJuIG5vZGVFbmRQb3M7XG4gICAgfVxuICAgIHJldHVybiBjb21tZW50UmFuZ2VzW2NvbW1lbnRSYW5nZXMubGVuZ3RoIC0gMV0hLmVuZDtcbiAgfVxufVxuIl19