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
        define("@angular/compiler/src/ml_parser/html_whitespaces", ["require", "exports", "@angular/compiler/src/ml_parser/ast", "@angular/compiler/src/ml_parser/parser", "@angular/compiler/src/ml_parser/tags"], factory);
    }
})(function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.removeWhitespaces = exports.WhitespaceVisitor = exports.replaceNgsp = exports.PRESERVE_WS_ATTR_NAME = void 0;
    var html = require("@angular/compiler/src/ml_parser/ast");
    var parser_1 = require("@angular/compiler/src/ml_parser/parser");
    var tags_1 = require("@angular/compiler/src/ml_parser/tags");
    exports.PRESERVE_WS_ATTR_NAME = 'ngPreserveWhitespaces';
    var SKIP_WS_TRIM_TAGS = new Set(['pre', 'template', 'textarea', 'script', 'style']);
    // Equivalent to \s with \u00a0 (non-breaking space) excluded.
    // Based on https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/RegExp
    var WS_CHARS = ' \f\n\r\t\v\u1680\u180e\u2000-\u200a\u2028\u2029\u202f\u205f\u3000\ufeff';
    var NO_WS_REGEXP = new RegExp("[^" + WS_CHARS + "]");
    var WS_REPLACE_REGEXP = new RegExp("[" + WS_CHARS + "]{2,}", 'g');
    function hasPreserveWhitespacesAttr(attrs) {
        return attrs.some(function (attr) { return attr.name === exports.PRESERVE_WS_ATTR_NAME; });
    }
    /**
     * Angular Dart introduced &ngsp; as a placeholder for non-removable space, see:
     * https://github.com/dart-lang/angular/blob/0bb611387d29d65b5af7f9d2515ab571fd3fbee4/_tests/test/compiler/preserve_whitespace_test.dart#L25-L32
     * In Angular Dart &ngsp; is converted to the 0xE500 PUA (Private Use Areas) unicode character
     * and later on replaced by a space. We are re-implementing the same idea here.
     */
    function replaceNgsp(value) {
        // lexer is replacing the &ngsp; pseudo-entity with NGSP_UNICODE
        return value.replace(new RegExp(tags_1.NGSP_UNICODE, 'g'), ' ');
    }
    exports.replaceNgsp = replaceNgsp;
    /**
     * This visitor can walk HTML parse tree and remove / trim text nodes using the following rules:
     * - consider spaces, tabs and new lines as whitespace characters;
     * - drop text nodes consisting of whitespace characters only;
     * - for all other text nodes replace consecutive whitespace characters with one space;
     * - convert &ngsp; pseudo-entity to a single space;
     *
     * Removal and trimming of whitespaces have positive performance impact (less code to generate
     * while compiling templates, faster view creation). At the same time it can be "destructive"
     * in some cases (whitespaces can influence layout). Because of the potential of breaking layout
     * this visitor is not activated by default in Angular 5 and people need to explicitly opt-in for
     * whitespace removal. The default option for whitespace removal will be revisited in Angular 6
     * and might be changed to "on" by default.
     */
    var WhitespaceVisitor = /** @class */ (function () {
        function WhitespaceVisitor() {
        }
        WhitespaceVisitor.prototype.visitElement = function (element, context) {
            if (SKIP_WS_TRIM_TAGS.has(element.name) || hasPreserveWhitespacesAttr(element.attrs)) {
                // don't descent into elements where we need to preserve whitespaces
                // but still visit all attributes to eliminate one used as a market to preserve WS
                return new html.Element(element.name, html.visitAll(this, element.attrs), element.children, element.sourceSpan, element.startSourceSpan, element.endSourceSpan, element.i18n);
            }
            return new html.Element(element.name, element.attrs, visitAllWithSiblings(this, element.children), element.sourceSpan, element.startSourceSpan, element.endSourceSpan, element.i18n);
        };
        WhitespaceVisitor.prototype.visitAttribute = function (attribute, context) {
            return attribute.name !== exports.PRESERVE_WS_ATTR_NAME ? attribute : null;
        };
        WhitespaceVisitor.prototype.visitText = function (text, context) {
            var isNotBlank = text.value.match(NO_WS_REGEXP);
            var hasExpansionSibling = context &&
                (context.prev instanceof html.Expansion || context.next instanceof html.Expansion);
            if (isNotBlank || hasExpansionSibling) {
                return new html.Text(replaceNgsp(text.value).replace(WS_REPLACE_REGEXP, ' '), text.sourceSpan, text.i18n);
            }
            return null;
        };
        WhitespaceVisitor.prototype.visitComment = function (comment, context) {
            return comment;
        };
        WhitespaceVisitor.prototype.visitExpansion = function (expansion, context) {
            return expansion;
        };
        WhitespaceVisitor.prototype.visitExpansionCase = function (expansionCase, context) {
            return expansionCase;
        };
        return WhitespaceVisitor;
    }());
    exports.WhitespaceVisitor = WhitespaceVisitor;
    function removeWhitespaces(htmlAstWithErrors) {
        return new parser_1.ParseTreeResult(html.visitAll(new WhitespaceVisitor(), htmlAstWithErrors.rootNodes), htmlAstWithErrors.errors);
    }
    exports.removeWhitespaces = removeWhitespaces;
    function visitAllWithSiblings(visitor, nodes) {
        var result = [];
        nodes.forEach(function (ast, i) {
            var context = { prev: nodes[i - 1], next: nodes[i + 1] };
            var astResult = ast.visit(visitor, context);
            if (astResult) {
                result.push(astResult);
            }
        });
        return result;
    }
});
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiaHRtbF93aGl0ZXNwYWNlcy5qcyIsInNvdXJjZVJvb3QiOiIiLCJzb3VyY2VzIjpbIi4uLy4uLy4uLy4uLy4uLy4uLy4uL3BhY2thZ2VzL2NvbXBpbGVyL3NyYy9tbF9wYXJzZXIvaHRtbF93aGl0ZXNwYWNlcy50cyJdLCJuYW1lcyI6W10sIm1hcHBpbmdzIjoiQUFBQTs7Ozs7O0dBTUc7Ozs7Ozs7Ozs7Ozs7SUFFSCwwREFBOEI7SUFDOUIsaUVBQXlDO0lBQ3pDLDZEQUFvQztJQUV2QixRQUFBLHFCQUFxQixHQUFHLHVCQUF1QixDQUFDO0lBRTdELElBQU0saUJBQWlCLEdBQUcsSUFBSSxHQUFHLENBQUMsQ0FBQyxLQUFLLEVBQUUsVUFBVSxFQUFFLFVBQVUsRUFBRSxRQUFRLEVBQUUsT0FBTyxDQUFDLENBQUMsQ0FBQztJQUV0Riw4REFBOEQ7SUFDOUQsbUdBQW1HO0lBQ25HLElBQU0sUUFBUSxHQUFHLDBFQUEwRSxDQUFDO0lBQzVGLElBQU0sWUFBWSxHQUFHLElBQUksTUFBTSxDQUFDLE9BQUssUUFBUSxNQUFHLENBQUMsQ0FBQztJQUNsRCxJQUFNLGlCQUFpQixHQUFHLElBQUksTUFBTSxDQUFDLE1BQUksUUFBUSxVQUFPLEVBQUUsR0FBRyxDQUFDLENBQUM7SUFFL0QsU0FBUywwQkFBMEIsQ0FBQyxLQUF1QjtRQUN6RCxPQUFPLEtBQUssQ0FBQyxJQUFJLENBQUMsVUFBQyxJQUFvQixJQUFLLE9BQUEsSUFBSSxDQUFDLElBQUksS0FBSyw2QkFBcUIsRUFBbkMsQ0FBbUMsQ0FBQyxDQUFDO0lBQ25GLENBQUM7SUFFRDs7Ozs7T0FLRztJQUNILFNBQWdCLFdBQVcsQ0FBQyxLQUFhO1FBQ3ZDLGdFQUFnRTtRQUNoRSxPQUFPLEtBQUssQ0FBQyxPQUFPLENBQUMsSUFBSSxNQUFNLENBQUMsbUJBQVksRUFBRSxHQUFHLENBQUMsRUFBRSxHQUFHLENBQUMsQ0FBQztJQUMzRCxDQUFDO0lBSEQsa0NBR0M7SUFFRDs7Ozs7Ozs7Ozs7OztPQWFHO0lBQ0g7UUFBQTtRQTJDQSxDQUFDO1FBMUNDLHdDQUFZLEdBQVosVUFBYSxPQUFxQixFQUFFLE9BQVk7WUFDOUMsSUFBSSxpQkFBaUIsQ0FBQyxHQUFHLENBQUMsT0FBTyxDQUFDLElBQUksQ0FBQyxJQUFJLDBCQUEwQixDQUFDLE9BQU8sQ0FBQyxLQUFLLENBQUMsRUFBRTtnQkFDcEYsb0VBQW9FO2dCQUNwRSxrRkFBa0Y7Z0JBQ2xGLE9BQU8sSUFBSSxJQUFJLENBQUMsT0FBTyxDQUNuQixPQUFPLENBQUMsSUFBSSxFQUFFLElBQUksQ0FBQyxRQUFRLENBQUMsSUFBSSxFQUFFLE9BQU8sQ0FBQyxLQUFLLENBQUMsRUFBRSxPQUFPLENBQUMsUUFBUSxFQUFFLE9BQU8sQ0FBQyxVQUFVLEVBQ3RGLE9BQU8sQ0FBQyxlQUFlLEVBQUUsT0FBTyxDQUFDLGFBQWEsRUFBRSxPQUFPLENBQUMsSUFBSSxDQUFDLENBQUM7YUFDbkU7WUFFRCxPQUFPLElBQUksSUFBSSxDQUFDLE9BQU8sQ0FDbkIsT0FBTyxDQUFDLElBQUksRUFBRSxPQUFPLENBQUMsS0FBSyxFQUFFLG9CQUFvQixDQUFDLElBQUksRUFBRSxPQUFPLENBQUMsUUFBUSxDQUFDLEVBQ3pFLE9BQU8sQ0FBQyxVQUFVLEVBQUUsT0FBTyxDQUFDLGVBQWUsRUFBRSxPQUFPLENBQUMsYUFBYSxFQUFFLE9BQU8sQ0FBQyxJQUFJLENBQUMsQ0FBQztRQUN4RixDQUFDO1FBRUQsMENBQWMsR0FBZCxVQUFlLFNBQXlCLEVBQUUsT0FBWTtZQUNwRCxPQUFPLFNBQVMsQ0FBQyxJQUFJLEtBQUssNkJBQXFCLENBQUMsQ0FBQyxDQUFDLFNBQVMsQ0FBQyxDQUFDLENBQUMsSUFBSSxDQUFDO1FBQ3JFLENBQUM7UUFFRCxxQ0FBUyxHQUFULFVBQVUsSUFBZSxFQUFFLE9BQW1DO1lBQzVELElBQU0sVUFBVSxHQUFHLElBQUksQ0FBQyxLQUFLLENBQUMsS0FBSyxDQUFDLFlBQVksQ0FBQyxDQUFDO1lBQ2xELElBQU0sbUJBQW1CLEdBQUcsT0FBTztnQkFDL0IsQ0FBQyxPQUFPLENBQUMsSUFBSSxZQUFZLElBQUksQ0FBQyxTQUFTLElBQUksT0FBTyxDQUFDLElBQUksWUFBWSxJQUFJLENBQUMsU0FBUyxDQUFDLENBQUM7WUFFdkYsSUFBSSxVQUFVLElBQUksbUJBQW1CLEVBQUU7Z0JBQ3JDLE9BQU8sSUFBSSxJQUFJLENBQUMsSUFBSSxDQUNoQixXQUFXLENBQUMsSUFBSSxDQUFDLEtBQUssQ0FBQyxDQUFDLE9BQU8sQ0FBQyxpQkFBaUIsRUFBRSxHQUFHLENBQUMsRUFBRSxJQUFJLENBQUMsVUFBVSxFQUFFLElBQUksQ0FBQyxJQUFJLENBQUMsQ0FBQzthQUMxRjtZQUVELE9BQU8sSUFBSSxDQUFDO1FBQ2QsQ0FBQztRQUVELHdDQUFZLEdBQVosVUFBYSxPQUFxQixFQUFFLE9BQVk7WUFDOUMsT0FBTyxPQUFPLENBQUM7UUFDakIsQ0FBQztRQUVELDBDQUFjLEdBQWQsVUFBZSxTQUF5QixFQUFFLE9BQVk7WUFDcEQsT0FBTyxTQUFTLENBQUM7UUFDbkIsQ0FBQztRQUVELDhDQUFrQixHQUFsQixVQUFtQixhQUFpQyxFQUFFLE9BQVk7WUFDaEUsT0FBTyxhQUFhLENBQUM7UUFDdkIsQ0FBQztRQUNILHdCQUFDO0lBQUQsQ0FBQyxBQTNDRCxJQTJDQztJQTNDWSw4Q0FBaUI7SUE2QzlCLFNBQWdCLGlCQUFpQixDQUFDLGlCQUFrQztRQUNsRSxPQUFPLElBQUksd0JBQWUsQ0FDdEIsSUFBSSxDQUFDLFFBQVEsQ0FBQyxJQUFJLGlCQUFpQixFQUFFLEVBQUUsaUJBQWlCLENBQUMsU0FBUyxDQUFDLEVBQ25FLGlCQUFpQixDQUFDLE1BQU0sQ0FBQyxDQUFDO0lBQ2hDLENBQUM7SUFKRCw4Q0FJQztJQU9ELFNBQVMsb0JBQW9CLENBQUMsT0FBMEIsRUFBRSxLQUFrQjtRQUMxRSxJQUFNLE1BQU0sR0FBVSxFQUFFLENBQUM7UUFFekIsS0FBSyxDQUFDLE9BQU8sQ0FBQyxVQUFDLEdBQUcsRUFBRSxDQUFDO1lBQ25CLElBQU0sT0FBTyxHQUEwQixFQUFDLElBQUksRUFBRSxLQUFLLENBQUMsQ0FBQyxHQUFHLENBQUMsQ0FBQyxFQUFFLElBQUksRUFBRSxLQUFLLENBQUMsQ0FBQyxHQUFHLENBQUMsQ0FBQyxFQUFDLENBQUM7WUFDaEYsSUFBTSxTQUFTLEdBQUcsR0FBRyxDQUFDLEtBQUssQ0FBQyxPQUFPLEVBQUUsT0FBTyxDQUFDLENBQUM7WUFDOUMsSUFBSSxTQUFTLEVBQUU7Z0JBQ2IsTUFBTSxDQUFDLElBQUksQ0FBQyxTQUFTLENBQUMsQ0FBQzthQUN4QjtRQUNILENBQUMsQ0FBQyxDQUFDO1FBQ0gsT0FBTyxNQUFNLENBQUM7SUFDaEIsQ0FBQyIsInNvdXJjZXNDb250ZW50IjpbIi8qKlxuICogQGxpY2Vuc2VcbiAqIENvcHlyaWdodCBHb29nbGUgTExDIEFsbCBSaWdodHMgUmVzZXJ2ZWQuXG4gKlxuICogVXNlIG9mIHRoaXMgc291cmNlIGNvZGUgaXMgZ292ZXJuZWQgYnkgYW4gTUlULXN0eWxlIGxpY2Vuc2UgdGhhdCBjYW4gYmVcbiAqIGZvdW5kIGluIHRoZSBMSUNFTlNFIGZpbGUgYXQgaHR0cHM6Ly9hbmd1bGFyLmlvL2xpY2Vuc2VcbiAqL1xuXG5pbXBvcnQgKiBhcyBodG1sIGZyb20gJy4vYXN0JztcbmltcG9ydCB7UGFyc2VUcmVlUmVzdWx0fSBmcm9tICcuL3BhcnNlcic7XG5pbXBvcnQge05HU1BfVU5JQ09ERX0gZnJvbSAnLi90YWdzJztcblxuZXhwb3J0IGNvbnN0IFBSRVNFUlZFX1dTX0FUVFJfTkFNRSA9ICduZ1ByZXNlcnZlV2hpdGVzcGFjZXMnO1xuXG5jb25zdCBTS0lQX1dTX1RSSU1fVEFHUyA9IG5ldyBTZXQoWydwcmUnLCAndGVtcGxhdGUnLCAndGV4dGFyZWEnLCAnc2NyaXB0JywgJ3N0eWxlJ10pO1xuXG4vLyBFcXVpdmFsZW50IHRvIFxccyB3aXRoIFxcdTAwYTAgKG5vbi1icmVha2luZyBzcGFjZSkgZXhjbHVkZWQuXG4vLyBCYXNlZCBvbiBodHRwczovL2RldmVsb3Blci5tb3ppbGxhLm9yZy9lbi1VUy9kb2NzL1dlYi9KYXZhU2NyaXB0L1JlZmVyZW5jZS9HbG9iYWxfT2JqZWN0cy9SZWdFeHBcbmNvbnN0IFdTX0NIQVJTID0gJyBcXGZcXG5cXHJcXHRcXHZcXHUxNjgwXFx1MTgwZVxcdTIwMDAtXFx1MjAwYVxcdTIwMjhcXHUyMDI5XFx1MjAyZlxcdTIwNWZcXHUzMDAwXFx1ZmVmZic7XG5jb25zdCBOT19XU19SRUdFWFAgPSBuZXcgUmVnRXhwKGBbXiR7V1NfQ0hBUlN9XWApO1xuY29uc3QgV1NfUkVQTEFDRV9SRUdFWFAgPSBuZXcgUmVnRXhwKGBbJHtXU19DSEFSU31dezIsfWAsICdnJyk7XG5cbmZ1bmN0aW9uIGhhc1ByZXNlcnZlV2hpdGVzcGFjZXNBdHRyKGF0dHJzOiBodG1sLkF0dHJpYnV0ZVtdKTogYm9vbGVhbiB7XG4gIHJldHVybiBhdHRycy5zb21lKChhdHRyOiBodG1sLkF0dHJpYnV0ZSkgPT4gYXR0ci5uYW1lID09PSBQUkVTRVJWRV9XU19BVFRSX05BTUUpO1xufVxuXG4vKipcbiAqIEFuZ3VsYXIgRGFydCBpbnRyb2R1Y2VkICZuZ3NwOyBhcyBhIHBsYWNlaG9sZGVyIGZvciBub24tcmVtb3ZhYmxlIHNwYWNlLCBzZWU6XG4gKiBodHRwczovL2dpdGh1Yi5jb20vZGFydC1sYW5nL2FuZ3VsYXIvYmxvYi8wYmI2MTEzODdkMjlkNjViNWFmN2Y5ZDI1MTVhYjU3MWZkM2ZiZWU0L190ZXN0cy90ZXN0L2NvbXBpbGVyL3ByZXNlcnZlX3doaXRlc3BhY2VfdGVzdC5kYXJ0I0wyNS1MMzJcbiAqIEluIEFuZ3VsYXIgRGFydCAmbmdzcDsgaXMgY29udmVydGVkIHRvIHRoZSAweEU1MDAgUFVBIChQcml2YXRlIFVzZSBBcmVhcykgdW5pY29kZSBjaGFyYWN0ZXJcbiAqIGFuZCBsYXRlciBvbiByZXBsYWNlZCBieSBhIHNwYWNlLiBXZSBhcmUgcmUtaW1wbGVtZW50aW5nIHRoZSBzYW1lIGlkZWEgaGVyZS5cbiAqL1xuZXhwb3J0IGZ1bmN0aW9uIHJlcGxhY2VOZ3NwKHZhbHVlOiBzdHJpbmcpOiBzdHJpbmcge1xuICAvLyBsZXhlciBpcyByZXBsYWNpbmcgdGhlICZuZ3NwOyBwc2V1ZG8tZW50aXR5IHdpdGggTkdTUF9VTklDT0RFXG4gIHJldHVybiB2YWx1ZS5yZXBsYWNlKG5ldyBSZWdFeHAoTkdTUF9VTklDT0RFLCAnZycpLCAnICcpO1xufVxuXG4vKipcbiAqIFRoaXMgdmlzaXRvciBjYW4gd2FsayBIVE1MIHBhcnNlIHRyZWUgYW5kIHJlbW92ZSAvIHRyaW0gdGV4dCBub2RlcyB1c2luZyB0aGUgZm9sbG93aW5nIHJ1bGVzOlxuICogLSBjb25zaWRlciBzcGFjZXMsIHRhYnMgYW5kIG5ldyBsaW5lcyBhcyB3aGl0ZXNwYWNlIGNoYXJhY3RlcnM7XG4gKiAtIGRyb3AgdGV4dCBub2RlcyBjb25zaXN0aW5nIG9mIHdoaXRlc3BhY2UgY2hhcmFjdGVycyBvbmx5O1xuICogLSBmb3IgYWxsIG90aGVyIHRleHQgbm9kZXMgcmVwbGFjZSBjb25zZWN1dGl2ZSB3aGl0ZXNwYWNlIGNoYXJhY3RlcnMgd2l0aCBvbmUgc3BhY2U7XG4gKiAtIGNvbnZlcnQgJm5nc3A7IHBzZXVkby1lbnRpdHkgdG8gYSBzaW5nbGUgc3BhY2U7XG4gKlxuICogUmVtb3ZhbCBhbmQgdHJpbW1pbmcgb2Ygd2hpdGVzcGFjZXMgaGF2ZSBwb3NpdGl2ZSBwZXJmb3JtYW5jZSBpbXBhY3QgKGxlc3MgY29kZSB0byBnZW5lcmF0ZVxuICogd2hpbGUgY29tcGlsaW5nIHRlbXBsYXRlcywgZmFzdGVyIHZpZXcgY3JlYXRpb24pLiBBdCB0aGUgc2FtZSB0aW1lIGl0IGNhbiBiZSBcImRlc3RydWN0aXZlXCJcbiAqIGluIHNvbWUgY2FzZXMgKHdoaXRlc3BhY2VzIGNhbiBpbmZsdWVuY2UgbGF5b3V0KS4gQmVjYXVzZSBvZiB0aGUgcG90ZW50aWFsIG9mIGJyZWFraW5nIGxheW91dFxuICogdGhpcyB2aXNpdG9yIGlzIG5vdCBhY3RpdmF0ZWQgYnkgZGVmYXVsdCBpbiBBbmd1bGFyIDUgYW5kIHBlb3BsZSBuZWVkIHRvIGV4cGxpY2l0bHkgb3B0LWluIGZvclxuICogd2hpdGVzcGFjZSByZW1vdmFsLiBUaGUgZGVmYXVsdCBvcHRpb24gZm9yIHdoaXRlc3BhY2UgcmVtb3ZhbCB3aWxsIGJlIHJldmlzaXRlZCBpbiBBbmd1bGFyIDZcbiAqIGFuZCBtaWdodCBiZSBjaGFuZ2VkIHRvIFwib25cIiBieSBkZWZhdWx0LlxuICovXG5leHBvcnQgY2xhc3MgV2hpdGVzcGFjZVZpc2l0b3IgaW1wbGVtZW50cyBodG1sLlZpc2l0b3Ige1xuICB2aXNpdEVsZW1lbnQoZWxlbWVudDogaHRtbC5FbGVtZW50LCBjb250ZXh0OiBhbnkpOiBhbnkge1xuICAgIGlmIChTS0lQX1dTX1RSSU1fVEFHUy5oYXMoZWxlbWVudC5uYW1lKSB8fCBoYXNQcmVzZXJ2ZVdoaXRlc3BhY2VzQXR0cihlbGVtZW50LmF0dHJzKSkge1xuICAgICAgLy8gZG9uJ3QgZGVzY2VudCBpbnRvIGVsZW1lbnRzIHdoZXJlIHdlIG5lZWQgdG8gcHJlc2VydmUgd2hpdGVzcGFjZXNcbiAgICAgIC8vIGJ1dCBzdGlsbCB2aXNpdCBhbGwgYXR0cmlidXRlcyB0byBlbGltaW5hdGUgb25lIHVzZWQgYXMgYSBtYXJrZXQgdG8gcHJlc2VydmUgV1NcbiAgICAgIHJldHVybiBuZXcgaHRtbC5FbGVtZW50KFxuICAgICAgICAgIGVsZW1lbnQubmFtZSwgaHRtbC52aXNpdEFsbCh0aGlzLCBlbGVtZW50LmF0dHJzKSwgZWxlbWVudC5jaGlsZHJlbiwgZWxlbWVudC5zb3VyY2VTcGFuLFxuICAgICAgICAgIGVsZW1lbnQuc3RhcnRTb3VyY2VTcGFuLCBlbGVtZW50LmVuZFNvdXJjZVNwYW4sIGVsZW1lbnQuaTE4bik7XG4gICAgfVxuXG4gICAgcmV0dXJuIG5ldyBodG1sLkVsZW1lbnQoXG4gICAgICAgIGVsZW1lbnQubmFtZSwgZWxlbWVudC5hdHRycywgdmlzaXRBbGxXaXRoU2libGluZ3ModGhpcywgZWxlbWVudC5jaGlsZHJlbiksXG4gICAgICAgIGVsZW1lbnQuc291cmNlU3BhbiwgZWxlbWVudC5zdGFydFNvdXJjZVNwYW4sIGVsZW1lbnQuZW5kU291cmNlU3BhbiwgZWxlbWVudC5pMThuKTtcbiAgfVxuXG4gIHZpc2l0QXR0cmlidXRlKGF0dHJpYnV0ZTogaHRtbC5BdHRyaWJ1dGUsIGNvbnRleHQ6IGFueSk6IGFueSB7XG4gICAgcmV0dXJuIGF0dHJpYnV0ZS5uYW1lICE9PSBQUkVTRVJWRV9XU19BVFRSX05BTUUgPyBhdHRyaWJ1dGUgOiBudWxsO1xuICB9XG5cbiAgdmlzaXRUZXh0KHRleHQ6IGh0bWwuVGV4dCwgY29udGV4dDogU2libGluZ1Zpc2l0b3JDb250ZXh0fG51bGwpOiBhbnkge1xuICAgIGNvbnN0IGlzTm90QmxhbmsgPSB0ZXh0LnZhbHVlLm1hdGNoKE5PX1dTX1JFR0VYUCk7XG4gICAgY29uc3QgaGFzRXhwYW5zaW9uU2libGluZyA9IGNvbnRleHQgJiZcbiAgICAgICAgKGNvbnRleHQucHJldiBpbnN0YW5jZW9mIGh0bWwuRXhwYW5zaW9uIHx8IGNvbnRleHQubmV4dCBpbnN0YW5jZW9mIGh0bWwuRXhwYW5zaW9uKTtcblxuICAgIGlmIChpc05vdEJsYW5rIHx8IGhhc0V4cGFuc2lvblNpYmxpbmcpIHtcbiAgICAgIHJldHVybiBuZXcgaHRtbC5UZXh0KFxuICAgICAgICAgIHJlcGxhY2VOZ3NwKHRleHQudmFsdWUpLnJlcGxhY2UoV1NfUkVQTEFDRV9SRUdFWFAsICcgJyksIHRleHQuc291cmNlU3BhbiwgdGV4dC5pMThuKTtcbiAgICB9XG5cbiAgICByZXR1cm4gbnVsbDtcbiAgfVxuXG4gIHZpc2l0Q29tbWVudChjb21tZW50OiBodG1sLkNvbW1lbnQsIGNvbnRleHQ6IGFueSk6IGFueSB7XG4gICAgcmV0dXJuIGNvbW1lbnQ7XG4gIH1cblxuICB2aXNpdEV4cGFuc2lvbihleHBhbnNpb246IGh0bWwuRXhwYW5zaW9uLCBjb250ZXh0OiBhbnkpOiBhbnkge1xuICAgIHJldHVybiBleHBhbnNpb247XG4gIH1cblxuICB2aXNpdEV4cGFuc2lvbkNhc2UoZXhwYW5zaW9uQ2FzZTogaHRtbC5FeHBhbnNpb25DYXNlLCBjb250ZXh0OiBhbnkpOiBhbnkge1xuICAgIHJldHVybiBleHBhbnNpb25DYXNlO1xuICB9XG59XG5cbmV4cG9ydCBmdW5jdGlvbiByZW1vdmVXaGl0ZXNwYWNlcyhodG1sQXN0V2l0aEVycm9yczogUGFyc2VUcmVlUmVzdWx0KTogUGFyc2VUcmVlUmVzdWx0IHtcbiAgcmV0dXJuIG5ldyBQYXJzZVRyZWVSZXN1bHQoXG4gICAgICBodG1sLnZpc2l0QWxsKG5ldyBXaGl0ZXNwYWNlVmlzaXRvcigpLCBodG1sQXN0V2l0aEVycm9ycy5yb290Tm9kZXMpLFxuICAgICAgaHRtbEFzdFdpdGhFcnJvcnMuZXJyb3JzKTtcbn1cblxuaW50ZXJmYWNlIFNpYmxpbmdWaXNpdG9yQ29udGV4dCB7XG4gIHByZXY6IGh0bWwuTm9kZXx1bmRlZmluZWQ7XG4gIG5leHQ6IGh0bWwuTm9kZXx1bmRlZmluZWQ7XG59XG5cbmZ1bmN0aW9uIHZpc2l0QWxsV2l0aFNpYmxpbmdzKHZpc2l0b3I6IFdoaXRlc3BhY2VWaXNpdG9yLCBub2RlczogaHRtbC5Ob2RlW10pOiBhbnlbXSB7XG4gIGNvbnN0IHJlc3VsdDogYW55W10gPSBbXTtcblxuICBub2Rlcy5mb3JFYWNoKChhc3QsIGkpID0+IHtcbiAgICBjb25zdCBjb250ZXh0OiBTaWJsaW5nVmlzaXRvckNvbnRleHQgPSB7cHJldjogbm9kZXNbaSAtIDFdLCBuZXh0OiBub2Rlc1tpICsgMV19O1xuICAgIGNvbnN0IGFzdFJlc3VsdCA9IGFzdC52aXNpdCh2aXNpdG9yLCBjb250ZXh0KTtcbiAgICBpZiAoYXN0UmVzdWx0KSB7XG4gICAgICByZXN1bHQucHVzaChhc3RSZXN1bHQpO1xuICAgIH1cbiAgfSk7XG4gIHJldHVybiByZXN1bHQ7XG59XG4iXX0=