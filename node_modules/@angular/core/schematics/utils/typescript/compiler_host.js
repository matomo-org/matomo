(function (factory) {
    if (typeof module === "object" && typeof module.exports === "object") {
        var v = factory(require, exports);
        if (v !== undefined) module.exports = v;
    }
    else if (typeof define === "function" && define.amd) {
        define("@angular/core/schematics/utils/typescript/compiler_host", ["require", "exports", "path", "typescript", "@angular/core/schematics/utils/typescript/parse_tsconfig"], factory);
    }
})(function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.canMigrateFile = exports.createMigrationCompilerHost = exports.createMigrationProgram = void 0;
    const path_1 = require("path");
    const ts = require("typescript");
    const parse_tsconfig_1 = require("@angular/core/schematics/utils/typescript/parse_tsconfig");
    /**
     * Creates a TypeScript program instance for a TypeScript project within
     * the virtual file system tree.
     * @param tree Virtual file system tree that contains the source files.
     * @param tsconfigPath Virtual file system path that resolves to the TypeScript project.
     * @param basePath Base path for the virtual file system tree.
     * @param fakeFileRead Optional file reader function. Can be used to overwrite files in
     *   the TypeScript program, or to add in-memory files (e.g. to add global types).
     * @param additionalFiles Additional file paths that should be added to the program.
     */
    function createMigrationProgram(tree, tsconfigPath, basePath, fakeFileRead, additionalFiles) {
        // Resolve the tsconfig path to an absolute path. This is needed as TypeScript otherwise
        // is not able to resolve root directories in the given tsconfig. More details can be found
        // in the following issue: https://github.com/microsoft/TypeScript/issues/37731.
        tsconfigPath = path_1.resolve(basePath, tsconfigPath);
        const parsed = parse_tsconfig_1.parseTsconfigFile(tsconfigPath, path_1.dirname(tsconfigPath));
        const host = createMigrationCompilerHost(tree, parsed.options, basePath, fakeFileRead);
        const program = ts.createProgram(parsed.fileNames.concat(additionalFiles || []), parsed.options, host);
        return { parsed, host, program };
    }
    exports.createMigrationProgram = createMigrationProgram;
    function createMigrationCompilerHost(tree, options, basePath, fakeRead) {
        const host = ts.createCompilerHost(options, true);
        // We need to overwrite the host "readFile" method, as we want the TypeScript
        // program to be based on the file contents in the virtual file tree. Otherwise
        // if we run multiple migrations we might have intersecting changes and
        // source files.
        host.readFile = fileName => {
            const treeRelativePath = path_1.relative(basePath, fileName);
            const fakeOutput = fakeRead ? fakeRead(treeRelativePath) : null;
            const buffer = fakeOutput === null ? tree.read(treeRelativePath) : fakeOutput;
            // Strip BOM as otherwise TSC methods (Ex: getWidth) will return an offset,
            // which breaks the CLI UpdateRecorder.
            // See: https://github.com/angular/angular/pull/30719
            return buffer ? buffer.toString().replace(/^\uFEFF/, '') : undefined;
        };
        return host;
    }
    exports.createMigrationCompilerHost = createMigrationCompilerHost;
    /**
     * Checks whether a file can be migrate by our automated migrations.
     * @param basePath Absolute path to the project.
     * @param sourceFile File being checked.
     * @param program Program that includes the source file.
     */
    function canMigrateFile(basePath, sourceFile, program) {
        // We shouldn't migrate .d.ts files or files from an external library.
        if (sourceFile.isDeclarationFile || program.isSourceFileFromExternalLibrary(sourceFile)) {
            return false;
        }
        // Our migrations are set up to create a `Program` from the project's tsconfig and to migrate all
        // the files within the program. This can include files that are outside of the Angular CLI
        // project. We can't migrate files outside of the project, because our file system interactions
        // go through the CLI's `Tree` which assumes that all files are within the project. See:
        // https://github.com/angular/angular-cli/blob/0b0961c9c233a825b6e4bb59ab7f0790f9b14676/packages/angular_devkit/schematics/src/tree/host-tree.ts#L131
        return !path_1.relative(basePath, sourceFile.fileName).startsWith('..');
    }
    exports.canMigrateFile = canMigrateFile;
});
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiY29tcGlsZXJfaG9zdC5qcyIsInNvdXJjZVJvb3QiOiIiLCJzb3VyY2VzIjpbIi4uLy4uLy4uLy4uLy4uLy4uLy4uLy4uL3BhY2thZ2VzL2NvcmUvc2NoZW1hdGljcy91dGlscy90eXBlc2NyaXB0L2NvbXBpbGVyX2hvc3QudHMiXSwibmFtZXMiOltdLCJtYXBwaW5ncyI6Ijs7Ozs7Ozs7Ozs7O0lBUUEsK0JBQWdEO0lBQ2hELGlDQUFpQztJQUNqQyw2RkFBbUQ7SUFJbkQ7Ozs7Ozs7OztPQVNHO0lBQ0gsU0FBZ0Isc0JBQXNCLENBQ2xDLElBQVUsRUFBRSxZQUFvQixFQUFFLFFBQWdCLEVBQUUsWUFBNkIsRUFDakYsZUFBMEI7UUFDNUIsd0ZBQXdGO1FBQ3hGLDJGQUEyRjtRQUMzRixnRkFBZ0Y7UUFDaEYsWUFBWSxHQUFHLGNBQU8sQ0FBQyxRQUFRLEVBQUUsWUFBWSxDQUFDLENBQUM7UUFDL0MsTUFBTSxNQUFNLEdBQUcsa0NBQWlCLENBQUMsWUFBWSxFQUFFLGNBQU8sQ0FBQyxZQUFZLENBQUMsQ0FBQyxDQUFDO1FBQ3RFLE1BQU0sSUFBSSxHQUFHLDJCQUEyQixDQUFDLElBQUksRUFBRSxNQUFNLENBQUMsT0FBTyxFQUFFLFFBQVEsRUFBRSxZQUFZLENBQUMsQ0FBQztRQUN2RixNQUFNLE9BQU8sR0FDVCxFQUFFLENBQUMsYUFBYSxDQUFDLE1BQU0sQ0FBQyxTQUFTLENBQUMsTUFBTSxDQUFDLGVBQWUsSUFBSSxFQUFFLENBQUMsRUFBRSxNQUFNLENBQUMsT0FBTyxFQUFFLElBQUksQ0FBQyxDQUFDO1FBQzNGLE9BQU8sRUFBQyxNQUFNLEVBQUUsSUFBSSxFQUFFLE9BQU8sRUFBQyxDQUFDO0lBQ2pDLENBQUM7SUFaRCx3REFZQztJQUVELFNBQWdCLDJCQUEyQixDQUN2QyxJQUFVLEVBQUUsT0FBMkIsRUFBRSxRQUFnQixFQUN6RCxRQUF5QjtRQUMzQixNQUFNLElBQUksR0FBRyxFQUFFLENBQUMsa0JBQWtCLENBQUMsT0FBTyxFQUFFLElBQUksQ0FBQyxDQUFDO1FBRWxELDZFQUE2RTtRQUM3RSwrRUFBK0U7UUFDL0UsdUVBQXVFO1FBQ3ZFLGdCQUFnQjtRQUNoQixJQUFJLENBQUMsUUFBUSxHQUFHLFFBQVEsQ0FBQyxFQUFFO1lBQ3pCLE1BQU0sZ0JBQWdCLEdBQUcsZUFBUSxDQUFDLFFBQVEsRUFBRSxRQUFRLENBQUMsQ0FBQztZQUN0RCxNQUFNLFVBQVUsR0FBRyxRQUFRLENBQUMsQ0FBQyxDQUFDLFFBQVEsQ0FBQyxnQkFBZ0IsQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFJLENBQUM7WUFDaEUsTUFBTSxNQUFNLEdBQUcsVUFBVSxLQUFLLElBQUksQ0FBQyxDQUFDLENBQUMsSUFBSSxDQUFDLElBQUksQ0FBQyxnQkFBZ0IsQ0FBQyxDQUFDLENBQUMsQ0FBQyxVQUFVLENBQUM7WUFDOUUsMkVBQTJFO1lBQzNFLHVDQUF1QztZQUN2QyxxREFBcUQ7WUFDckQsT0FBTyxNQUFNLENBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxRQUFRLEVBQUUsQ0FBQyxPQUFPLENBQUMsU0FBUyxFQUFFLEVBQUUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxTQUFTLENBQUM7UUFDdkUsQ0FBQyxDQUFDO1FBRUYsT0FBTyxJQUFJLENBQUM7SUFDZCxDQUFDO0lBcEJELGtFQW9CQztJQUVEOzs7OztPQUtHO0lBQ0gsU0FBZ0IsY0FBYyxDQUMxQixRQUFnQixFQUFFLFVBQXlCLEVBQUUsT0FBbUI7UUFDbEUsc0VBQXNFO1FBQ3RFLElBQUksVUFBVSxDQUFDLGlCQUFpQixJQUFJLE9BQU8sQ0FBQywrQkFBK0IsQ0FBQyxVQUFVLENBQUMsRUFBRTtZQUN2RixPQUFPLEtBQUssQ0FBQztTQUNkO1FBRUQsaUdBQWlHO1FBQ2pHLDJGQUEyRjtRQUMzRiwrRkFBK0Y7UUFDL0Ysd0ZBQXdGO1FBQ3hGLHFKQUFxSjtRQUNySixPQUFPLENBQUMsZUFBUSxDQUFDLFFBQVEsRUFBRSxVQUFVLENBQUMsUUFBUSxDQUFDLENBQUMsVUFBVSxDQUFDLElBQUksQ0FBQyxDQUFDO0lBQ25FLENBQUM7SUFiRCx3Q0FhQyIsInNvdXJjZXNDb250ZW50IjpbIi8qKlxuICogQGxpY2Vuc2VcbiAqIENvcHlyaWdodCBHb29nbGUgTExDIEFsbCBSaWdodHMgUmVzZXJ2ZWQuXG4gKlxuICogVXNlIG9mIHRoaXMgc291cmNlIGNvZGUgaXMgZ292ZXJuZWQgYnkgYW4gTUlULXN0eWxlIGxpY2Vuc2UgdGhhdCBjYW4gYmVcbiAqIGZvdW5kIGluIHRoZSBMSUNFTlNFIGZpbGUgYXQgaHR0cHM6Ly9hbmd1bGFyLmlvL2xpY2Vuc2VcbiAqL1xuaW1wb3J0IHtUcmVlfSBmcm9tICdAYW5ndWxhci1kZXZraXQvc2NoZW1hdGljcyc7XG5pbXBvcnQge2Rpcm5hbWUsIHJlbGF0aXZlLCByZXNvbHZlfSBmcm9tICdwYXRoJztcbmltcG9ydCAqIGFzIHRzIGZyb20gJ3R5cGVzY3JpcHQnO1xuaW1wb3J0IHtwYXJzZVRzY29uZmlnRmlsZX0gZnJvbSAnLi9wYXJzZV90c2NvbmZpZyc7XG5cbmV4cG9ydCB0eXBlIEZha2VSZWFkRmlsZUZuID0gKGZpbGVOYW1lOiBzdHJpbmcpID0+IHN0cmluZ3xudWxsO1xuXG4vKipcbiAqIENyZWF0ZXMgYSBUeXBlU2NyaXB0IHByb2dyYW0gaW5zdGFuY2UgZm9yIGEgVHlwZVNjcmlwdCBwcm9qZWN0IHdpdGhpblxuICogdGhlIHZpcnR1YWwgZmlsZSBzeXN0ZW0gdHJlZS5cbiAqIEBwYXJhbSB0cmVlIFZpcnR1YWwgZmlsZSBzeXN0ZW0gdHJlZSB0aGF0IGNvbnRhaW5zIHRoZSBzb3VyY2UgZmlsZXMuXG4gKiBAcGFyYW0gdHNjb25maWdQYXRoIFZpcnR1YWwgZmlsZSBzeXN0ZW0gcGF0aCB0aGF0IHJlc29sdmVzIHRvIHRoZSBUeXBlU2NyaXB0IHByb2plY3QuXG4gKiBAcGFyYW0gYmFzZVBhdGggQmFzZSBwYXRoIGZvciB0aGUgdmlydHVhbCBmaWxlIHN5c3RlbSB0cmVlLlxuICogQHBhcmFtIGZha2VGaWxlUmVhZCBPcHRpb25hbCBmaWxlIHJlYWRlciBmdW5jdGlvbi4gQ2FuIGJlIHVzZWQgdG8gb3ZlcndyaXRlIGZpbGVzIGluXG4gKiAgIHRoZSBUeXBlU2NyaXB0IHByb2dyYW0sIG9yIHRvIGFkZCBpbi1tZW1vcnkgZmlsZXMgKGUuZy4gdG8gYWRkIGdsb2JhbCB0eXBlcykuXG4gKiBAcGFyYW0gYWRkaXRpb25hbEZpbGVzIEFkZGl0aW9uYWwgZmlsZSBwYXRocyB0aGF0IHNob3VsZCBiZSBhZGRlZCB0byB0aGUgcHJvZ3JhbS5cbiAqL1xuZXhwb3J0IGZ1bmN0aW9uIGNyZWF0ZU1pZ3JhdGlvblByb2dyYW0oXG4gICAgdHJlZTogVHJlZSwgdHNjb25maWdQYXRoOiBzdHJpbmcsIGJhc2VQYXRoOiBzdHJpbmcsIGZha2VGaWxlUmVhZD86IEZha2VSZWFkRmlsZUZuLFxuICAgIGFkZGl0aW9uYWxGaWxlcz86IHN0cmluZ1tdKSB7XG4gIC8vIFJlc29sdmUgdGhlIHRzY29uZmlnIHBhdGggdG8gYW4gYWJzb2x1dGUgcGF0aC4gVGhpcyBpcyBuZWVkZWQgYXMgVHlwZVNjcmlwdCBvdGhlcndpc2VcbiAgLy8gaXMgbm90IGFibGUgdG8gcmVzb2x2ZSByb290IGRpcmVjdG9yaWVzIGluIHRoZSBnaXZlbiB0c2NvbmZpZy4gTW9yZSBkZXRhaWxzIGNhbiBiZSBmb3VuZFxuICAvLyBpbiB0aGUgZm9sbG93aW5nIGlzc3VlOiBodHRwczovL2dpdGh1Yi5jb20vbWljcm9zb2Z0L1R5cGVTY3JpcHQvaXNzdWVzLzM3NzMxLlxuICB0c2NvbmZpZ1BhdGggPSByZXNvbHZlKGJhc2VQYXRoLCB0c2NvbmZpZ1BhdGgpO1xuICBjb25zdCBwYXJzZWQgPSBwYXJzZVRzY29uZmlnRmlsZSh0c2NvbmZpZ1BhdGgsIGRpcm5hbWUodHNjb25maWdQYXRoKSk7XG4gIGNvbnN0IGhvc3QgPSBjcmVhdGVNaWdyYXRpb25Db21waWxlckhvc3QodHJlZSwgcGFyc2VkLm9wdGlvbnMsIGJhc2VQYXRoLCBmYWtlRmlsZVJlYWQpO1xuICBjb25zdCBwcm9ncmFtID1cbiAgICAgIHRzLmNyZWF0ZVByb2dyYW0ocGFyc2VkLmZpbGVOYW1lcy5jb25jYXQoYWRkaXRpb25hbEZpbGVzIHx8IFtdKSwgcGFyc2VkLm9wdGlvbnMsIGhvc3QpO1xuICByZXR1cm4ge3BhcnNlZCwgaG9zdCwgcHJvZ3JhbX07XG59XG5cbmV4cG9ydCBmdW5jdGlvbiBjcmVhdGVNaWdyYXRpb25Db21waWxlckhvc3QoXG4gICAgdHJlZTogVHJlZSwgb3B0aW9uczogdHMuQ29tcGlsZXJPcHRpb25zLCBiYXNlUGF0aDogc3RyaW5nLFxuICAgIGZha2VSZWFkPzogRmFrZVJlYWRGaWxlRm4pOiB0cy5Db21waWxlckhvc3Qge1xuICBjb25zdCBob3N0ID0gdHMuY3JlYXRlQ29tcGlsZXJIb3N0KG9wdGlvbnMsIHRydWUpO1xuXG4gIC8vIFdlIG5lZWQgdG8gb3ZlcndyaXRlIHRoZSBob3N0IFwicmVhZEZpbGVcIiBtZXRob2QsIGFzIHdlIHdhbnQgdGhlIFR5cGVTY3JpcHRcbiAgLy8gcHJvZ3JhbSB0byBiZSBiYXNlZCBvbiB0aGUgZmlsZSBjb250ZW50cyBpbiB0aGUgdmlydHVhbCBmaWxlIHRyZWUuIE90aGVyd2lzZVxuICAvLyBpZiB3ZSBydW4gbXVsdGlwbGUgbWlncmF0aW9ucyB3ZSBtaWdodCBoYXZlIGludGVyc2VjdGluZyBjaGFuZ2VzIGFuZFxuICAvLyBzb3VyY2UgZmlsZXMuXG4gIGhvc3QucmVhZEZpbGUgPSBmaWxlTmFtZSA9PiB7XG4gICAgY29uc3QgdHJlZVJlbGF0aXZlUGF0aCA9IHJlbGF0aXZlKGJhc2VQYXRoLCBmaWxlTmFtZSk7XG4gICAgY29uc3QgZmFrZU91dHB1dCA9IGZha2VSZWFkID8gZmFrZVJlYWQodHJlZVJlbGF0aXZlUGF0aCkgOiBudWxsO1xuICAgIGNvbnN0IGJ1ZmZlciA9IGZha2VPdXRwdXQgPT09IG51bGwgPyB0cmVlLnJlYWQodHJlZVJlbGF0aXZlUGF0aCkgOiBmYWtlT3V0cHV0O1xuICAgIC8vIFN0cmlwIEJPTSBhcyBvdGhlcndpc2UgVFNDIG1ldGhvZHMgKEV4OiBnZXRXaWR0aCkgd2lsbCByZXR1cm4gYW4gb2Zmc2V0LFxuICAgIC8vIHdoaWNoIGJyZWFrcyB0aGUgQ0xJIFVwZGF0ZVJlY29yZGVyLlxuICAgIC8vIFNlZTogaHR0cHM6Ly9naXRodWIuY29tL2FuZ3VsYXIvYW5ndWxhci9wdWxsLzMwNzE5XG4gICAgcmV0dXJuIGJ1ZmZlciA/IGJ1ZmZlci50b1N0cmluZygpLnJlcGxhY2UoL15cXHVGRUZGLywgJycpIDogdW5kZWZpbmVkO1xuICB9O1xuXG4gIHJldHVybiBob3N0O1xufVxuXG4vKipcbiAqIENoZWNrcyB3aGV0aGVyIGEgZmlsZSBjYW4gYmUgbWlncmF0ZSBieSBvdXIgYXV0b21hdGVkIG1pZ3JhdGlvbnMuXG4gKiBAcGFyYW0gYmFzZVBhdGggQWJzb2x1dGUgcGF0aCB0byB0aGUgcHJvamVjdC5cbiAqIEBwYXJhbSBzb3VyY2VGaWxlIEZpbGUgYmVpbmcgY2hlY2tlZC5cbiAqIEBwYXJhbSBwcm9ncmFtIFByb2dyYW0gdGhhdCBpbmNsdWRlcyB0aGUgc291cmNlIGZpbGUuXG4gKi9cbmV4cG9ydCBmdW5jdGlvbiBjYW5NaWdyYXRlRmlsZShcbiAgICBiYXNlUGF0aDogc3RyaW5nLCBzb3VyY2VGaWxlOiB0cy5Tb3VyY2VGaWxlLCBwcm9ncmFtOiB0cy5Qcm9ncmFtKTogYm9vbGVhbiB7XG4gIC8vIFdlIHNob3VsZG4ndCBtaWdyYXRlIC5kLnRzIGZpbGVzIG9yIGZpbGVzIGZyb20gYW4gZXh0ZXJuYWwgbGlicmFyeS5cbiAgaWYgKHNvdXJjZUZpbGUuaXNEZWNsYXJhdGlvbkZpbGUgfHwgcHJvZ3JhbS5pc1NvdXJjZUZpbGVGcm9tRXh0ZXJuYWxMaWJyYXJ5KHNvdXJjZUZpbGUpKSB7XG4gICAgcmV0dXJuIGZhbHNlO1xuICB9XG5cbiAgLy8gT3VyIG1pZ3JhdGlvbnMgYXJlIHNldCB1cCB0byBjcmVhdGUgYSBgUHJvZ3JhbWAgZnJvbSB0aGUgcHJvamVjdCdzIHRzY29uZmlnIGFuZCB0byBtaWdyYXRlIGFsbFxuICAvLyB0aGUgZmlsZXMgd2l0aGluIHRoZSBwcm9ncmFtLiBUaGlzIGNhbiBpbmNsdWRlIGZpbGVzIHRoYXQgYXJlIG91dHNpZGUgb2YgdGhlIEFuZ3VsYXIgQ0xJXG4gIC8vIHByb2plY3QuIFdlIGNhbid0IG1pZ3JhdGUgZmlsZXMgb3V0c2lkZSBvZiB0aGUgcHJvamVjdCwgYmVjYXVzZSBvdXIgZmlsZSBzeXN0ZW0gaW50ZXJhY3Rpb25zXG4gIC8vIGdvIHRocm91Z2ggdGhlIENMSSdzIGBUcmVlYCB3aGljaCBhc3N1bWVzIHRoYXQgYWxsIGZpbGVzIGFyZSB3aXRoaW4gdGhlIHByb2plY3QuIFNlZTpcbiAgLy8gaHR0cHM6Ly9naXRodWIuY29tL2FuZ3VsYXIvYW5ndWxhci1jbGkvYmxvYi8wYjA5NjFjOWMyMzNhODI1YjZlNGJiNTlhYjdmMDc5MGY5YjE0Njc2L3BhY2thZ2VzL2FuZ3VsYXJfZGV2a2l0L3NjaGVtYXRpY3Mvc3JjL3RyZWUvaG9zdC10cmVlLnRzI0wxMzFcbiAgcmV0dXJuICFyZWxhdGl2ZShiYXNlUGF0aCwgc291cmNlRmlsZS5maWxlTmFtZSkuc3RhcnRzV2l0aCgnLi4nKTtcbn1cbiJdfQ==