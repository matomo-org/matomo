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
        define("@angular/compiler/src/aot/generated_file", ["require", "exports", "@angular/compiler/src/output/output_ast", "@angular/compiler/src/output/ts_emitter"], factory);
    }
})(function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.toTypeScript = exports.GeneratedFile = void 0;
    var output_ast_1 = require("@angular/compiler/src/output/output_ast");
    var ts_emitter_1 = require("@angular/compiler/src/output/ts_emitter");
    var GeneratedFile = /** @class */ (function () {
        function GeneratedFile(srcFileUrl, genFileUrl, sourceOrStmts) {
            this.srcFileUrl = srcFileUrl;
            this.genFileUrl = genFileUrl;
            if (typeof sourceOrStmts === 'string') {
                this.source = sourceOrStmts;
                this.stmts = null;
            }
            else {
                this.source = null;
                this.stmts = sourceOrStmts;
            }
        }
        GeneratedFile.prototype.isEquivalent = function (other) {
            if (this.genFileUrl !== other.genFileUrl) {
                return false;
            }
            if (this.source) {
                return this.source === other.source;
            }
            if (other.stmts == null) {
                return false;
            }
            // Note: the constructor guarantees that if this.source is not filled,
            // then this.stmts is.
            return output_ast_1.areAllEquivalent(this.stmts, other.stmts);
        };
        return GeneratedFile;
    }());
    exports.GeneratedFile = GeneratedFile;
    function toTypeScript(file, preamble) {
        if (preamble === void 0) { preamble = ''; }
        if (!file.stmts) {
            throw new Error("Illegal state: No stmts present on GeneratedFile " + file.genFileUrl);
        }
        return new ts_emitter_1.TypeScriptEmitter().emitStatements(file.genFileUrl, file.stmts, preamble);
    }
    exports.toTypeScript = toTypeScript;
});
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiZ2VuZXJhdGVkX2ZpbGUuanMiLCJzb3VyY2VSb290IjoiIiwic291cmNlcyI6WyIuLi8uLi8uLi8uLi8uLi8uLi8uLi9wYWNrYWdlcy9jb21waWxlci9zcmMvYW90L2dlbmVyYXRlZF9maWxlLnRzIl0sIm5hbWVzIjpbXSwibWFwcGluZ3MiOiJBQUFBOzs7Ozs7R0FNRzs7Ozs7Ozs7Ozs7OztJQUVILHNFQUFpRTtJQUNqRSxzRUFBdUQ7SUFFdkQ7UUFJRSx1QkFDVyxVQUFrQixFQUFTLFVBQWtCLEVBQUUsYUFBaUM7WUFBaEYsZUFBVSxHQUFWLFVBQVUsQ0FBUTtZQUFTLGVBQVUsR0FBVixVQUFVLENBQVE7WUFDdEQsSUFBSSxPQUFPLGFBQWEsS0FBSyxRQUFRLEVBQUU7Z0JBQ3JDLElBQUksQ0FBQyxNQUFNLEdBQUcsYUFBYSxDQUFDO2dCQUM1QixJQUFJLENBQUMsS0FBSyxHQUFHLElBQUksQ0FBQzthQUNuQjtpQkFBTTtnQkFDTCxJQUFJLENBQUMsTUFBTSxHQUFHLElBQUksQ0FBQztnQkFDbkIsSUFBSSxDQUFDLEtBQUssR0FBRyxhQUFhLENBQUM7YUFDNUI7UUFDSCxDQUFDO1FBRUQsb0NBQVksR0FBWixVQUFhLEtBQW9CO1lBQy9CLElBQUksSUFBSSxDQUFDLFVBQVUsS0FBSyxLQUFLLENBQUMsVUFBVSxFQUFFO2dCQUN4QyxPQUFPLEtBQUssQ0FBQzthQUNkO1lBQ0QsSUFBSSxJQUFJLENBQUMsTUFBTSxFQUFFO2dCQUNmLE9BQU8sSUFBSSxDQUFDLE1BQU0sS0FBSyxLQUFLLENBQUMsTUFBTSxDQUFDO2FBQ3JDO1lBQ0QsSUFBSSxLQUFLLENBQUMsS0FBSyxJQUFJLElBQUksRUFBRTtnQkFDdkIsT0FBTyxLQUFLLENBQUM7YUFDZDtZQUNELHNFQUFzRTtZQUN0RSxzQkFBc0I7WUFDdEIsT0FBTyw2QkFBZ0IsQ0FBQyxJQUFJLENBQUMsS0FBTSxFQUFFLEtBQUssQ0FBQyxLQUFNLENBQUMsQ0FBQztRQUNyRCxDQUFDO1FBQ0gsb0JBQUM7SUFBRCxDQUFDLEFBN0JELElBNkJDO0lBN0JZLHNDQUFhO0lBK0IxQixTQUFnQixZQUFZLENBQUMsSUFBbUIsRUFBRSxRQUFxQjtRQUFyQix5QkFBQSxFQUFBLGFBQXFCO1FBQ3JFLElBQUksQ0FBQyxJQUFJLENBQUMsS0FBSyxFQUFFO1lBQ2YsTUFBTSxJQUFJLEtBQUssQ0FBQyxzREFBb0QsSUFBSSxDQUFDLFVBQVksQ0FBQyxDQUFDO1NBQ3hGO1FBQ0QsT0FBTyxJQUFJLDhCQUFpQixFQUFFLENBQUMsY0FBYyxDQUFDLElBQUksQ0FBQyxVQUFVLEVBQUUsSUFBSSxDQUFDLEtBQUssRUFBRSxRQUFRLENBQUMsQ0FBQztJQUN2RixDQUFDO0lBTEQsb0NBS0MiLCJzb3VyY2VzQ29udGVudCI6WyIvKipcbiAqIEBsaWNlbnNlXG4gKiBDb3B5cmlnaHQgR29vZ2xlIExMQyBBbGwgUmlnaHRzIFJlc2VydmVkLlxuICpcbiAqIFVzZSBvZiB0aGlzIHNvdXJjZSBjb2RlIGlzIGdvdmVybmVkIGJ5IGFuIE1JVC1zdHlsZSBsaWNlbnNlIHRoYXQgY2FuIGJlXG4gKiBmb3VuZCBpbiB0aGUgTElDRU5TRSBmaWxlIGF0IGh0dHBzOi8vYW5ndWxhci5pby9saWNlbnNlXG4gKi9cblxuaW1wb3J0IHthcmVBbGxFcXVpdmFsZW50LCBTdGF0ZW1lbnR9IGZyb20gJy4uL291dHB1dC9vdXRwdXRfYXN0JztcbmltcG9ydCB7VHlwZVNjcmlwdEVtaXR0ZXJ9IGZyb20gJy4uL291dHB1dC90c19lbWl0dGVyJztcblxuZXhwb3J0IGNsYXNzIEdlbmVyYXRlZEZpbGUge1xuICBwdWJsaWMgc291cmNlOiBzdHJpbmd8bnVsbDtcbiAgcHVibGljIHN0bXRzOiBTdGF0ZW1lbnRbXXxudWxsO1xuXG4gIGNvbnN0cnVjdG9yKFxuICAgICAgcHVibGljIHNyY0ZpbGVVcmw6IHN0cmluZywgcHVibGljIGdlbkZpbGVVcmw6IHN0cmluZywgc291cmNlT3JTdG10czogc3RyaW5nfFN0YXRlbWVudFtdKSB7XG4gICAgaWYgKHR5cGVvZiBzb3VyY2VPclN0bXRzID09PSAnc3RyaW5nJykge1xuICAgICAgdGhpcy5zb3VyY2UgPSBzb3VyY2VPclN0bXRzO1xuICAgICAgdGhpcy5zdG10cyA9IG51bGw7XG4gICAgfSBlbHNlIHtcbiAgICAgIHRoaXMuc291cmNlID0gbnVsbDtcbiAgICAgIHRoaXMuc3RtdHMgPSBzb3VyY2VPclN0bXRzO1xuICAgIH1cbiAgfVxuXG4gIGlzRXF1aXZhbGVudChvdGhlcjogR2VuZXJhdGVkRmlsZSk6IGJvb2xlYW4ge1xuICAgIGlmICh0aGlzLmdlbkZpbGVVcmwgIT09IG90aGVyLmdlbkZpbGVVcmwpIHtcbiAgICAgIHJldHVybiBmYWxzZTtcbiAgICB9XG4gICAgaWYgKHRoaXMuc291cmNlKSB7XG4gICAgICByZXR1cm4gdGhpcy5zb3VyY2UgPT09IG90aGVyLnNvdXJjZTtcbiAgICB9XG4gICAgaWYgKG90aGVyLnN0bXRzID09IG51bGwpIHtcbiAgICAgIHJldHVybiBmYWxzZTtcbiAgICB9XG4gICAgLy8gTm90ZTogdGhlIGNvbnN0cnVjdG9yIGd1YXJhbnRlZXMgdGhhdCBpZiB0aGlzLnNvdXJjZSBpcyBub3QgZmlsbGVkLFxuICAgIC8vIHRoZW4gdGhpcy5zdG10cyBpcy5cbiAgICByZXR1cm4gYXJlQWxsRXF1aXZhbGVudCh0aGlzLnN0bXRzISwgb3RoZXIuc3RtdHMhKTtcbiAgfVxufVxuXG5leHBvcnQgZnVuY3Rpb24gdG9UeXBlU2NyaXB0KGZpbGU6IEdlbmVyYXRlZEZpbGUsIHByZWFtYmxlOiBzdHJpbmcgPSAnJyk6IHN0cmluZyB7XG4gIGlmICghZmlsZS5zdG10cykge1xuICAgIHRocm93IG5ldyBFcnJvcihgSWxsZWdhbCBzdGF0ZTogTm8gc3RtdHMgcHJlc2VudCBvbiBHZW5lcmF0ZWRGaWxlICR7ZmlsZS5nZW5GaWxlVXJsfWApO1xuICB9XG4gIHJldHVybiBuZXcgVHlwZVNjcmlwdEVtaXR0ZXIoKS5lbWl0U3RhdGVtZW50cyhmaWxlLmdlbkZpbGVVcmwsIGZpbGUuc3RtdHMsIHByZWFtYmxlKTtcbn1cbiJdfQ==