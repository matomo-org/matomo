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
        define("@angular/core/schematics/migrations/undecorated-classes-with-di/create_ngc_program", ["require", "exports", "@angular/compiler-cli"], factory);
    }
})(function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.createNgcProgram = void 0;
    const compiler_cli_1 = require("@angular/compiler-cli");
    /** Creates an NGC program that can be used to read and parse metadata for files. */
    function createNgcProgram(createHost, tsconfigPath) {
        const { rootNames, options } = compiler_cli_1.readConfiguration(tsconfigPath);
        // https://github.com/angular/angular/commit/ec4381dd401f03bded652665b047b6b90f2b425f made Ivy
        // the default. This breaks the assumption that "createProgram" from compiler-cli returns the
        // NGC program. In order to ensure that the migration runs properly, we set "enableIvy" to false.
        options.enableIvy = false;
        // Libraries which have been generated with CLI versions past v6.2.0, explicitly set the
        // flat-module options in their tsconfig files. This is problematic because by default,
        // those tsconfig files do not specify explicit source files which can be considered as
        // entry point for the flat-module bundle. Therefore the `@angular/compiler-cli` is unable
        // to determine the flat module entry point and throws a compile error. This is not an issue
        // for the libraries built with `ng-packagr`, because the tsconfig files are modified in-memory
        // to specify an explicit flat module entry-point. Our migrations don't distinguish between
        // libraries and applications, and also don't run `ng-packagr`. To ensure that such libraries
        // can be successfully migrated, we remove the flat-module options to eliminate the flat module
        // entry-point requirement. More context: https://github.com/angular/angular/issues/34985.
        options.flatModuleId = undefined;
        options.flatModuleOutFile = undefined;
        const host = createHost(options);
        // For this migration, we never need to read resources and can just return
        // an empty string for requested resources. We need to handle requested resources
        // because our created NGC compiler program does not know about special resolutions
        // which are set up by the Angular CLI. i.e. resolving stylesheets through "tilde".
        host.readResource = () => '';
        host.resourceNameToFileName = () => '$fake-file$';
        const ngcProgram = compiler_cli_1.createProgram({ rootNames, options, host });
        // The "AngularCompilerProgram" does not expose the "AotCompiler" instance, nor does it
        // expose the logic that is necessary to analyze the determined modules. We work around
        // this by just accessing the necessary private properties using the bracket notation.
        const compiler = ngcProgram['compiler'];
        const program = ngcProgram.getTsProgram();
        return { host, ngcProgram, program, compiler };
    }
    exports.createNgcProgram = createNgcProgram;
});
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiY3JlYXRlX25nY19wcm9ncmFtLmpzIiwic291cmNlUm9vdCI6IiIsInNvdXJjZXMiOlsiLi4vLi4vLi4vLi4vLi4vLi4vLi4vLi4vcGFja2FnZXMvY29yZS9zY2hlbWF0aWNzL21pZ3JhdGlvbnMvdW5kZWNvcmF0ZWQtY2xhc3Nlcy13aXRoLWRpL2NyZWF0ZV9uZ2NfcHJvZ3JhbS50cyJdLCJuYW1lcyI6W10sIm1hcHBpbmdzIjoiQUFBQTs7Ozs7O0dBTUc7Ozs7Ozs7Ozs7Ozs7SUFHSCx3REFBcUY7SUFHckYsb0ZBQW9GO0lBQ3BGLFNBQWdCLGdCQUFnQixDQUM1QixVQUF5RCxFQUFFLFlBQW9CO1FBQ2pGLE1BQU0sRUFBQyxTQUFTLEVBQUUsT0FBTyxFQUFDLEdBQUcsZ0NBQWlCLENBQUMsWUFBWSxDQUFDLENBQUM7UUFFN0QsOEZBQThGO1FBQzlGLDZGQUE2RjtRQUM3RixpR0FBaUc7UUFDakcsT0FBTyxDQUFDLFNBQVMsR0FBRyxLQUFLLENBQUM7UUFFMUIsd0ZBQXdGO1FBQ3hGLHVGQUF1RjtRQUN2Rix1RkFBdUY7UUFDdkYsMEZBQTBGO1FBQzFGLDRGQUE0RjtRQUM1RiwrRkFBK0Y7UUFDL0YsMkZBQTJGO1FBQzNGLDZGQUE2RjtRQUM3RiwrRkFBK0Y7UUFDL0YsMEZBQTBGO1FBQzFGLE9BQU8sQ0FBQyxZQUFZLEdBQUcsU0FBUyxDQUFDO1FBQ2pDLE9BQU8sQ0FBQyxpQkFBaUIsR0FBRyxTQUFTLENBQUM7UUFFdEMsTUFBTSxJQUFJLEdBQUcsVUFBVSxDQUFDLE9BQU8sQ0FBQyxDQUFDO1FBRWpDLDBFQUEwRTtRQUMxRSxpRkFBaUY7UUFDakYsbUZBQW1GO1FBQ25GLG1GQUFtRjtRQUNuRixJQUFJLENBQUMsWUFBWSxHQUFHLEdBQUcsRUFBRSxDQUFDLEVBQUUsQ0FBQztRQUM3QixJQUFJLENBQUMsc0JBQXNCLEdBQUcsR0FBRyxFQUFFLENBQUMsYUFBYSxDQUFDO1FBRWxELE1BQU0sVUFBVSxHQUFHLDRCQUFhLENBQUMsRUFBQyxTQUFTLEVBQUUsT0FBTyxFQUFFLElBQUksRUFBQyxDQUFDLENBQUM7UUFFN0QsdUZBQXVGO1FBQ3ZGLHVGQUF1RjtRQUN2RixzRkFBc0Y7UUFDdEYsTUFBTSxRQUFRLEdBQWlCLFVBQWtCLENBQUMsVUFBVSxDQUFDLENBQUM7UUFDOUQsTUFBTSxPQUFPLEdBQUcsVUFBVSxDQUFDLFlBQVksRUFBRSxDQUFDO1FBRTFDLE9BQU8sRUFBQyxJQUFJLEVBQUUsVUFBVSxFQUFFLE9BQU8sRUFBRSxRQUFRLEVBQUMsQ0FBQztJQUMvQyxDQUFDO0lBeENELDRDQXdDQyIsInNvdXJjZXNDb250ZW50IjpbIi8qKlxuICogQGxpY2Vuc2VcbiAqIENvcHlyaWdodCBHb29nbGUgTExDIEFsbCBSaWdodHMgUmVzZXJ2ZWQuXG4gKlxuICogVXNlIG9mIHRoaXMgc291cmNlIGNvZGUgaXMgZ292ZXJuZWQgYnkgYW4gTUlULXN0eWxlIGxpY2Vuc2UgdGhhdCBjYW4gYmVcbiAqIGZvdW5kIGluIHRoZSBMSUNFTlNFIGZpbGUgYXQgaHR0cHM6Ly9hbmd1bGFyLmlvL2xpY2Vuc2VcbiAqL1xuXG5pbXBvcnQge0FvdENvbXBpbGVyfSBmcm9tICdAYW5ndWxhci9jb21waWxlcic7XG5pbXBvcnQge0NvbXBpbGVySG9zdCwgY3JlYXRlUHJvZ3JhbSwgcmVhZENvbmZpZ3VyYXRpb259IGZyb20gJ0Bhbmd1bGFyL2NvbXBpbGVyLWNsaSc7XG5pbXBvcnQgKiBhcyB0cyBmcm9tICd0eXBlc2NyaXB0JztcblxuLyoqIENyZWF0ZXMgYW4gTkdDIHByb2dyYW0gdGhhdCBjYW4gYmUgdXNlZCB0byByZWFkIGFuZCBwYXJzZSBtZXRhZGF0YSBmb3IgZmlsZXMuICovXG5leHBvcnQgZnVuY3Rpb24gY3JlYXRlTmdjUHJvZ3JhbShcbiAgICBjcmVhdGVIb3N0OiAob3B0aW9uczogdHMuQ29tcGlsZXJPcHRpb25zKSA9PiBDb21waWxlckhvc3QsIHRzY29uZmlnUGF0aDogc3RyaW5nKSB7XG4gIGNvbnN0IHtyb290TmFtZXMsIG9wdGlvbnN9ID0gcmVhZENvbmZpZ3VyYXRpb24odHNjb25maWdQYXRoKTtcblxuICAvLyBodHRwczovL2dpdGh1Yi5jb20vYW5ndWxhci9hbmd1bGFyL2NvbW1pdC9lYzQzODFkZDQwMWYwM2JkZWQ2NTI2NjViMDQ3YjZiOTBmMmI0MjVmIG1hZGUgSXZ5XG4gIC8vIHRoZSBkZWZhdWx0LiBUaGlzIGJyZWFrcyB0aGUgYXNzdW1wdGlvbiB0aGF0IFwiY3JlYXRlUHJvZ3JhbVwiIGZyb20gY29tcGlsZXItY2xpIHJldHVybnMgdGhlXG4gIC8vIE5HQyBwcm9ncmFtLiBJbiBvcmRlciB0byBlbnN1cmUgdGhhdCB0aGUgbWlncmF0aW9uIHJ1bnMgcHJvcGVybHksIHdlIHNldCBcImVuYWJsZUl2eVwiIHRvIGZhbHNlLlxuICBvcHRpb25zLmVuYWJsZUl2eSA9IGZhbHNlO1xuXG4gIC8vIExpYnJhcmllcyB3aGljaCBoYXZlIGJlZW4gZ2VuZXJhdGVkIHdpdGggQ0xJIHZlcnNpb25zIHBhc3QgdjYuMi4wLCBleHBsaWNpdGx5IHNldCB0aGVcbiAgLy8gZmxhdC1tb2R1bGUgb3B0aW9ucyBpbiB0aGVpciB0c2NvbmZpZyBmaWxlcy4gVGhpcyBpcyBwcm9ibGVtYXRpYyBiZWNhdXNlIGJ5IGRlZmF1bHQsXG4gIC8vIHRob3NlIHRzY29uZmlnIGZpbGVzIGRvIG5vdCBzcGVjaWZ5IGV4cGxpY2l0IHNvdXJjZSBmaWxlcyB3aGljaCBjYW4gYmUgY29uc2lkZXJlZCBhc1xuICAvLyBlbnRyeSBwb2ludCBmb3IgdGhlIGZsYXQtbW9kdWxlIGJ1bmRsZS4gVGhlcmVmb3JlIHRoZSBgQGFuZ3VsYXIvY29tcGlsZXItY2xpYCBpcyB1bmFibGVcbiAgLy8gdG8gZGV0ZXJtaW5lIHRoZSBmbGF0IG1vZHVsZSBlbnRyeSBwb2ludCBhbmQgdGhyb3dzIGEgY29tcGlsZSBlcnJvci4gVGhpcyBpcyBub3QgYW4gaXNzdWVcbiAgLy8gZm9yIHRoZSBsaWJyYXJpZXMgYnVpbHQgd2l0aCBgbmctcGFja2FncmAsIGJlY2F1c2UgdGhlIHRzY29uZmlnIGZpbGVzIGFyZSBtb2RpZmllZCBpbi1tZW1vcnlcbiAgLy8gdG8gc3BlY2lmeSBhbiBleHBsaWNpdCBmbGF0IG1vZHVsZSBlbnRyeS1wb2ludC4gT3VyIG1pZ3JhdGlvbnMgZG9uJ3QgZGlzdGluZ3Vpc2ggYmV0d2VlblxuICAvLyBsaWJyYXJpZXMgYW5kIGFwcGxpY2F0aW9ucywgYW5kIGFsc28gZG9uJ3QgcnVuIGBuZy1wYWNrYWdyYC4gVG8gZW5zdXJlIHRoYXQgc3VjaCBsaWJyYXJpZXNcbiAgLy8gY2FuIGJlIHN1Y2Nlc3NmdWxseSBtaWdyYXRlZCwgd2UgcmVtb3ZlIHRoZSBmbGF0LW1vZHVsZSBvcHRpb25zIHRvIGVsaW1pbmF0ZSB0aGUgZmxhdCBtb2R1bGVcbiAgLy8gZW50cnktcG9pbnQgcmVxdWlyZW1lbnQuIE1vcmUgY29udGV4dDogaHR0cHM6Ly9naXRodWIuY29tL2FuZ3VsYXIvYW5ndWxhci9pc3N1ZXMvMzQ5ODUuXG4gIG9wdGlvbnMuZmxhdE1vZHVsZUlkID0gdW5kZWZpbmVkO1xuICBvcHRpb25zLmZsYXRNb2R1bGVPdXRGaWxlID0gdW5kZWZpbmVkO1xuXG4gIGNvbnN0IGhvc3QgPSBjcmVhdGVIb3N0KG9wdGlvbnMpO1xuXG4gIC8vIEZvciB0aGlzIG1pZ3JhdGlvbiwgd2UgbmV2ZXIgbmVlZCB0byByZWFkIHJlc291cmNlcyBhbmQgY2FuIGp1c3QgcmV0dXJuXG4gIC8vIGFuIGVtcHR5IHN0cmluZyBmb3IgcmVxdWVzdGVkIHJlc291cmNlcy4gV2UgbmVlZCB0byBoYW5kbGUgcmVxdWVzdGVkIHJlc291cmNlc1xuICAvLyBiZWNhdXNlIG91ciBjcmVhdGVkIE5HQyBjb21waWxlciBwcm9ncmFtIGRvZXMgbm90IGtub3cgYWJvdXQgc3BlY2lhbCByZXNvbHV0aW9uc1xuICAvLyB3aGljaCBhcmUgc2V0IHVwIGJ5IHRoZSBBbmd1bGFyIENMSS4gaS5lLiByZXNvbHZpbmcgc3R5bGVzaGVldHMgdGhyb3VnaCBcInRpbGRlXCIuXG4gIGhvc3QucmVhZFJlc291cmNlID0gKCkgPT4gJyc7XG4gIGhvc3QucmVzb3VyY2VOYW1lVG9GaWxlTmFtZSA9ICgpID0+ICckZmFrZS1maWxlJCc7XG5cbiAgY29uc3QgbmdjUHJvZ3JhbSA9IGNyZWF0ZVByb2dyYW0oe3Jvb3ROYW1lcywgb3B0aW9ucywgaG9zdH0pO1xuXG4gIC8vIFRoZSBcIkFuZ3VsYXJDb21waWxlclByb2dyYW1cIiBkb2VzIG5vdCBleHBvc2UgdGhlIFwiQW90Q29tcGlsZXJcIiBpbnN0YW5jZSwgbm9yIGRvZXMgaXRcbiAgLy8gZXhwb3NlIHRoZSBsb2dpYyB0aGF0IGlzIG5lY2Vzc2FyeSB0byBhbmFseXplIHRoZSBkZXRlcm1pbmVkIG1vZHVsZXMuIFdlIHdvcmsgYXJvdW5kXG4gIC8vIHRoaXMgYnkganVzdCBhY2Nlc3NpbmcgdGhlIG5lY2Vzc2FyeSBwcml2YXRlIHByb3BlcnRpZXMgdXNpbmcgdGhlIGJyYWNrZXQgbm90YXRpb24uXG4gIGNvbnN0IGNvbXBpbGVyOiBBb3RDb21waWxlciA9IChuZ2NQcm9ncmFtIGFzIGFueSlbJ2NvbXBpbGVyJ107XG4gIGNvbnN0IHByb2dyYW0gPSBuZ2NQcm9ncmFtLmdldFRzUHJvZ3JhbSgpO1xuXG4gIHJldHVybiB7aG9zdCwgbmdjUHJvZ3JhbSwgcHJvZ3JhbSwgY29tcGlsZXJ9O1xufVxuIl19