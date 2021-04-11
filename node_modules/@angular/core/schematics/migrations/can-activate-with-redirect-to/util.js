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
        define("@angular/core/schematics/migrations/can-activate-with-redirect-to/util", ["require", "exports", "typescript"], factory);
    }
})(function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.findLiteralsToMigrate = exports.migrateLiteral = void 0;
    const ts = require("typescript");
    const CAN_ACTIVATE = 'canActivate';
    const REDIRECT_TO = 'redirectTo';
    function migrateLiteral(node) {
        const propertiesToKeep = [];
        node.properties.forEach(property => {
            // Only look for regular and shorthand property assignments since resolving things
            // like spread operators becomes too complicated for this migration.
            if ((ts.isPropertyAssignment(property) || ts.isShorthandPropertyAssignment(property)) &&
                (ts.isStringLiteralLike(property.name) || ts.isNumericLiteral(property.name) ||
                    ts.isIdentifier(property.name))) {
                if (property.name.text !== CAN_ACTIVATE) {
                    propertiesToKeep.push(property);
                }
            }
            else {
                propertiesToKeep.push(property);
            }
        });
        return ts.createObjectLiteral(propertiesToKeep);
    }
    exports.migrateLiteral = migrateLiteral;
    function findLiteralsToMigrate(sourceFile) {
        const results = new Set();
        sourceFile.forEachChild(function visitNode(node) {
            if (!ts.isObjectLiteralExpression(node)) {
                node.forEachChild(visitNode);
                return;
            }
            if (hasProperty(node, REDIRECT_TO) && hasProperty(node, CAN_ACTIVATE)) {
                results.add(node);
            }
        });
        return results;
    }
    exports.findLiteralsToMigrate = findLiteralsToMigrate;
    function hasProperty(node, propertyName) {
        for (const property of node.properties) {
            // Only look for regular and shorthand property assignments since resolving things
            // like spread operators becomes too complicated for this migration.
            if ((ts.isPropertyAssignment(property) || ts.isShorthandPropertyAssignment(property)) &&
                (ts.isStringLiteralLike(property.name) || ts.isNumericLiteral(property.name) ||
                    ts.isIdentifier(property.name)) &&
                property.name.text === propertyName) {
                return true;
            }
        }
        return false;
    }
});
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoidXRpbC5qcyIsInNvdXJjZVJvb3QiOiIiLCJzb3VyY2VzIjpbIi4uLy4uLy4uLy4uLy4uLy4uLy4uLy4uL3BhY2thZ2VzL2NvcmUvc2NoZW1hdGljcy9taWdyYXRpb25zL2Nhbi1hY3RpdmF0ZS13aXRoLXJlZGlyZWN0LXRvL3V0aWwudHMiXSwibmFtZXMiOltdLCJtYXBwaW5ncyI6IkFBQUE7Ozs7OztHQU1HOzs7Ozs7Ozs7Ozs7O0lBRUgsaUNBQWlDO0lBRWpDLE1BQU0sWUFBWSxHQUFHLGFBQWEsQ0FBQztJQUNuQyxNQUFNLFdBQVcsR0FBRyxZQUFZLENBQUM7SUFFakMsU0FBZ0IsY0FBYyxDQUFDLElBQWdDO1FBQzdELE1BQU0sZ0JBQWdCLEdBQWtDLEVBQUUsQ0FBQztRQUMzRCxJQUFJLENBQUMsVUFBVSxDQUFDLE9BQU8sQ0FBQyxRQUFRLENBQUMsRUFBRTtZQUNqQyxrRkFBa0Y7WUFDbEYsb0VBQW9FO1lBQ3BFLElBQUksQ0FBQyxFQUFFLENBQUMsb0JBQW9CLENBQUMsUUFBUSxDQUFDLElBQUksRUFBRSxDQUFDLDZCQUE2QixDQUFDLFFBQVEsQ0FBQyxDQUFDO2dCQUNqRixDQUFDLEVBQUUsQ0FBQyxtQkFBbUIsQ0FBQyxRQUFRLENBQUMsSUFBSSxDQUFDLElBQUksRUFBRSxDQUFDLGdCQUFnQixDQUFDLFFBQVEsQ0FBQyxJQUFJLENBQUM7b0JBQzNFLEVBQUUsQ0FBQyxZQUFZLENBQUMsUUFBUSxDQUFDLElBQUksQ0FBQyxDQUFDLEVBQUU7Z0JBQ3BDLElBQUksUUFBUSxDQUFDLElBQUksQ0FBQyxJQUFJLEtBQUssWUFBWSxFQUFFO29CQUN2QyxnQkFBZ0IsQ0FBQyxJQUFJLENBQUMsUUFBUSxDQUFDLENBQUM7aUJBQ2pDO2FBQ0Y7aUJBQU07Z0JBQ0wsZ0JBQWdCLENBQUMsSUFBSSxDQUFDLFFBQVEsQ0FBQyxDQUFDO2FBQ2pDO1FBQ0gsQ0FBQyxDQUFDLENBQUM7UUFFSCxPQUFPLEVBQUUsQ0FBQyxtQkFBbUIsQ0FBQyxnQkFBZ0IsQ0FBQyxDQUFDO0lBQ2xELENBQUM7SUFqQkQsd0NBaUJDO0lBR0QsU0FBZ0IscUJBQXFCLENBQUMsVUFBeUI7UUFDN0QsTUFBTSxPQUFPLEdBQUcsSUFBSSxHQUFHLEVBQThCLENBQUM7UUFFdEQsVUFBVSxDQUFDLFlBQVksQ0FBQyxTQUFTLFNBQVMsQ0FBQyxJQUFhO1lBQ3RELElBQUksQ0FBQyxFQUFFLENBQUMseUJBQXlCLENBQUMsSUFBSSxDQUFDLEVBQUU7Z0JBQ3ZDLElBQUksQ0FBQyxZQUFZLENBQUMsU0FBUyxDQUFDLENBQUM7Z0JBQzdCLE9BQU87YUFDUjtZQUNELElBQUksV0FBVyxDQUFDLElBQUksRUFBRSxXQUFXLENBQUMsSUFBSSxXQUFXLENBQUMsSUFBSSxFQUFFLFlBQVksQ0FBQyxFQUFFO2dCQUNyRSxPQUFPLENBQUMsR0FBRyxDQUFDLElBQUksQ0FBQyxDQUFDO2FBQ25CO1FBQ0gsQ0FBQyxDQUFDLENBQUM7UUFFSCxPQUFPLE9BQU8sQ0FBQztJQUNqQixDQUFDO0lBZEQsc0RBY0M7SUFFRCxTQUFTLFdBQVcsQ0FBQyxJQUFnQyxFQUFFLFlBQW9CO1FBQ3pFLEtBQUssTUFBTSxRQUFRLElBQUksSUFBSSxDQUFDLFVBQVUsRUFBRTtZQUN0QyxrRkFBa0Y7WUFDbEYsb0VBQW9FO1lBQ3BFLElBQUksQ0FBQyxFQUFFLENBQUMsb0JBQW9CLENBQUMsUUFBUSxDQUFDLElBQUksRUFBRSxDQUFDLDZCQUE2QixDQUFDLFFBQVEsQ0FBQyxDQUFDO2dCQUNqRixDQUFDLEVBQUUsQ0FBQyxtQkFBbUIsQ0FBQyxRQUFRLENBQUMsSUFBSSxDQUFDLElBQUksRUFBRSxDQUFDLGdCQUFnQixDQUFDLFFBQVEsQ0FBQyxJQUFJLENBQUM7b0JBQzNFLEVBQUUsQ0FBQyxZQUFZLENBQUMsUUFBUSxDQUFDLElBQUksQ0FBQyxDQUFDO2dCQUNoQyxRQUFRLENBQUMsSUFBSSxDQUFDLElBQUksS0FBSyxZQUFZLEVBQUU7Z0JBQ3ZDLE9BQU8sSUFBSSxDQUFDO2FBQ2I7U0FDRjtRQUNELE9BQU8sS0FBSyxDQUFDO0lBQ2YsQ0FBQyIsInNvdXJjZXNDb250ZW50IjpbIi8qKlxuICogQGxpY2Vuc2VcbiAqIENvcHlyaWdodCBHb29nbGUgTExDIEFsbCBSaWdodHMgUmVzZXJ2ZWQuXG4gKlxuICogVXNlIG9mIHRoaXMgc291cmNlIGNvZGUgaXMgZ292ZXJuZWQgYnkgYW4gTUlULXN0eWxlIGxpY2Vuc2UgdGhhdCBjYW4gYmVcbiAqIGZvdW5kIGluIHRoZSBMSUNFTlNFIGZpbGUgYXQgaHR0cHM6Ly9hbmd1bGFyLmlvL2xpY2Vuc2VcbiAqL1xuXG5pbXBvcnQgKiBhcyB0cyBmcm9tICd0eXBlc2NyaXB0JztcblxuY29uc3QgQ0FOX0FDVElWQVRFID0gJ2NhbkFjdGl2YXRlJztcbmNvbnN0IFJFRElSRUNUX1RPID0gJ3JlZGlyZWN0VG8nO1xuXG5leHBvcnQgZnVuY3Rpb24gbWlncmF0ZUxpdGVyYWwobm9kZTogdHMuT2JqZWN0TGl0ZXJhbEV4cHJlc3Npb24pOiB0cy5PYmplY3RMaXRlcmFsRXhwcmVzc2lvbiB7XG4gIGNvbnN0IHByb3BlcnRpZXNUb0tlZXA6IHRzLk9iamVjdExpdGVyYWxFbGVtZW50TGlrZVtdID0gW107XG4gIG5vZGUucHJvcGVydGllcy5mb3JFYWNoKHByb3BlcnR5ID0+IHtcbiAgICAvLyBPbmx5IGxvb2sgZm9yIHJlZ3VsYXIgYW5kIHNob3J0aGFuZCBwcm9wZXJ0eSBhc3NpZ25tZW50cyBzaW5jZSByZXNvbHZpbmcgdGhpbmdzXG4gICAgLy8gbGlrZSBzcHJlYWQgb3BlcmF0b3JzIGJlY29tZXMgdG9vIGNvbXBsaWNhdGVkIGZvciB0aGlzIG1pZ3JhdGlvbi5cbiAgICBpZiAoKHRzLmlzUHJvcGVydHlBc3NpZ25tZW50KHByb3BlcnR5KSB8fCB0cy5pc1Nob3J0aGFuZFByb3BlcnR5QXNzaWdubWVudChwcm9wZXJ0eSkpICYmXG4gICAgICAgICh0cy5pc1N0cmluZ0xpdGVyYWxMaWtlKHByb3BlcnR5Lm5hbWUpIHx8IHRzLmlzTnVtZXJpY0xpdGVyYWwocHJvcGVydHkubmFtZSkgfHxcbiAgICAgICAgIHRzLmlzSWRlbnRpZmllcihwcm9wZXJ0eS5uYW1lKSkpIHtcbiAgICAgIGlmIChwcm9wZXJ0eS5uYW1lLnRleHQgIT09IENBTl9BQ1RJVkFURSkge1xuICAgICAgICBwcm9wZXJ0aWVzVG9LZWVwLnB1c2gocHJvcGVydHkpO1xuICAgICAgfVxuICAgIH0gZWxzZSB7XG4gICAgICBwcm9wZXJ0aWVzVG9LZWVwLnB1c2gocHJvcGVydHkpO1xuICAgIH1cbiAgfSk7XG5cbiAgcmV0dXJuIHRzLmNyZWF0ZU9iamVjdExpdGVyYWwocHJvcGVydGllc1RvS2VlcCk7XG59XG5cblxuZXhwb3J0IGZ1bmN0aW9uIGZpbmRMaXRlcmFsc1RvTWlncmF0ZShzb3VyY2VGaWxlOiB0cy5Tb3VyY2VGaWxlKSB7XG4gIGNvbnN0IHJlc3VsdHMgPSBuZXcgU2V0PHRzLk9iamVjdExpdGVyYWxFeHByZXNzaW9uPigpO1xuXG4gIHNvdXJjZUZpbGUuZm9yRWFjaENoaWxkKGZ1bmN0aW9uIHZpc2l0Tm9kZShub2RlOiB0cy5Ob2RlKSB7XG4gICAgaWYgKCF0cy5pc09iamVjdExpdGVyYWxFeHByZXNzaW9uKG5vZGUpKSB7XG4gICAgICBub2RlLmZvckVhY2hDaGlsZCh2aXNpdE5vZGUpO1xuICAgICAgcmV0dXJuO1xuICAgIH1cbiAgICBpZiAoaGFzUHJvcGVydHkobm9kZSwgUkVESVJFQ1RfVE8pICYmIGhhc1Byb3BlcnR5KG5vZGUsIENBTl9BQ1RJVkFURSkpIHtcbiAgICAgIHJlc3VsdHMuYWRkKG5vZGUpO1xuICAgIH1cbiAgfSk7XG5cbiAgcmV0dXJuIHJlc3VsdHM7XG59XG5cbmZ1bmN0aW9uIGhhc1Byb3BlcnR5KG5vZGU6IHRzLk9iamVjdExpdGVyYWxFeHByZXNzaW9uLCBwcm9wZXJ0eU5hbWU6IHN0cmluZyk6IGJvb2xlYW4ge1xuICBmb3IgKGNvbnN0IHByb3BlcnR5IG9mIG5vZGUucHJvcGVydGllcykge1xuICAgIC8vIE9ubHkgbG9vayBmb3IgcmVndWxhciBhbmQgc2hvcnRoYW5kIHByb3BlcnR5IGFzc2lnbm1lbnRzIHNpbmNlIHJlc29sdmluZyB0aGluZ3NcbiAgICAvLyBsaWtlIHNwcmVhZCBvcGVyYXRvcnMgYmVjb21lcyB0b28gY29tcGxpY2F0ZWQgZm9yIHRoaXMgbWlncmF0aW9uLlxuICAgIGlmICgodHMuaXNQcm9wZXJ0eUFzc2lnbm1lbnQocHJvcGVydHkpIHx8IHRzLmlzU2hvcnRoYW5kUHJvcGVydHlBc3NpZ25tZW50KHByb3BlcnR5KSkgJiZcbiAgICAgICAgKHRzLmlzU3RyaW5nTGl0ZXJhbExpa2UocHJvcGVydHkubmFtZSkgfHwgdHMuaXNOdW1lcmljTGl0ZXJhbChwcm9wZXJ0eS5uYW1lKSB8fFxuICAgICAgICAgdHMuaXNJZGVudGlmaWVyKHByb3BlcnR5Lm5hbWUpKSAmJlxuICAgICAgICBwcm9wZXJ0eS5uYW1lLnRleHQgPT09IHByb3BlcnR5TmFtZSkge1xuICAgICAgcmV0dXJuIHRydWU7XG4gICAgfVxuICB9XG4gIHJldHVybiBmYWxzZTtcbn0iXX0=