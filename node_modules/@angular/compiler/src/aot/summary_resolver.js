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
        define("@angular/compiler/src/aot/summary_resolver", ["require", "exports", "@angular/compiler/src/aot/summary_serializer", "@angular/compiler/src/aot/util"], factory);
    }
})(function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.AotSummaryResolver = void 0;
    var summary_serializer_1 = require("@angular/compiler/src/aot/summary_serializer");
    var util_1 = require("@angular/compiler/src/aot/util");
    var AotSummaryResolver = /** @class */ (function () {
        function AotSummaryResolver(host, staticSymbolCache) {
            this.host = host;
            this.staticSymbolCache = staticSymbolCache;
            // Note: this will only contain StaticSymbols without members!
            this.summaryCache = new Map();
            this.loadedFilePaths = new Map();
            // Note: this will only contain StaticSymbols without members!
            this.importAs = new Map();
            this.knownFileNameToModuleNames = new Map();
        }
        AotSummaryResolver.prototype.isLibraryFile = function (filePath) {
            // Note: We need to strip the .ngfactory. file path,
            // so this method also works for generated files
            // (for which host.isSourceFile will always return false).
            return !this.host.isSourceFile(util_1.stripGeneratedFileSuffix(filePath));
        };
        AotSummaryResolver.prototype.toSummaryFileName = function (filePath, referringSrcFileName) {
            return this.host.toSummaryFileName(filePath, referringSrcFileName);
        };
        AotSummaryResolver.prototype.fromSummaryFileName = function (fileName, referringLibFileName) {
            return this.host.fromSummaryFileName(fileName, referringLibFileName);
        };
        AotSummaryResolver.prototype.resolveSummary = function (staticSymbol) {
            var rootSymbol = staticSymbol.members.length ?
                this.staticSymbolCache.get(staticSymbol.filePath, staticSymbol.name) :
                staticSymbol;
            var summary = this.summaryCache.get(rootSymbol);
            if (!summary) {
                this._loadSummaryFile(staticSymbol.filePath);
                summary = this.summaryCache.get(staticSymbol);
            }
            return (rootSymbol === staticSymbol && summary) || null;
        };
        AotSummaryResolver.prototype.getSymbolsOf = function (filePath) {
            if (this._loadSummaryFile(filePath)) {
                return Array.from(this.summaryCache.keys()).filter(function (symbol) { return symbol.filePath === filePath; });
            }
            return null;
        };
        AotSummaryResolver.prototype.getImportAs = function (staticSymbol) {
            staticSymbol.assertNoMembers();
            return this.importAs.get(staticSymbol);
        };
        /**
         * Converts a file path to a module name that can be used as an `import`.
         */
        AotSummaryResolver.prototype.getKnownModuleName = function (importedFilePath) {
            return this.knownFileNameToModuleNames.get(importedFilePath) || null;
        };
        AotSummaryResolver.prototype.addSummary = function (summary) {
            this.summaryCache.set(summary.symbol, summary);
        };
        AotSummaryResolver.prototype._loadSummaryFile = function (filePath) {
            var _this = this;
            var hasSummary = this.loadedFilePaths.get(filePath);
            if (hasSummary != null) {
                return hasSummary;
            }
            var json = null;
            if (this.isLibraryFile(filePath)) {
                var summaryFilePath = util_1.summaryFileName(filePath);
                try {
                    json = this.host.loadSummary(summaryFilePath);
                }
                catch (e) {
                    console.error("Error loading summary file " + summaryFilePath);
                    throw e;
                }
            }
            hasSummary = json != null;
            this.loadedFilePaths.set(filePath, hasSummary);
            if (json) {
                var _a = summary_serializer_1.deserializeSummaries(this.staticSymbolCache, this, filePath, json), moduleName = _a.moduleName, summaries = _a.summaries, importAs = _a.importAs;
                summaries.forEach(function (summary) { return _this.summaryCache.set(summary.symbol, summary); });
                if (moduleName) {
                    this.knownFileNameToModuleNames.set(filePath, moduleName);
                }
                importAs.forEach(function (importAs) {
                    _this.importAs.set(importAs.symbol, importAs.importAs);
                });
            }
            return hasSummary;
        };
        return AotSummaryResolver;
    }());
    exports.AotSummaryResolver = AotSummaryResolver;
});
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoic3VtbWFyeV9yZXNvbHZlci5qcyIsInNvdXJjZVJvb3QiOiIiLCJzb3VyY2VzIjpbIi4uLy4uLy4uLy4uLy4uLy4uLy4uL3BhY2thZ2VzL2NvbXBpbGVyL3NyYy9hb3Qvc3VtbWFyeV9yZXNvbHZlci50cyJdLCJuYW1lcyI6W10sIm1hcHBpbmdzIjoiQUFBQTs7Ozs7O0dBTUc7Ozs7Ozs7Ozs7Ozs7SUFLSCxtRkFBMEQ7SUFDMUQsdURBQWlFO0lBNkJqRTtRQVFFLDRCQUFvQixJQUE0QixFQUFVLGlCQUFvQztZQUExRSxTQUFJLEdBQUosSUFBSSxDQUF3QjtZQUFVLHNCQUFpQixHQUFqQixpQkFBaUIsQ0FBbUI7WUFQOUYsOERBQThEO1lBQ3RELGlCQUFZLEdBQUcsSUFBSSxHQUFHLEVBQXVDLENBQUM7WUFDOUQsb0JBQWUsR0FBRyxJQUFJLEdBQUcsRUFBbUIsQ0FBQztZQUNyRCw4REFBOEQ7WUFDdEQsYUFBUSxHQUFHLElBQUksR0FBRyxFQUE4QixDQUFDO1lBQ2pELCtCQUEwQixHQUFHLElBQUksR0FBRyxFQUFrQixDQUFDO1FBRWtDLENBQUM7UUFFbEcsMENBQWEsR0FBYixVQUFjLFFBQWdCO1lBQzVCLG9EQUFvRDtZQUNwRCxnREFBZ0Q7WUFDaEQsMERBQTBEO1lBQzFELE9BQU8sQ0FBQyxJQUFJLENBQUMsSUFBSSxDQUFDLFlBQVksQ0FBQywrQkFBd0IsQ0FBQyxRQUFRLENBQUMsQ0FBQyxDQUFDO1FBQ3JFLENBQUM7UUFFRCw4Q0FBaUIsR0FBakIsVUFBa0IsUUFBZ0IsRUFBRSxvQkFBNEI7WUFDOUQsT0FBTyxJQUFJLENBQUMsSUFBSSxDQUFDLGlCQUFpQixDQUFDLFFBQVEsRUFBRSxvQkFBb0IsQ0FBQyxDQUFDO1FBQ3JFLENBQUM7UUFFRCxnREFBbUIsR0FBbkIsVUFBb0IsUUFBZ0IsRUFBRSxvQkFBNEI7WUFDaEUsT0FBTyxJQUFJLENBQUMsSUFBSSxDQUFDLG1CQUFtQixDQUFDLFFBQVEsRUFBRSxvQkFBb0IsQ0FBQyxDQUFDO1FBQ3ZFLENBQUM7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UUFBUSxjQUNnQyxDQUFDO2dCQUN2RSxTQUFTLENBQUMsT0FBTyxDQUFDLFVBQUMsT0FBTyxJQUFLLE9BQUEsS0FBSSxDQUFDLFlBQVksQ0FBQyxHQUFHLENBQUMsT0FBTyxDQUFDLE1BQU0sRUFBRSxPQUFPLENBQUMsRUFBOUMsQ0FBOEMsQ0FBQyxDQUFDO2dCQUMvRSxJQUFJLFVBQVUsRUFBRTtvQkFDZCxJQUFJLENBQUMsMEJBQTBCLENBQUMsR0FBRyxDQUFDLFFBQVEsRUFBRSxVQUFVLENBQUMsQ0FBQztpQkFDM0Q7Z0JBQ0QsUUFBUSxDQUFDLE9BQU8sQ0FBQyxVQUFDLFFBQVE7b0JBQ3hCLEtBQUksQ0FBQyxRQUFRLENBQUMsR0FBRyxDQUFDLFFBQVEsQ0FBQyxNQUFNLEVBQUUsUUFBUSxDQUFDLFFBQVEsQ0FBQyxDQUFDO2dCQUN4RCxDQUFDLENBQUMsQ0FBQzthQUNKO1lBQ0QsT0FBTyxVQUFVLENBQUM7UUFDcEIsQ0FBQztRQUNILHlCQUFDO0lBQUQsQ0FBQyxBQTFGRCxJQTBGQztJQTFGWSxnREFBa0IiLCJzb3VyY2VzQ29udGVudCI6WyIvKipcbiAqIEBsaWNlbnNlXG4gKiBDb3B5cmlnaHQgR29vZ2xlIExMQyBBbGwgUmlnaHRzIFJlc2VydmVkLlxuICpcbiAqIFVzZSBvZiB0aGlzIHNvdXJjZSBjb2RlIGlzIGdvdmVybmVkIGJ5IGFuIE1JVC1zdHlsZSBsaWNlbnNlIHRoYXQgY2FuIGJlXG4gKiBmb3VuZCBpbiB0aGUgTElDRU5TRSBmaWxlIGF0IGh0dHBzOi8vYW5ndWxhci5pby9saWNlbnNlXG4gKi9cblxuaW1wb3J0IHtTdW1tYXJ5LCBTdW1tYXJ5UmVzb2x2ZXJ9IGZyb20gJy4uL3N1bW1hcnlfcmVzb2x2ZXInO1xuXG5pbXBvcnQge1N0YXRpY1N5bWJvbCwgU3RhdGljU3ltYm9sQ2FjaGV9IGZyb20gJy4vc3RhdGljX3N5bWJvbCc7XG5pbXBvcnQge2Rlc2VyaWFsaXplU3VtbWFyaWVzfSBmcm9tICcuL3N1bW1hcnlfc2VyaWFsaXplcic7XG5pbXBvcnQge3N0cmlwR2VuZXJhdGVkRmlsZVN1ZmZpeCwgc3VtbWFyeUZpbGVOYW1lfSBmcm9tICcuL3V0aWwnO1xuXG5leHBvcnQgaW50ZXJmYWNlIEFvdFN1bW1hcnlSZXNvbHZlckhvc3Qge1xuICAvKipcbiAgICogTG9hZHMgYW4gTmdNb2R1bGUvRGlyZWN0aXZlL1BpcGUgc3VtbWFyeSBmaWxlXG4gICAqL1xuICBsb2FkU3VtbWFyeShmaWxlUGF0aDogc3RyaW5nKTogc3RyaW5nfG51bGw7XG5cbiAgLyoqXG4gICAqIFJldHVybnMgd2hldGhlciBhIGZpbGUgaXMgYSBzb3VyY2UgZmlsZSBvciBub3QuXG4gICAqL1xuICBpc1NvdXJjZUZpbGUoc291cmNlRmlsZVBhdGg6IHN0cmluZyk6IGJvb2xlYW47XG4gIC8qKlxuICAgKiBDb252ZXJ0cyBhIGZpbGUgbmFtZSBpbnRvIGEgcmVwcmVzZW50YXRpb24gdGhhdCBzaG91bGQgYmUgc3RvcmVkIGluIGEgc3VtbWFyeSBmaWxlLlxuICAgKiBUaGlzIGhhcyB0byBpbmNsdWRlIGNoYW5naW5nIHRoZSBzdWZmaXggYXMgd2VsbC5cbiAgICogRS5nLlxuICAgKiBgc29tZV9maWxlLnRzYCAtPiBgc29tZV9maWxlLmQudHNgXG4gICAqXG4gICAqIEBwYXJhbSByZWZlcnJpbmdTcmNGaWxlTmFtZSB0aGUgc291cmUgZmlsZSB0aGF0IHJlZmVycyB0byBmaWxlTmFtZVxuICAgKi9cbiAgdG9TdW1tYXJ5RmlsZU5hbWUoZmlsZU5hbWU6IHN0cmluZywgcmVmZXJyaW5nU3JjRmlsZU5hbWU6IHN0cmluZyk6IHN0cmluZztcblxuICAvKipcbiAgICogQ29udmVydHMgYSBmaWxlTmFtZSB0aGF0IHdhcyBwcm9jZXNzZWQgYnkgYHRvU3VtbWFyeUZpbGVOYW1lYCBiYWNrIGludG8gYSByZWFsIGZpbGVOYW1lXG4gICAqIGdpdmVuIHRoZSBmaWxlTmFtZSBvZiB0aGUgbGlicmFyeSB0aGF0IGlzIHJlZmVycmlnIHRvIGl0LlxuICAgKi9cbiAgZnJvbVN1bW1hcnlGaWxlTmFtZShmaWxlTmFtZTogc3RyaW5nLCByZWZlcnJpbmdMaWJGaWxlTmFtZTogc3RyaW5nKTogc3RyaW5nO1xufVxuXG5leHBvcnQgY2xhc3MgQW90U3VtbWFyeVJlc29sdmVyIGltcGxlbWVudHMgU3VtbWFyeVJlc29sdmVyPFN0YXRpY1N5bWJvbD4ge1xuICAvLyBOb3RlOiB0aGlzIHdpbGwgb25seSBjb250YWluIFN0YXRpY1N5bWJvbHMgd2l0aG91dCBtZW1iZXJzIVxuICBwcml2YXRlIHN1bW1hcnlDYWNoZSA9IG5ldyBNYXA8U3RhdGljU3ltYm9sLCBTdW1tYXJ5PFN0YXRpY1N5bWJvbD4+KCk7XG4gIHByaXZhdGUgbG9hZGVkRmlsZVBhdGhzID0gbmV3IE1hcDxzdHJpbmcsIGJvb2xlYW4+KCk7XG4gIC8vIE5vdGU6IHRoaXMgd2lsbCBvbmx5IGNvbnRhaW4gU3RhdGljU3ltYm9scyB3aXRob3V0IG1lbWJlcnMhXG4gIHByaXZhdGUgaW1wb3J0QXMgPSBuZXcgTWFwPFN0YXRpY1N5bWJvbCwgU3RhdGljU3ltYm9sPigpO1xuICBwcml2YXRlIGtub3duRmlsZU5hbWVUb01vZHVsZU5hbWVzID0gbmV3IE1hcDxzdHJpbmcsIHN0cmluZz4oKTtcblxuICBjb25zdHJ1Y3Rvcihwcml2YXRlIGhvc3Q6IEFvdFN1bW1hcnlSZXNvbHZlckhvc3QsIHByaXZhdGUgc3RhdGljU3ltYm9sQ2FjaGU6IFN0YXRpY1N5bWJvbENhY2hlKSB7fVxuXG4gIGlzTGlicmFyeUZpbGUoZmlsZVBhdGg6IHN0cmluZyk6IGJvb2xlYW4ge1xuICAgIC8vIE5vdGU6IFdlIG5lZWQgdG8gc3RyaXAgdGhlIC5uZ2ZhY3RvcnkuIGZpbGUgcGF0aCxcbiAgICAvLyBzbyB0aGlzIG1ldGhvZCBhbHNvIHdvcmtzIGZvciBnZW5lcmF0ZWQgZmlsZXNcbiAgICAvLyAoZm9yIHdoaWNoIGhvc3QuaXNTb3VyY2VGaWxlIHdpbGwgYWx3YXlzIHJldHVybiBmYWxzZSkuXG4gICAgcmV0dXJuICF0aGlzLmhvc3QuaXNTb3VyY2VGaWxlKHN0cmlwR2VuZXJhdGVkRmlsZVN1ZmZpeChmaWxlUGF0aCkpO1xuICB9XG5cbiAgdG9TdW1tYXJ5RmlsZU5hbWUoZmlsZVBhdGg6IHN0cmluZywgcmVmZXJyaW5nU3JjRmlsZU5hbWU6IHN0cmluZykge1xuICAgIHJldHVybiB0aGlzLmhvc3QudG9TdW1tYXJ5RmlsZU5hbWUoZmlsZVBhdGgsIHJlZmVycmluZ1NyY0ZpbGVOYW1lKTtcbiAgfVxuXG4gIGZyb21TdW1tYXJ5RmlsZU5hbWUoZmlsZU5hbWU6IHN0cmluZywgcmVmZXJyaW5nTGliRmlsZU5hbWU6IHN0cmluZykge1xuICAgIHJldHVybiB0aGlzLmhvc3QuZnJvbVN1bW1hcnlGaWxlTmFtZShmaWxlTmFtZSwgcmVmZXJyaW5nTGliRmlsZU5hbWUpO1xuICB9XG5cbiAgcmVzb2x2ZVN1bW1hcnkoc3RhdGljU3ltYm9sOiBTdGF0aWNTeW1ib2wpOiBTdW1tYXJ5PFN0YXRpY1N5bWJvbD58bnVsbCB7XG4gICAgY29uc3Qgcm9vdFN5bWJvbCA9IHN0YXRpY1N5bWJvbC5tZW1iZXJzLmxlbmd0aCA/XG4gICAgICAgIHRoaXMuc3RhdGljU3ltYm9sQ2FjaGUuZ2V0KHN0YXRpY1N5bWJvbC5maWxlUGF0aCwgc3RhdGljU3ltYm9sLm5hbWUpIDpcbiAgICAgICAgc3RhdGljU3ltYm9sO1xuICAgIGxldCBzdW1tYXJ5ID0gdGhpcy5zdW1tYXJ5Q2FjaGUuZ2V0KHJvb3RTeW1ib2wpO1xuICAgIGlmICghc3VtbWFyeSkge1xuICAgICAgdGhpcy5fbG9hZFN1bW1hcnlGaWxlKHN0YXRpY1N5bWJvbC5maWxlUGF0aCk7XG4gICAgICBzdW1tYXJ5ID0gdGhpcy5zdW1tYXJ5Q2FjaGUuZ2V0KHN0YXRpY1N5bWJvbCkhO1xuICAgIH1cbiAgICByZXR1cm4gKHJvb3RTeW1ib2wgPT09IHN0YXRpY1N5bWJvbCAmJiBzdW1tYXJ5KSB8fCBudWxsO1xuICB9XG5cbiAgZ2V0U3ltYm9sc09mKGZpbGVQYXRoOiBzdHJpbmcpOiBTdGF0aWNTeW1ib2xbXXxudWxsIHtcbiAgICBpZiAodGhpcy5fbG9hZFN1bW1hcnlGaWxlKGZpbGVQYXRoKSkge1xuICAgICAgcmV0dXJuIEFycmF5LmZyb20odGhpcy5zdW1tYXJ5Q2FjaGUua2V5cygpKS5maWx0ZXIoKHN5bWJvbCkgPT4gc3ltYm9sLmZpbGVQYXRoID09PSBmaWxlUGF0aCk7XG4gICAgfVxuICAgIHJldHVybiBudWxsO1xuICB9XG5cbiAgZ2V0SW1wb3J0QXMoc3RhdGljU3ltYm9sOiBTdGF0aWNTeW1ib2wpOiBTdGF0aWNTeW1ib2wge1xuICAgIHN0YXRpY1N5bWJvbC5hc3NlcnROb01lbWJlcnMoKTtcbiAgICByZXR1cm4gdGhpcy5pbXBvcnRBcy5nZXQoc3RhdGljU3ltYm9sKSE7XG4gIH1cblxuICAvKipcbiAgICogQ29udmVydHMgYSBmaWxlIHBhdGggdG8gYSBtb2R1bGUgbmFtZSB0aGF0IGNhbiBiZSB1c2VkIGFzIGFuIGBpbXBvcnRgLlxuICAgKi9cbiAgZ2V0S25vd25Nb2R1bGVOYW1lKGltcG9ydGVkRmlsZVBhdGg6IHN0cmluZyk6IHN0cmluZ3xudWxsIHtcbiAgICByZXR1cm4gdGhpcy5rbm93bkZpbGVOYW1lVG9Nb2R1bGVOYW1lcy5nZXQoaW1wb3J0ZWRGaWxlUGF0aCkgfHwgbnVsbDtcbiAgfVxuXG4gIGFkZFN1bW1hcnkoc3VtbWFyeTogU3VtbWFyeTxTdGF0aWNTeW1ib2w+KSB7XG4gICAgdGhpcy5zdW1tYXJ5Q2FjaGUuc2V0KHN1bW1hcnkuc3ltYm9sLCBzdW1tYXJ5KTtcbiAgfVxuXG4gIHByaXZhdGUgX2xvYWRTdW1tYXJ5RmlsZShmaWxlUGF0aDogc3RyaW5nKTogYm9vbGVhbiB7XG4gICAgbGV0IGhhc1N1bW1hcnkgPSB0aGlzLmxvYWRlZEZpbGVQYXRocy5nZXQoZmlsZVBhdGgpO1xuICAgIGlmIChoYXNTdW1tYXJ5ICE9IG51bGwpIHtcbiAgICAgIHJldHVybiBoYXNTdW1tYXJ5O1xuICAgIH1cbiAgICBsZXQganNvbjogc3RyaW5nfG51bGwgPSBudWxsO1xuICAgIGlmICh0aGlzLmlzTGlicmFyeUZpbGUoZmlsZVBhdGgpKSB7XG4gICAgICBjb25zdCBzdW1tYXJ5RmlsZVBhdGggPSBzdW1tYXJ5RmlsZU5hbWUoZmlsZVBhdGgpO1xuICAgICAgdHJ5IHtcbiAgICAgICAganNvbiA9IHRoaXMuaG9zdC5sb2FkU3VtbWFyeShzdW1tYXJ5RmlsZVBhdGgpO1xuICAgICAgfSBjYXRjaCAoZSkge1xuICAgICAgICBjb25zb2xlLmVycm9yKGBFcnJvciBsb2FkaW5nIHN1bW1hcnkgZmlsZSAke3N1bW1hcnlGaWxlUGF0aH1gKTtcbiAgICAgICAgdGhyb3cgZTtcbiAgICAgIH1cbiAgICB9XG4gICAgaGFzU3VtbWFyeSA9IGpzb24gIT0gbnVsbDtcbiAgICB0aGlzLmxvYWRlZEZpbGVQYXRocy5zZXQoZmlsZVBhdGgsIGhhc1N1bW1hcnkpO1xuICAgIGlmIChqc29uKSB7XG4gICAgICBjb25zdCB7bW9kdWxlTmFtZSwgc3VtbWFyaWVzLCBpbXBvcnRBc30gPVxuICAgICAgICAgIGRlc2VyaWFsaXplU3VtbWFyaWVzKHRoaXMuc3RhdGljU3ltYm9sQ2FjaGUsIHRoaXMsIGZpbGVQYXRoLCBqc29uKTtcbiAgICAgIHN1bW1hcmllcy5mb3JFYWNoKChzdW1tYXJ5KSA9PiB0aGlzLnN1bW1hcnlDYWNoZS5zZXQoc3VtbWFyeS5zeW1ib2wsIHN1bW1hcnkpKTtcbiAgICAgIGlmIChtb2R1bGVOYW1lKSB7XG4gICAgICAgIHRoaXMua25vd25GaWxlTmFtZVRvTW9kdWxlTmFtZXMuc2V0KGZpbGVQYXRoLCBtb2R1bGVOYW1lKTtcbiAgICAgIH1cbiAgICAgIGltcG9ydEFzLmZvckVhY2goKGltcG9ydEFzKSA9PiB7XG4gICAgICAgIHRoaXMuaW1wb3J0QXMuc2V0KGltcG9ydEFzLnN5bWJvbCwgaW1wb3J0QXMuaW1wb3J0QXMpO1xuICAgICAgfSk7XG4gICAgfVxuICAgIHJldHVybiBoYXNTdW1tYXJ5O1xuICB9XG59XG4iXX0=