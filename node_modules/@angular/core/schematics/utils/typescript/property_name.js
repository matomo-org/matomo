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
        define("@angular/core/schematics/utils/typescript/property_name", ["require", "exports", "typescript"], factory);
    }
})(function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.hasPropertyNameText = exports.getPropertyNameText = void 0;
    const ts = require("typescript");
    /**
     * Gets the text of the given property name. Returns null if the property
     * name couldn't be determined statically.
     */
    function getPropertyNameText(node) {
        if (ts.isIdentifier(node) || ts.isStringLiteralLike(node)) {
            return node.text;
        }
        return null;
    }
    exports.getPropertyNameText = getPropertyNameText;
    /** Checks whether the given property name has a text. */
    function hasPropertyNameText(node) {
        return ts.isStringLiteral(node) || ts.isNumericLiteral(node) || ts.isIdentifier(node);
    }
    exports.hasPropertyNameText = hasPropertyNameText;
});
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoicHJvcGVydHlfbmFtZS5qcyIsInNvdXJjZVJvb3QiOiIiLCJzb3VyY2VzIjpbIi4uLy4uLy4uLy4uLy4uLy4uLy4uLy4uL3BhY2thZ2VzL2NvcmUvc2NoZW1hdGljcy91dGlscy90eXBlc2NyaXB0L3Byb3BlcnR5X25hbWUudHMiXSwibmFtZXMiOltdLCJtYXBwaW5ncyI6IkFBQUE7Ozs7OztHQU1HOzs7Ozs7Ozs7Ozs7O0lBRUgsaUNBQWlDO0lBS2pDOzs7T0FHRztJQUNILFNBQWdCLG1CQUFtQixDQUFDLElBQXFCO1FBQ3ZELElBQUksRUFBRSxDQUFDLFlBQVksQ0FBQyxJQUFJLENBQUMsSUFBSSxFQUFFLENBQUMsbUJBQW1CLENBQUMsSUFBSSxDQUFDLEVBQUU7WUFDekQsT0FBTyxJQUFJLENBQUMsSUFBSSxDQUFDO1NBQ2xCO1FBQ0QsT0FBTyxJQUFJLENBQUM7SUFDZCxDQUFDO0lBTEQsa0RBS0M7SUFFRCx5REFBeUQ7SUFDekQsU0FBZ0IsbUJBQW1CLENBQUMsSUFBcUI7UUFDdkQsT0FBTyxFQUFFLENBQUMsZUFBZSxDQUFDLElBQUksQ0FBQyxJQUFJLEVBQUUsQ0FBQyxnQkFBZ0IsQ0FBQyxJQUFJLENBQUMsSUFBSSxFQUFFLENBQUMsWUFBWSxDQUFDLElBQUksQ0FBQyxDQUFDO0lBQ3hGLENBQUM7SUFGRCxrREFFQyIsInNvdXJjZXNDb250ZW50IjpbIi8qKlxuICogQGxpY2Vuc2VcbiAqIENvcHlyaWdodCBHb29nbGUgTExDIEFsbCBSaWdodHMgUmVzZXJ2ZWQuXG4gKlxuICogVXNlIG9mIHRoaXMgc291cmNlIGNvZGUgaXMgZ292ZXJuZWQgYnkgYW4gTUlULXN0eWxlIGxpY2Vuc2UgdGhhdCBjYW4gYmVcbiAqIGZvdW5kIGluIHRoZSBMSUNFTlNFIGZpbGUgYXQgaHR0cHM6Ly9hbmd1bGFyLmlvL2xpY2Vuc2VcbiAqL1xuXG5pbXBvcnQgKiBhcyB0cyBmcm9tICd0eXBlc2NyaXB0JztcblxuLyoqIFR5cGUgdGhhdCBkZXNjcmliZXMgYSBwcm9wZXJ0eSBuYW1lIHdpdGggYW4gb2J0YWluYWJsZSB0ZXh0LiAqL1xudHlwZSBQcm9wZXJ0eU5hbWVXaXRoVGV4dCA9IEV4Y2x1ZGU8dHMuUHJvcGVydHlOYW1lLCB0cy5Db21wdXRlZFByb3BlcnR5TmFtZT47XG5cbi8qKlxuICogR2V0cyB0aGUgdGV4dCBvZiB0aGUgZ2l2ZW4gcHJvcGVydHkgbmFtZS4gUmV0dXJucyBudWxsIGlmIHRoZSBwcm9wZXJ0eVxuICogbmFtZSBjb3VsZG4ndCBiZSBkZXRlcm1pbmVkIHN0YXRpY2FsbHkuXG4gKi9cbmV4cG9ydCBmdW5jdGlvbiBnZXRQcm9wZXJ0eU5hbWVUZXh0KG5vZGU6IHRzLlByb3BlcnR5TmFtZSk6IHN0cmluZ3xudWxsIHtcbiAgaWYgKHRzLmlzSWRlbnRpZmllcihub2RlKSB8fCB0cy5pc1N0cmluZ0xpdGVyYWxMaWtlKG5vZGUpKSB7XG4gICAgcmV0dXJuIG5vZGUudGV4dDtcbiAgfVxuICByZXR1cm4gbnVsbDtcbn1cblxuLyoqIENoZWNrcyB3aGV0aGVyIHRoZSBnaXZlbiBwcm9wZXJ0eSBuYW1lIGhhcyBhIHRleHQuICovXG5leHBvcnQgZnVuY3Rpb24gaGFzUHJvcGVydHlOYW1lVGV4dChub2RlOiB0cy5Qcm9wZXJ0eU5hbWUpOiBub2RlIGlzIFByb3BlcnR5TmFtZVdpdGhUZXh0IHtcbiAgcmV0dXJuIHRzLmlzU3RyaW5nTGl0ZXJhbChub2RlKSB8fCB0cy5pc051bWVyaWNMaXRlcmFsKG5vZGUpIHx8IHRzLmlzSWRlbnRpZmllcihub2RlKTtcbn1cbiJdfQ==