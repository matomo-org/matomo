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
        define("@angular/core/schematics/migrations/abstract-control-parent", ["require", "exports", "@angular-devkit/schematics", "path", "@angular/core/schematics/utils/project_tsconfig_paths", "@angular/core/schematics/utils/typescript/compiler_host", "@angular/core/schematics/migrations/abstract-control-parent/util"], factory);
    }
})(function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    const schematics_1 = require("@angular-devkit/schematics");
    const path_1 = require("path");
    const project_tsconfig_paths_1 = require("@angular/core/schematics/utils/project_tsconfig_paths");
    const compiler_host_1 = require("@angular/core/schematics/utils/typescript/compiler_host");
    const util_1 = require("@angular/core/schematics/migrations/abstract-control-parent/util");
    /** Migration that marks accesses of `AbstractControl.parent` as non-null. */
    function default_1() {
        return (tree) => {
            const { buildPaths, testPaths } = project_tsconfig_paths_1.getProjectTsConfigPaths(tree);
            const basePath = process.cwd();
            const allPaths = [...buildPaths, ...testPaths];
            if (!allPaths.length) {
                throw new schematics_1.SchematicsException('Could not find any tsconfig file. Cannot migrate AbstractControl.parent accesses.');
            }
            for (const tsconfigPath of allPaths) {
                runNativeAbstractControlParentMigration(tree, tsconfigPath, basePath);
            }
        };
    }
    exports.default = default_1;
    function runNativeAbstractControlParentMigration(tree, tsconfigPath, basePath) {
        const { program } = compiler_host_1.createMigrationProgram(tree, tsconfigPath, basePath);
        const typeChecker = program.getTypeChecker();
        const sourceFiles = program.getSourceFiles().filter(sourceFile => compiler_host_1.canMigrateFile(basePath, sourceFile, program));
        sourceFiles.forEach(sourceFile => {
            // We sort the nodes based on their position in the file and we offset the positions by one
            // for each non-null assertion that we've added. We have to do it this way, rather than
            // creating and printing a new AST node like in other migrations, because property access
            // expressions can be nested (e.g. `control.parent.parent.value`), but the node positions
            // aren't being updated as we're inserting new code. If we were to go through the AST,
            // we'd have to update the `SourceFile` and start over after each operation.
            util_1.findParentAccesses(typeChecker, sourceFile)
                .sort((a, b) => a.getStart() - b.getStart())
                .forEach((node, index) => {
                const update = tree.beginUpdate(path_1.relative(basePath, sourceFile.fileName));
                update.insertRight(node.getStart() + node.getWidth() + index, '!');
                tree.commitUpdate(update);
            });
        });
    }
});
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiaW5kZXguanMiLCJzb3VyY2VSb290IjoiIiwic291cmNlcyI6WyIuLi8uLi8uLi8uLi8uLi8uLi8uLi8uLi9wYWNrYWdlcy9jb3JlL3NjaGVtYXRpY3MvbWlncmF0aW9ucy9hYnN0cmFjdC1jb250cm9sLXBhcmVudC9pbmRleC50cyJdLCJuYW1lcyI6W10sIm1hcHBpbmdzIjoiQUFBQTs7Ozs7O0dBTUc7Ozs7Ozs7Ozs7OztJQUVILDJEQUEyRTtJQUMzRSwrQkFBOEI7SUFFOUIsa0dBQTJFO0lBQzNFLDJGQUE0RjtJQUM1RiwyRkFBMEM7SUFHMUMsNkVBQTZFO0lBQzdFO1FBQ0UsT0FBTyxDQUFDLElBQVUsRUFBRSxFQUFFO1lBQ3BCLE1BQU0sRUFBQyxVQUFVLEVBQUUsU0FBUyxFQUFDLEdBQUcsZ0RBQXVCLENBQUMsSUFBSSxDQUFDLENBQUM7WUFDOUQsTUFBTSxRQUFRLEdBQUcsT0FBTyxDQUFDLEdBQUcsRUFBRSxDQUFDO1lBQy9CLE1BQU0sUUFBUSxHQUFHLENBQUMsR0FBRyxVQUFVLEVBQUUsR0FBRyxTQUFTLENBQUMsQ0FBQztZQUUvQyxJQUFJLENBQUMsUUFBUSxDQUFDLE1BQU0sRUFBRTtnQkFDcEIsTUFBTSxJQUFJLGdDQUFtQixDQUN6QixtRkFBbUYsQ0FBQyxDQUFDO2FBQzFGO1lBRUQsS0FBSyxNQUFNLFlBQVksSUFBSSxRQUFRLEVBQUU7Z0JBQ25DLHVDQUF1QyxDQUFDLElBQUksRUFBRSxZQUFZLEVBQUUsUUFBUSxDQUFDLENBQUM7YUFDdkU7UUFDSCxDQUFDLENBQUM7SUFDSixDQUFDO0lBZkQsNEJBZUM7SUFFRCxTQUFTLHVDQUF1QyxDQUM1QyxJQUFVLEVBQUUsWUFBb0IsRUFBRSxRQUFnQjtRQUNwRCxNQUFNLEVBQUMsT0FBTyxFQUFDLEdBQUcsc0NBQXNCLENBQUMsSUFBSSxFQUFFLFlBQVksRUFBRSxRQUFRLENBQUMsQ0FBQztRQUN2RSxNQUFNLFdBQVcsR0FBRyxPQUFPLENBQUMsY0FBYyxFQUFFLENBQUM7UUFDN0MsTUFBTSxXQUFXLEdBQ2IsT0FBTyxDQUFDLGNBQWMsRUFBRSxDQUFDLE1BQU0sQ0FBQyxVQUFVLENBQUMsRUFBRSxDQUFDLDhCQUFjLENBQUMsUUFBUSxFQUFFLFVBQVUsRUFBRSxPQUFPLENBQUMsQ0FBQyxDQUFDO1FBRWpHLFdBQVcsQ0FBQyxPQUFPLENBQUMsVUFBVSxDQUFDLEVBQUU7WUFDL0IsMkZBQTJGO1lBQzNGLHVGQUF1RjtZQUN2Rix5RkFBeUY7WUFDekYseUZBQXlGO1lBQ3pGLHNGQUFzRjtZQUN0Riw0RUFBNEU7WUFDNUUseUJBQWtCLENBQUMsV0FBVyxFQUFFLFVBQVUsQ0FBQztpQkFDdEMsSUFBSSxDQUFDLENBQUMsQ0FBQyxFQUFFLENBQUMsRUFBRSxFQUFFLENBQUMsQ0FBQyxDQUFDLFFBQVEsRUFBRSxHQUFHLENBQUMsQ0FBQyxRQUFRLEVBQUUsQ0FBQztpQkFDM0MsT0FBTyxDQUFDLENBQUMsSUFBSSxFQUFFLEtBQUssRUFBRSxFQUFFO2dCQUN2QixNQUFNLE1BQU0sR0FBRyxJQUFJLENBQUMsV0FBVyxDQUFDLGVBQVEsQ0FBQyxRQUFRLEVBQUUsVUFBVSxDQUFDLFFBQVEsQ0FBQyxDQUFDLENBQUM7Z0JBQ3pFLE1BQU0sQ0FBQyxXQUFXLENBQUMsSUFBSSxDQUFDLFFBQVEsRUFBRSxHQUFHLElBQUksQ0FBQyxRQUFRLEVBQUUsR0FBRyxLQUFLLEVBQUUsR0FBRyxDQUFDLENBQUM7Z0JBQ25FLElBQUksQ0FBQyxZQUFZLENBQUMsTUFBTSxDQUFDLENBQUM7WUFDNUIsQ0FBQyxDQUFDLENBQUM7UUFDVCxDQUFDLENBQUMsQ0FBQztJQUNMLENBQUMiLCJzb3VyY2VzQ29udGVudCI6WyIvKipcbiAqIEBsaWNlbnNlXG4gKiBDb3B5cmlnaHQgR29vZ2xlIExMQyBBbGwgUmlnaHRzIFJlc2VydmVkLlxuICpcbiAqIFVzZSBvZiB0aGlzIHNvdXJjZSBjb2RlIGlzIGdvdmVybmVkIGJ5IGFuIE1JVC1zdHlsZSBsaWNlbnNlIHRoYXQgY2FuIGJlXG4gKiBmb3VuZCBpbiB0aGUgTElDRU5TRSBmaWxlIGF0IGh0dHBzOi8vYW5ndWxhci5pby9saWNlbnNlXG4gKi9cblxuaW1wb3J0IHtSdWxlLCBTY2hlbWF0aWNzRXhjZXB0aW9uLCBUcmVlfSBmcm9tICdAYW5ndWxhci1kZXZraXQvc2NoZW1hdGljcyc7XG5pbXBvcnQge3JlbGF0aXZlfSBmcm9tICdwYXRoJztcblxuaW1wb3J0IHtnZXRQcm9qZWN0VHNDb25maWdQYXRoc30gZnJvbSAnLi4vLi4vdXRpbHMvcHJvamVjdF90c2NvbmZpZ19wYXRocyc7XG5pbXBvcnQge2Nhbk1pZ3JhdGVGaWxlLCBjcmVhdGVNaWdyYXRpb25Qcm9ncmFtfSBmcm9tICcuLi8uLi91dGlscy90eXBlc2NyaXB0L2NvbXBpbGVyX2hvc3QnO1xuaW1wb3J0IHtmaW5kUGFyZW50QWNjZXNzZXN9IGZyb20gJy4vdXRpbCc7XG5cblxuLyoqIE1pZ3JhdGlvbiB0aGF0IG1hcmtzIGFjY2Vzc2VzIG9mIGBBYnN0cmFjdENvbnRyb2wucGFyZW50YCBhcyBub24tbnVsbC4gKi9cbmV4cG9ydCBkZWZhdWx0IGZ1bmN0aW9uKCk6IFJ1bGUge1xuICByZXR1cm4gKHRyZWU6IFRyZWUpID0+IHtcbiAgICBjb25zdCB7YnVpbGRQYXRocywgdGVzdFBhdGhzfSA9IGdldFByb2plY3RUc0NvbmZpZ1BhdGhzKHRyZWUpO1xuICAgIGNvbnN0IGJhc2VQYXRoID0gcHJvY2Vzcy5jd2QoKTtcbiAgICBjb25zdCBhbGxQYXRocyA9IFsuLi5idWlsZFBhdGhzLCAuLi50ZXN0UGF0aHNdO1xuXG4gICAgaWYgKCFhbGxQYXRocy5sZW5ndGgpIHtcbiAgICAgIHRocm93IG5ldyBTY2hlbWF0aWNzRXhjZXB0aW9uKFxuICAgICAgICAgICdDb3VsZCBub3QgZmluZCBhbnkgdHNjb25maWcgZmlsZS4gQ2Fubm90IG1pZ3JhdGUgQWJzdHJhY3RDb250cm9sLnBhcmVudCBhY2Nlc3Nlcy4nKTtcbiAgICB9XG5cbiAgICBmb3IgKGNvbnN0IHRzY29uZmlnUGF0aCBvZiBhbGxQYXRocykge1xuICAgICAgcnVuTmF0aXZlQWJzdHJhY3RDb250cm9sUGFyZW50TWlncmF0aW9uKHRyZWUsIHRzY29uZmlnUGF0aCwgYmFzZVBhdGgpO1xuICAgIH1cbiAgfTtcbn1cblxuZnVuY3Rpb24gcnVuTmF0aXZlQWJzdHJhY3RDb250cm9sUGFyZW50TWlncmF0aW9uKFxuICAgIHRyZWU6IFRyZWUsIHRzY29uZmlnUGF0aDogc3RyaW5nLCBiYXNlUGF0aDogc3RyaW5nKSB7XG4gIGNvbnN0IHtwcm9ncmFtfSA9IGNyZWF0ZU1pZ3JhdGlvblByb2dyYW0odHJlZSwgdHNjb25maWdQYXRoLCBiYXNlUGF0aCk7XG4gIGNvbnN0IHR5cGVDaGVja2VyID0gcHJvZ3JhbS5nZXRUeXBlQ2hlY2tlcigpO1xuICBjb25zdCBzb3VyY2VGaWxlcyA9XG4gICAgICBwcm9ncmFtLmdldFNvdXJjZUZpbGVzKCkuZmlsdGVyKHNvdXJjZUZpbGUgPT4gY2FuTWlncmF0ZUZpbGUoYmFzZVBhdGgsIHNvdXJjZUZpbGUsIHByb2dyYW0pKTtcblxuICBzb3VyY2VGaWxlcy5mb3JFYWNoKHNvdXJjZUZpbGUgPT4ge1xuICAgIC8vIFdlIHNvcnQgdGhlIG5vZGVzIGJhc2VkIG9uIHRoZWlyIHBvc2l0aW9uIGluIHRoZSBmaWxlIGFuZCB3ZSBvZmZzZXQgdGhlIHBvc2l0aW9ucyBieSBvbmVcbiAgICAvLyBmb3IgZWFjaCBub24tbnVsbCBhc3NlcnRpb24gdGhhdCB3ZSd2ZSBhZGRlZC4gV2UgaGF2ZSB0byBkbyBpdCB0aGlzIHdheSwgcmF0aGVyIHRoYW5cbiAgICAvLyBjcmVhdGluZyBhbmQgcHJpbnRpbmcgYSBuZXcgQVNUIG5vZGUgbGlrZSBpbiBvdGhlciBtaWdyYXRpb25zLCBiZWNhdXNlIHByb3BlcnR5IGFjY2Vzc1xuICAgIC8vIGV4cHJlc3Npb25zIGNhbiBiZSBuZXN0ZWQgKGUuZy4gYGNvbnRyb2wucGFyZW50LnBhcmVudC52YWx1ZWApLCBidXQgdGhlIG5vZGUgcG9zaXRpb25zXG4gICAgLy8gYXJlbid0IGJlaW5nIHVwZGF0ZWQgYXMgd2UncmUgaW5zZXJ0aW5nIG5ldyBjb2RlLiBJZiB3ZSB3ZXJlIHRvIGdvIHRocm91Z2ggdGhlIEFTVCxcbiAgICAvLyB3ZSdkIGhhdmUgdG8gdXBkYXRlIHRoZSBgU291cmNlRmlsZWAgYW5kIHN0YXJ0IG92ZXIgYWZ0ZXIgZWFjaCBvcGVyYXRpb24uXG4gICAgZmluZFBhcmVudEFjY2Vzc2VzKHR5cGVDaGVja2VyLCBzb3VyY2VGaWxlKVxuICAgICAgICAuc29ydCgoYSwgYikgPT4gYS5nZXRTdGFydCgpIC0gYi5nZXRTdGFydCgpKVxuICAgICAgICAuZm9yRWFjaCgobm9kZSwgaW5kZXgpID0+IHtcbiAgICAgICAgICBjb25zdCB1cGRhdGUgPSB0cmVlLmJlZ2luVXBkYXRlKHJlbGF0aXZlKGJhc2VQYXRoLCBzb3VyY2VGaWxlLmZpbGVOYW1lKSk7XG4gICAgICAgICAgdXBkYXRlLmluc2VydFJpZ2h0KG5vZGUuZ2V0U3RhcnQoKSArIG5vZGUuZ2V0V2lkdGgoKSArIGluZGV4LCAnIScpO1xuICAgICAgICAgIHRyZWUuY29tbWl0VXBkYXRlKHVwZGF0ZSk7XG4gICAgICAgIH0pO1xuICB9KTtcbn1cbiJdfQ==