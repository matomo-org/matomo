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
        define("@angular/core/schematics/migrations/wait-for-async", ["require", "exports", "@angular-devkit/schematics", "path", "typescript", "@angular/core/schematics/utils/project_tsconfig_paths", "@angular/core/schematics/utils/typescript/compiler_host", "@angular/core/schematics/utils/typescript/imports", "@angular/core/schematics/utils/typescript/nodes", "@angular/core/schematics/migrations/wait-for-async/util"], factory);
    }
})(function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    const schematics_1 = require("@angular-devkit/schematics");
    const path_1 = require("path");
    const ts = require("typescript");
    const project_tsconfig_paths_1 = require("@angular/core/schematics/utils/project_tsconfig_paths");
    const compiler_host_1 = require("@angular/core/schematics/utils/typescript/compiler_host");
    const imports_1 = require("@angular/core/schematics/utils/typescript/imports");
    const nodes_1 = require("@angular/core/schematics/utils/typescript/nodes");
    const util_1 = require("@angular/core/schematics/migrations/wait-for-async/util");
    const MODULE_AUGMENTATION_FILENAME = 'ɵɵASYNC_MIGRATION_CORE_AUGMENTATION.d.ts';
    /** Migration that switches from `async` to `waitForAsync`. */
    function default_1() {
        return (tree) => {
            const { buildPaths, testPaths } = project_tsconfig_paths_1.getProjectTsConfigPaths(tree);
            const basePath = process.cwd();
            const allPaths = [...buildPaths, ...testPaths];
            if (!allPaths.length) {
                throw new schematics_1.SchematicsException('Could not find any tsconfig file. Cannot migrate async usages to waitForAsync.');
            }
            for (const tsconfigPath of allPaths) {
                runWaitForAsyncMigration(tree, tsconfigPath, basePath);
            }
        };
    }
    exports.default = default_1;
    function runWaitForAsyncMigration(tree, tsconfigPath, basePath) {
        const { program } = compiler_host_1.createMigrationProgram(tree, tsconfigPath, basePath, fileName => {
            // In case the module augmentation file has been requested, we return a source file that
            // augments "@angular/core/testing" to include a named export called "async". This ensures that
            // we can rely on the type checker for this migration after `async` has been removed.
            if (fileName === MODULE_AUGMENTATION_FILENAME) {
                return `
        import '@angular/core/testing';
        declare module "@angular/core/testing" {
          function async(fn: Function): any;
        }
      `;
            }
            return null;
        }, [MODULE_AUGMENTATION_FILENAME]);
        const typeChecker = program.getTypeChecker();
        const printer = ts.createPrinter();
        const sourceFiles = program.getSourceFiles().filter(sourceFile => compiler_host_1.canMigrateFile(basePath, sourceFile, program));
        const deprecatedFunction = 'async';
        const newFunction = 'waitForAsync';
        sourceFiles.forEach(sourceFile => {
            const asyncImportSpecifier = imports_1.getImportSpecifier(sourceFile, '@angular/core/testing', deprecatedFunction);
            const asyncImport = asyncImportSpecifier ?
                nodes_1.closestNode(asyncImportSpecifier, ts.SyntaxKind.NamedImports) :
                null;
            // If there are no imports for `async`, we can exit early.
            if (!asyncImportSpecifier || !asyncImport) {
                return;
            }
            const update = tree.beginUpdate(path_1.relative(basePath, sourceFile.fileName));
            // Change the `async` import to `waitForAsync`.
            update.remove(asyncImport.getStart(), asyncImport.getWidth());
            update.insertRight(asyncImport.getStart(), printer.printNode(ts.EmitHint.Unspecified, imports_1.replaceImport(asyncImport, deprecatedFunction, newFunction), sourceFile));
            // Change `async` calls to `waitForAsync`.
            util_1.findAsyncReferences(sourceFile, typeChecker, asyncImportSpecifier).forEach(node => {
                update.remove(node.getStart(), node.getWidth());
                update.insertRight(node.getStart(), newFunction);
            });
            tree.commitUpdate(update);
        });
    }
});
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiaW5kZXguanMiLCJzb3VyY2VSb290IjoiIiwic291cmNlcyI6WyIuLi8uLi8uLi8uLi8uLi8uLi8uLi8uLi9wYWNrYWdlcy9jb3JlL3NjaGVtYXRpY3MvbWlncmF0aW9ucy93YWl0LWZvci1hc3luYy9pbmRleC50cyJdLCJuYW1lcyI6W10sIm1hcHBpbmdzIjoiQUFBQTs7Ozs7O0dBTUc7Ozs7Ozs7Ozs7OztJQUVILDJEQUEyRTtJQUMzRSwrQkFBOEI7SUFDOUIsaUNBQWlDO0lBRWpDLGtHQUEyRTtJQUMzRSwyRkFBNEY7SUFDNUYsK0VBQWlGO0lBQ2pGLDJFQUF5RDtJQUV6RCxrRkFBMkM7SUFFM0MsTUFBTSw0QkFBNEIsR0FBRywwQ0FBMEMsQ0FBQztJQUVoRiw4REFBOEQ7SUFDOUQ7UUFDRSxPQUFPLENBQUMsSUFBVSxFQUFFLEVBQUU7WUFDcEIsTUFBTSxFQUFDLFVBQVUsRUFBRSxTQUFTLEVBQUMsR0FBRyxnREFBdUIsQ0FBQyxJQUFJLENBQUMsQ0FBQztZQUM5RCxNQUFNLFFBQVEsR0FBRyxPQUFPLENBQUMsR0FBRyxFQUFFLENBQUM7WUFDL0IsTUFBTSxRQUFRLEdBQUcsQ0FBQyxHQUFHLFVBQVUsRUFBRSxHQUFHLFNBQVMsQ0FBQyxDQUFDO1lBRS9DLElBQUksQ0FBQyxRQUFRLENBQUMsTUFBTSxFQUFFO2dCQUNwQixNQUFNLElBQUksZ0NBQW1CLENBQ3pCLGdGQUFnRixDQUFDLENBQUM7YUFDdkY7WUFFRCxLQUFLLE1BQU0sWUFBWSxJQUFJLFFBQVEsRUFBRTtnQkFDbkMsd0JBQXdCLENBQUMsSUFBSSxFQUFFLFlBQVksRUFBRSxRQUFRLENBQUMsQ0FBQzthQUN4RDtRQUNILENBQUMsQ0FBQztJQUNKLENBQUM7SUFmRCw0QkFlQztJQUVELFNBQVMsd0JBQXdCLENBQUMsSUFBVSxFQUFFLFlBQW9CLEVBQUUsUUFBZ0I7UUFDbEYsTUFBTSxFQUFDLE9BQU8sRUFBQyxHQUFHLHNDQUFzQixDQUFDLElBQUksRUFBRSxZQUFZLEVBQUUsUUFBUSxFQUFFLFFBQVEsQ0FBQyxFQUFFO1lBQ2hGLHdGQUF3RjtZQUN4RiwrRkFBK0Y7WUFDL0YscUZBQXFGO1lBQ3JGLElBQUksUUFBUSxLQUFLLDRCQUE0QixFQUFFO2dCQUM3QyxPQUFPOzs7OztPQUtOLENBQUM7YUFDSDtZQUNELE9BQU8sSUFBSSxDQUFDO1FBQ2QsQ0FBQyxFQUFFLENBQUMsNEJBQTRCLENBQUMsQ0FBQyxDQUFDO1FBQ25DLE1BQU0sV0FBVyxHQUFHLE9BQU8sQ0FBQyxjQUFjLEVBQUUsQ0FBQztRQUM3QyxNQUFNLE9BQU8sR0FBRyxFQUFFLENBQUMsYUFBYSxFQUFFLENBQUM7UUFDbkMsTUFBTSxXQUFXLEdBQ2IsT0FBTyxDQUFDLGNBQWMsRUFBRSxDQUFDLE1BQU0sQ0FBQyxVQUFVLENBQUMsRUFBRSxDQUFDLDhCQUFjLENBQUMsUUFBUSxFQUFFLFVBQVUsRUFBRSxPQUFPLENBQUMsQ0FBQyxDQUFDO1FBQ2pHLE1BQU0sa0JBQWtCLEdBQUcsT0FBTyxDQUFDO1FBQ25DLE1BQU0sV0FBVyxHQUFHLGNBQWMsQ0FBQztRQUVuQyxXQUFXLENBQUMsT0FBTyxDQUFDLFVBQVUsQ0FBQyxFQUFFO1lBQy9CLE1BQU0sb0JBQW9CLEdBQ3RCLDRCQUFrQixDQUFDLFVBQVUsRUFBRSx1QkFBdUIsRUFBRSxrQkFBa0IsQ0FBQyxDQUFDO1lBQ2hGLE1BQU0sV0FBVyxHQUFHLG9CQUFvQixDQUFDLENBQUM7Z0JBQ3RDLG1CQUFXLENBQWtCLG9CQUFvQixFQUFFLEVBQUUsQ0FBQyxVQUFVLENBQUMsWUFBWSxDQUFDLENBQUMsQ0FBQztnQkFDaEYsSUFBSSxDQUFDO1lBRVQsMERBQTBEO1lBQzFELElBQUksQ0FBQyxvQkFBb0IsSUFBSSxDQUFDLFdBQVcsRUFBRTtnQkFDekMsT0FBTzthQUNSO1lBRUQsTUFBTSxNQUFNLEdBQUcsSUFBSSxDQUFDLFdBQVcsQ0FBQyxlQUFRLENBQUMsUUFBUSxFQUFFLFVBQVUsQ0FBQyxRQUFRLENBQUMsQ0FBQyxDQUFDO1lBRXpFLCtDQUErQztZQUMvQyxNQUFNLENBQUMsTUFBTSxDQUFDLFdBQVcsQ0FBQyxRQUFRLEVBQUUsRUFBRSxXQUFXLENBQUMsUUFBUSxFQUFFLENBQUMsQ0FBQztZQUM5RCxNQUFNLENBQUMsV0FBVyxDQUNkLFdBQVcsQ0FBQyxRQUFRLEVBQUUsRUFDdEIsT0FBTyxDQUFDLFNBQVMsQ0FDYixFQUFFLENBQUMsUUFBUSxDQUFDLFdBQVcsRUFBRSx1QkFBYSxDQUFDLFdBQVcsRUFBRSxrQkFBa0IsRUFBRSxXQUFXLENBQUMsRUFDcEYsVUFBVSxDQUFDLENBQUMsQ0FBQztZQUVyQiwwQ0FBMEM7WUFDMUMsMEJBQW1CLENBQUMsVUFBVSxFQUFFLFdBQVcsRUFBRSxvQkFBb0IsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxJQUFJLENBQUMsRUFBRTtnQkFDaEYsTUFBTSxDQUFDLE1BQU0sQ0FBQyxJQUFJLENBQUMsUUFBUSxFQUFFLEVBQUUsSUFBSSxDQUFDLFFBQVEsRUFBRSxDQUFDLENBQUM7Z0JBQ2hELE1BQU0sQ0FBQyxXQUFXLENBQUMsSUFBSSxDQUFDLFFBQVEsRUFBRSxFQUFFLFdBQVcsQ0FBQyxDQUFDO1lBQ25ELENBQUMsQ0FBQyxDQUFDO1lBRUgsSUFBSSxDQUFDLFlBQVksQ0FBQyxNQUFNLENBQUMsQ0FBQztRQUM1QixDQUFDLENBQUMsQ0FBQztJQUNMLENBQUMiLCJzb3VyY2VzQ29udGVudCI6WyIvKipcbiAqIEBsaWNlbnNlXG4gKiBDb3B5cmlnaHQgR29vZ2xlIExMQyBBbGwgUmlnaHRzIFJlc2VydmVkLlxuICpcbiAqIFVzZSBvZiB0aGlzIHNvdXJjZSBjb2RlIGlzIGdvdmVybmVkIGJ5IGFuIE1JVC1zdHlsZSBsaWNlbnNlIHRoYXQgY2FuIGJlXG4gKiBmb3VuZCBpbiB0aGUgTElDRU5TRSBmaWxlIGF0IGh0dHBzOi8vYW5ndWxhci5pby9saWNlbnNlXG4gKi9cblxuaW1wb3J0IHtSdWxlLCBTY2hlbWF0aWNzRXhjZXB0aW9uLCBUcmVlfSBmcm9tICdAYW5ndWxhci1kZXZraXQvc2NoZW1hdGljcyc7XG5pbXBvcnQge3JlbGF0aXZlfSBmcm9tICdwYXRoJztcbmltcG9ydCAqIGFzIHRzIGZyb20gJ3R5cGVzY3JpcHQnO1xuXG5pbXBvcnQge2dldFByb2plY3RUc0NvbmZpZ1BhdGhzfSBmcm9tICcuLi8uLi91dGlscy9wcm9qZWN0X3RzY29uZmlnX3BhdGhzJztcbmltcG9ydCB7Y2FuTWlncmF0ZUZpbGUsIGNyZWF0ZU1pZ3JhdGlvblByb2dyYW19IGZyb20gJy4uLy4uL3V0aWxzL3R5cGVzY3JpcHQvY29tcGlsZXJfaG9zdCc7XG5pbXBvcnQge2dldEltcG9ydFNwZWNpZmllciwgcmVwbGFjZUltcG9ydH0gZnJvbSAnLi4vLi4vdXRpbHMvdHlwZXNjcmlwdC9pbXBvcnRzJztcbmltcG9ydCB7Y2xvc2VzdE5vZGV9IGZyb20gJy4uLy4uL3V0aWxzL3R5cGVzY3JpcHQvbm9kZXMnO1xuXG5pbXBvcnQge2ZpbmRBc3luY1JlZmVyZW5jZXN9IGZyb20gJy4vdXRpbCc7XG5cbmNvbnN0IE1PRFVMRV9BVUdNRU5UQVRJT05fRklMRU5BTUUgPSAnybXJtUFTWU5DX01JR1JBVElPTl9DT1JFX0FVR01FTlRBVElPTi5kLnRzJztcblxuLyoqIE1pZ3JhdGlvbiB0aGF0IHN3aXRjaGVzIGZyb20gYGFzeW5jYCB0byBgd2FpdEZvckFzeW5jYC4gKi9cbmV4cG9ydCBkZWZhdWx0IGZ1bmN0aW9uKCk6IFJ1bGUge1xuICByZXR1cm4gKHRyZWU6IFRyZWUpID0+IHtcbiAgICBjb25zdCB7YnVpbGRQYXRocywgdGVzdFBhdGhzfSA9IGdldFByb2plY3RUc0NvbmZpZ1BhdGhzKHRyZWUpO1xuICAgIGNvbnN0IGJhc2VQYXRoID0gcHJvY2Vzcy5jd2QoKTtcbiAgICBjb25zdCBhbGxQYXRocyA9IFsuLi5idWlsZFBhdGhzLCAuLi50ZXN0UGF0aHNdO1xuXG4gICAgaWYgKCFhbGxQYXRocy5sZW5ndGgpIHtcbiAgICAgIHRocm93IG5ldyBTY2hlbWF0aWNzRXhjZXB0aW9uKFxuICAgICAgICAgICdDb3VsZCBub3QgZmluZCBhbnkgdHNjb25maWcgZmlsZS4gQ2Fubm90IG1pZ3JhdGUgYXN5bmMgdXNhZ2VzIHRvIHdhaXRGb3JBc3luYy4nKTtcbiAgICB9XG5cbiAgICBmb3IgKGNvbnN0IHRzY29uZmlnUGF0aCBvZiBhbGxQYXRocykge1xuICAgICAgcnVuV2FpdEZvckFzeW5jTWlncmF0aW9uKHRyZWUsIHRzY29uZmlnUGF0aCwgYmFzZVBhdGgpO1xuICAgIH1cbiAgfTtcbn1cblxuZnVuY3Rpb24gcnVuV2FpdEZvckFzeW5jTWlncmF0aW9uKHRyZWU6IFRyZWUsIHRzY29uZmlnUGF0aDogc3RyaW5nLCBiYXNlUGF0aDogc3RyaW5nKSB7XG4gIGNvbnN0IHtwcm9ncmFtfSA9IGNyZWF0ZU1pZ3JhdGlvblByb2dyYW0odHJlZSwgdHNjb25maWdQYXRoLCBiYXNlUGF0aCwgZmlsZU5hbWUgPT4ge1xuICAgIC8vIEluIGNhc2UgdGhlIG1vZHVsZSBhdWdtZW50YXRpb24gZmlsZSBoYXMgYmVlbiByZXF1ZXN0ZWQsIHdlIHJldHVybiBhIHNvdXJjZSBmaWxlIHRoYXRcbiAgICAvLyBhdWdtZW50cyBcIkBhbmd1bGFyL2NvcmUvdGVzdGluZ1wiIHRvIGluY2x1ZGUgYSBuYW1lZCBleHBvcnQgY2FsbGVkIFwiYXN5bmNcIi4gVGhpcyBlbnN1cmVzIHRoYXRcbiAgICAvLyB3ZSBjYW4gcmVseSBvbiB0aGUgdHlwZSBjaGVja2VyIGZvciB0aGlzIG1pZ3JhdGlvbiBhZnRlciBgYXN5bmNgIGhhcyBiZWVuIHJlbW92ZWQuXG4gICAgaWYgKGZpbGVOYW1lID09PSBNT0RVTEVfQVVHTUVOVEFUSU9OX0ZJTEVOQU1FKSB7XG4gICAgICByZXR1cm4gYFxuICAgICAgICBpbXBvcnQgJ0Bhbmd1bGFyL2NvcmUvdGVzdGluZyc7XG4gICAgICAgIGRlY2xhcmUgbW9kdWxlIFwiQGFuZ3VsYXIvY29yZS90ZXN0aW5nXCIge1xuICAgICAgICAgIGZ1bmN0aW9uIGFzeW5jKGZuOiBGdW5jdGlvbik6IGFueTtcbiAgICAgICAgfVxuICAgICAgYDtcbiAgICB9XG4gICAgcmV0dXJuIG51bGw7XG4gIH0sIFtNT0RVTEVfQVVHTUVOVEFUSU9OX0ZJTEVOQU1FXSk7XG4gIGNvbnN0IHR5cGVDaGVja2VyID0gcHJvZ3JhbS5nZXRUeXBlQ2hlY2tlcigpO1xuICBjb25zdCBwcmludGVyID0gdHMuY3JlYXRlUHJpbnRlcigpO1xuICBjb25zdCBzb3VyY2VGaWxlcyA9XG4gICAgICBwcm9ncmFtLmdldFNvdXJjZUZpbGVzKCkuZmlsdGVyKHNvdXJjZUZpbGUgPT4gY2FuTWlncmF0ZUZpbGUoYmFzZVBhdGgsIHNvdXJjZUZpbGUsIHByb2dyYW0pKTtcbiAgY29uc3QgZGVwcmVjYXRlZEZ1bmN0aW9uID0gJ2FzeW5jJztcbiAgY29uc3QgbmV3RnVuY3Rpb24gPSAnd2FpdEZvckFzeW5jJztcblxuICBzb3VyY2VGaWxlcy5mb3JFYWNoKHNvdXJjZUZpbGUgPT4ge1xuICAgIGNvbnN0IGFzeW5jSW1wb3J0U3BlY2lmaWVyID1cbiAgICAgICAgZ2V0SW1wb3J0U3BlY2lmaWVyKHNvdXJjZUZpbGUsICdAYW5ndWxhci9jb3JlL3Rlc3RpbmcnLCBkZXByZWNhdGVkRnVuY3Rpb24pO1xuICAgIGNvbnN0IGFzeW5jSW1wb3J0ID0gYXN5bmNJbXBvcnRTcGVjaWZpZXIgP1xuICAgICAgICBjbG9zZXN0Tm9kZTx0cy5OYW1lZEltcG9ydHM+KGFzeW5jSW1wb3J0U3BlY2lmaWVyLCB0cy5TeW50YXhLaW5kLk5hbWVkSW1wb3J0cykgOlxuICAgICAgICBudWxsO1xuXG4gICAgLy8gSWYgdGhlcmUgYXJlIG5vIGltcG9ydHMgZm9yIGBhc3luY2AsIHdlIGNhbiBleGl0IGVhcmx5LlxuICAgIGlmICghYXN5bmNJbXBvcnRTcGVjaWZpZXIgfHwgIWFzeW5jSW1wb3J0KSB7XG4gICAgICByZXR1cm47XG4gICAgfVxuXG4gICAgY29uc3QgdXBkYXRlID0gdHJlZS5iZWdpblVwZGF0ZShyZWxhdGl2ZShiYXNlUGF0aCwgc291cmNlRmlsZS5maWxlTmFtZSkpO1xuXG4gICAgLy8gQ2hhbmdlIHRoZSBgYXN5bmNgIGltcG9ydCB0byBgd2FpdEZvckFzeW5jYC5cbiAgICB1cGRhdGUucmVtb3ZlKGFzeW5jSW1wb3J0LmdldFN0YXJ0KCksIGFzeW5jSW1wb3J0LmdldFdpZHRoKCkpO1xuICAgIHVwZGF0ZS5pbnNlcnRSaWdodChcbiAgICAgICAgYXN5bmNJbXBvcnQuZ2V0U3RhcnQoKSxcbiAgICAgICAgcHJpbnRlci5wcmludE5vZGUoXG4gICAgICAgICAgICB0cy5FbWl0SGludC5VbnNwZWNpZmllZCwgcmVwbGFjZUltcG9ydChhc3luY0ltcG9ydCwgZGVwcmVjYXRlZEZ1bmN0aW9uLCBuZXdGdW5jdGlvbiksXG4gICAgICAgICAgICBzb3VyY2VGaWxlKSk7XG5cbiAgICAvLyBDaGFuZ2UgYGFzeW5jYCBjYWxscyB0byBgd2FpdEZvckFzeW5jYC5cbiAgICBmaW5kQXN5bmNSZWZlcmVuY2VzKHNvdXJjZUZpbGUsIHR5cGVDaGVja2VyLCBhc3luY0ltcG9ydFNwZWNpZmllcikuZm9yRWFjaChub2RlID0+IHtcbiAgICAgIHVwZGF0ZS5yZW1vdmUobm9kZS5nZXRTdGFydCgpLCBub2RlLmdldFdpZHRoKCkpO1xuICAgICAgdXBkYXRlLmluc2VydFJpZ2h0KG5vZGUuZ2V0U3RhcnQoKSwgbmV3RnVuY3Rpb24pO1xuICAgIH0pO1xuXG4gICAgdHJlZS5jb21taXRVcGRhdGUodXBkYXRlKTtcbiAgfSk7XG59XG4iXX0=