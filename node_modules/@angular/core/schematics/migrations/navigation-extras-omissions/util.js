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
        define("@angular/core/schematics/migrations/navigation-extras-omissions/util", ["require", "exports", "typescript", "@angular/core/schematics/utils/typescript/imports", "@angular/core/schematics/utils/typescript/symbol"], factory);
    }
})(function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.findLiteralsToMigrate = exports.migrateLiteral = void 0;
    const ts = require("typescript");
    const imports_1 = require("@angular/core/schematics/utils/typescript/imports");
    const symbol_1 = require("@angular/core/schematics/utils/typescript/symbol");
    /**
     * Configures the methods that the migration should be looking for
     * and the properties from `NavigationExtras` that should be preserved.
     */
    const methodConfig = new Map([
        ['navigateByUrl', new Set(['skipLocationChange', 'replaceUrl', 'state'])],
        [
            'createUrlTree',
            new Set([
                'relativeTo', 'queryParams', 'fragment', 'preserveQueryParams', 'queryParamsHandling',
                'preserveFragment'
            ])
        ]
    ]);
    function migrateLiteral(methodName, node) {
        const allowedProperties = methodConfig.get(methodName);
        if (!allowedProperties) {
            throw Error(`Attempting to migrate unconfigured method called ${methodName}.`);
        }
        const propertiesToKeep = [];
        const removedPropertyNames = [];
        node.properties.forEach(property => {
            // Only look for regular and shorthand property assignments since resolving things
            // like spread operators becomes too complicated for this migration.
            if ((ts.isPropertyAssignment(property) || ts.isShorthandPropertyAssignment(property)) &&
                (ts.isStringLiteralLike(property.name) || ts.isNumericLiteral(property.name) ||
                    ts.isIdentifier(property.name))) {
                if (allowedProperties.has(property.name.text)) {
                    propertiesToKeep.push(property);
                }
                else {
                    removedPropertyNames.push(property.name.text);
                }
            }
            else {
                propertiesToKeep.push(property);
            }
        });
        // Don't modify the node if there's nothing to remove.
        if (removedPropertyNames.length === 0) {
            return node;
        }
        // Note that the trailing/leading spaces are necessary so the comment looks good.
        const removalComment = ` Removed unsupported properties by Angular migration: ${removedPropertyNames.join(', ')}. `;
        if (propertiesToKeep.length > 0) {
            propertiesToKeep[0] = addUniqueLeadingComment(propertiesToKeep[0], removalComment);
            return ts.createObjectLiteral(propertiesToKeep);
        }
        else {
            return addUniqueLeadingComment(ts.createObjectLiteral(propertiesToKeep), removalComment);
        }
    }
    exports.migrateLiteral = migrateLiteral;
    function findLiteralsToMigrate(sourceFile, typeChecker) {
        const results = new Map(Array.from(methodConfig.keys(), key => [key, new Set()]));
        const routerImport = imports_1.getImportSpecifier(sourceFile, '@angular/router', 'Router');
        const seenLiterals = new Map();
        if (routerImport) {
            sourceFile.forEachChild(function visitNode(node) {
                var _a;
                // Look for calls that look like `foo.<method to migrate>` with more than one parameter.
                if (ts.isCallExpression(node) && node.arguments.length > 1 &&
                    ts.isPropertyAccessExpression(node.expression) && ts.isIdentifier(node.expression.name) &&
                    methodConfig.has(node.expression.name.text)) {
                    // Check whether the type of the object on which the
                    // function is called refers to the Router import.
                    if (symbol_1.isReferenceToImport(typeChecker, node.expression.expression, routerImport)) {
                        const methodName = node.expression.name.text;
                        const parameterDeclaration = (_a = typeChecker.getTypeAtLocation(node.arguments[1]).getSymbol()) === null || _a === void 0 ? void 0 : _a.valueDeclaration;
                        // Find the source of the object literal.
                        if (parameterDeclaration && ts.isObjectLiteralExpression(parameterDeclaration)) {
                            if (!seenLiterals.has(parameterDeclaration)) {
                                results.get(methodName).add(parameterDeclaration);
                                seenLiterals.set(parameterDeclaration, methodName);
                                // If the same literal has been passed into multiple different methods, we can't
                                // migrate it, because the supported properties are different. When we detect such
                                // a case, we drop it from the results so that it gets ignored. If it's used multiple
                                // times for the same method, it can still be migrated.
                            }
                            else if (seenLiterals.get(parameterDeclaration) !== methodName) {
                                results.forEach(literals => literals.delete(parameterDeclaration));
                            }
                        }
                    }
                }
                else {
                    node.forEachChild(visitNode);
                }
            });
        }
        return results;
    }
    exports.findLiteralsToMigrate = findLiteralsToMigrate;
    /** Adds a leading comment to a node, if the node doesn't have such a comment already. */
    function addUniqueLeadingComment(node, comment) {
        const existingComments = ts.getSyntheticLeadingComments(node);
        // This logic is primarily to ensure that we don't add the same comment multiple
        // times when tslint runs over the same file again with outdated information.
        if (!existingComments || existingComments.every(c => c.text !== comment)) {
            return ts.addSyntheticLeadingComment(node, ts.SyntaxKind.MultiLineCommentTrivia, comment);
        }
        return node;
    }
});
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoidXRpbC5qcyIsInNvdXJjZVJvb3QiOiIiLCJzb3VyY2VzIjpbIi4uLy4uLy4uLy4uLy4uLy4uLy4uLy4uL3BhY2thZ2VzL2NvcmUvc2NoZW1hdGljcy9taWdyYXRpb25zL25hdmlnYXRpb24tZXh0cmFzLW9taXNzaW9ucy91dGlsLnRzIl0sIm5hbWVzIjpbXSwibWFwcGluZ3MiOiJBQUFBOzs7Ozs7R0FNRzs7Ozs7Ozs7Ozs7OztJQUVILGlDQUFpQztJQUVqQywrRUFBa0U7SUFDbEUsNkVBQWtFO0lBRWxFOzs7T0FHRztJQUNILE1BQU0sWUFBWSxHQUFHLElBQUksR0FBRyxDQUFzQjtRQUNoRCxDQUFDLGVBQWUsRUFBRSxJQUFJLEdBQUcsQ0FBUyxDQUFDLG9CQUFvQixFQUFFLFlBQVksRUFBRSxPQUFPLENBQUMsQ0FBQyxDQUFDO1FBQ2pGO1lBQ0UsZUFBZTtZQUFFLElBQUksR0FBRyxDQUFTO2dCQUMvQixZQUFZLEVBQUUsYUFBYSxFQUFFLFVBQVUsRUFBRSxxQkFBcUIsRUFBRSxxQkFBcUI7Z0JBQ3JGLGtCQUFrQjthQUNuQixDQUFDO1NBQ0g7S0FDRixDQUFDLENBQUM7SUFFSCxTQUFnQixjQUFjLENBQzFCLFVBQWtCLEVBQUUsSUFBZ0M7UUFDdEQsTUFBTSxpQkFBaUIsR0FBRyxZQUFZLENBQUMsR0FBRyxDQUFDLFVBQVUsQ0FBQyxDQUFDO1FBRXZELElBQUksQ0FBQyxpQkFBaUIsRUFBRTtZQUN0QixNQUFNLEtBQUssQ0FBQyxvREFBb0QsVUFBVSxHQUFHLENBQUMsQ0FBQztTQUNoRjtRQUVELE1BQU0sZ0JBQWdCLEdBQWtDLEVBQUUsQ0FBQztRQUMzRCxNQUFNLG9CQUFvQixHQUFhLEVBQUUsQ0FBQztRQUUxQyxJQUFJLENBQUMsVUFBVSxDQUFDLE9BQU8sQ0FBQyxRQUFRLENBQUMsRUFBRTtZQUNqQyxrRkFBa0Y7WUFDbEYsb0VBQW9FO1lBQ3BFLElBQUksQ0FBQyxFQUFFLENBQUMsb0JBQW9CLENBQUMsUUFBUSxDQUFDLElBQUksRUFBRSxDQUFDLDZC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QUU7WUFDeEUsT0FBTyxFQUFFLENBQUMsMEJBQTBCLENBQUMsSUFBSSxFQUFFLEVBQUUsQ0FBQyxVQUFVLENBQUMsc0JBQXNCLEVBQUUsT0FBTyxDQUFDLENBQUM7U0FDM0Y7UUFFRCxPQUFPLElBQUksQ0FBQztJQUNkLENBQUMiLCJzb3VyY2VzQ29udGVudCI6WyIvKipcbiAqIEBsaWNlbnNlXG4gKiBDb3B5cmlnaHQgR29vZ2xlIExMQyBBbGwgUmlnaHRzIFJlc2VydmVkLlxuICpcbiAqIFVzZSBvZiB0aGlzIHNvdXJjZSBjb2RlIGlzIGdvdmVybmVkIGJ5IGFuIE1JVC1zdHlsZSBsaWNlbnNlIHRoYXQgY2FuIGJlXG4gKiBmb3VuZCBpbiB0aGUgTElDRU5TRSBmaWxlIGF0IGh0dHBzOi8vYW5ndWxhci5pby9saWNlbnNlXG4gKi9cblxuaW1wb3J0ICogYXMgdHMgZnJvbSAndHlwZXNjcmlwdCc7XG5cbmltcG9ydCB7Z2V0SW1wb3J0U3BlY2lmaWVyfSBmcm9tICcuLi8uLi91dGlscy90eXBlc2NyaXB0L2ltcG9ydHMnO1xuaW1wb3J0IHtpc1JlZmVyZW5jZVRvSW1wb3J0fSBmcm9tICcuLi8uLi91dGlscy90eXBlc2NyaXB0L3N5bWJvbCc7XG5cbi8qKlxuICogQ29uZmlndXJlcyB0aGUgbWV0aG9kcyB0aGF0IHRoZSBtaWdyYXRpb24gc2hvdWxkIGJlIGxvb2tpbmcgZm9yXG4gKiBhbmQgdGhlIHByb3BlcnRpZXMgZnJvbSBgTmF2aWdhdGlvbkV4dHJhc2AgdGhhdCBzaG91bGQgYmUgcHJlc2VydmVkLlxuICovXG5jb25zdCBtZXRob2RDb25maWcgPSBuZXcgTWFwPHN0cmluZywgU2V0PHN0cmluZz4+KFtcbiAgWyduYXZpZ2F0ZUJ5VXJsJywgbmV3IFNldDxzdHJpbmc+KFsnc2tpcExvY2F0aW9uQ2hhbmdlJywgJ3JlcGxhY2VVcmwnLCAnc3RhdGUnXSldLFxuICBbXG4gICAgJ2NyZWF0ZVVybFRyZWUnLCBuZXcgU2V0PHN0cmluZz4oW1xuICAgICAgJ3JlbGF0aXZlVG8nLCAncXVlcnlQYXJhbXMnLCAnZnJhZ21lbnQnLCAncHJlc2VydmVRdWVyeVBhcmFtcycsICdxdWVyeVBhcmFtc0hhbmRsaW5nJyxcbiAgICAgICdwcmVzZXJ2ZUZyYWdtZW50J1xuICAgIF0pXG4gIF1cbl0pO1xuXG5leHBvcnQgZnVuY3Rpb24gbWlncmF0ZUxpdGVyYWwoXG4gICAgbWV0aG9kTmFtZTogc3RyaW5nLCBub2RlOiB0cy5PYmplY3RMaXRlcmFsRXhwcmVzc2lvbik6IHRzLk9iamVjdExpdGVyYWxFeHByZXNzaW9uIHtcbiAgY29uc3QgYWxsb3dlZFByb3BlcnRpZXMgPSBtZXRob2RDb25maWcuZ2V0KG1ldGhvZE5hbWUpO1xuXG4gIGlmICghYWxsb3dlZFByb3BlcnRpZXMpIHtcbiAgICB0aHJvdyBFcnJvcihgQXR0ZW1wdGluZyB0byBtaWdyYXRlIHVuY29uZmlndXJlZCBtZXRob2QgY2FsbGVkICR7bWV0aG9kTmFtZX0uYCk7XG4gIH1cblxuICBjb25zdCBwcm9wZXJ0aWVzVG9LZWVwOiB0cy5PYmplY3RMaXRlcmFsRWxlbWVudExpa2VbXSA9IFtdO1xuICBjb25zdCByZW1vdmVkUHJvcGVydHlOYW1lczogc3RyaW5nW10gPSBbXTtcblxuICBub2RlLnByb3BlcnRpZXMuZm9yRWFjaChwcm9wZXJ0eSA9PiB7XG4gICAgLy8gT25seSBsb29rIGZvciByZWd1bGFyIGFuZCBzaG9ydGhhbmQgcHJvcGVydHkgYXNzaWdubWVudHMgc2luY2UgcmVzb2x2aW5nIHRoaW5nc1xuICAgIC8vIGxpa2Ugc3ByZWFkIG9wZXJhdG9ycyBiZWNvbWVzIHRvbyBjb21wbGljYXRlZCBmb3IgdGhpcyBtaWdyYXRpb24uXG4gICAgaWYgKCh0cy5pc1Byb3BlcnR5QXNzaWdubWVudChwcm9wZXJ0eSkgfHwgdHMuaXNTaG9ydGhhbmRQcm9wZXJ0eUFzc2lnbm1lbnQocHJvcGVydHkpKSAmJlxuICAgICAgICAodHMuaXNTdHJpbmdMaXRlcmFsTGlrZShwcm9wZXJ0eS5uYW1lKSB8fCB0cy5pc051bWVyaWNMaXRlcmFsKHByb3BlcnR5Lm5hbWUpIHx8XG4gICAgICAgICB0cy5pc0lkZW50aWZpZXIocHJvcGVydHkubmFtZSkpKSB7XG4gICAgICBpZiAoYWxsb3dlZFByb3BlcnRpZXMuaGFzKHByb3BlcnR5Lm5hbWUudGV4dCkpIHtcbiAgICAgICAgcHJvcGVydGllc1RvS2VlcC5wdXNoKHByb3BlcnR5KTtcbiAgICAgIH0gZWxzZSB7XG4gICAgICAgIHJlbW92ZWRQcm9wZXJ0eU5hbWVzLnB1c2gocHJvcGVydHkubmFtZS50ZXh0KTtcbiAgICAgIH1cbiAgICB9IGVsc2Uge1xuICAgICAgcHJvcGVydGllc1RvS2VlcC5wdXNoKHByb3BlcnR5KTtcbiAgICB9XG4gIH0pO1xuXG4gIC8vIERvbid0IG1vZGlmeSB0aGUgbm9kZSBpZiB0aGVyZSdzIG5vdGhpbmcgdG8gcmVtb3ZlLlxuICBpZiAocmVtb3ZlZFByb3BlcnR5TmFtZXMubGVuZ3RoID09PSAwKSB7XG4gICAgcmV0dXJuIG5vZGU7XG4gIH1cblxuICAvLyBOb3RlIHRoYXQgdGhlIHRyYWlsaW5nL2xlYWRpbmcgc3BhY2VzIGFyZSBuZWNlc3Nhcnkgc28gdGhlIGNvbW1lbnQgbG9va3MgZ29vZC5cbiAgY29uc3QgcmVtb3ZhbENvbW1lbnQgPVxuICAgICAgYCBSZW1vdmVkIHVuc3VwcG9ydGVkIHByb3BlcnRpZXMgYnkgQW5ndWxhciBtaWdyYXRpb246ICR7cmVtb3ZlZFByb3BlcnR5TmFtZXMuam9pbignLCAnKX0uIGA7XG5cbiAgaWYgKHByb3BlcnRpZXNUb0tlZXAubGVuZ3RoID4gMCkge1xuICAgIHByb3BlcnRpZXNUb0tlZXBbMF0gPSBhZGRVbmlxdWVMZWFkaW5nQ29tbWVudChwcm9wZXJ0aWVzVG9LZWVwWzBdLCByZW1vdmFsQ29tbWVudCk7XG4gICAgcmV0dXJuIHRzLmNyZWF0ZU9iamVjdExpdGVyYWwocHJvcGVydGllc1RvS2VlcCk7XG4gIH0gZWxzZSB7XG4gICAgcmV0dXJuIGFkZFVuaXF1ZUxlYWRpbmdDb21tZW50KHRzLmNyZWF0ZU9iamVjdExpdGVyYWwocHJvcGVydGllc1RvS2VlcCksIHJlbW92YWxDb21tZW50KTtcbiAgfVxufVxuXG5leHBvcnQgZnVuY3Rpb24gZmluZExpdGVyYWxzVG9NaWdyYXRlKHNvdXJjZUZpbGU6IHRzLlNvdXJjZUZpbGUsIHR5cGVDaGVja2VyOiB0cy5UeXBlQ2hlY2tlcikge1xuICBjb25zdCByZXN1bHRzID0gbmV3IE1hcDxzdHJpbmcsIFNldDx0cy5PYmplY3RMaXRlcmFsRXhwcmVzc2lvbj4+KFxuICAgICAgQXJyYXkuZnJvbShtZXRob2RDb25maWcua2V5cygpLCBrZXkgPT4gW2tleSwgbmV3IFNldCgpXSkpO1xuICBjb25zdCByb3V0ZXJJbXBvcnQgPSBnZXRJbXBvcnRTcGVjaWZpZXIoc291cmNlRmlsZSwgJ0Bhbmd1bGFyL3JvdXRlcicsICdSb3V0ZXInKTtcbiAgY29uc3Qgc2VlbkxpdGVyYWxzID0gbmV3IE1hcDx0cy5PYmplY3RMaXRlcmFsRXhwcmVzc2lvbiwgc3RyaW5nPigpO1xuXG4gIGlmIChyb3V0ZXJJbXBvcnQpIHtcbiAgICBzb3VyY2VGaWxlLmZvckVhY2hDaGlsZChmdW5jdGlvbiB2aXNpdE5vZGUobm9kZTogdHMuTm9kZSkge1xuICAgICAgLy8gTG9vayBmb3IgY2FsbHMgdGhhdCBsb29rIGxpa2UgYGZvby48bWV0aG9kIHRvIG1pZ3JhdGU+YCB3aXRoIG1vcmUgdGhhbiBvbmUgcGFyYW1ldGVyLlxuICAgICAgaWYgKHRzLmlzQ2FsbEV4cHJlc3Npb24obm9kZSkgJiYgbm9kZS5hcmd1bWVudHMubGVuZ3RoID4gMSAmJlxuICAgICAgICAgIHRzLmlzUHJvcGVydHlBY2Nlc3NFeHByZXNzaW9uKG5vZGUuZXhwcmVzc2lvbikgJiYgdHMuaXNJZGVudGlmaWVyKG5vZGUuZXhwcmVzc2lvbi5uYW1lKSAmJlxuICAgICAgICAgIG1ldGhvZENvbmZpZy5oYXMobm9kZS5leHByZXNzaW9uLm5hbWUudGV4dCkpIHtcbiAgICAgICAgLy8gQ2hlY2sgd2hldGhlciB0aGUgdHlwZSBvZiB0aGUgb2JqZWN0IG9uIHdoaWNoIHRoZVxuICAgICAgICAvLyBmdW5jdGlvbiBpcyBjYWxsZWQgcmVmZXJzIHRvIHRoZSBSb3V0ZXIgaW1wb3J0LlxuICAgICAgICBpZiAoaXNSZWZlcmVuY2VUb0ltcG9ydCh0eXBlQ2hlY2tlciwgbm9kZS5leHByZXNzaW9uLmV4cHJlc3Npb24sIHJvdXRlckltcG9ydCkpIHtcbiAgICAgICAgICBjb25zdCBtZXRob2ROYW1lID0gbm9kZS5leHByZXNzaW9uLm5hbWUudGV4dDtcbiAgICAgICAgICBjb25zdCBwYXJhbWV0ZXJEZWNsYXJhdGlvbiA9XG4gICAgICAgICAgICAgIHR5cGVDaGVja2VyLmdldFR5cGVBdExvY2F0aW9uKG5vZGUuYXJndW1lbnRzWzFdKS5nZXRTeW1ib2woKT8udmFsdWVEZWNsYXJhdGlvbjtcblxuICAgICAgICAgIC8vIEZpbmQgdGhlIHNvdXJjZSBvZiB0aGUgb2JqZWN0IGxpdGVyYWwuXG4gICAgICAgICAgaWYgKHBhcmFtZXRlckRlY2xhcmF0aW9uICYmIHRzLmlzT2JqZWN0TGl0ZXJhbEV4cHJlc3Npb24ocGFyYW1ldGVyRGVjbGFyYXRpb24pKSB7XG4gICAgICAgICAgICBpZiAoIXNlZW5MaXRlcmFscy5oYXMocGFyYW1ldGVyRGVjbGFyYXRpb24pKSB7XG4gICAgICAgICAgICAgIHJlc3VsdHMuZ2V0KG1ldGhvZE5hbWUpIS5hZGQocGFyYW1ldGVyRGVjbGFyYXRpb24pO1xuICAgICAgICAgICAgICBzZWVuTGl0ZXJhbHMuc2V0KHBhcmFtZXRlckRlY2xhcmF0aW9uLCBtZXRob2ROYW1lKTtcbiAgICAgICAgICAgICAgLy8gSWYgdGhlIHNhbWUgbGl0ZXJhbCBoYXMgYmVlbiBwYXNzZWQgaW50byBtdWx0aXBsZSBkaWZmZXJlbnQgbWV0aG9kcywgd2UgY2FuJ3RcbiAgICAgICAgICAgICAgLy8gbWlncmF0ZSBpdCwgYmVjYXVzZSB0aGUgc3VwcG9ydGVkIHByb3BlcnRpZXMgYXJlIGRpZmZlcmVudC4gV2hlbiB3ZSBkZXRlY3Qgc3VjaFxuICAgICAgICAgICAgICAvLyBhIGNhc2UsIHdlIGRyb3AgaXQgZnJvbSB0aGUgcmVzdWx0cyBzbyB0aGF0IGl0IGdldHMgaWdub3JlZC4gSWYgaXQncyB1c2VkIG11bHRpcGxlXG4gICAgICAgICAgICAgIC8vIHRpbWVzIGZvciB0aGUgc2FtZSBtZXRob2QsIGl0IGNhbiBzdGlsbCBiZSBtaWdyYXRlZC5cbiAgICAgICAgICAgIH0gZWxzZSBpZiAoc2VlbkxpdGVyYWxzLmdldChwYXJhbWV0ZXJEZWNsYXJhdGlvbikgIT09IG1ldGhvZE5hbWUpIHtcbiAgICAgICAgICAgICAgcmVzdWx0cy5mb3JFYWNoKGxpdGVyYWxzID0+IGxpdGVyYWxzLmRlbGV0ZShwYXJhbWV0ZXJEZWNsYXJhdGlvbikpO1xuICAgICAgICAgICAgfVxuICAgICAgICAgIH1cbiAgICAgICAgfVxuICAgICAgfSBlbHNlIHtcbiAgICAgICAgbm9kZS5mb3JFYWNoQ2hpbGQodmlzaXROb2RlKTtcbiAgICAgIH1cbiAgICB9KTtcbiAgfVxuXG4gIHJldHVybiByZXN1bHRzO1xufVxuXG4vKiogQWRkcyBhIGxlYWRpbmcgY29tbWVudCB0byBhIG5vZGUsIGlmIHRoZSBub2RlIGRvZXNuJ3QgaGF2ZSBzdWNoIGEgY29tbWVudCBhbHJlYWR5LiAqL1xuZnVuY3Rpb24gYWRkVW5pcXVlTGVhZGluZ0NvbW1lbnQ8VCBleHRlbmRzIHRzLk5vZGU+KG5vZGU6IFQsIGNvbW1lbnQ6IHN0cmluZyk6IFQge1xuICBjb25zdCBleGlzdGluZ0NvbW1lbnRzID0gdHMuZ2V0U3ludGhldGljTGVhZGluZ0NvbW1lbnRzKG5vZGUpO1xuXG4gIC8vIFRoaXMgbG9naWMgaXMgcHJpbWFyaWx5IHRvIGVuc3VyZSB0aGF0IHdlIGRvbid0IGFkZCB0aGUgc2FtZSBjb21tZW50IG11bHRpcGxlXG4gIC8vIHRpbWVzIHdoZW4gdHNsaW50IHJ1bnMgb3ZlciB0aGUgc2FtZSBmaWxlIGFnYWluIHdpdGggb3V0ZGF0ZWQgaW5mb3JtYXRpb24uXG4gIGlmICghZXhpc3RpbmdDb21tZW50cyB8fCBleGlzdGluZ0NvbW1lbnRzLmV2ZXJ5KGMgPT4gYy50ZXh0ICE9PSBjb21tZW50KSkge1xuICAgIHJldHVybiB0cy5hZGRTeW50aGV0aWNMZWFkaW5nQ29tbWVudChub2RlLCB0cy5TeW50YXhLaW5kLk11bHRpTGluZUNvbW1lbnRUcml2aWEsIGNvbW1lbnQpO1xuICB9XG5cbiAgcmV0dXJuIG5vZGU7XG59XG4iXX0=