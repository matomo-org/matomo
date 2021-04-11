(function (factory) {
    if (typeof module === "object" && typeof module.exports === "object") {
        var v = factory(require, exports);
        if (v !== undefined) module.exports = v;
    }
    else if (typeof define === "function" && define.amd) {
        define("@angular/compiler/src/parse_util", ["require", "exports", "@angular/compiler/src/chars", "@angular/compiler/src/compile_metadata"], factory);
    }
})(function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.r3JitTypeSourceSpan = exports.typeSourceSpan = exports.ParseError = exports.ParseErrorLevel = exports.ParseSourceSpan = exports.ParseSourceFile = exports.ParseLocation = void 0;
    /**
     * @license
     * Copyright Google LLC All Rights Reserved.
     *
     * Use of this source code is governed by an MIT-style license that can be
     * found in the LICENSE file at https://angular.io/license
     */
    var chars = require("@angular/compiler/src/chars");
    var compile_metadata_1 = require("@angular/compiler/src/compile_metadata");
    var ParseLocation = /** @class */ (function () {
        function ParseLocation(file, offset, line, col) {
            this.file = file;
            this.offset = offset;
            this.line = line;
            this.col = col;
        }
        ParseLocation.prototype.toString = function () {
            return this.offset != null ? this.file.url + "@" + this.line + ":" + this.col : this.file.url;
        };
        ParseLocation.prototype.moveBy = function (delta) {
            var source = this.file.content;
            var len = source.length;
            var offset = this.offset;
            var line = this.line;
            var col = this.col;
            while (offset > 0 && delta < 0) {
                offset--;
                delta++;
                var ch = source.charCodeAt(offset);
                if (ch == chars.$LF) {
                    line--;
                    var priorLine = source.substr(0, offset - 1).lastIndexOf(String.fromCharCode(chars.$LF));
                    col = priorLine > 0 ? offset - priorLine : offset;
                }
                else {
                    col--;
                }
            }
            while (offset < len && delta > 0) {
                var ch = source.charCodeAt(offset);
                offset++;
                delta--;
                if (ch == chars.$LF) {
                    line++;
                    col = 0;
                }
                else {
                    col++;
                }
            }
            return new ParseLocation(this.file, offset, line, col);
        };
        // Return the source around the location
        // Up to `maxChars` or `maxLines` on each side of the location
        ParseLocation.prototype.getContext = function (maxChars, maxLines) {
            var content = this.file.content;
            var startOffset = this.offset;
            if (startOffset != null) {
                if (startOffset > content.length - 1) {
                    startOffset = content.length - 1;
                }
                var endOffset = startOffset;
                var ctxChars = 0;
                var ctxLines = 0;
                while (ctxChars < maxChars && startOffset > 0) {
                    startOffset--;
                    ctxChars++;
                    if (content[startOffset] == '\n') {
                        if (++ctxLines == maxLines) {
                            break;
                        }
                    }
                }
                ctxChars = 0;
                ctxLines = 0;
                while (ctxChars < maxChars && endOffset < content.length - 1) {
                    endOffset++;
                    ctxChars++;
                    if (content[endOffset] == '\n') {
                        if (++ctxLines == maxLines) {
                            break;
                        }
                    }
                }
                return {
                    before: content.substring(startOffset, this.offset),
                    after: content.substring(this.offset, endOffset + 1),
                };
            }
            return null;
        };
        return ParseLocation;
    }());
    exports.ParseLocation = ParseLocation;
    var ParseSourceFile = /** @class */ (function () {
        function ParseSourceFile(content, url) {
            this.content = content;
            this.url = url;
        }
        return ParseSourceFile;
    }());
    exports.ParseSourceFile = ParseSourceFile;
    var ParseSourceSpan = /** @class */ (function () {
        /**
         * Create an object that holds information about spans of tokens/nodes captured during
         * lexing/parsing of text.
         *
         * @param start
         * The location of the start of the span (having skipped leading trivia).
         * Skipping leading trivia makes source-spans more "user friendly", since things like HTML
         * elements will appear to begin at the start of the opening tag, rather than at the start of any
         * leading trivia, which could include newlines.
         *
         * @param end
         * The location of the end of the span.
         *
         * @param fullStart
         * The start of the token without skipping the leading trivia.
         * This is used by tooling that splits tokens further, such as extracting Angular interpolations
         * from text tokens. Such tooling creates new source-spans relative to the original token's
         * source-span. If leading trivia characters have been skipped then the new source-spans may be
         * incorrectly offset.
         *
         * @param details
         * Additional information (such as identifier names) that should be associated with the span.
         */
        function ParseSourceSpan(start, end, fullStart, details) {
            if (fullStart === void 0) { fullStart = start; }
            if (details === void 0) { details = null; }
            this.start = start;
            this.end = end;
            this.fullStart = fullStart;
            this.details = details;
        }
        ParseSourceSpan.prototype.toString = function () {
            return this.start.file.content.substring(this.start.offset, this.end.offset);
        };
        return ParseSourceSpan;
    }());
    exports.ParseSourceSpan = ParseSourceSpan;
    var ParseErrorLevel;
    (function (ParseErrorLevel) {
        ParseErrorLevel[ParseErrorLevel["WARNING"] = 0] = "WARNING";
        ParseErrorLevel[ParseErrorLevel["ERROR"] = 1] = "ERROR";
    })(ParseErrorLevel = exports.ParseErrorLevel || (exports.ParseErrorLevel = {}));
    var ParseError = /** @class */ (function () {
        function ParseError(span, msg, level) {
            if (level === void 0) { level = ParseErrorLevel.ERROR; }
            this.span = span;
            this.msg = msg;
            this.level = level;
        }
        ParseError.prototype.contextualMessage = function () {
            var ctx = this.span.start.getContext(100, 3);
            return ctx ? this.msg + " (\"" + ctx.before + "[" + ParseErrorLevel[this.level] + " ->]" + ctx.after + "\")" :
                this.msg;
        };
        ParseError.prototype.toString = function () {
            var details = this.span.details ? ", " + this.span.details : '';
            return this.contextualMessage() + ": " + this.span.start + details;
        };
        return ParseError;
    }());
    exports.ParseError = ParseError;
    function typeSourceSpan(kind, type) {
        var moduleUrl = compile_metadata_1.identifierModuleUrl(type);
        var sourceFileName = moduleUrl != null ? "in " + kind + " " + compile_metadata_1.identifierName(type) + " in " + moduleUrl :
            "in " + kind + " " + compile_metadata_1.identifierName(type);
        var sourceFile = new ParseSourceFile('', sourceFileName);
        return new ParseSourceSpan(new ParseLocation(sourceFile, -1, -1, -1), new ParseLocation(sourceFile, -1, -1, -1));
    }
    exports.typeSourceSpan = typeSourceSpan;
    /**
     * Generates Source Span object for a given R3 Type for JIT mode.
     *
     * @param kind Component or Directive.
     * @param typeName name of the Component or Directive.
     * @param sourceUrl reference to Component or Directive source.
     * @returns instance of ParseSourceSpan that represent a given Component or Directive.
     */
    function r3JitTypeSourceSpan(kind, typeName, sourceUrl) {
        var sourceFileName = "in " + kind + " " + typeName + " in " + sourceUrl;
        var sourceFile = new ParseSourceFile('', sourceFileName);
        return new ParseSourceSpan(new ParseLocation(sourceFile, -1, -1, -1), new ParseLocation(sourceFile, -1, -1, -1));
    }
    exports.r3JitTypeSourceSpan = r3JitTypeSourceSpan;
});
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoicGFyc2VfdXRpbC5qcyIsInNvdXJjZVJvb3QiOiIiLCJzb3VyY2VzIjpbIi4uLy4uLy4uLy4uLy4uLy4uL3BhY2thZ2VzL2NvbXBpbGVyL3NyYy9wYXJzZV91dGlsLnRzIl0sIm5hbWVzIjpbXSwibWFwcGluZ3MiOiI7Ozs7Ozs7Ozs7OztJQUFBOzs7Ozs7T0FNRztJQUNILG1EQUFpQztJQUNqQywyRUFBa0c7SUFFbEc7UUFDRSx1QkFDVyxJQUFxQixFQUFTLE1BQWMsRUFBUyxJQUFZLEVBQ2pFLEdBQVc7WUFEWCxTQUFJLEdBQUosSUFBSSxDQUFpQjtZQUFTLFdBQU0sR0FBTixNQUFNLENBQVE7WUFBUyxTQUFJLEdBQUosSUFBSSxDQUFRO1lBQ2pFLFFBQUcsR0FBSCxHQUFHLENBQVE7UUFBRyxDQUFDO1FBRTFCLGdDQUFRLEdBQVI7WUFDRSxPQUFPLElBQUksQ0FBQyxNQUFNLElBQUksSUFBSSxDQUFDLENBQUMsQ0FBSSxJQUFJLENBQUMsSUFBSSxDQUFDLEdBQUcsU0FBSSxJQUFJLENBQUMsSUFBSSxTQUFJLElBQUksQ0FBQyxHQUFLLENBQUMsQ0FBQyxDQUFDLElBQUksQ0FBQyxJQUFJLENBQUMsR0FBRyxDQUFDO1FBQzNGLENBQUM7UUFFRCw4QkFBTSxHQUFOLFVBQU8sS0FBYTtZQUNsQixJQUFNLE1BQU0sR0FBRyxJQUFJLENBQUMsSUFBSSxDQUFDLE9BQU8sQ0FBQztZQUNqQyxJQUFNLEdBQUcsR0FBRyxNQUFNLENBQUMsTUFBTSxDQUFDO1lBQzFCLElBQUksTUFBTSxHQUFHLElBQUksQ0FBQyxNQUFNLENBQUM7WUFDekIsSUFBSSxJQUFJLEdBQUcsSUFBSSxDQUFDLElBQUksQ0FBQztZQUNyQixJQUFJLEdBQUcsR0FBRyxJQUFJLENBQUMsR0FBRyxDQUFDO1lBQ25CLE9BQU8sTUFBTSxHQUFHLENBQUMsSUFBSSxLQUFLLEdBQUcsQ0FBQyxFQUFFO2dCQUM5QixNQUFNLEVBQUUsQ0FBQztnQkFDVCxLQUFLLEVBQUUsQ0FBQztnQkFDUixJQUFNLEVBQUUs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QUN0QixJQUFJLGFBQWEsQ0FBQyxVQUFVLEVBQUUsQ0FBQyxDQUFDLEVBQUUsQ0FBQyxDQUFDLEVBQUUsQ0FBQyxDQUFDLENBQUMsRUFBRSxJQUFJLGFBQWEsQ0FBQyxVQUFVLEVBQUUsQ0FBQyxDQUFDLEVBQUUsQ0FBQyxDQUFDLEVBQUUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDO0lBQzVGLENBQUM7SUFQRCx3Q0FPQztJQUVEOzs7Ozs7O09BT0c7SUFDSCxTQUFnQixtQkFBbUIsQ0FDL0IsSUFBWSxFQUFFLFFBQWdCLEVBQUUsU0FBaUI7UUFDbkQsSUFBTSxjQUFjLEdBQUcsUUFBTSxJQUFJLFNBQUksUUFBUSxZQUFPLFNBQVcsQ0FBQztRQUNoRSxJQUFNLFVBQVUsR0FBRyxJQUFJLGVBQWUsQ0FBQyxFQUFFLEVBQUUsY0FBYyxDQUFDLENBQUM7UUFDM0QsT0FBTyxJQUFJLGVBQWUsQ0FDdEIsSUFBSSxhQUFhLENBQUMsVUFBVSxFQUFFLENBQUMsQ0FBQyxFQUFFLENBQUMsQ0FBQyxFQUFFLENBQUMsQ0FBQyxDQUFDLEVBQUUsSUFBSSxhQUFhLENBQUMsVUFBVSxFQUFFLENBQUMsQ0FBQyxFQUFFLENBQUMsQ0FBQyxFQUFFLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQztJQUM1RixDQUFDO0lBTkQsa0RBTUMiLCJzb3VyY2VzQ29udGVudCI6WyIvKipcbiAqIEBsaWNlbnNlXG4gKiBDb3B5cmlnaHQgR29vZ2xlIExMQyBBbGwgUmlnaHRzIFJlc2VydmVkLlxuICpcbiAqIFVzZSBvZiB0aGlzIHNvdXJjZSBjb2RlIGlzIGdvdmVybmVkIGJ5IGFuIE1JVC1zdHlsZSBsaWNlbnNlIHRoYXQgY2FuIGJlXG4gKiBmb3VuZCBpbiB0aGUgTElDRU5TRSBmaWxlIGF0IGh0dHBzOi8vYW5ndWxhci5pby9saWNlbnNlXG4gKi9cbmltcG9ydCAqIGFzIGNoYXJzIGZyb20gJy4vY2hhcnMnO1xuaW1wb3J0IHtDb21waWxlSWRlbnRpZmllck1ldGFkYXRhLCBpZGVudGlmaWVyTW9kdWxlVXJsLCBpZGVudGlmaWVyTmFtZX0gZnJvbSAnLi9jb21waWxlX21ldGFkYXRhJztcblxuZXhwb3J0IGNsYXNzIFBhcnNlTG9jYXRpb24ge1xuICBjb25zdHJ1Y3RvcihcbiAgICAgIHB1YmxpYyBmaWxlOiBQYXJzZVNvdXJjZUZpbGUsIHB1YmxpYyBvZmZzZXQ6IG51bWJlciwgcHVibGljIGxpbmU6IG51bWJlcixcbiAgICAgIHB1YmxpYyBjb2w6IG51bWJlcikge31cblxuICB0b1N0cmluZygpOiBzdHJpbmcge1xuICAgIHJldHVybiB0aGlzLm9mZnNldCAhPSBudWxsID8gYCR7dGhpcy5maWxlLnVybH1AJHt0aGlzLmxpbmV9OiR7dGhpcy5jb2x9YCA6IHRoaXMuZmlsZS51cmw7XG4gIH1cblxuICBtb3ZlQnkoZGVsdGE6IG51bWJlcik6IFBhcnNlTG9jYXRpb24ge1xuICAgIGNvbnN0IHNvdXJjZSA9IHRoaXMuZmlsZS5jb250ZW50O1xuICAgIGNvbnN0IGxlbiA9IHNvdXJjZS5sZW5ndGg7XG4gICAgbGV0IG9mZnNldCA9IHRoaXMub2Zmc2V0O1xuICAgIGxldCBsaW5lID0gdGhpcy5saW5lO1xuICAgIGxldCBjb2wgPSB0aGlzLmNvbDtcbiAgICB3aGlsZSAob2Zmc2V0ID4gMCAmJiBkZWx0YSA8IDApIHtcbiAgICAgIG9mZnNldC0tO1xuICAgICAgZGVsdGErKztcbiAgICAgIGNvbnN0IGNoID0gc291cmNlLmNoYXJDb2RlQXQob2Zmc2V0KTtcbiAgICAgIGlmIChjaCA9PSBjaGFycy4kTEYpIHtcbiAgICAgICAgbGluZS0tO1xuICAgICAgICBjb25zdCBwcmlvckxpbmUgPSBzb3VyY2Uuc3Vic3RyKDAsIG9mZnNldCAtIDEpLmxhc3RJbmRleE9mKFN0cmluZy5mcm9tQ2hhckNvZGUoY2hhcnMuJExGKSk7XG4gICAgICAgIGNvbCA9IHByaW9yTGluZSA+IDAgPyBvZmZzZXQgLSBwcmlvckxpbmUgOiBvZmZzZXQ7XG4gICAgICB9IGVsc2Uge1xuICAgICAgICBjb2wtLTtcbiAgICAgIH1cbiAgICB9XG4gICAgd2hpbGUgKG9mZnNldCA8IGxlbiAmJiBkZWx0YSA+IDApIHtcbiAgICAgIGNvbnN0IGNoID0gc291cmNlLmNoYXJDb2RlQXQob2Zmc2V0KTtcbiAgICAgIG9mZnNldCsrO1xuICAgICAgZGVsdGEtLTtcbiAgICAgIGlmIChjaCA9PSBjaGFycy4kTEYpIHtcbiAgICAgICAgbGluZSsrO1xuICAgICAgICBjb2wgPSAwO1xuICAgICAgfSBlbHNlIHtcbiAgICAgICAgY29sKys7XG4gICAgICB9XG4gICAgfVxuICAgIHJldHVybiBuZXcgUGFyc2VMb2NhdGlvbih0aGlzLmZpbGUsIG9mZnNldCwgbGluZSwgY29sKTtcbiAgfVxuXG4gIC8vIFJldHVybiB0aGUgc291cmNlIGFyb3VuZCB0aGUgbG9jYXRpb25cbiAgLy8gVXAgdG8gYG1heENoYXJzYCBvciBgbWF4TGluZXNgIG9uIGVhY2ggc2lkZSBvZiB0aGUgbG9jYXRpb25cbiAgZ2V0Q29udGV4dChtYXhDaGFyczogbnVtYmVyLCBtYXhMaW5lczogbnVtYmVyKToge2JlZm9yZTogc3RyaW5nLCBhZnRlcjogc3RyaW5nfXxudWxsIHtcbiAgICBjb25zdCBjb250ZW50ID0gdGhpcy5maWxlLmNvbnRlbnQ7XG4gICAgbGV0IHN0YXJ0T2Zmc2V0ID0gdGhpcy5vZmZzZXQ7XG5cbiAgICBpZiAoc3RhcnRPZmZzZXQgIT0gbnVsbCkge1xuICAgICAgaWYgKHN0YXJ0T2Zmc2V0ID4gY29udGVudC5sZW5ndGggLSAxKSB7XG4gICAgICAgIHN0YXJ0T2Zmc2V0ID0gY29udGVudC5sZW5ndGggLSAxO1xuICAgICAgfVxuICAgICAgbGV0IGVuZE9mZnNldCA9IHN0YXJ0T2Zmc2V0O1xuICAgICAgbGV0IGN0eENoYXJzID0gMDtcbiAgICAgIGxldCBjdHhMaW5lcyA9IDA7XG5cbiAgICAgIHdoaWxlIChjdHhDaGFycyA8IG1heENoYXJzICYmIHN0YXJ0T2Zmc2V0ID4gMCkge1xuICAgICAgICBzdGFydE9mZnNldC0tO1xuICAgICAgICBjdHhDaGFycysrO1xuICAgICAgICBpZiAoY29udGVudFtzdGFydE9mZnNldF0gPT0gJ1xcbicpIHtcbiAgICAgICAgICBpZiAoKytjdHhMaW5lcyA9PSBtYXhMaW5lcykge1xuICAgICAgICAgICAgYnJlYWs7XG4gICAgICAgICAgfVxuICAgICAgICB9XG4gICAgICB9XG5cbiAgICAgIGN0eENoYXJzID0gMDtcbiAgICAgIGN0eExpbmVzID0gMDtcbiAgICAgIHdoaWxlIChjdHhDaGFycyA8IG1heENoYXJzICYmIGVuZE9mZnNldCA8IGNvbnRlbnQubGVuZ3RoIC0gMSkge1xuICAgICAgICBlbmRPZmZzZXQrKztcbiAgICAgICAgY3R4Q2hhcnMrKztcbiAgICAgICAgaWYgKGNvbnRlbnRbZW5kT2Zmc2V0XSA9PSAnXFxuJykge1xuICAgICAgICAgIGlmICgrK2N0eExpbmVzID09IG1heExpbmVzKSB7XG4gICAgICAgICAgICBicmVhaztcbiAgICAgICAgICB9XG4gICAgICAgIH1cbiAgICAgIH1cblxuICAgICAgcmV0dXJuIHtcbiAgICAgICAgYmVmb3JlOiBjb250ZW50LnN1YnN0cmluZyhzdGFydE9mZnNldCwgdGhpcy5vZmZzZXQpLFxuICAgICAgICBhZnRlcjogY29udGVudC5zdWJzdHJpbmcodGhpcy5vZmZzZXQsIGVuZE9mZnNldCArIDEpLFxuICAgICAgfTtcbiAgICB9XG5cbiAgICByZXR1cm4gbnVsbDtcbiAgfVxufVxuXG5leHBvcnQgY2xhc3MgUGFyc2VTb3VyY2VGaWxlIHtcbiAgY29uc3RydWN0b3IocHVibGljIGNvbnRlbnQ6IHN0cmluZywgcHVibGljIHVybDogc3RyaW5nKSB7fVxufVxuXG5leHBvcnQgY2xhc3MgUGFyc2VTb3VyY2VTcGFuIHtcbiAgLyoqXG4gICAqIENyZWF0ZSBhbiBvYmplY3QgdGhhdCBob2xkcyBpbmZvcm1hdGlvbiBhYm91dCBzcGFucyBvZiB0b2tlbnMvbm9kZXMgY2FwdHVyZWQgZHVyaW5nXG4gICAqIGxleGluZy9wYXJzaW5nIG9mIHRleHQuXG4gICAqXG4gICAqIEBwYXJhbSBzdGFydFxuICAgKiBUaGUgbG9jYXRpb24gb2YgdGhlIHN0YXJ0IG9mIHRoZSBzcGFuIChoYXZpbmcgc2tpcHBlZCBsZWFkaW5nIHRyaXZpYSkuXG4gICAqIFNraXBwaW5nIGxlYWRpbmcgdHJpdmlhIG1ha2VzIHNvdXJjZS1zcGFucyBtb3JlIFwidXNlciBmcmllbmRseVwiLCBzaW5jZSB0aGluZ3MgbGlrZSBIVE1MXG4gICAqIGVsZW1lbnRzIHdpbGwgYXBwZWFyIHRvIGJlZ2luIGF0IHRoZSBzdGFydCBvZiB0aGUgb3BlbmluZyB0YWcsIHJhdGhlciB0aGFuIGF0IHRoZSBzdGFydCBvZiBhbnlcbiAgICogbGVhZGluZyB0cml2aWEsIHdoaWNoIGNvdWxkIGluY2x1ZGUgbmV3bGluZXMuXG4gICAqXG4gICAqIEBwYXJhbSBlbmRcbiAgICogVGhlIGxvY2F0aW9uIG9mIHRoZSBlbmQgb2YgdGhlIHNwYW4uXG4gICAqXG4gICAqIEBwYXJhbSBmdWxsU3RhcnRcbiAgICogVGhlIHN0YXJ0IG9mIHRoZSB0b2tlbiB3aXRob3V0IHNraXBwaW5nIHRoZSBsZWFkaW5nIHRyaXZpYS5cbiAgICogVGhpcyBpcyB1c2VkIGJ5IHRvb2xpbmcgdGhhdCBzcGxpdHMgdG9rZW5zIGZ1cnRoZXIsIHN1Y2ggYXMgZXh0cmFjdGluZyBBbmd1bGFyIGludGVycG9sYXRpb25zXG4gICAqIGZyb20gdGV4dCB0b2tlbnMuIFN1Y2ggdG9vbGluZyBjcmVhdGVzIG5ldyBzb3VyY2Utc3BhbnMgcmVsYXRpdmUgdG8gdGhlIG9yaWdpbmFsIHRva2VuJ3NcbiAgICogc291cmNlLXNwYW4uIElmIGxlYWRpbmcgdHJpdmlhIGNoYXJhY3RlcnMgaGF2ZSBiZWVuIHNraXBwZWQgdGhlbiB0aGUgbmV3IHNvdXJjZS1zcGFucyBtYXkgYmVcbiAgICogaW5jb3JyZWN0bHkgb2Zmc2V0LlxuICAgKlxuICAgKiBAcGFyYW0gZGV0YWlsc1xuICAgKiBBZGRpdGlvbmFsIGluZm9ybWF0aW9uIChzdWNoIGFzIGlkZW50aWZpZXIgbmFtZXMpIHRoYXQgc2hvdWxkIGJlIGFzc29jaWF0ZWQgd2l0aCB0aGUgc3Bhbi5cbiAgICovXG4gIGNvbnN0cnVjdG9yKFxuICAgICAgcHVibGljIHN0YXJ0OiBQYXJzZUxvY2F0aW9uLCBwdWJsaWMgZW5kOiBQYXJzZUxvY2F0aW9uLFxuICAgICAgcHVibGljIGZ1bGxTdGFydDogUGFyc2VMb2NhdGlvbiA9IHN0YXJ0LCBwdWJsaWMgZGV0YWlsczogc3RyaW5nfG51bGwgPSBudWxsKSB7fVxuXG4gIHRvU3RyaW5nKCk6IHN0cmluZyB7XG4gICAgcmV0dXJuIHRoaXMuc3RhcnQuZmlsZS5jb250ZW50LnN1YnN0cmluZyh0aGlzLnN0YXJ0Lm9mZnNldCwgdGhpcy5lbmQub2Zmc2V0KTtcbiAgfVxufVxuXG5leHBvcnQgZW51bSBQYXJzZUVycm9yTGV2ZWwge1xuICBXQVJOSU5HLFxuICBFUlJPUixcbn1cblxuZXhwb3J0IGNsYXNzIFBhcnNlRXJyb3Ige1xuICBjb25zdHJ1Y3RvcihcbiAgICAgIHB1YmxpYyBzcGFuOiBQYXJzZVNvdXJjZVNwYW4sIHB1YmxpYyBtc2c6IHN0cmluZyxcbiAgICAgIHB1YmxpYyBsZXZlbDogUGFyc2VFcnJvckxldmVsID0gUGFyc2VFcnJvckxldmVsLkVSUk9SKSB7fVxuXG4gIGNvbnRleHR1YWxNZXNzYWdlKCk6IHN0cmluZyB7XG4gICAgY29uc3QgY3R4ID0gdGhpcy5zcGFuLnN0YXJ0LmdldENvbnRleHQoMTAwLCAzKTtcbiAgICByZXR1cm4gY3R4ID8gYCR7dGhpcy5tc2d9IChcIiR7Y3R4LmJlZm9yZX1bJHtQYXJzZUVycm9yTGV2ZWxbdGhpcy5sZXZlbF19IC0+XSR7Y3R4LmFmdGVyfVwiKWAgOlxuICAgICAgICAgICAgICAgICB0aGlzLm1zZztcbiAgfVxuXG4gIHRvU3RyaW5nKCk6IHN0cmluZyB7XG4gICAgY29uc3QgZGV0YWlscyA9IHRoaXMuc3Bhbi5kZXRhaWxzID8gYCwgJHt0aGlzLnNwYW4uZGV0YWlsc31gIDogJyc7XG4gICAgcmV0dXJuIGAke3RoaXMuY29udGV4dHVhbE1lc3NhZ2UoKX06ICR7dGhpcy5zcGFuLnN0YXJ0fSR7ZGV0YWlsc31gO1xuICB9XG59XG5cbmV4cG9ydCBmdW5jdGlvbiB0eXBlU291cmNlU3BhbihraW5kOiBzdHJpbmcsIHR5cGU6IENvbXBpbGVJZGVudGlmaWVyTWV0YWRhdGEpOiBQYXJzZVNvdXJjZVNwYW4ge1xuICBjb25zdCBtb2R1bGVVcmwgPSBpZGVudGlmaWVyTW9kdWxlVXJsKHR5cGUpO1xuICBjb25zdCBzb3VyY2VGaWxlTmFtZSA9IG1vZHVsZVVybCAhPSBudWxsID8gYGluICR7a2luZH0gJHtpZGVudGlmaWVyTmFtZSh0eXBlKX0gaW4gJHttb2R1bGVVcmx9YCA6XG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBgaW4gJHtraW5kfSAke2lkZW50aWZpZXJOYW1lKHR5cGUpfWA7XG4gIGNvbnN0IHNvdXJjZUZpbGUgPSBuZXcgUGFyc2VTb3VyY2VGaWxlKCcnLCBzb3VyY2VGaWxlTmFtZSk7XG4gIHJldHVybiBuZXcgUGFyc2VTb3VyY2VTcGFuKFxuICAgICAgbmV3IFBhcnNlTG9jYXRpb24oc291cmNlRmlsZSwgLTEsIC0xLCAtMSksIG5ldyBQYXJzZUxvY2F0aW9uKHNvdXJjZUZpbGUsIC0xLCAtMSwgLTEpKTtcbn1cblxuLyoqXG4gKiBHZW5lcmF0ZXMgU291cmNlIFNwYW4gb2JqZWN0IGZvciBhIGdpdmVuIFIzIFR5cGUgZm9yIEpJVCBtb2RlLlxuICpcbiAqIEBwYXJhbSBraW5kIENvbXBvbmVudCBvciBEaXJlY3RpdmUuXG4gKiBAcGFyYW0gdHlwZU5hbWUgbmFtZSBvZiB0aGUgQ29tcG9uZW50IG9yIERpcmVjdGl2ZS5cbiAqIEBwYXJhbSBzb3VyY2VVcmwgcmVmZXJlbmNlIHRvIENvbXBvbmVudCBvciBEaXJlY3RpdmUgc291cmNlLlxuICogQHJldHVybnMgaW5zdGFuY2Ugb2YgUGFyc2VTb3VyY2VTcGFuIHRoYXQgcmVwcmVzZW50IGEgZ2l2ZW4gQ29tcG9uZW50IG9yIERpcmVjdGl2ZS5cbiAqL1xuZXhwb3J0IGZ1bmN0aW9uIHIzSml0VHlwZVNvdXJjZVNwYW4oXG4gICAga2luZDogc3RyaW5nLCB0eXBlTmFtZTogc3RyaW5nLCBzb3VyY2VVcmw6IHN0cmluZyk6IFBhcnNlU291cmNlU3BhbiB7XG4gIGNvbnN0IHNvdXJjZUZpbGVOYW1lID0gYGluICR7a2luZH0gJHt0eXBlTmFtZX0gaW4gJHtzb3VyY2VVcmx9YDtcbiAgY29uc3Qgc291cmNlRmlsZSA9IG5ldyBQYXJzZVNvdXJjZUZpbGUoJycsIHNvdXJjZUZpbGVOYW1lKTtcbiAgcmV0dXJuIG5ldyBQYXJzZVNvdXJjZVNwYW4oXG4gICAgICBuZXcgUGFyc2VMb2NhdGlvbihzb3VyY2VGaWxlLCAtMSwgLTEsIC0xKSwgbmV3IFBhcnNlTG9jYXRpb24oc291cmNlRmlsZSwgLTEsIC0xLCAtMSkpO1xufVxuIl19