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
        define("@angular/core/schematics/migrations/abstract-control-parent/util", ["require", "exports", "path", "typescript"], factory);
    }
})(function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.findParentAccesses = void 0;
    const path_1 = require("path");
    const ts = require("typescript");
    /** Names of symbols from `@angular/forms` whose `parent` accesses have to be migrated. */
    const abstractControlSymbols = new Set([
        'AbstractControl',
        'FormArray',
        'FormControl',
        'FormGroup',
    ]);
    /**
     * Finds the `PropertyAccessExpression`-s that are accessing the `parent` property in
     * such a way that may result in a compilation error after the v11 type changes.
     */
    function findParentAccesses(typeChecker, sourceFile) {
        const results = [];
        sourceFile.forEachChild(function walk(node) {
            if (ts.isPropertyAccessExpression(node) && node.name.text === 'parent' && !isNullCheck(node) &&
                !isSafeAccess(node) && results.indexOf(node) === -1 &&
                isAbstractControlReference(typeChecker, node) && isNullableType(typeChecker, node)) {
                results.unshift(node);
            }
            node.forEachChild(walk);
        });
        return results;
    }
    exports.findParentAccesses = findParentAccesses;
    /** Checks whether a node's type is nullable (`null`, `undefined` or `void`). */
    function isNullableType(typeChecker, node) {
        // Skip expressions in the form of `foo.bar!.baz` since the `TypeChecker` seems
        // to identify them as null, even though the user indicated that it won't be.
        if (node.parent && ts.isNonNullExpression(node.parent)) {
            return false;
        }
        const type = typeChecker.getTypeAtLocation(node);
        const typeNode = typeChecker.typeToTypeNode(type, undefined, ts.NodeBuilderFlags.None);
        let hasSeenNullableType = false;
        // Trace the type of the node back to a type node, walk
        // through all of its sub-nodes and look for nullable tyes.
        if (typeNode) {
            (function walk(current) {
                if (current.kind === ts.SyntaxKind.NullKeyword ||
                    current.kind === ts.SyntaxKind.UndefinedKeyword ||
                    current.kind === ts.SyntaxKind.VoidKeyword) {
                    hasSeenNullableType = true;
                    // Note that we don't descend into type literals, because it may cause
                    // us to mis-identify the root type as nullable, because it has a nullable
                    // property (e.g. `{ foo: string | null }`).
                }
                else if (!hasSeenNullableType && !ts.isTypeLiteralNode(current)) {
                    current.forEachChild(walk);
                }
            })(typeNode);
        }
        return hasSeenNullableType;
    }
    /**
     * Checks whether a particular node is part of a null check. E.g. given:
     * `control.parent ? control.parent.value : null` the null check would be `control.parent`.
     */
    function isNullCheck(node) {
        if (!node.parent) {
            return false;
        }
        // `control.parent && control.parent.value` where `node` is `control.parent`.
        if (ts.isBinaryExpression(node.parent) && node.parent.left === node) {
            return true;
        }
        // `control.parent && control.parent.parent && control.parent.parent.value`
        // where `node` is `control.parent`.
        if (node.parent.parent && ts.isBinaryExpression(node.parent.parent) &&
            node.parent.parent.left === node.parent) {
            return true;
        }
        // `if (control.parent) {...}` where `node` is `control.parent`.
        if (ts.isIfStatement(node.parent) && node.parent.expression === node) {
            return true;
        }
        // `control.parent ? control.parent.value : null` where `node` is `control.parent`.
        if (ts.isConditionalExpression(node.parent) && node.parent.condition === node) {
            return true;
        }
        return false;
    }
    /** Checks whether a property access is safe (e.g. `foo.parent?.value`). */
    function isSafeAccess(node) {
        return node.parent != null && ts.isPropertyAccessExpression(node.parent) &&
            node.parent.expression === node && node.parent.questionDotToken != null;
    }
    /** Checks whether a property access is on an `AbstractControl` coming from `@angular/forms`. */
    function isAbstractControlReference(typeChecker, node) {
        var _a;
        let current = node;
        const formsPattern = /node_modules\/?.*\/@angular\/forms/;
        // Walks up the property access chain and tries to find a symbol tied to a `SourceFile`.
        // If such a node is found, we check whether the type is one of the `AbstractControl` symbols
        // and whether it comes from the `@angular/forms` directory in the `node_modules`.
        while (ts.isPropertyAccessExpression(current)) {
            const type = typeChecker.getTypeAtLocation(current.expression);
            const symbol = type.getSymbol();
            if (symbol && type) {
                const sourceFile = (_a = symbol.valueDeclaration) === null || _a === void 0 ? void 0 : _a.getSourceFile();
                return sourceFile != null &&
                    formsPattern.test(path_1.normalize(sourceFile.fileName).replace(/\\/g, '/')) &&
                    hasAbstractControlType(typeChecker, type);
            }
            current = current.expression;
        }
        return false;
    }
    /**
     * Walks through the sub-types of a type, looking for a type that
     * has the same name as one of the `AbstractControl` types.
     */
    function hasAbstractControlType(typeChecker, type) {
        const typeNode = typeChecker.typeToTypeNode(type, undefined, ts.NodeBuilderFlags.None);
        let hasMatch = false;
        if (typeNode) {
            (function walk(current) {
                if (ts.isIdentifier(current) && abstractControlSymbols.has(current.text)) {
                    hasMatch = true;
                    // Note that we don't descend into type literals, because it may cause
                    // us to mis-identify the root type as nullable, because it has a nullable
                    // property (e.g. `{ foo: FormControl }`).
                }
                else if (!hasMatch && !ts.isTypeLiteralNode(current)) {
                    current.forEachChild(walk);
                }
            })(typeNode);
        }
        return hasMatch;
    }
});
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoidXRpbC5qcyIsInNvdXJjZVJvb3QiOiIiLCJzb3VyY2VzIjpbIi4uLy4uLy4uLy4uLy4uLy4uLy4uLy4uL3BhY2thZ2VzL2NvcmUvc2NoZW1hdGljcy9taWdyYXRpb25zL2Fic3RyYWN0LWNvbnRyb2wtcGFyZW50L3V0aWwudHMiXSwibmFtZXMiOltdLCJtYXBwaW5ncyI6IkFBQUE7Ozs7OztHQU1HOzs7Ozs7Ozs7Ozs7O0lBRUgsK0JBQStCO0lBQy9CLGlDQUFpQztJQUVqQywwRkFBMEY7SUFDMUYsTUFBTSxzQkFBc0IsR0FBRyxJQUFJLEdBQUcsQ0FBUztRQUM3QyxpQkFBaUI7UUFDakIsV0FBVztRQUNYLGFBQWE7UUFDYixXQUFXO0tBQ1osQ0FBQyxDQUFDO0lBRUg7OztPQUdHO0lBQ0gsU0FBZ0Isa0JBQWtCLENBQzlCLFdBQTJCLEVBQUUsVUFBeUI7UUFDeEQsTUFBTSxPQUFPLEdBQWtDLEVBQUUsQ0FBQztRQUVsRCxVQUFVLENBQUMsWUFBWSxDQUFDLFNBQVMsSUFBSSxDQUFDLElBQWE7WUFDakQsSUFBSSxFQUFFLENBQUMsMEJBQTBCLENBQUMsSUFBSSxDQUFDLElBQUksSUFBSSxDQUFDLElBQUksQ0FBQyxJQUFJLEtBQUssUUFBUSxJQUFJLENBQUMsV0FBVyxDQUFDLElBQUksQ0FBQztnQkFDeEYsQ0FBQyxZQUFZLENBQUMsSUFBSSxDQUFDLElBQUksT0FBTyxDQUFDLE9BQU8sQ0FBQyxJQUFJLENBQUMsS0FBSyxDQUFDLENBQUM7Z0JBQ25ELDBCQUEwQixDQUFDLFdBQVcsRUFBRSxJQUFJLENBQUMsSUFBSSxjQUFjLENBQUMsV0FBVyxFQUFFLElBQUksQ0FBQyxFQUFFO2dCQUN0RixPQUFPLENBQUMsT0FBTyxDQUFDLElBQUksQ0FBQyxDQUFDO2FBQ3ZCO1lBRUQsSUFBSSxDQUFDLFlBQVksQ0FBQyxJQUFJLENBQUMsQ0FBQztRQUMxQixDQUFDLENBQUMsQ0FBQztRQUVILE9BQU8sT0FBTyxDQUFDO0lBQ2pCLENBQUM7SUFmRCxnREFlQztJQUVELGdG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WUFDWixDQUFDLFNBQVMsSUFBSSxDQUFDLE9BQWdCO2dCQUM3QixJQUFJLEVBQUUsQ0FBQyxZQUFZLENBQUMsT0FBTyxDQUFDLElBQUksc0JBQXNCLENBQUMsR0FBRyxDQUFDLE9BQU8sQ0FBQyxJQUFJLENBQUMsRUFBRTtvQkFDeEUsUUFBUSxHQUFHLElBQUksQ0FBQztvQkFDaEIsc0VBQXNFO29CQUN0RSwwRUFBMEU7b0JBQzFFLDBDQUEwQztpQkFDM0M7cUJBQU0sSUFBSSxDQUFDLFFBQVEsSUFBSSxDQUFDLEVBQUUsQ0FBQyxpQkFBaUIsQ0FBQyxPQUFPLENBQUMsRUFBRTtvQkFDdEQsT0FBTyxDQUFDLFlBQVksQ0FBQyxJQUFJLENBQUMsQ0FBQztpQkFDNUI7WUFDSCxDQUFDLENBQUMsQ0FBQyxRQUFRLENBQUMsQ0FBQztTQUNkO1FBQ0QsT0FBTyxRQUFRLENBQUM7SUFDbEIsQ0FBQyIsInNvdXJjZXNDb250ZW50IjpbIi8qKlxuICogQGxpY2Vuc2VcbiAqIENvcHlyaWdodCBHb29nbGUgTExDIEFsbCBSaWdodHMgUmVzZXJ2ZWQuXG4gKlxuICogVXNlIG9mIHRoaXMgc291cmNlIGNvZGUgaXMgZ292ZXJuZWQgYnkgYW4gTUlULXN0eWxlIGxpY2Vuc2UgdGhhdCBjYW4gYmVcbiAqIGZvdW5kIGluIHRoZSBMSUNFTlNFIGZpbGUgYXQgaHR0cHM6Ly9hbmd1bGFyLmlvL2xpY2Vuc2VcbiAqL1xuXG5pbXBvcnQge25vcm1hbGl6ZX0gZnJvbSAncGF0aCc7XG5pbXBvcnQgKiBhcyB0cyBmcm9tICd0eXBlc2NyaXB0JztcblxuLyoqIE5hbWVzIG9mIHN5bWJvbHMgZnJvbSBgQGFuZ3VsYXIvZm9ybXNgIHdob3NlIGBwYXJlbnRgIGFjY2Vzc2VzIGhhdmUgdG8gYmUgbWlncmF0ZWQuICovXG5jb25zdCBhYnN0cmFjdENvbnRyb2xTeW1ib2xzID0gbmV3IFNldDxzdHJpbmc+KFtcbiAgJ0Fic3RyYWN0Q29udHJvbCcsXG4gICdGb3JtQXJyYXknLFxuICAnRm9ybUNvbnRyb2wnLFxuICAnRm9ybUdyb3VwJyxcbl0pO1xuXG4vKipcbiAqIEZpbmRzIHRoZSBgUHJvcGVydHlBY2Nlc3NFeHByZXNzaW9uYC1zIHRoYXQgYXJlIGFjY2Vzc2luZyB0aGUgYHBhcmVudGAgcHJvcGVydHkgaW5cbiAqIHN1Y2ggYSB3YXkgdGhhdCBtYXkgcmVzdWx0IGluIGEgY29tcGlsYXRpb24gZXJyb3IgYWZ0ZXIgdGhlIHYxMSB0eXBlIGNoYW5nZXMuXG4gKi9cbmV4cG9ydCBmdW5jdGlvbiBmaW5kUGFyZW50QWNjZXNzZXMoXG4gICAgdHlwZUNoZWNrZXI6IHRzLlR5cGVDaGVja2VyLCBzb3VyY2VGaWxlOiB0cy5Tb3VyY2VGaWxlKTogdHMuUHJvcGVydHlBY2Nlc3NFeHByZXNzaW9uW10ge1xuICBjb25zdCByZXN1bHRzOiB0cy5Qcm9wZXJ0eUFjY2Vzc0V4cHJlc3Npb25bXSA9IFtdO1xuXG4gIHNvdXJjZUZpbGUuZm9yRWFjaENoaWxkKGZ1bmN0aW9uIHdhbGsobm9kZTogdHMuTm9kZSkge1xuICAgIGlmICh0cy5pc1Byb3BlcnR5QWNjZXNzRXhwcmVzc2lvbihub2RlKSAmJiBub2RlLm5hbWUudGV4dCA9PT0gJ3BhcmVudCcgJiYgIWlzTnVsbENoZWNrKG5vZGUpICYmXG4gICAgICAgICFpc1NhZmVBY2Nlc3Mobm9kZSkgJiYgcmVzdWx0cy5pbmRleE9mKG5vZGUpID09PSAtMSAmJlxuICAgICAgICBpc0Fic3RyYWN0Q29udHJvbFJlZmVyZW5jZSh0eXBlQ2hlY2tlciwgbm9kZSkgJiYgaXNOdWxsYWJsZVR5cGUodHlwZUNoZWNrZXIsIG5vZGUpKSB7XG4gICAgICByZXN1bHRzLnVuc2hpZnQobm9kZSk7XG4gICAgfVxuXG4gICAgbm9kZS5mb3JFYWNoQ2hpbGQod2Fsayk7XG4gIH0pO1xuXG4gIHJldHVybiByZXN1bHRzO1xufVxuXG4vKiogQ2hlY2tzIHdoZXRoZXIgYSBub2RlJ3MgdHlwZSBpcyBudWxsYWJsZSAoYG51bGxgLCBgdW5kZWZpbmVkYCBvciBgdm9pZGApLiAqL1xuZnVuY3Rpb24gaXNOdWxsYWJsZVR5cGUodHlwZUNoZWNrZXI6IHRzLlR5cGVDaGVja2VyLCBub2RlOiB0cy5Ob2RlKSB7XG4gIC8vIFNraXAgZXhwcmVzc2lvbnMgaW4gdGhlIGZvcm0gb2YgYGZvby5iYXIhLmJhemAgc2luY2UgdGhlIGBUeXBlQ2hlY2tlcmAgc2VlbXNcbiAgLy8gdG8gaWRlbnRpZnkgdGhlbSBhcyBudWxsLCBldmVuIHRob3VnaCB0aGUgdXNlciBpbmRpY2F0ZWQgdGhhdCBpdCB3b24ndCBiZS5cbiAgaWYgKG5vZGUucGFyZW50ICYmIHRzLmlzTm9uTnVsbEV4cHJlc3Npb24obm9kZS5wYXJlbnQpKSB7XG4gICAgcmV0dXJuIGZhbHNlO1xuICB9XG5cbiAgY29uc3QgdHlwZSA9IHR5cGVDaGVja2VyLmdldFR5cGVBdExvY2F0aW9uKG5vZGUpO1xuICBjb25zdCB0eXBlTm9kZSA9IHR5cGVDaGVja2VyLnR5cGVUb1R5cGVOb2RlKHR5cGUsIHVuZGVmaW5lZCwgdHMuTm9kZUJ1aWxkZXJGbGFncy5Ob25lKTtcbiAgbGV0IGhhc1NlZW5OdWxsYWJsZVR5cGUgPSBmYWxzZTtcblxuICAvLyBUcmFjZSB0aGUgdHlwZSBvZiB0aGUgbm9kZSBiYWNrIHRvIGEgdHlwZSBub2RlLCB3YWxrXG4gIC8vIHRocm91Z2ggYWxsIG9mIGl0cyBzdWItbm9kZXMgYW5kIGxvb2sgZm9yIG51bGxhYmxlIHR5ZXMuXG4gIGlmICh0eXBlTm9kZSkge1xuICAgIChmdW5jdGlvbiB3YWxrKGN1cnJlbnQ6IHRzLk5vZGUpIHtcbiAgICAgIGlmIChjdXJyZW50LmtpbmQgPT09IHRzLlN5bnRheEtpbmQuTnVsbEtleXdvcmQgfHxcbiAgICAgICAgICBjdXJyZW50LmtpbmQgPT09IHRzLlN5bnRheEtpbmQuVW5kZWZpbmVkS2V5d29yZCB8fFxuICAgICAgICAgIGN1cnJlbnQua2luZCA9PT0gdHMuU3ludGF4S2luZC5Wb2lkS2V5d29yZCkge1xuICAgICAgICBoYXNTZWVuTnVsbGFibGVUeXBlID0gdHJ1ZTtcbiAgICAgICAgLy8gTm90ZSB0aGF0IHdlIGRvbid0IGRlc2NlbmQgaW50byB0eXBlIGxpdGVyYWxzLCBiZWNhdXNlIGl0IG1heSBjYXVzZVxuICAgICAgICAvLyB1cyB0byBtaXMtaWRlbnRpZnkgdGhlIHJvb3QgdHlwZSBhcyBudWxsYWJsZSwgYmVjYXVzZSBpdCBoYXMgYSBudWxsYWJsZVxuICAgICAgICAvLyBwcm9wZXJ0eSAoZS5nLiBgeyBmb286IHN0cmluZyB8IG51bGwgfWApLlxuICAgICAgfSBlbHNlIGlmICghaGFzU2Vlbk51bGxhYmxlVHlwZSAmJiAhdHMuaXNUeXBlTGl0ZXJhbE5vZGUoY3VycmVudCkpIHtcbiAgICAgICAgY3VycmVudC5mb3JFYWNoQ2hpbGQod2Fsayk7XG4gICAgICB9XG4gICAgfSkodHlwZU5vZGUpO1xuICB9XG5cbiAgcmV0dXJuIGhhc1NlZW5OdWxsYWJsZVR5cGU7XG59XG5cbi8qKlxuICogQ2hlY2tzIHdoZXRoZXIgYSBwYXJ0aWN1bGFyIG5vZGUgaXMgcGFydCBvZiBhIG51bGwgY2hlY2suIEUuZy4gZ2l2ZW46XG4gKiBgY29udHJvbC5wYXJlbnQgPyBjb250cm9sLnBhcmVudC52YWx1ZSA6IG51bGxgIHRoZSBudWxsIGNoZWNrIHdvdWxkIGJlIGBjb250cm9sLnBhcmVudGAuXG4gKi9cbmZ1bmN0aW9uIGlzTnVsbENoZWNrKG5vZGU6IHRzLlByb3BlcnR5QWNjZXNzRXhwcmVzc2lvbik6IGJvb2xlYW4ge1xuICBpZiAoIW5vZGUucGFyZW50KSB7XG4gICAgcmV0dXJuIGZhbHNlO1xuICB9XG5cbiAgLy8gYGNvbnRyb2wucGFyZW50ICYmIGNvbnRyb2wucGFyZW50LnZhbHVlYCB3aGVyZSBgbm9kZWAgaXMgYGNvbnRyb2wucGFyZW50YC5cbiAgaWYgKHRzLmlzQmluYXJ5RXhwcmVzc2lvbihub2RlLnBhcmVudCkgJiYgbm9kZS5wYXJlbnQubGVmdCA9PT0gbm9kZSkge1xuICAgIHJldHVybiB0cnVlO1xuICB9XG5cbiAgLy8gYGNvbnRyb2wucGFyZW50ICYmIGNvbnRyb2wucGFyZW50LnBhcmVudCAmJiBjb250cm9sLnBhcmVudC5wYXJlbnQudmFsdWVgXG4gIC8vIHdoZXJlIGBub2RlYCBpcyBgY29udHJvbC5wYXJlbnRgLlxuICBpZiAobm9kZS5wYXJlbnQucGFyZW50ICYmIHRzLmlzQmluYXJ5RXhwcmVzc2lvbihub2RlLnBhcmVudC5wYXJlbnQpICYmXG4gICAgICBub2RlLnBhcmVudC5wYXJlbnQubGVmdCA9PT0gbm9kZS5wYXJlbnQpIHtcbiAgICByZXR1cm4gdHJ1ZTtcbiAgfVxuXG4gIC8vIGBpZiAoY29udHJvbC5wYXJlbnQpIHsuLi59YCB3aGVyZSBgbm9kZWAgaXMgYGNvbnRyb2wucGFyZW50YC5cbiAgaWYgKHRzLmlzSWZTdGF0ZW1lbnQobm9kZS5wYXJlbnQpICYmIG5vZGUucGFyZW50LmV4cHJlc3Npb24gPT09IG5vZGUpIHtcbiAgICByZXR1cm4gdHJ1ZTtcbiAgfVxuXG4gIC8vIGBjb250cm9sLnBhcmVudCA/IGNvbnRyb2wucGFyZW50LnZhbHVlIDogbnVsbGAgd2hlcmUgYG5vZGVgIGlzIGBjb250cm9sLnBhcmVudGAuXG4gIGlmICh0cy5pc0NvbmRpdGlvbmFsRXhwcmVzc2lvbihub2RlLnBhcmVudCkgJiYgbm9kZS5wYXJlbnQuY29uZGl0aW9uID09PSBub2RlKSB7XG4gICAgcmV0dXJuIHRydWU7XG4gIH1cblxuICByZXR1cm4gZmFsc2U7XG59XG5cbi8qKiBDaGVja3Mgd2hldGhlciBhIHByb3BlcnR5IGFjY2VzcyBpcyBzYWZlIChlLmcuIGBmb28ucGFyZW50Py52YWx1ZWApLiAqL1xuZnVuY3Rpb24gaXNTYWZlQWNjZXNzKG5vZGU6IHRzLlByb3BlcnR5QWNjZXNzRXhwcmVzc2lvbik6IGJvb2xlYW4ge1xuICByZXR1cm4gbm9kZS5wYXJlbnQgIT0gbnVsbCAmJiB0cy5pc1Byb3BlcnR5QWNjZXNzRXhwcmVzc2lvbihub2RlLnBhcmVudCkgJiZcbiAgICAgIG5vZGUucGFyZW50LmV4cHJlc3Npb24gPT09IG5vZGUgJiYgbm9kZS5wYXJlbnQucXVlc3Rpb25Eb3RUb2tlbiAhPSBudWxsO1xufVxuXG4vKiogQ2hlY2tzIHdoZXRoZXIgYSBwcm9wZXJ0eSBhY2Nlc3MgaXMgb24gYW4gYEFic3RyYWN0Q29udHJvbGAgY29taW5nIGZyb20gYEBhbmd1bGFyL2Zvcm1zYC4gKi9cbmZ1bmN0aW9uIGlzQWJzdHJhY3RDb250cm9sUmVmZXJlbmNlKFxuICAgIHR5cGVDaGVja2VyOiB0cy5UeXBlQ2hlY2tlciwgbm9kZTogdHMuUHJvcGVydHlBY2Nlc3NFeHByZXNzaW9uKTogYm9vbGVhbiB7XG4gIGxldCBjdXJyZW50OiB0cy5FeHByZXNzaW9uID0gbm9kZTtcbiAgY29uc3QgZm9ybXNQYXR0ZXJuID0gL25vZGVfbW9kdWxlc1xcLz8uKlxcL0Bhbmd1bGFyXFwvZm9ybXMvO1xuICAvLyBXYWxrcyB1cCB0aGUgcHJvcGVydHkgYWNjZXNzIGNoYWluIGFuZCB0cmllcyB0byBmaW5kIGEgc3ltYm9sIHRpZWQgdG8gYSBgU291cmNlRmlsZWAuXG4gIC8vIElmIHN1Y2ggYSBub2RlIGlzIGZvdW5kLCB3ZSBjaGVjayB3aGV0aGVyIHRoZSB0eXBlIGlzIG9uZSBvZiB0aGUgYEFic3RyYWN0Q29udHJvbGAgc3ltYm9sc1xuICAvLyBhbmQgd2hldGhlciBpdCBjb21lcyBmcm9tIHRoZSBgQGFuZ3VsYXIvZm9ybXNgIGRpcmVjdG9yeSBpbiB0aGUgYG5vZGVfbW9kdWxlc2AuXG4gIHdoaWxlICh0cy5pc1Byb3BlcnR5QWNjZXNzRXhwcmVzc2lvbihjdXJyZW50KSkge1xuICAgIGNvbnN0IHR5cGUgPSB0eXBlQ2hlY2tlci5nZXRUeXBlQXRMb2NhdGlvbihjdXJyZW50LmV4cHJlc3Npb24pO1xuICAgIGNvbnN0IHN5bWJvbCA9IHR5cGUuZ2V0U3ltYm9sKCk7XG4gICAgaWYgKHN5bWJvbCAmJiB0eXBlKSB7XG4gICAgICBjb25zdCBzb3VyY2VGaWxlID0gc3ltYm9sLnZhbHVlRGVjbGFyYXRpb24/LmdldFNvdXJjZUZpbGUoKTtcbiAgICAgIHJldHVybiBzb3VyY2VGaWxlICE9IG51bGwgJiZcbiAgICAgICAgICBmb3Jtc1BhdHRlcm4udGVzdChub3JtYWxpemUoc291cmNlRmlsZS5maWxlTmFtZSkucmVwbGFjZSgvXFxcXC9nLCAnLycpKSAmJlxuICAgICAgICAgIGhhc0Fic3RyYWN0Q29udHJvbFR5cGUodHlwZUNoZWNrZXIsIHR5cGUpO1xuICAgIH1cbiAgICBjdXJyZW50ID0gY3VycmVudC5leHByZXNzaW9uO1xuICB9XG4gIHJldHVybiBmYWxzZTtcbn1cblxuLyoqXG4gKiBXYWxrcyB0aHJvdWdoIHRoZSBzdWItdHlwZXMgb2YgYSB0eXBlLCBsb29raW5nIGZvciBhIHR5cGUgdGhhdFxuICogaGFzIHRoZSBzYW1lIG5hbWUgYXMgb25lIG9mIHRoZSBgQWJzdHJhY3RDb250cm9sYCB0eXBlcy5cbiAqL1xuZnVuY3Rpb24gaGFzQWJzdHJhY3RDb250cm9sVHlwZSh0eXBlQ2hlY2tlcjogdHMuVHlwZUNoZWNrZXIsIHR5cGU6IHRzLlR5cGUpOiBib29sZWFuIHtcbiAgY29uc3QgdHlwZU5vZGUgPSB0eXBlQ2hlY2tlci50eXBlVG9UeXBlTm9kZSh0eXBlLCB1bmRlZmluZWQsIHRzLk5vZGVCdWlsZGVyRmxhZ3MuTm9uZSk7XG4gIGxldCBoYXNNYXRjaCA9IGZhbHNlO1xuICBpZiAodHlwZU5vZGUpIHtcbiAgICAoZnVuY3Rpb24gd2FsayhjdXJyZW50OiB0cy5Ob2RlKSB7XG4gICAgICBpZiAodHMuaXNJZGVudGlmaWVyKGN1cnJlbnQpICYmIGFic3RyYWN0Q29udHJvbFN5bWJvbHMuaGFzKGN1cnJlbnQudGV4dCkpIHtcbiAgICAgICAgaGFzTWF0Y2ggPSB0cnVlO1xuICAgICAgICAvLyBOb3RlIHRoYXQgd2UgZG9uJ3QgZGVzY2VuZCBpbnRvIHR5cGUgbGl0ZXJhbHMsIGJlY2F1c2UgaXQgbWF5IGNhdXNlXG4gICAgICAgIC8vIHVzIHRvIG1pcy1pZGVudGlmeSB0aGUgcm9vdCB0eXBlIGFzIG51bGxhYmxlLCBiZWNhdXNlIGl0IGhhcyBhIG51bGxhYmxlXG4gICAgICAgIC8vIHByb3BlcnR5IChlLmcuIGB7IGZvbzogRm9ybUNvbnRyb2wgfWApLlxuICAgICAgfSBlbHNlIGlmICghaGFzTWF0Y2ggJiYgIXRzLmlzVHlwZUxpdGVyYWxOb2RlKGN1cnJlbnQpKSB7XG4gICAgICAgIGN1cnJlbnQuZm9yRWFjaENoaWxkKHdhbGspO1xuICAgICAgfVxuICAgIH0pKHR5cGVOb2RlKTtcbiAgfVxuICByZXR1cm4gaGFzTWF0Y2g7XG59XG4iXX0=