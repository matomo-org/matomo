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
        define("@angular/compiler/src/style_url_resolver", ["require", "exports"], factory);
    }
})(function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.extractStyleUrls = exports.isStyleUrlResolvable = exports.StyleWithImports = void 0;
    var StyleWithImports = /** @class */ (function () {
        function StyleWithImports(style, styleUrls) {
            this.style = style;
            this.styleUrls = styleUrls;
        }
        return StyleWithImports;
    }());
    exports.StyleWithImports = StyleWithImports;
    function isStyleUrlResolvable(url) {
        if (url == null || url.length === 0 || url[0] == '/')
            return false;
        var schemeMatch = url.match(URL_WITH_SCHEMA_REGEXP);
        return schemeMatch === null || schemeMatch[1] == 'package' || schemeMatch[1] == 'asset';
    }
    exports.isStyleUrlResolvable = isStyleUrlResolvable;
    /**
     * Rewrites stylesheets by resolving and removing the @import urls that
     * are either relative or don't have a `package:` scheme
     */
    function extractStyleUrls(resolver, baseUrl, cssText) {
        var foundUrls = [];
        var modifiedCssText = cssText.replace(CSS_STRIPPABLE_COMMENT_REGEXP, '')
            .replace(CSS_IMPORT_REGEXP, function () {
            var m = [];
            for (var _i = 0; _i < arguments.length; _i++) {
                m[_i] = arguments[_i];
            }
            var url = m[1] || m[2];
            if (!isStyleUrlResolvable(url)) {
                // Do not attempt to resolve non-package absolute URLs with URI
                // scheme
                return m[0];
            }
            foundUrls.push(resolver.resolve(baseUrl, url));
            return '';
        });
        return new StyleWithImports(modifiedCssText, foundUrls);
    }
    exports.extractStyleUrls = extractStyleUrls;
    var CSS_IMPORT_REGEXP = /@import\s+(?:url\()?\s*(?:(?:['"]([^'"]*))|([^;\)\s]*))[^;]*;?/g;
    var CSS_STRIPPABLE_COMMENT_REGEXP = /\/\*(?!#\s*(?:sourceURL|sourceMappingURL)=)[\s\S]+?\*\//g;
    var URL_WITH_SCHEMA_REGEXP = /^([^:/?#]+):/;
});
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoic3R5bGVfdXJsX3Jlc29sdmVyLmpzIiwic291cmNlUm9vdCI6IiIsInNvdXJjZXMiOlsiLi4vLi4vLi4vLi4vLi4vLi4vcGFja2FnZXMvY29tcGlsZXIvc3JjL3N0eWxlX3VybF9yZXNvbHZlci50cyJdLCJuYW1lcyI6W10sIm1hcHBpbmdzIjoiQUFBQTs7Ozs7O0dBTUc7Ozs7Ozs7Ozs7Ozs7SUFPSDtRQUNFLDBCQUFtQixLQUFhLEVBQVMsU0FBbUI7WUFBekMsVUFBSyxHQUFMLEtBQUssQ0FBUTtZQUFTLGNBQVMsR0FBVCxTQUFTLENBQVU7UUFBRyxDQUFDO1FBQ2xFLHVCQUFDO0lBQUQsQ0FBQyxBQUZELElBRUM7SUFGWSw0Q0FBZ0I7SUFJN0IsU0FBZ0Isb0JBQW9CLENBQUMsR0FBVztRQUM5QyxJQUFJLEdBQUcsSUFBSSxJQUFJLElBQUksR0FBRyxDQUFDLE1BQU0sS0FBSyxDQUFDLElBQUksR0FBRyxDQUFDLENBQUMsQ0FBQyxJQUFJLEdBQUc7WUFBRSxPQUFPLEtBQUssQ0FBQztRQUNuRSxJQUFNLFdBQVcsR0FBRyxHQUFHLENBQUMsS0FBSyxDQUFDLHNCQUFzQixDQUFDLENBQUM7UUFDdEQsT0FBTyxXQUFXLEtBQUssSUFBSSxJQUFJLFdBQVcsQ0FBQyxDQUFDLENBQUMsSUFBSSxTQUFTLElBQUksV0FBVyxDQUFDLENBQUMsQ0FBQyxJQUFJLE9BQU8sQ0FBQztJQUMxRixDQUFDO0lBSkQsb0RBSUM7SUFFRDs7O09BR0c7SUFDSCxTQUFnQixnQkFBZ0IsQ0FDNUIsUUFBcUIsRUFBRSxPQUFlLEVBQUUsT0FBZTtRQUN6RCxJQUFNLFNBQVMsR0FBYSxFQUFFLENBQUM7UUFFL0IsSUFBTSxlQUFlLEdBQUcsT0FBTyxDQUFDLE9BQU8sQ0FBQyw2QkFBNkIsRUFBRSxFQUFFLENBQUM7YUFDN0MsT0FBTyxDQUFDLGlCQUFpQixFQUFFO1lBQUMsV0FBYztpQkFBZCxVQUFjLEVBQWQscUJBQWMsRUFBZCxJQUFjO2dCQUFkLHNCQUFjOztZQUN6QyxJQUFNLEdBQUcsR0FBRyxDQUFDLENBQUMsQ0FBQyxDQUFDLElBQUksQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDO1lBQ3pCLElBQUksQ0FBQyxvQkFBb0IsQ0FBQyxHQUFHLENBQUMsRUFBRTtnQkFDOUIsK0RBQStEO2dCQUMvRCxTQUFTO2dCQUNULE9BQU8sQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDO2FBQ2I7WUFDRCxTQUFTLENBQUMsSUFBSSxDQUFDLFFBQVEsQ0FBQyxPQUFPLENBQUMsT0FBTyxFQUFFLEdBQUcsQ0FBQyxDQUFDLENBQUM7WUFDL0MsT0FBTyxFQUFFLENBQUM7UUFDWixDQUFDLENBQUMsQ0FBQztRQUMvQixPQUFPLElBQUksZ0JBQWdCLENBQUMsZUFBZSxFQUFFLFNBQVMsQ0FBQyxDQUFDO0lBQzFELENBQUM7SUFoQkQsNENBZ0JDO0lBRUQsSUFBTSxpQkFBaUIsR0FBRyxpRUFBaUUsQ0FBQztJQUM1RixJQUFNLDZCQUE2QixHQUFHLDBEQUEwRCxDQUFDO0lBQ2pHLElBQU0sc0JBQXNCLEdBQUcsY0FBYyxDQUFDIiwic291cmNlc0NvbnRlbnQiOlsiLyoqXG4gKiBAbGljZW5zZVxuICogQ29weXJpZ2h0IEdvb2dsZSBMTEMgQWxsIFJpZ2h0cyBSZXNlcnZlZC5cbiAqXG4gKiBVc2Ugb2YgdGhpcyBzb3VyY2UgY29kZSBpcyBnb3Zlcm5lZCBieSBhbiBNSVQtc3R5bGUgbGljZW5zZSB0aGF0IGNhbiBiZVxuICogZm91bmQgaW4gdGhlIExJQ0VOU0UgZmlsZSBhdCBodHRwczovL2FuZ3VsYXIuaW8vbGljZW5zZVxuICovXG5cbi8vIFNvbWUgb2YgdGhlIGNvZGUgY29tZXMgZnJvbSBXZWJDb21wb25lbnRzLkpTXG4vLyBodHRwczovL2dpdGh1Yi5jb20vd2ViY29tcG9uZW50cy93ZWJjb21wb25lbnRzanMvYmxvYi9tYXN0ZXIvc3JjL0hUTUxJbXBvcnRzL3BhdGguanNcblxuaW1wb3J0IHtVcmxSZXNvbHZlcn0gZnJvbSAnLi91cmxfcmVzb2x2ZXInO1xuXG5leHBvcnQgY2xhc3MgU3R5bGVXaXRoSW1wb3J0cyB7XG4gIGNvbnN0cnVjdG9yKHB1YmxpYyBzdHlsZTogc3RyaW5nLCBwdWJsaWMgc3R5bGVVcmxzOiBzdHJpbmdbXSkge31cbn1cblxuZXhwb3J0IGZ1bmN0aW9uIGlzU3R5bGVVcmxSZXNvbHZhYmxlKHVybDogc3RyaW5nKTogYm9vbGVhbiB7XG4gIGlmICh1cmwgPT0gbnVsbCB8fCB1cmwubGVuZ3RoID09PSAwIHx8IHVybFswXSA9PSAnLycpIHJldHVybiBmYWxzZTtcbiAgY29uc3Qgc2NoZW1lTWF0Y2ggPSB1cmwubWF0Y2goVVJMX1dJVEhfU0NIRU1BX1JFR0VYUCk7XG4gIHJldHVybiBzY2hlbWVNYXRjaCA9PT0gbnVsbCB8fCBzY2hlbWVNYXRjaFsxXSA9PSAncGFja2FnZScgfHwgc2NoZW1lTWF0Y2hbMV0gPT0gJ2Fzc2V0Jztcbn1cblxuLyoqXG4gKiBSZXdyaXRlcyBzdHlsZXNoZWV0cyBieSByZXNvbHZpbmcgYW5kIHJlbW92aW5nIHRoZSBAaW1wb3J0IHVybHMgdGhhdFxuICogYXJlIGVpdGhlciByZWxhdGl2ZSBvciBkb24ndCBoYXZlIGEgYHBhY2thZ2U6YCBzY2hlbWVcbiAqL1xuZXhwb3J0IGZ1bmN0aW9uIGV4dHJhY3RTdHlsZVVybHMoXG4gICAgcmVzb2x2ZXI6IFVybFJlc29sdmVyLCBiYXNlVXJsOiBzdHJpbmcsIGNzc1RleHQ6IHN0cmluZyk6IFN0eWxlV2l0aEltcG9ydHMge1xuICBjb25zdCBmb3VuZFVybHM6IHN0cmluZ1tdID0gW107XG5cbiAgY29uc3QgbW9kaWZpZWRDc3NUZXh0ID0gY3NzVGV4dC5yZXBsYWNlKENTU19TVFJJUFBBQkxFX0NPTU1FTlRfUkVHRVhQLCAnJylcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIC5yZXBsYWNlKENTU19JTVBPUlRfUkVHRVhQLCAoLi4ubTogc3RyaW5nW10pID0+IHtcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgY29uc3QgdXJsID0gbVsxXSB8fCBtWzJdO1xuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBpZiAoIWlzU3R5bGVVcmxSZXNvbHZhYmxlKHVybCkpIHtcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAvLyBEbyBub3QgYXR0ZW1wdCB0byByZXNvbHZlIG5vbi1wYWNrYWdlIGFic29sdXRlIFVSTHMgd2l0aCBVUklcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAvLyBzY2hlbWVcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICByZXR1cm4gbVswXTtcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgfVxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBmb3VuZFVybHMucHVzaChyZXNvbHZlci5yZXNvbHZlKGJhc2VVcmwsIHVybCkpO1xuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICByZXR1cm4gJyc7XG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICB9KTtcbiAgcmV0dXJuIG5ldyBTdHlsZVdpdGhJbXBvcnRzKG1vZGlmaWVkQ3NzVGV4dCwgZm91bmRVcmxzKTtcbn1cblxuY29uc3QgQ1NTX0lNUE9SVF9SRUdFWFAgPSAvQGltcG9ydFxccysoPzp1cmxcXCgpP1xccyooPzooPzpbJ1wiXShbXidcIl0qKSl8KFteO1xcKVxcc10qKSlbXjtdKjs/L2c7XG5jb25zdCBDU1NfU1RSSVBQQUJMRV9DT01NRU5UX1JFR0VYUCA9IC9cXC9cXCooPyEjXFxzKig/OnNvdXJjZVVSTHxzb3VyY2VNYXBwaW5nVVJMKT0pW1xcc1xcU10rP1xcKlxcLy9nO1xuY29uc3QgVVJMX1dJVEhfU0NIRU1BX1JFR0VYUCA9IC9eKFteOi8/I10rKTovO1xuIl19