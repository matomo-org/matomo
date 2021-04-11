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
        define("@angular/compiler/src/ml_parser/xml_tags", ["require", "exports", "@angular/compiler/src/ml_parser/tags"], factory);
    }
})(function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.getXmlTagDefinition = exports.XmlTagDefinition = void 0;
    var tags_1 = require("@angular/compiler/src/ml_parser/tags");
    var XmlTagDefinition = /** @class */ (function () {
        function XmlTagDefinition() {
            this.closedByParent = false;
            this.isVoid = false;
            this.ignoreFirstLf = false;
            this.canSelfClose = true;
            this.preventNamespaceInheritance = false;
        }
        XmlTagDefinition.prototype.requireExtraParent = function (currentParent) {
            return false;
        };
        XmlTagDefinition.prototype.isClosedByChild = function (name) {
            return false;
        };
        XmlTagDefinition.prototype.getContentType = function () {
            return tags_1.TagContentType.PARSABLE_DATA;
        };
        return XmlTagDefinition;
    }());
    exports.XmlTagDefinition = XmlTagDefinition;
    var _TAG_DEFINITION = new XmlTagDefinition();
    function getXmlTagDefinition(tagName) {
        return _TAG_DEFINITION;
    }
    exports.getXmlTagDefinition = getXmlTagDefinition;
});
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoieG1sX3RhZ3MuanMiLCJzb3VyY2VSb290IjoiIiwic291cmNlcyI6WyIuLi8uLi8uLi8uLi8uLi8uLi8uLi9wYWNrYWdlcy9jb21waWxlci9zcmMvbWxfcGFyc2VyL3htbF90YWdzLnRzIl0sIm5hbWVzIjpbXSwibWFwcGluZ3MiOiJBQUFBOzs7Ozs7R0FNRzs7Ozs7Ozs7Ozs7OztJQUVILDZEQUFxRDtJQUVyRDtRQUFBO1lBQ0UsbUJBQWMsR0FBWSxLQUFLLENBQUM7WUFPaEMsV0FBTSxHQUFZLEtBQUssQ0FBQztZQUN4QixrQkFBYSxHQUFZLEtBQUssQ0FBQztZQUMvQixpQkFBWSxHQUFZLElBQUksQ0FBQztZQUM3QixnQ0FBMkIsR0FBWSxLQUFLLENBQUM7UUFhL0MsQ0FBQztRQVhDLDZDQUFrQixHQUFsQixVQUFtQixhQUFxQjtZQUN0QyxPQUFPLEtBQUssQ0FBQztRQUNmLENBQUM7UUFFRCwwQ0FBZSxHQUFmLFVBQWdCLElBQVk7WUFDMUIsT0FBTyxLQUFLLENBQUM7UUFDZixDQUFDO1FBRUQseUNBQWMsR0FBZDtZQUNFLE9BQU8scUJBQWMsQ0FBQyxhQUFhLENBQUM7UUFDdEMsQ0FBQztRQUNILHVCQUFDO0lBQUQsQ0FBQyxBQXhCRCxJQXdCQztJQXhCWSw0Q0FBZ0I7SUEwQjdCLElBQU0sZUFBZSxHQUFHLElBQUksZ0JBQWdCLEVBQUUsQ0FBQztJQUUvQyxTQUFnQixtQkFBbUIsQ0FBQyxPQUFlO1FBQ2pELE9BQU8sZUFBZSxDQUFDO0lBQ3pCLENBQUM7SUFGRCxrREFFQyIsInNvdXJjZXNDb250ZW50IjpbIi8qKlxuICogQGxpY2Vuc2VcbiAqIENvcHlyaWdodCBHb29nbGUgTExDIEFsbCBSaWdodHMgUmVzZXJ2ZWQuXG4gKlxuICogVXNlIG9mIHRoaXMgc291cmNlIGNvZGUgaXMgZ292ZXJuZWQgYnkgYW4gTUlULXN0eWxlIGxpY2Vuc2UgdGhhdCBjYW4gYmVcbiAqIGZvdW5kIGluIHRoZSBMSUNFTlNFIGZpbGUgYXQgaHR0cHM6Ly9hbmd1bGFyLmlvL2xpY2Vuc2VcbiAqL1xuXG5pbXBvcnQge1RhZ0NvbnRlbnRUeXBlLCBUYWdEZWZpbml0aW9ufSBmcm9tICcuL3RhZ3MnO1xuXG5leHBvcnQgY2xhc3MgWG1sVGFnRGVmaW5pdGlvbiBpbXBsZW1lbnRzIFRhZ0RlZmluaXRpb24ge1xuICBjbG9zZWRCeVBhcmVudDogYm9vbGVhbiA9IGZhbHNlO1xuICAvLyBUT0RPKGlzc3VlLzI0NTcxKTogcmVtb3ZlICchJy5cbiAgcmVxdWlyZWRQYXJlbnRzIToge1trZXk6IHN0cmluZ106IGJvb2xlYW59O1xuICAvLyBUT0RPKGlzc3VlLzI0NTcxKTogcmVtb3ZlICchJy5cbiAgcGFyZW50VG9BZGQhOiBzdHJpbmc7XG4gIC8vIFRPRE8oaXNzdWUvMjQ1NzEpOiByZW1vdmUgJyEnLlxuICBpbXBsaWNpdE5hbWVzcGFjZVByZWZpeCE6IHN0cmluZztcbiAgaXNWb2lkOiBib29sZWFuID0gZmFsc2U7XG4gIGlnbm9yZUZpcnN0TGY6IGJvb2xlYW4gPSBmYWxzZTtcbiAgY2FuU2VsZkNsb3NlOiBib29sZWFuID0gdHJ1ZTtcbiAgcHJldmVudE5hbWVzcGFjZUluaGVyaXRhbmNlOiBib29sZWFuID0gZmFsc2U7XG5cbiAgcmVxdWlyZUV4dHJhUGFyZW50KGN1cnJlbnRQYXJlbnQ6IHN0cmluZyk6IGJvb2xlYW4ge1xuICAgIHJldHVybiBmYWxzZTtcbiAgfVxuXG4gIGlzQ2xvc2VkQnlDaGlsZChuYW1lOiBzdHJpbmcpOiBib29sZWFuIHtcbiAgICByZXR1cm4gZmFsc2U7XG4gIH1cblxuICBnZXRDb250ZW50VHlwZSgpOiBUYWdDb250ZW50VHlwZSB7XG4gICAgcmV0dXJuIFRhZ0NvbnRlbnRUeXBlLlBBUlNBQkxFX0RBVEE7XG4gIH1cbn1cblxuY29uc3QgX1RBR19ERUZJTklUSU9OID0gbmV3IFhtbFRhZ0RlZmluaXRpb24oKTtcblxuZXhwb3J0IGZ1bmN0aW9uIGdldFhtbFRhZ0RlZmluaXRpb24odGFnTmFtZTogc3RyaW5nKTogWG1sVGFnRGVmaW5pdGlvbiB7XG4gIHJldHVybiBfVEFHX0RFRklOSVRJT047XG59XG4iXX0=