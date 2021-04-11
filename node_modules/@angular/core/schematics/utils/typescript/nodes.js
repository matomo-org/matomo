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
        define("@angular/core/schematics/utils/typescript/nodes", ["require", "exports", "typescript"], factory);
    }
})(function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.closestNode = exports.hasModifier = void 0;
    const ts = require("typescript");
    /** Checks whether the given TypeScript node has the specified modifier set. */
    function hasModifier(node, modifierKind) {
        return !!node.modifiers && node.modifiers.some(m => m.kind === modifierKind);
    }
    exports.hasModifier = hasModifier;
    /** Find the closest parent node of a particular kind. */
    function closestNode(node, kind) {
        let current = node;
        while (current && !ts.isSourceFile(current)) {
            if (current.kind === kind) {
                return current;
            }
            current = current.parent;
        }
        return null;
    }
    exports.closestNode = closestNode;
});
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoibm9kZXMuanMiLCJzb3VyY2VSb290IjoiIiwic291cmNlcyI6WyIuLi8uLi8uLi8uLi8uLi8uLi8uLi8uLi9wYWNrYWdlcy9jb3JlL3NjaGVtYXRpY3MvdXRpbHMvdHlwZXNjcmlwdC9ub2Rlcy50cyJdLCJuYW1lcyI6W10sIm1hcHBpbmdzIjoiQUFBQTs7Ozs7O0dBTUc7Ozs7Ozs7Ozs7Ozs7SUFFSCxpQ0FBaUM7SUFFakMsK0VBQStFO0lBQy9FLFNBQWdCLFdBQVcsQ0FBQyxJQUFhLEVBQUUsWUFBMkI7UUFDcEUsT0FBTyxDQUFDLENBQUMsSUFBSSxDQUFDLFNBQVMsSUFBSSxJQUFJLENBQUMsU0FBUyxDQUFDLElBQUksQ0FBQyxDQUFDLENBQUMsRUFBRSxDQUFDLENBQUMsQ0FBQyxJQUFJLEtBQUssWUFBWSxDQUFDLENBQUM7SUFDL0UsQ0FBQztJQUZELGtDQUVDO0lBRUQseURBQXlEO0lBQ3pELFNBQWdCLFdBQVcsQ0FBb0IsSUFBYSxFQUFFLElBQW1CO1FBQy9FLElBQUksT0FBTyxHQUFZLElBQUksQ0FBQztRQUU1QixPQUFPLE9BQU8sSUFBSSxDQUFDLEVBQUUsQ0FBQyxZQUFZLENBQUMsT0FBTyxDQUFDLEVBQUU7WUFDM0MsSUFBSSxPQUFPLENBQUMsSUFBSSxLQUFLLElBQUksRUFBRTtnQkFDekIsT0FBTyxPQUFZLENBQUM7YUFDckI7WUFDRCxPQUFPLEdBQUcsT0FBTyxDQUFDLE1BQU0sQ0FBQztTQUMxQjtRQUVELE9BQU8sSUFBSSxDQUFDO0lBQ2QsQ0FBQztJQVhELGtDQVdDIiwic291cmNlc0NvbnRlbnQiOlsiLyoqXG4gKiBAbGljZW5zZVxuICogQ29weXJpZ2h0IEdvb2dsZSBMTEMgQWxsIFJpZ2h0cyBSZXNlcnZlZC5cbiAqXG4gKiBVc2Ugb2YgdGhpcyBzb3VyY2UgY29kZSBpcyBnb3Zlcm5lZCBieSBhbiBNSVQtc3R5bGUgbGljZW5zZSB0aGF0IGNhbiBiZVxuICogZm91bmQgaW4gdGhlIExJQ0VOU0UgZmlsZSBhdCBodHRwczovL2FuZ3VsYXIuaW8vbGljZW5zZVxuICovXG5cbmltcG9ydCAqIGFzIHRzIGZyb20gJ3R5cGVzY3JpcHQnO1xuXG4vKiogQ2hlY2tzIHdoZXRoZXIgdGhlIGdpdmVuIFR5cGVTY3JpcHQgbm9kZSBoYXMgdGhlIHNwZWNpZmllZCBtb2RpZmllciBzZXQuICovXG5leHBvcnQgZnVuY3Rpb24gaGFzTW9kaWZpZXIobm9kZTogdHMuTm9kZSwgbW9kaWZpZXJLaW5kOiB0cy5TeW50YXhLaW5kKSB7XG4gIHJldHVybiAhIW5vZGUubW9kaWZpZXJzICYmIG5vZGUubW9kaWZpZXJzLnNvbWUobSA9PiBtLmtpbmQgPT09IG1vZGlmaWVyS2luZCk7XG59XG5cbi8qKiBGaW5kIHRoZSBjbG9zZXN0IHBhcmVudCBub2RlIG9mIGEgcGFydGljdWxhciBraW5kLiAqL1xuZXhwb3J0IGZ1bmN0aW9uIGNsb3Nlc3ROb2RlPFQgZXh0ZW5kcyB0cy5Ob2RlPihub2RlOiB0cy5Ob2RlLCBraW5kOiB0cy5TeW50YXhLaW5kKTogVHxudWxsIHtcbiAgbGV0IGN1cnJlbnQ6IHRzLk5vZGUgPSBub2RlO1xuXG4gIHdoaWxlIChjdXJyZW50ICYmICF0cy5pc1NvdXJjZUZpbGUoY3VycmVudCkpIHtcbiAgICBpZiAoY3VycmVudC5raW5kID09PSBraW5kKSB7XG4gICAgICByZXR1cm4gY3VycmVudCBhcyBUO1xuICAgIH1cbiAgICBjdXJyZW50ID0gY3VycmVudC5wYXJlbnQ7XG4gIH1cblxuICByZXR1cm4gbnVsbDtcbn1cbiJdfQ==