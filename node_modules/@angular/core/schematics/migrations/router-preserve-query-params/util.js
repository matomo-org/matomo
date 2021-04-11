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
        define("@angular/core/schematics/migrations/router-preserve-query-params/util", ["require", "exports", "typescript", "@angular/core/schematics/utils/typescript/imports", "@angular/core/schematics/utils/typescript/symbol"], factory);
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
    const methodConfig = new Set(['navigate', 'createUrlTree']);
    const preserveQueryParamsKey = 'preserveQueryParams';
    function migrateLiteral(methodName, node) {
        var _a;
        const isMigratableMethod = methodConfig.has(methodName);
        if (!isMigratableMethod) {
            throw Error(`Attempting to migrate unconfigured method called ${methodName}.`);
        }
        const propertiesToKeep = [];
        let propertyToMigrate = undefined;
        for (const property of node.properties) {
            // Only look for regular and shorthand property assignments since resolving things
            // like spread operators becomes too complicated for this migration.
            if ((ts.isPropertyAssignment(property) || ts.isShorthandPropertyAssignment(property)) &&
                (ts.isStringLiteralLike(property.name) || ts.isNumericLiteral(property.name) ||
                    ts.isIdentifier(property.name)) &&
                (property.name.text === preserveQueryParamsKey)) {
                propertyToMigrate = property;
                continue;
            }
            propertiesToKeep.push(property);
        }
        // Don't modify the node if there's nothing to migrate.
        if (propertyToMigrate === undefined) {
            return node;
        }
        if ((ts.isShorthandPropertyAssignment(propertyToMigrate) &&
            ((_a = propertyToMigrate.objectAssignmentInitializer) === null || _a === void 0 ? void 0 : _a.kind) === ts.SyntaxKind.TrueKeyword) ||
            (ts.isPropertyAssignment(propertyToMigrate) &&
                propertyToMigrate.initializer.kind === ts.SyntaxKind.TrueKeyword)) {
            return ts.updateObjectLiteral(node, propertiesToKeep.concat(ts.createPropertyAssignment('queryParamsHandling', ts.createIdentifier(`'preserve'`))));
        }
        return ts.updateObjectLiteral(node, propertiesToKeep);
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
});
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoidXRpbC5qcyIsInNvdXJjZVJvb3QiOiIiLCJzb3VyY2VzIjpbIi4uLy4uLy4uLy4uLy4uLy4uLy4uLy4uL3BhY2thZ2VzL2NvcmUvc2NoZW1hdGljcy9taWdyYXRpb25zL3JvdXRlci1wcmVzZXJ2ZS1xdWVyeS1wYXJhbXMvdXRpbC50cyJdLCJuYW1lcyI6W10sIm1hcHBpbmdzIjoiQUFBQTs7Ozs7O0dBTUc7Ozs7Ozs7Ozs7Ozs7SUFFSCxpQ0FBaUM7SUFFakMsK0VBQWtFO0lBQ2xFLDZFQUFrRTtJQUVsRTs7O09BR0c7SUFDSCxNQUFNLFlBQVksR0FBRyxJQUFJLEdBQUcsQ0FBUyxDQUFDLFVBQVUsRUFBRSxlQUFlLENBQUMsQ0FBQyxDQUFDO0lBRXBFLE1BQU0sc0JBQXNCLEdBQUcscUJBQXFCLENBQUM7SUFFckQsU0FBZ0IsY0FBYyxDQUMxQixVQUFrQixFQUFFLElBQWdDOztRQUN0RCxNQUFNLGtCQUFrQixHQUFHLFlBQVksQ0FBQyxHQUFHLENBQUMsVUFBVSxDQUFDLENBQUM7UUFFeEQsSUFBSSxDQUFDLGtCQUFrQixFQUFFO1lBQ3ZCLE1BQU0sS0FBSyxDQUFDLG9EQUFvRCxVQUFVLEdBQUcsQ0FBQyxDQUFDO1NBQ2hGO1FBR0QsTUFBTSxnQkFBZ0IsR0FBa0MsRUFBRSxDQUFDO1FBQzNELElBQUksaUJBQWlCLEdBQW1FLFNBQVMsQ0FBQztRQUVsRyxLQUFLLE1BQU0sUUFBUSxJQUFJLElBQUksQ0FBQyxVQUFVLEVBQUU7WUFDdEMsa0ZBQWtGO1lBQ2xGLG9FQUFvRTtZQUNwRSxJQUFJLENBQUMsRUFBRSxDQUFDLG9CQUFvQixDQUFDLFFBQVEsQ0FBQyxJQUFJLEVBQUUsQ0FBQyw2QkFBNkIsQ0FBQyxRQUFRLENBQUMsQ0FBQztnQkFDakYsQ0FBQyxFQUFFLENBQUMsbUJBQW1CLENBQUMsUUFBUSxDQUFDLElBQUksQ0FBQyxJQUFJLEVBQUUsQ0FBQyxnQkFBZ0IsQ0FBQyxRQUFRLENBQUMsSUFBSSxDQUFDO29CQUMzRSxFQUFFLENBQUMsWUFBWSxDQUFD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QzNDLE9BQU8sQ0FBQyxHQUFHLENBQUMsVUFBVSxDQUFFLENBQUMsR0FBRyxDQUFDLG9CQUFvQixDQUFDLENBQUM7Z0NBQ25ELFlBQVksQ0FBQyxHQUFHLENBQUMsb0JBQW9CLEVBQUUsVUFBVSxDQUFDLENBQUM7Z0NBQ25ELGdGQUFnRjtnQ0FDaEYsa0ZBQWtGO2dDQUNsRixxRkFBcUY7Z0NBQ3JGLHVEQUF1RDs2QkFDeEQ7aUNBQU0sSUFBSSxZQUFZLENBQUMsR0FBRyxDQUFDLG9CQUFvQixDQUFDLEtBQUssVUFBVSxFQUFFO2dDQUNoRSxPQUFPLENBQUMsT0FBTyxDQUFDLFFBQVEsQ0FBQyxFQUFFLENBQUMsUUFBUSxDQUFDLE1BQU0sQ0FBQyxvQkFBb0IsQ0FBQyxDQUFDLENBQUM7NkJBQ3BFO3lCQUNGO3FCQUNGO2lCQUNGO3FCQUFNO29CQUNMLElBQUksQ0FBQyxZQUFZLENBQUMsU0FBUyxDQUFDLENBQUM7aUJBQzlCO1lBQ0gsQ0FBQyxDQUFDLENBQUM7U0FDSjtRQUVELE9BQU8sT0FBTyxDQUFDO0lBQ2pCLENBQUM7SUF4Q0Qsc0RBd0NDIiwic291cmNlc0NvbnRlbnQiOlsiLyoqXG4gKiBAbGljZW5zZVxuICogQ29weXJpZ2h0IEdvb2dsZSBMTEMgQWxsIFJpZ2h0cyBSZXNlcnZlZC5cbiAqXG4gKiBVc2Ugb2YgdGhpcyBzb3VyY2UgY29kZSBpcyBnb3Zlcm5lZCBieSBhbiBNSVQtc3R5bGUgbGljZW5zZSB0aGF0IGNhbiBiZVxuICogZm91bmQgaW4gdGhlIExJQ0VOU0UgZmlsZSBhdCBodHRwczovL2FuZ3VsYXIuaW8vbGljZW5zZVxuICovXG5cbmltcG9ydCAqIGFzIHRzIGZyb20gJ3R5cGVzY3JpcHQnO1xuXG5pbXBvcnQge2dldEltcG9ydFNwZWNpZmllcn0gZnJvbSAnLi4vLi4vdXRpbHMvdHlwZXNjcmlwdC9pbXBvcnRzJztcbmltcG9ydCB7aXNSZWZlcmVuY2VUb0ltcG9ydH0gZnJvbSAnLi4vLi4vdXRpbHMvdHlwZXNjcmlwdC9zeW1ib2wnO1xuXG4vKipcbiAqIENvbmZpZ3VyZXMgdGhlIG1ldGhvZHMgdGhhdCB0aGUgbWlncmF0aW9uIHNob3VsZCBiZSBsb29raW5nIGZvclxuICogYW5kIHRoZSBwcm9wZXJ0aWVzIGZyb20gYE5hdmlnYXRpb25FeHRyYXNgIHRoYXQgc2hvdWxkIGJlIHByZXNlcnZlZC5cbiAqL1xuY29uc3QgbWV0aG9kQ29uZmlnID0gbmV3IFNldDxzdHJpbmc+KFsnbmF2aWdhdGUnLCAnY3JlYXRlVXJsVHJlZSddKTtcblxuY29uc3QgcHJlc2VydmVRdWVyeVBhcmFtc0tleSA9ICdwcmVzZXJ2ZVF1ZXJ5UGFyYW1zJztcblxuZXhwb3J0IGZ1bmN0aW9uIG1pZ3JhdGVMaXRlcmFsKFxuICAgIG1ldGhvZE5hbWU6IHN0cmluZywgbm9kZTogdHMuT2JqZWN0TGl0ZXJhbEV4cHJlc3Npb24pOiB0cy5PYmplY3RMaXRlcmFsRXhwcmVzc2lvbiB7XG4gIGNvbnN0IGlzTWlncmF0YWJsZU1ldGhvZCA9IG1ldGhvZENvbmZpZy5oYXMobWV0aG9kTmFtZSk7XG5cbiAgaWYgKCFpc01pZ3JhdGFibGVNZXRob2QpIHtcbiAgICB0aHJvdyBFcnJvcihgQXR0ZW1wdGluZyB0byBtaWdyYXRlIHVuY29uZmlndXJlZCBtZXRob2QgY2FsbGVkICR7bWV0aG9kTmFtZX0uYCk7XG4gIH1cblxuXG4gIGNvbnN0IHByb3BlcnRpZXNUb0tlZXA6IHRzLk9iamVjdExpdGVyYWxFbGVtZW50TGlrZVtdID0gW107XG4gIGxldCBwcm9wZXJ0eVRvTWlncmF0ZTogdHMuUHJvcGVydHlBc3NpZ25tZW50fHRzLlNob3J0aGFuZFByb3BlcnR5QXNzaWdubWVudHx1bmRlZmluZWQgPSB1bmRlZmluZWQ7XG5cbiAgZm9yIChjb25zdCBwcm9wZXJ0eSBvZiBub2RlLnByb3BlcnRpZXMpIHtcbiAgICAvLyBPbmx5IGxvb2sgZm9yIHJlZ3VsYXIgYW5kIHNob3J0aGFuZCBwcm9wZXJ0eSBhc3NpZ25tZW50cyBzaW5jZSByZXNvbHZpbmcgdGhpbmdzXG4gICAgLy8gbGlrZSBzcHJlYWQgb3BlcmF0b3JzIGJlY29tZXMgdG9vIGNvbXBsaWNhdGVkIGZvciB0aGlzIG1pZ3JhdGlvbi5cbiAgICBpZiAoKHRzLmlzUHJvcGVydHlBc3NpZ25tZW50KHByb3BlcnR5KSB8fCB0cy5pc1Nob3J0aGFuZFByb3BlcnR5QXNzaWdubWVudChwcm9wZXJ0eSkpICYmXG4gICAgICAgICh0cy5pc1N0cmluZ0xpdGVyYWxMaWtlKHByb3BlcnR5Lm5hbWUpIHx8IHRzLmlzTnVtZXJpY0xpdGVyYWwocHJvcGVydHkubmFtZSkgfHxcbiAgICAgICAgIHRzLmlzSWRlbnRpZmllcihwcm9wZXJ0eS5uYW1lKSkgJiZcbiAgICAgICAgKHByb3BlcnR5Lm5hbWUudGV4dCA9PT0gcHJlc2VydmVRdWVyeVBhcmFtc0tleSkpIHtcbiAgICAgIHByb3BlcnR5VG9NaWdyYXRlID0gcHJvcGVydHk7XG4gICAgICBjb250aW51ZTtcbiAgICB9XG4gICAgcHJvcGVydGllc1RvS2VlcC5wdXNoKHByb3BlcnR5KTtcbiAgfVxuXG4gIC8vIERvbid0IG1vZGlmeSB0aGUgbm9kZSBpZiB0aGVyZSdzIG5vdGhpbmcgdG8gbWlncmF0ZS5cbiAgaWYgKHByb3BlcnR5VG9NaWdyYXRlID09PSB1bmRlZmluZWQpIHtcbiAgICByZXR1cm4gbm9kZTtcbiAgfVxuXG4gIGlmICgodHMuaXNTaG9ydGhhbmRQcm9wZXJ0eUFzc2lnbm1lbnQocHJvcGVydHlUb01pZ3JhdGUpICYmXG4gICAgICAgcHJvcGVydHlUb01pZ3JhdGUub2JqZWN0QXNzaWdubWVudEluaXRpYWxpemVyPy5raW5kID09PSB0cy5TeW50YXhLaW5kLlRydWVLZXl3b3JkKSB8fFxuICAgICAgKHRzLmlzUHJvcGVydHlBc3NpZ25tZW50KHByb3BlcnR5VG9NaWdyYXRlKSAmJlxuICAgICAgIHByb3BlcnR5VG9NaWdyYXRlLmluaXRpYWxpemVyLmtpbmQgPT09IHRzLlN5bnRheEtpbmQuVHJ1ZUtleXdvcmQpKSB7XG4gICAgcmV0dXJuIHRzLnVwZGF0ZU9iamVjdExpdGVyYWwoXG4gICAgICAgIG5vZGUsXG4gICAgICAgIHByb3BlcnRpZXNUb0tlZXAuY29uY2F0KFxuICAgICAgICAgICAgdHMuY3JlYXRlUHJvcGVydHlBc3NpZ25tZW50KCdxdWVyeVBhcmFtc0hhbmRsaW5nJywgdHMuY3JlYXRlSWRlbnRpZmllcihgJ3ByZXNlcnZlJ2ApKSkpO1xuICB9XG5cbiAgcmV0dXJuIHRzLnVwZGF0ZU9iamVjdExpdGVyYWwobm9kZSwgcHJvcGVydGllc1RvS2VlcCk7XG59XG5cbmV4cG9ydCBmdW5jdGlvbiBmaW5kTGl0ZXJhbHNUb01pZ3JhdGUoc291cmNlRmlsZTogdHMuU291cmNlRmlsZSwgdHlwZUNoZWNrZXI6IHRzLlR5cGVDaGVja2VyKSB7XG4gIGNvbnN0IHJlc3VsdHMgPSBuZXcgTWFwPHN0cmluZywgU2V0PHRzLk9iamVjdExpdGVyYWxFeHByZXNzaW9uPj4oXG4gICAgICBBcnJheS5mcm9tKG1ldGhvZENvbmZpZy5rZXlzKCksIGtleSA9PiBba2V5LCBuZXcgU2V0KCldKSk7XG4gIGNvbnN0IHJvdXRlckltcG9ydCA9IGdldEltcG9ydFNwZWNpZmllcihzb3VyY2VGaWxlLCAnQGFuZ3VsYXIvcm91dGVyJywgJ1JvdXRlcicpO1xuICBjb25zdCBzZWVuTGl0ZXJhbHMgPSBuZXcgTWFwPHRzLk9iamVjdExpdGVyYWxFeHByZXNzaW9uLCBzdHJpbmc+KCk7XG5cbiAgaWYgKHJvdXRlckltcG9ydCkge1xuICAgIHNvdXJjZUZpbGUuZm9yRWFjaENoaWxkKGZ1bmN0aW9uIHZpc2l0Tm9kZShub2RlOiB0cy5Ob2RlKSB7XG4gICAgICAvLyBMb29rIGZvciBjYWxscyB0aGF0IGxvb2sgbGlrZSBgZm9vLjxtZXRob2QgdG8gbWlncmF0ZT5gIHdpdGggbW9yZSB0aGFuIG9uZSBwYXJhbWV0ZXIuXG4gICAgICBpZiAodHMuaXNDYWxsRXhwcmVzc2lvbihub2RlKSAmJiBub2RlLmFyZ3VtZW50cy5sZW5ndGggPiAxICYmXG4gICAgICAgICAgdHMuaXNQcm9wZXJ0eUFjY2Vzc0V4cHJlc3Npb24obm9kZS5leHByZXNzaW9uKSAmJiB0cy5pc0lkZW50aWZpZXIobm9kZS5leHByZXNzaW9uLm5hbWUpICYmXG4gICAgICAgICAgbWV0aG9kQ29uZmlnLmhhcyhub2RlLmV4cHJlc3Npb24ubmFtZS50ZXh0KSkge1xuICAgICAgICAvLyBDaGVjayB3aGV0aGVyIHRoZSB0eXBlIG9mIHRoZSBvYmplY3Qgb24gd2hpY2ggdGhlXG4gICAgICAgIC8vIGZ1bmN0aW9uIGlzIGNhbGxlZCByZWZlcnMgdG8gdGhlIFJvdXRlciBpbXBvcnQuXG4gICAgICAgIGlmIChpc1JlZmVyZW5jZVRvSW1wb3J0KHR5cGVDaGVja2VyLCBub2RlLmV4cHJlc3Npb24uZXhwcmVzc2lvbiwgcm91dGVySW1wb3J0KSkge1xuICAgICAgICAgIGNvbnN0IG1ldGhvZE5hbWUgPSBub2RlLmV4cHJlc3Npb24ubmFtZS50ZXh0O1xuICAgICAgICAgIGNvbnN0IHBhcmFtZXRlckRlY2xhcmF0aW9uID1cbiAgICAgICAgICAgICAgdHlwZUNoZWNrZXIuZ2V0VHlwZUF0TG9jYXRpb24obm9kZS5hcmd1bWVudHNbMV0pLmdldFN5bWJvbCgpPy52YWx1ZURlY2xhcmF0aW9uO1xuXG4gICAgICAgICAgLy8gRmluZCB0aGUgc291cmNlIG9mIHRoZSBvYmplY3QgbGl0ZXJhbC5cbiAgICAgICAgICBpZiAocGFyYW1ldGVyRGVjbGFyYXRpb24gJiYgdHMuaXNPYmplY3RMaXRlcmFsRXhwcmVzc2lvbihwYXJhbWV0ZXJEZWNsYXJhdGlvbikpIHtcbiAgICAgICAgICAgIGlmICghc2VlbkxpdGVyYWxzLmhhcyhwYXJhbWV0ZXJEZWNsYXJhdGlvbikpIHtcbiAgICAgICAgICAgICAgcmVzdWx0cy5nZXQobWV0aG9kTmFtZSkhLmFkZChwYXJhbWV0ZXJEZWNsYXJhdGlvbik7XG4gICAgICAgICAgICAgIHNlZW5MaXRlcmFscy5zZXQocGFyYW1ldGVyRGVjbGFyYXRpb24sIG1ldGhvZE5hbWUpO1xuICAgICAgICAgICAgICAvLyBJZiB0aGUgc2FtZSBsaXRlcmFsIGhhcyBiZWVuIHBhc3NlZCBpbnRvIG11bHRpcGxlIGRpZmZlcmVudCBtZXRob2RzLCB3ZSBjYW4ndFxuICAgICAgICAgICAgICAvLyBtaWdyYXRlIGl0LCBiZWNhdXNlIHRoZSBzdXBwb3J0ZWQgcHJvcGVydGllcyBhcmUgZGlmZmVyZW50LiBXaGVuIHdlIGRldGVjdCBzdWNoXG4gICAgICAgICAgICAgIC8vIGEgY2FzZSwgd2UgZHJvcCBpdCBmcm9tIHRoZSByZXN1bHRzIHNvIHRoYXQgaXQgZ2V0cyBpZ25vcmVkLiBJZiBpdCdzIHVzZWQgbXVsdGlwbGVcbiAgICAgICAgICAgICAgLy8gdGltZXMgZm9yIHRoZSBzYW1lIG1ldGhvZCwgaXQgY2FuIHN0aWxsIGJlIG1pZ3JhdGVkLlxuICAgICAgICAgICAgfSBlbHNlIGlmIChzZWVuTGl0ZXJhbHMuZ2V0KHBhcmFtZXRlckRlY2xhcmF0aW9uKSAhPT0gbWV0aG9kTmFtZSkge1xuICAgICAgICAgICAgICByZXN1bHRzLmZvckVhY2gobGl0ZXJhbHMgPT4gbGl0ZXJhbHMuZGVsZXRlKHBhcmFtZXRlckRlY2xhcmF0aW9uKSk7XG4gICAgICAgICAgICB9XG4gICAgICAgICAgfVxuICAgICAgICB9XG4gICAgICB9IGVsc2Uge1xuICAgICAgICBub2RlLmZvckVhY2hDaGlsZCh2aXNpdE5vZGUpO1xuICAgICAgfVxuICAgIH0pO1xuICB9XG5cbiAgcmV0dXJuIHJlc3VsdHM7XG59XG4iXX0=