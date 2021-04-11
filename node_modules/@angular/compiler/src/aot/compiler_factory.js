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
        define("@angular/compiler/src/aot/compiler_factory", ["require", "exports", "@angular/compiler/src/config", "@angular/compiler/src/core", "@angular/compiler/src/directive_normalizer", "@angular/compiler/src/directive_resolver", "@angular/compiler/src/expression_parser/lexer", "@angular/compiler/src/expression_parser/parser", "@angular/compiler/src/i18n/i18n_html_parser", "@angular/compiler/src/injectable_compiler", "@angular/compiler/src/metadata_resolver", "@angular/compiler/src/ml_parser/html_parser", "@angular/compiler/src/ng_module_compiler", "@angular/compiler/src/ng_module_resolver", "@angular/compiler/src/output/ts_emitter", "@angular/compiler/src/pipe_resolver", "@angular/compiler/src/schema/dom_element_schema_registry", "@angular/compiler/src/style_compiler", "@angular/compiler/src/template_parser/template_parser", "@angular/compiler/src/util", "@angular/compiler/src/view_compiler/type_check_compiler", "@angular/compiler/src/view_compiler/view_compiler", "@angular/compiler/src/aot/compiler", "@angular/compiler/src/aot/static_reflector", "@angular/compiler/src/aot/static_symbol", "@angular/compiler/src/aot/static_symbol_resolver", "@angular/compiler/src/aot/summary_resolver"], factory);
    }
})(function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.createAotCompiler = exports.createAotUrlResolver = void 0;
    var config_1 = require("@angular/compiler/src/config");
    var core_1 = require("@angular/compiler/src/core");
    var directive_normalizer_1 = require("@angular/compiler/src/directive_normalizer");
    var directive_resolver_1 = require("@angular/compiler/src/directive_resolver");
    var lexer_1 = require("@angular/compiler/src/expression_parser/lexer");
    var parser_1 = require("@angular/compiler/src/expression_parser/parser");
    var i18n_html_parser_1 = require("@angular/compiler/src/i18n/i18n_html_parser");
    var injectable_compiler_1 = require("@angular/compiler/src/injectable_compiler");
    var metadata_resolver_1 = require("@angular/compiler/src/metadata_resolver");
    var html_parser_1 = require("@angular/compiler/src/ml_parser/html_parser");
    var ng_module_compiler_1 = require("@angular/compiler/src/ng_module_compiler");
    var ng_module_resolver_1 = require("@angular/compiler/src/ng_module_resolver");
    var ts_emitter_1 = require("@angular/compiler/src/output/ts_emitter");
    var pipe_resolver_1 = require("@angular/compiler/src/pipe_resolver");
    var dom_element_schema_registry_1 = require("@angular/compiler/src/schema/dom_element_schema_registry");
    var style_compiler_1 = require("@angular/compiler/src/style_compiler");
    var template_parser_1 = require("@angular/compiler/src/template_parser/template_parser");
    var util_1 = require("@angular/compiler/src/util");
    var type_check_compiler_1 = require("@angular/compiler/src/view_compiler/type_check_compiler");
    var view_compiler_1 = require("@angular/compiler/src/view_compiler/view_compiler");
    var compiler_1 = require("@angular/compiler/src/aot/compiler");
    var static_reflector_1 = require("@angular/compiler/src/aot/static_reflector");
    var static_symbol_1 = require("@angular/compiler/src/aot/static_symbol");
    var static_symbol_resolver_1 = require("@angular/compiler/src/aot/static_symbol_resolver");
    var summary_resolver_1 = require("@angular/compiler/src/aot/summary_resolver");
    function createAotUrlResolver(host) {
        return {
            resolve: function (basePath, url) {
                var filePath = host.resourceNameToFileName(url, basePath);
                if (!filePath) {
                    throw util_1.syntaxError("Couldn't resolve resource " + url + " from " + basePath);
                }
                return filePath;
            }
        };
    }
    exports.createAotUrlResolver = createAotUrlResolver;
    /**
     * Creates a new AotCompiler based on options and a host.
     */
    function createAotCompiler(compilerHost, options, errorCollector) {
        var translations = options.translations || '';
        var urlResolver = createAotUrlResolver(compilerHost);
        var symbolCache = new static_symbol_1.StaticSymbolCache();
        var summaryResolver = new summary_resolver_1.AotSummaryResolver(compilerHost, symbolCache);
        var symbolResolver = new static_symbol_resolver_1.StaticSymbolResolver(compilerHost, symbolCache, summaryResolver);
        var staticReflector = new static_reflector_1.StaticReflector(summaryResolver, symbolResolver, [], [], errorCollector);
        var htmlParser;
        if (!!options.enableIvy) {
            // Ivy handles i18n at the compiler level so we must use a regular parser
            htmlParser = new html_parser_1.HtmlParser();
        }
        else {
            htmlParser = new i18n_html_parser_1.I18NHtmlParser(new html_parser_1.HtmlParser(), translations, options.i18nFormat, options.missingTranslation, console);
        }
        var config = new config_1.CompilerConfig({
            defaultEncapsulation: core_1.ViewEncapsulation.Emulated,
            useJit: false,
            missingTranslation: options.missingTranslation,
            preserveWhitespaces: options.preserveWhitespaces,
            strictInjectionParameters: options.strictInjectionParameters,
        });
        var normalizer = new directive_normalizer_1.DirectiveNormalizer({ get: function (url) { return compilerHost.loadResource(url); } }, urlResolver, htmlParser, config);
        var expressionParser = new parser_1.Parser(new lexer_1.Lexer());
        var elementSchemaRegistry = new dom_element_schema_registry_1.DomElementSchemaRegistry();
        var tmplParser = new template_parser_1.TemplateParser(config, staticReflector, expressionParser, elementSchemaRegistry, htmlParser, console, []);
        var resolver = new metadata_resolver_1.CompileMetadataResolver(config, htmlParser, new ng_module_resolver_1.NgModuleResolver(staticReflector), new directive_resolver_1.DirectiveResolver(staticReflector), new pipe_resolver_1.PipeResolver(staticReflector), summaryResolver, elementSchemaRegistry, normalizer, console, symbolCache, staticReflector, errorCollector);
        // TODO(vicb): do not pass options.i18nFormat here
        var viewCompiler = new view_compiler_1.ViewCompiler(staticReflector);
        var typeCheckCompiler = new type_check_compiler_1.TypeCheckCompiler(options, staticReflector);
        var compiler = new compiler_1.AotCompiler(config, options, compilerHost, staticReflector, resolver, tmplParser, new style_compiler_1.StyleCompiler(urlResolver), viewCompiler, typeCheckCompiler, new ng_module_compiler_1.NgModuleCompiler(staticReflector), new injectable_compiler_1.InjectableCompiler(staticReflector, !!options.enableIvy), new ts_emitter_1.TypeScriptEmitter(), summaryResolver, symbolResolver);
        return { compiler: compiler, reflector: staticReflector };
    }
    exports.createAotCompiler = createAotCompiler;
});
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiY29tcGlsZXJfZmFjdG9yeS5qcyIsInNvdXJjZVJvb3QiOiIiLCJzb3VyY2VzIjpbIi4uLy4uLy4uLy4uLy4uLy4uLy4uL3BhY2thZ2VzL2NvbXBpbGVyL3NyYy9hb3QvY29tcGlsZXJfZmFjdG9yeS50cyJdLCJuYW1lcyI6W10sIm1hcHBpbmdzIjoiQUFBQTs7Ozs7O0dBTUc7Ozs7Ozs7Ozs7Ozs7SUFFSCx1REFBeUM7SUFDekMsbURBQTBDO0lBQzFDLG1GQUE0RDtJQUM1RCwrRUFBd0Q7SUFDeEQsdUVBQWlEO0lBQ2pELHlFQUFtRDtJQUNuRCxnRkFBd0Q7SUFDeEQsaUZBQTBEO0lBQzFELDZFQUE2RDtJQUM3RCwyRUFBb0Q7SUFDcEQsK0VBQXVEO0lBQ3ZELCtFQUF1RDtJQUN2RCxzRUFBdUQ7SUFDdkQscUVBQThDO0lBQzlDLHdHQUErRTtJQUMvRSx1RUFBZ0Q7SUFDaEQseUZBQWtFO0lBRWxFLG1EQUFvQztJQUNwQywrRkFBdUU7SUFDdkUsbUZBQTREO0lBRTVELCtEQUF1QztJQUd2QywrRUFBbUQ7SUFDbkQseUVBQWtEO0lBQ2xELDJGQUE4RDtJQUM5RCwrRUFBc0Q7SUFFdEQsU0FBZ0Isb0JBQW9CLENBQ2hDLElBQThGO1FBRWhHLE9BQU87WUFDTCxPQUFPLEVBQUUsVUFBQyxRQUFnQixFQUFFLEdBQVc7Z0JBQ3JDLElBQU0sUUFBUSxHQUFHLElBQUksQ0FBQyxzQkFBc0IsQ0FBQyxHQUFHLEVBQUUsUUFBUSxDQUFDLENBQUM7Z0JBQzVELElBQUksQ0FBQyxRQUFRLEVBQUU7b0JBQ2IsTUFBTSxrQkFBVyxDQUFDLCtCQUE2QixHQUFHLGNBQVMsUUFBVSxDQUFDLENBQUM7aUJBQ3hFO2dCQUNELE9BQU8sUUFBUSxDQUFDO1lBQ2xCLENBQUM7U0FDRixDQUFDO0lBQ0osQ0FBQztJQVpELG9EQVlDO0lBRUQ7O09BRUc7SUFDSCxTQUFnQixpQkFBaUIsQ0FDN0IsWUFBNkIsRUFBRSxPQUEyQixFQUMxRCxjQUNRO1FBQ1YsSUFBSSxZQUFZLEdBQVcsT0FBTyxD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QUUsSUFBSSw4QkFBaUIsRUFBRSxFQUNyRixlQUFlLEVBQUUsY0FBYyxDQUFDLENBQUM7UUFDckMsT0FBTyxFQUFDLFFBQVEsVUFBQSxFQUFFLFNBQVMsRUFBRSxlQUFlLEVBQUMsQ0FBQztJQUNoRCxDQUFDO0lBL0NELDhDQStDQyIsInNvdXJjZXNDb250ZW50IjpbIi8qKlxuICogQGxpY2Vuc2VcbiAqIENvcHlyaWdodCBHb29nbGUgTExDIEFsbCBSaWdodHMgUmVzZXJ2ZWQuXG4gKlxuICogVXNlIG9mIHRoaXMgc291cmNlIGNvZGUgaXMgZ292ZXJuZWQgYnkgYW4gTUlULXN0eWxlIGxpY2Vuc2UgdGhhdCBjYW4gYmVcbiAqIGZvdW5kIGluIHRoZSBMSUNFTlNFIGZpbGUgYXQgaHR0cHM6Ly9hbmd1bGFyLmlvL2xpY2Vuc2VcbiAqL1xuXG5pbXBvcnQge0NvbXBpbGVyQ29uZmlnfSBmcm9tICcuLi9jb25maWcnO1xuaW1wb3J0IHtWaWV3RW5jYXBzdWxhdGlvbn0gZnJvbSAnLi4vY29yZSc7XG5pbXBvcnQge0RpcmVjdGl2ZU5vcm1hbGl6ZXJ9IGZyb20gJy4uL2RpcmVjdGl2ZV9ub3JtYWxpemVyJztcbmltcG9ydCB7RGlyZWN0aXZlUmVzb2x2ZXJ9IGZyb20gJy4uL2RpcmVjdGl2ZV9yZXNvbHZlcic7XG5pbXBvcnQge0xleGVyfSBmcm9tICcuLi9leHByZXNzaW9uX3BhcnNlci9sZXhlcic7XG5pbXBvcnQge1BhcnNlcn0gZnJvbSAnLi4vZXhwcmVzc2lvbl9wYXJzZXIvcGFyc2VyJztcbmltcG9ydCB7STE4Tkh0bWxQYXJzZXJ9IGZyb20gJy4uL2kxOG4vaTE4bl9odG1sX3BhcnNlcic7XG5pbXBvcnQge0luamVjdGFibGVDb21waWxlcn0gZnJvbSAnLi4vaW5qZWN0YWJsZV9jb21waWxlcic7XG5pbXBvcnQge0NvbXBpbGVNZXRhZGF0YVJlc29sdmVyfSBmcm9tICcuLi9tZXRhZGF0YV9yZXNvbHZlcic7XG5pbXBvcnQge0h0bWxQYXJzZXJ9IGZyb20gJy4uL21sX3BhcnNlci9odG1sX3BhcnNlcic7XG5pbXBvcnQge05nTW9kdWxlQ29tcGlsZXJ9IGZyb20gJy4uL25nX21vZHVsZV9jb21waWxlcic7XG5pbXBvcnQge05nTW9kdWxlUmVzb2x2ZXJ9IGZyb20gJy4uL25nX21vZHVsZV9yZXNvbHZlcic7XG5pbXBvcnQge1R5cGVTY3JpcHRFbWl0dGVyfSBmcm9tICcuLi9vdXRwdXQvdHNfZW1pdHRlcic7XG5pbXBvcnQge1BpcGVSZXNvbHZlcn0gZnJvbSAnLi4vcGlwZV9yZXNvbHZlcic7XG5pbXBvcnQge0RvbUVsZW1lbnRTY2hlbWFSZWdpc3RyeX0gZnJvbSAnLi4vc2NoZW1hL2RvbV9lbGVtZW50X3NjaGVtYV9yZWdpc3RyeSc7XG5pbXBvcnQge1N0eWxlQ29tcGlsZXJ9IGZyb20gJy4uL3N0eWxlX2NvbXBpbGVyJztcbmltcG9ydCB7VGVtcGxhdGVQYXJzZXJ9IGZyb20gJy4uL3RlbXBsYXRlX3BhcnNlci90ZW1wbGF0ZV9wYXJzZXInO1xuaW1wb3J0IHtVcmxSZXNvbHZlcn0gZnJvbSAnLi4vdXJsX3Jlc29sdmVyJztcbmltcG9ydCB7c3ludGF4RXJyb3J9IGZyb20gJy4uL3V0aWwnO1xuaW1wb3J0IHtUeXBlQ2hlY2tDb21waWxlcn0gZnJvbSAnLi4vdmlld19jb21waWxlci90eXBlX2NoZWNrX2NvbXBpbGVyJztcbmltcG9ydCB7Vmlld0NvbXBpbGVyfSBmcm9tICcuLi92aWV3X2NvbXBpbGVyL3ZpZXdfY29tcGlsZXInO1xuXG5pbXBvcnQge0FvdENvbXBpbGVyfSBmcm9tICcuL2NvbXBpbGVyJztcbmltcG9ydCB7QW90Q29tcGlsZXJIb3N0fSBmcm9tICcuL2NvbXBpbGVyX2hvc3QnO1xuaW1wb3J0IHtBb3RDb21waWxlck9wdGlvbnN9IGZyb20gJy4vY29tcGlsZXJfb3B0aW9ucyc7XG5pbXBvcnQge1N0YXRpY1JlZmxlY3Rvcn0gZnJvbSAnLi9zdGF0aWNfcmVmbGVjdG9yJztcbmltcG9ydCB7U3RhdGljU3ltYm9sQ2FjaGV9IGZyb20gJy4vc3RhdGljX3N5bWJvbCc7XG5pbXBvcnQge1N0YXRpY1N5bWJvbFJlc29sdmVyfSBmcm9tICcuL3N0YXRpY19zeW1ib2xfcmVzb2x2ZXInO1xuaW1wb3J0IHtBb3RTdW1tYXJ5UmVzb2x2ZXJ9IGZyb20gJy4vc3VtbWFyeV9yZXNvbHZlcic7XG5cbmV4cG9ydCBmdW5jdGlvbiBjcmVhdGVBb3RVcmxSZXNvbHZlcihcbiAgICBob3N0OiB7cmVzb3VyY2VOYW1lVG9GaWxlTmFtZShyZXNvdXJjZU5hbWU6IHN0cmluZywgY29udGFpbmluZ0ZpbGVOYW1lOiBzdHJpbmcpOiBzdHJpbmd8bnVsbDt9KTpcbiAgICBVcmxSZXNvbHZlciB7XG4gIHJldHVybiB7XG4gICAgcmVzb2x2ZTogKGJhc2VQYXRoOiBzdHJpbmcsIHVybDogc3RyaW5nKSA9PiB7XG4gICAgICBjb25zdCBmaWxlUGF0aCA9IGhvc3QucmVzb3VyY2VOYW1lVG9GaWxlTmFtZSh1cmwsIGJhc2VQYXRoKTtcbiAgICAgIGlmICghZmlsZVBhdGgpIHtcbiAgICAgICAgdGhyb3cgc3ludGF4RXJyb3IoYENvdWxkbid0IHJlc29sdmUgcmVzb3VyY2UgJHt1cmx9IGZyb20gJHtiYXNlUGF0aH1gKTtcbiAgICAgIH1cbiAgICAgIHJldHVybiBmaWxlUGF0aDtcbiAgICB9XG4gIH07XG59XG5cbi8qKlxuICogQ3JlYXRlcyBhIG5ldyBBb3RDb21waWxlciBiYXNlZCBvbiBvcHRpb25zIGFuZCBhIGhvc3QuXG4gKi9cbmV4cG9ydCBmdW5jdGlvbiBjcmVhdGVBb3RDb21waWxlcihcbiAgICBjb21waWxlckhvc3Q6IEFvdENvbXBpbGVySG9zdCwgb3B0aW9uczogQW90Q29tcGlsZXJPcHRpb25zLFxuICAgIGVycm9yQ29sbGVjdG9yPzogKGVycm9yOiBhbnksIHR5cGU/OiBhbnkpID0+XG4gICAgICAgIHZvaWQpOiB7Y29tcGlsZXI6IEFvdENvbXBpbGVyLCByZWZsZWN0b3I6IFN0YXRpY1JlZmxlY3Rvcn0ge1xuICBsZXQgdHJhbnNsYXRpb25zOiBzdHJpbmcgPSBvcHRpb25zLnRyYW5zbGF0aW9ucyB8fCAnJztcblxuICBjb25zdCB1cmxSZXNvbHZlciA9IGNyZWF0ZUFvdFVybFJlc29sdmVyKGNvbXBpbGVySG9zdCk7XG4gIGNvbnN0IHN5bWJvbENhY2hlID0gbmV3IFN0YXRpY1N5bWJvbENhY2hlKCk7XG4gIGNvbnN0IHN1bW1hcnlSZXNvbHZlciA9IG5ldyBBb3RTdW1tYXJ5UmVzb2x2ZXIoY29tcGlsZXJIb3N0LCBzeW1ib2xDYWNoZSk7XG4gIGNvbnN0IHN5bWJvbFJlc29sdmVyID0gbmV3IFN0YXRpY1N5bWJvbFJlc29sdmVyKGNvbXBpbGVySG9zdCwgc3ltYm9sQ2FjaGUsIHN1bW1hcnlSZXNvbHZlcik7XG4gIGNvbnN0IHN0YXRpY1JlZmxlY3RvciA9XG4gICAgICBuZXcgU3RhdGljUmVmbGVjdG9yKHN1bW1hcnlSZXNvbHZlciwgc3ltYm9sUmVzb2x2ZXIsIFtdLCBbXSwgZXJyb3JDb2xsZWN0b3IpO1xuICBsZXQgaHRtbFBhcnNlcjogSTE4Tkh0bWxQYXJzZXI7XG4gIGlmICghIW9wdGlvbnMuZW5hYmxlSXZ5KSB7XG4gICAgLy8gSXZ5IGhhbmRsZXMgaTE4biBhdCB0aGUgY29tcGlsZXIgbGV2ZWwgc28gd2UgbXVzdCB1c2UgYSByZWd1bGFyIHBhcnNlclxuICAgIGh0bWxQYXJzZXIgPSBuZXcgSHRtbFBhcnNlcigpIGFzIEkxOE5IdG1sUGFyc2VyO1xuICB9IGVsc2Uge1xuICAgIGh0bWxQYXJzZXIgPSBuZXcgSTE4Tkh0bWxQYXJzZXIoXG4gICAgICAgIG5ldyBIdG1sUGFyc2VyKCksIHRyYW5zbGF0aW9ucywgb3B0aW9ucy5pMThuRm9ybWF0LCBvcHRpb25zLm1pc3NpbmdUcmFuc2xhdGlvbiwgY29uc29sZSk7XG4gIH1cbiAgY29uc3QgY29uZmlnID0gbmV3IENvbXBpbGVyQ29uZmlnKHtcbiAgICBkZWZhdWx0RW5jYXBzdWxhdGlvbjogVmlld0VuY2Fwc3VsYXRpb24uRW11bGF0ZWQsXG4gICAgdXNlSml0OiBmYWxzZSxcbiAgICBtaXNzaW5nVHJhbnNsYXRpb246IG9wdGlvbnMubWlzc2luZ1RyYW5zbGF0aW9uLFxuICAgIHByZXNlcnZlV2hpdGVzcGFjZXM6IG9wdGlvbnMucHJlc2VydmVXaGl0ZXNwYWNlcyxcbiAgICBzdHJpY3RJbmplY3Rpb25QYXJhbWV0ZXJzOiBvcHRpb25zLnN0cmljdEluamVjdGlvblBhcmFtZXRlcnMsXG4gIH0pO1xuICBjb25zdCBub3JtYWxpemVyID0gbmV3IERpcmVjdGl2ZU5vcm1hbGl6ZXIoXG4gICAgICB7Z2V0OiAodXJsOiBzdHJpbmcpID0+IGNvbXBpbGVySG9zdC5sb2FkUmVzb3VyY2UodXJsKX0sIHVybFJlc29sdmVyLCBodG1sUGFyc2VyLCBjb25maWcpO1xuICBjb25zdCBleHByZXNzaW9uUGFyc2VyID0gbmV3IFBhcnNlcihuZXcgTGV4ZXIoKSk7XG4gIGNvbnN0IGVsZW1lbnRTY2hlbWFSZWdpc3RyeSA9IG5ldyBEb21FbGVtZW50U2NoZW1hUmVnaXN0cnkoKTtcbiAgY29uc3QgdG1wbFBhcnNlciA9IG5ldyBUZW1wbGF0ZVBhcnNlcihcbiAgICAgIGNvbmZpZywgc3RhdGljUmVmbGVjdG9yLCBleHByZXNzaW9uUGFyc2VyLCBlbGVtZW50U2NoZW1hUmVnaXN0cnksIGh0bWxQYXJzZXIsIGNvbnNvbGUsIFtdKTtcbiAgY29uc3QgcmVzb2x2ZXIgPSBuZXcgQ29tcGlsZU1ldGFkYXRhUmVzb2x2ZXIoXG4gICAgICBjb25maWcsIGh0bWxQYXJzZXIsIG5ldyBOZ01vZHVsZVJlc29sdmVyKHN0YXRpY1JlZmxlY3RvciksXG4gICAgICBuZXcgRGlyZWN0aXZlUmVzb2x2ZXIoc3RhdGljUmVmbGVjdG9yKSwgbmV3IFBpcGVSZXNvbHZlcihzdGF0aWNSZWZsZWN0b3IpLCBzdW1tYXJ5UmVzb2x2ZXIsXG4gICAgICBlbGVtZW50U2NoZW1hUmVnaXN0cnksIG5vcm1hbGl6ZXIsIGNvbnNvbGUsIHN5bWJvbENhY2hlLCBzdGF0aWNSZWZsZWN0b3IsIGVycm9yQ29sbGVjdG9yKTtcbiAgLy8gVE9ETyh2aWNiKTogZG8gbm90IHBhc3Mgb3B0aW9ucy5pMThuRm9ybWF0IGhlcmVcbiAgY29uc3Qgdmlld0NvbXBpbGVyID0gbmV3IFZpZXdDb21waWxlcihzdGF0aWNSZWZsZWN0b3IpO1xuICBjb25zdCB0eXBlQ2hlY2tDb21waWxlciA9IG5ldyBUeXBlQ2hlY2tDb21waWxlcihvcHRpb25zLCBzdGF0aWNSZWZsZWN0b3IpO1xuICBjb25zdCBjb21waWxlciA9IG5ldyBBb3RDb21waWxlcihcbiAgICAgIGNvbmZpZywgb3B0aW9ucywgY29tcGlsZXJIb3N0LCBzdGF0aWNSZWZsZWN0b3IsIHJlc29sdmVyLCB0bXBsUGFyc2VyLFxuICAgICAgbmV3IFN0eWxlQ29tcGlsZXIodXJsUmVzb2x2ZXIpLCB2aWV3Q29tcGlsZXIsIHR5cGVDaGVja0NvbXBpbGVyLFxuICAgICAgbmV3IE5nTW9kdWxlQ29tcGlsZXIoc3RhdGljUmVmbGVjdG9yKSxcbiAgICAgIG5ldyBJbmplY3RhYmxlQ29tcGlsZXIoc3RhdGljUmVmbGVjdG9yLCAhIW9wdGlvbnMuZW5hYmxlSXZ5KSwgbmV3IFR5cGVTY3JpcHRFbWl0dGVyKCksXG4gICAgICBzdW1tYXJ5UmVzb2x2ZXIsIHN5bWJvbFJlc29sdmVyKTtcbiAgcmV0dXJuIHtjb21waWxlciwgcmVmbGVjdG9yOiBzdGF0aWNSZWZsZWN0b3J9O1xufVxuIl19