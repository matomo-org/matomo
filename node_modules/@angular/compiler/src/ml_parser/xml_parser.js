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
        define("@angular/compiler/src/ml_parser/xml_parser", ["require", "exports", "tslib", "@angular/compiler/src/ml_parser/parser", "@angular/compiler/src/ml_parser/xml_tags", "@angular/compiler/src/ml_parser/parser"], factory);
    }
})(function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.XmlParser = exports.TreeError = exports.ParseTreeResult = void 0;
    var tslib_1 = require("tslib");
    var parser_1 = require("@angular/compiler/src/ml_parser/parser");
    var xml_tags_1 = require("@angular/compiler/src/ml_parser/xml_tags");
    var parser_2 = require("@angular/compiler/src/ml_parser/parser");
    Object.defineProperty(exports, "ParseTreeResult", { enumerable: true, get: function () { return parser_2.ParseTreeResult; } });
    Object.defineProperty(exports, "TreeError", { enumerable: true, get: function () { return parser_2.TreeError; } });
    var XmlParser = /** @class */ (function (_super) {
        tslib_1.__extends(XmlParser, _super);
        function XmlParser() {
            return _super.call(this, xml_tags_1.getXmlTagDefinition) || this;
        }
        XmlParser.prototype.parse = function (source, url, options) {
            return _super.prototype.parse.call(this, source, url, options);
        };
        return XmlParser;
    }(parser_1.Parser));
    exports.XmlParser = XmlParser;
});
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoieG1sX3BhcnNlci5qcyIsInNvdXJjZVJvb3QiOiIiLCJzb3VyY2VzIjpbIi4uLy4uLy4uLy4uLy4uLy4uLy4uL3BhY2thZ2VzL2NvbXBpbGVyL3NyYy9tbF9wYXJzZXIveG1sX3BhcnNlci50cyJdLCJuYW1lcyI6W10sIm1hcHBpbmdzIjoiQUFBQTs7Ozs7O0dBTUc7Ozs7Ozs7Ozs7Ozs7O0lBR0gsaUVBQWlEO0lBQ2pELHFFQUErQztJQUUvQyxpRUFBb0Q7SUFBNUMseUdBQUEsZUFBZSxPQUFBO0lBQUUsbUdBQUEsU0FBUyxPQUFBO0lBRWxDO1FBQStCLHFDQUFNO1FBQ25DO21CQUNFLGtCQUFNLDhCQUFtQixDQUFDO1FBQzVCLENBQUM7UUFFRCx5QkFBSyxHQUFMLFVBQU0sTUFBYyxFQUFFLEdBQVcsRUFBRSxPQUF5QjtZQUMxRCxPQUFPLGlCQUFNLEtBQUssWUFBQyxNQUFNLEVBQUUsR0FBRyxFQUFFLE9BQU8sQ0FBQyxDQUFDO1FBQzNDLENBQUM7UUFDSCxnQkFBQztJQUFELENBQUMsQUFSRCxDQUErQixlQUFNLEdBUXBDO0lBUlksOEJBQVMiLCJzb3VyY2VzQ29udGVudCI6WyIvKipcbiAqIEBsaWNlbnNlXG4gKiBDb3B5cmlnaHQgR29vZ2xlIExMQyBBbGwgUmlnaHRzIFJlc2VydmVkLlxuICpcbiAqIFVzZSBvZiB0aGlzIHNvdXJjZSBjb2RlIGlzIGdvdmVybmVkIGJ5IGFuIE1JVC1zdHlsZSBsaWNlbnNlIHRoYXQgY2FuIGJlXG4gKiBmb3VuZCBpbiB0aGUgTElDRU5TRSBmaWxlIGF0IGh0dHBzOi8vYW5ndWxhci5pby9saWNlbnNlXG4gKi9cblxuaW1wb3J0IHtUb2tlbml6ZU9wdGlvbnN9IGZyb20gJy4vbGV4ZXInO1xuaW1wb3J0IHtQYXJzZXIsIFBhcnNlVHJlZVJlc3VsdH0gZnJvbSAnLi9wYXJzZXInO1xuaW1wb3J0IHtnZXRYbWxUYWdEZWZpbml0aW9ufSBmcm9tICcuL3htbF90YWdzJztcblxuZXhwb3J0IHtQYXJzZVRyZWVSZXN1bHQsIFRyZWVFcnJvcn0gZnJvbSAnLi9wYXJzZXInO1xuXG5leHBvcnQgY2xhc3MgWG1sUGFyc2VyIGV4dGVuZHMgUGFyc2VyIHtcbiAgY29uc3RydWN0b3IoKSB7XG4gICAgc3VwZXIoZ2V0WG1sVGFnRGVmaW5pdGlvbik7XG4gIH1cblxuICBwYXJzZShzb3VyY2U6IHN0cmluZywgdXJsOiBzdHJpbmcsIG9wdGlvbnM/OiBUb2tlbml6ZU9wdGlvbnMpOiBQYXJzZVRyZWVSZXN1bHQge1xuICAgIHJldHVybiBzdXBlci5wYXJzZShzb3VyY2UsIHVybCwgb3B0aW9ucyk7XG4gIH1cbn1cbiJdfQ==