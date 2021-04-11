(function (factory) {
    if (typeof module === "object" && typeof module.exports === "object") {
        var v = factory(require, exports);
        if (v !== undefined) module.exports = v;
    }
    else if (typeof define === "function" && define.amd) {
        define("@angular/core/schematics/migrations/initial-navigation/collector", ["require", "exports", "typescript", "@angular/core/schematics/migrations/initial-navigation/util"], factory);
    }
})(function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.InitialNavigationCollector = void 0;
    /**
     * @license
     * Copyright Google LLC All Rights Reserved.
     *
     * Use of this source code is governed by an MIT-style license that can be
     * found in the LICENSE file at https://angular.io/license
     */
    const ts = require("typescript");
    const util_1 = require("@angular/core/schematics/migrations/initial-navigation/util");
    /** The property name for the options that need to be migrated */
    const INITIAL_NAVIGATION = 'initialNavigation';
    /**
     * Visitor that walks through specified TypeScript nodes and collects all
     * found ExtraOptions#InitialNavigation assignments.
     */
    class InitialNavigationCollector {
        constructor(typeChecker) {
            this.typeChecker = typeChecker;
            this.assignments = new Set();
        }
        visitNode(node) {
            let extraOptionsLiteral = null;
            if (util_1.isRouterModuleForRoot(this.typeChecker, node) && node.arguments.length > 0) {
                if (node.arguments.length === 1) {
                    return;
                }
                if (ts.isObjectLiteralExpression(node.arguments[1])) {
                    extraOptionsLiteral = node.arguments[1];
                }
                else if (ts.isIdentifier(node.arguments[1])) {
                    extraOptionsLiteral =
                        this.getLiteralNeedingMigrationFromIdentifier(node.arguments[1]);
                }
            }
            else if (ts.isVariableDeclaration(node)) {
                extraOptionsLiteral = this.getLiteralNeedingMigration(node);
            }
            if (extraOptionsLiteral !== null) {
                this.visitExtraOptionsLiteral(extraOptionsLiteral);
            }
            else {
                // no match found, continue iteration
                ts.forEachChild(node, n => this.visitNode(n));
            }
        }
        visitExtraOptionsLiteral(extraOptionsLiteral) {
            for (const prop of extraOptionsLiteral.properties) {
                if (ts.isPropertyAssignment(prop) &&
                    (ts.isIdentifier(prop.name) || ts.isStringLiteralLike(prop.name))) {
                    if (prop.name.text === INITIAL_NAVIGATION && isValidInitialNavigationValue(prop)) {
                        this.assignments.add(prop);
                    }
                }
                else if (ts.isSpreadAssignment(prop) && ts.isIdentifier(prop.expression)) {
                    const literalFromSpreadAssignment = this.getLiteralNeedingMigrationFromIdentifier(prop.expression);
                    if (literalFromSpreadAssignment !== null) {
                        this.visitExtraOptionsLiteral(literalFromSpreadAssignment);
                    }
                }
            }
        }
        getLiteralNeedingMigrationFromIdentifier(id) {
            const symbolForIdentifier = this.typeChecker.getSymbolAtLocation(id);
            if (symbolForIdentifier === undefined) {
                return null;
            }
            if (symbolForIdentifier.declarations.length === 0) {
                return null;
            }
            const declarationNode = symbolForIdentifier.declarations[0];
            if (!ts.isVariableDeclaration(declarationNode) || declarationNode.initializer === undefined ||
                !ts.isObjectLiteralExpression(declarationNode.initializer)) {
                return null;
            }
            return declarationNode.initializer;
        }
        getLiteralNeedingMigration(node) {
            if (node.initializer === undefined) {
                return null;
            }
            // declaration could be `x: ExtraOptions = {}` or `x = {} as ExtraOptions`
            if (ts.isAsExpression(node.initializer) &&
                ts.isObjectLiteralExpression(node.initializer.expression) &&
                util_1.isExtraOptions(this.typeChecker, node.initializer.type)) {
                return node.initializer.expression;
            }
            else if (node.type !== undefined && ts.isObjectLiteralExpression(node.initializer) &&
                util_1.isExtraOptions(this.typeChecker, node.type)) {
                return node.initializer;
            }
            return null;
        }
    }
    exports.InitialNavigationCollector = InitialNavigationCollector;
    /**
     * Check whether the value assigned to an `initialNavigation` assignment
     * conforms to the expected types for ExtraOptions#InitialNavigation
     * @param node the property assignment to check
     */
    function isValidInitialNavigationValue(node) {
        return ts.isStringLiteralLike(node.initializer) ||
            node.initializer.kind === ts.SyntaxKind.FalseKeyword ||
            node.initializer.kind === ts.SyntaxKind.TrueKeyword;
    }
});
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiY29sbGVjdG9yLmpzIiwic291cmNlUm9vdCI6IiIsInNvdXJjZXMiOlsiLi4vLi4vLi4vLi4vLi4vLi4vLi4vLi4vcGFja2FnZXMvY29yZS9zY2hlbWF0aWNzL21pZ3JhdGlvbnMvaW5pdGlhbC1uYXZpZ2F0aW9uL2NvbGxlY3Rvci50cyJdLCJuYW1lcyI6W10sIm1hcHBpbmdzIjoiOzs7Ozs7Ozs7Ozs7SUFBQTs7Ozs7O09BTUc7SUFDSCxpQ0FBaUM7SUFDakMsc0ZBQTZEO0lBRzdELGlFQUFpRTtJQUNqRSxNQUFNLGtCQUFrQixHQUFHLG1CQUFtQixDQUFDO0lBRS9DOzs7T0FHRztJQUNILE1BQWEsMEJBQTBCO1FBR3JDLFlBQTZCLFdBQTJCO1lBQTNCLGdCQUFXLEdBQVgsV0FBVyxDQUFnQjtZQUZqRCxnQkFBVyxHQUErQixJQUFJLEdBQUcsRUFBRSxDQUFDO1FBRUEsQ0FBQztRQUU1RCxTQUFTLENBQUMsSUFBYTtZQUNyQixJQUFJLG1CQUFtQixHQUFvQyxJQUFJLENBQUM7WUFDaEUsSUFBSSw0QkFBcUIsQ0FBQyxJQUFJLENBQUMsV0FBVyxFQUFFLElBQUksQ0FBQyxJQUFJLElBQUksQ0FBQyxTQUFTLENBQUMsTUFBTSxHQUFHLENBQUMsRUFBRTtnQkFDOUUsSUFBSSxJQUFJLENBQUMsU0FBUyxDQUFDLE1BQU0sS0FBSyxDQUFDLEVBQUU7b0JBQy9CLE9BQU87aUJBQ1I7Z0JBRUQsSUFBSSxFQUFFLENBQUMseUJBQXlCLENBQUMsSUFBSSxDQUFDLFNBQVMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFFO29CQUNuRCxtQkFBbUIsR0FBRyxJQUFJLENBQUMsU0FBUyxDQUFDLENBQUMsQ0FBK0IsQ0FBQztpQkFDdkU7cUJBQU0sSUFBSSxFQUFFLENBQUMsWUFBWSxDQUFDLElBQUksQ0FBQyxTQUFTLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBRTtvQkFDN0MsbUJBQW1CO3dCQUNmLElBQUksQ0FBQyx3Q0FBd0MsQ0FBQyxJQUFJLENBQUMsU0FBUyxDQUFDLENBQUMs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V0FBVyxDQUFDLFVBQVUsQ0FBQzthQUNwQztpQkFBTSxJQUNILElBQUksQ0FBQyxJQUFJLEtBQUssU0FBUyxJQUFJLEVBQUUsQ0FBQyx5QkFBeUIsQ0FBQyxJQUFJLENBQUMsV0FBVyxDQUFDO2dCQUN6RSxxQkFBYyxDQUFDLElBQUksQ0FBQyxXQUFXLEVBQUUsSUFBSSxDQUFDLElBQUksQ0FBQyxFQUFFO2dCQUMvQyxPQUFPLElBQUksQ0FBQyxXQUFXLENBQUM7YUFDekI7WUFFRCxPQUFPLElBQUksQ0FBQztRQUNkLENBQUM7S0FDRjtJQXRGRCxnRUFzRkM7SUFFRDs7OztPQUlHO0lBQ0gsU0FBUyw2QkFBNkIsQ0FBQyxJQUEyQjtRQUNoRSxPQUFPLEVBQUUsQ0FBQyxtQkFBbUIsQ0FBQyxJQUFJLENBQUMsV0FBVyxDQUFDO1lBQzNDLElBQUksQ0FBQyxXQUFXLENBQUMsSUFBSSxLQUFLLEVBQUUsQ0FBQyxVQUFVLENBQUMsWUFBWTtZQUNwRCxJQUFJLENBQUMsV0FBVyxDQUFDLElBQUksS0FBSyxFQUFFLENBQUMsVUFBVSxDQUFDLFdBQVcsQ0FBQztJQUMxRCxDQUFDIiwic291cmNlc0NvbnRlbnQiOlsiLyoqXG4gKiBAbGljZW5zZVxuICogQ29weXJpZ2h0IEdvb2dsZSBMTEMgQWxsIFJpZ2h0cyBSZXNlcnZlZC5cbiAqXG4gKiBVc2Ugb2YgdGhpcyBzb3VyY2UgY29kZSBpcyBnb3Zlcm5lZCBieSBhbiBNSVQtc3R5bGUgbGljZW5zZSB0aGF0IGNhbiBiZVxuICogZm91bmQgaW4gdGhlIExJQ0VOU0UgZmlsZSBhdCBodHRwczovL2FuZ3VsYXIuaW8vbGljZW5zZVxuICovXG5pbXBvcnQgKiBhcyB0cyBmcm9tICd0eXBlc2NyaXB0JztcbmltcG9ydCB7aXNFeHRyYU9wdGlvbnMsIGlzUm91dGVyTW9kdWxlRm9yUm9vdH0gZnJvbSAnLi91dGlsJztcblxuXG4vKiogVGhlIHByb3BlcnR5IG5hbWUgZm9yIHRoZSBvcHRpb25zIHRoYXQgbmVlZCB0byBiZSBtaWdyYXRlZCAqL1xuY29uc3QgSU5JVElBTF9OQVZJR0FUSU9OID0gJ2luaXRpYWxOYXZpZ2F0aW9uJztcblxuLyoqXG4gKiBWaXNpdG9yIHRoYXQgd2Fsa3MgdGhyb3VnaCBzcGVjaWZpZWQgVHlwZVNjcmlwdCBub2RlcyBhbmQgY29sbGVjdHMgYWxsXG4gKiBmb3VuZCBFeHRyYU9wdGlvbnMjSW5pdGlhbE5hdmlnYXRpb24gYXNzaWdubWVudHMuXG4gKi9cbmV4cG9ydCBjbGFzcyBJbml0aWFsTmF2aWdhdGlvbkNvbGxlY3RvciB7XG4gIHB1YmxpYyBhc3NpZ25tZW50czogU2V0PHRzLlByb3BlcnR5QXNzaWdubWVudD4gPSBuZXcgU2V0KCk7XG5cbiAgY29uc3RydWN0b3IocHJpdmF0ZSByZWFkb25seSB0eXBlQ2hlY2tlcjogdHMuVHlwZUNoZWNrZXIpIHt9XG5cbiAgdmlzaXROb2RlKG5vZGU6IHRzLk5vZGUpIHtcbiAgICBsZXQgZXh0cmFPcHRpb25zTGl0ZXJhbDogdHMuT2JqZWN0TGl0ZXJhbEV4cHJlc3Npb258bnVsbCA9IG51bGw7XG4gICAgaWYgKGlzUm91dGVyTW9kdWxlRm9yUm9vdCh0aGlzLnR5cGVDaGVja2VyLCBub2RlKSAmJiBub2RlLmFyZ3VtZW50cy5sZW5ndGggPiAwKSB7XG4gICAgICBpZiAobm9kZS5hcmd1bWVudHMubGVuZ3RoID09PSAxKSB7XG4gICAgICAgIHJldHVybjtcbiAgICAgIH1cblxuICAgICAgaWYgKHRzLmlzT2JqZWN0TGl0ZXJhbEV4cHJlc3Npb24obm9kZS5hcmd1bWVudHNbMV0pKSB7XG4gICAgICAgIGV4dHJhT3B0aW9uc0xpdGVyYWwgPSBub2RlLmFyZ3VtZW50c1sxXSBhcyB0cy5PYmplY3RMaXRlcmFsRXhwcmVzc2lvbjtcbiAgICAgIH0gZWxzZSBpZiAodHMuaXNJZGVudGlmaWVyKG5vZGUuYXJndW1lbnRzWzFdKSkge1xuICAgICAgICBleHRyYU9wdGlvbnNMaXRlcmFsID1cbiAgICAgICAgICAgIHRoaXMuZ2V0TGl0ZXJhbE5lZWRpbmdNaWdyYXRpb25Gcm9tSWRlbnRpZmllcihub2RlLmFyZ3VtZW50c1sxXSBhcyB0cy5JZGVudGlmaWVyKTtcbiAgICAgIH1cbiAgICB9IGVsc2UgaWYgKHRzLmlzVmFyaWFibGVEZWNsYXJhdGlvbihub2RlKSkge1xuICAgICAgZXh0cmFPcHRpb25zTGl0ZXJhbCA9IHRoaXMuZ2V0TGl0ZXJhbE5lZWRpbmdNaWdyYXRpb24obm9kZSk7XG4gICAgfVxuXG4gICAgaWYgKGV4dHJhT3B0aW9uc0xpdGVyYWwgIT09IG51bGwpIHtcbiAgICAgIHRoaXMudmlzaXRFeHRyYU9wdGlvbnNMaXRlcmFsKGV4dHJhT3B0aW9uc0xpdGVyYWwpO1xuICAgIH0gZWxzZSB7XG4gICAgICAvLyBubyBtYXRjaCBmb3VuZCwgY29udGludWUgaXRlcmF0aW9uXG4gICAgICB0cy5mb3JFYWNoQ2hpbGQobm9kZSwgbiA9PiB0aGlzLnZpc2l0Tm9kZShuKSk7XG4gICAgfVxuICB9XG5cbiAgdmlzaXRFeHRyYU9wdGlvbnNMaXRlcmFsKGV4dHJhT3B0aW9uc0xpdGVyYWw6IHRzLk9iamVjdExpdGVyYWxFeHByZXNzaW9uKSB7XG4gICAgZm9yIChjb25zdCBwcm9wIG9mIGV4dHJhT3B0aW9uc0xpdGVyYWwucHJvcGVydGllcykge1xuICAgICAgaWYgKHRzLmlzUHJvcGVydHlBc3NpZ25tZW50KHByb3ApICYmXG4gICAgICAgICAgKHRzLmlzSWRlbnRpZmllcihwcm9wLm5hbWUpIHx8IHRzLmlzU3RyaW5nTGl0ZXJhbExpa2UocHJvcC5uYW1lKSkpIHtcbiAgICAgICAgaWYgKHByb3AubmFtZS50ZXh0ID09PSBJTklUSUFMX05BVklHQVRJT04gJiYgaXNWYWxpZEluaXRpYWxOYXZpZ2F0aW9uVmFsdWUocHJvcCkpIHtcbiAgICAgICAgICB0aGlzLmFzc2lnbm1lbnRzLmFkZChwcm9wKTtcbiAgICAgICAgfVxuICAgICAgfSBlbHNlIGlmICh0cy5pc1NwcmVhZEFzc2lnbm1lbnQocHJvcCkgJiYgdHMuaXNJZGVudGlmaWVyKHByb3AuZXhwcmVzc2lvbikpIHtcbiAgICAgICAgY29uc3QgbGl0ZXJhbEZyb21TcHJlYWRBc3NpZ25tZW50ID1cbiAgICAgICAgICAgIHRoaXMuZ2V0TGl0ZXJhbE5lZWRpbmdNaWdyYXRpb25Gcm9tSWRlbnRpZmllcihwcm9wLmV4cHJlc3Npb24pO1xuICAgICAgICBpZiAobGl0ZXJhbEZyb21TcHJlYWRBc3NpZ25tZW50ICE9PSBudWxsKSB7XG4gICAgICAgICAgdGhpcy52aXNpdEV4dHJhT3B0aW9uc0xpdGVyYWwobGl0ZXJhbEZyb21TcHJlYWRBc3NpZ25tZW50KTtcbiAgICAgICAgfVxuICAgICAgfVxuICAgIH1cbiAgfVxuXG4gIHByaXZhdGUgZ2V0TGl0ZXJhbE5lZWRpbmdNaWdyYXRpb25Gcm9tSWRlbnRpZmllcihpZDogdHMuSWRlbnRpZmllcik6IHRzLk9iamVjdExpdGVyYWxFeHByZXNzaW9uXG4gICAgICB8bnVsbCB7XG4gICAgY29uc3Qgc3ltYm9sRm9ySWRlbnRpZmllciA9IHRoaXMudHlwZUNoZWNrZXIuZ2V0U3ltYm9sQXRMb2NhdGlvbihpZCk7XG4gICAgaWYgKHN5bWJvbEZvcklkZW50aWZpZXIgPT09IHVuZGVmaW5lZCkge1xuICAgICAgcmV0dXJuIG51bGw7XG4gICAgfVxuXG4gICAgaWYgKHN5bWJvbEZvcklkZW50aWZpZXIuZGVjbGFyYXRpb25zLmxlbmd0aCA9PT0gMCkge1xuICAgICAgcmV0dXJuIG51bGw7XG4gICAgfVxuXG4gICAgY29uc3QgZGVjbGFyYXRpb25Ob2RlID0gc3ltYm9sRm9ySWRlbnRpZmllci5kZWNsYXJhdGlvbnNbMF07XG4gICAgaWYgKCF0cy5pc1ZhcmlhYmxlRGVjbGFyYXRpb24oZGVjbGFyYXRpb25Ob2RlKSB8fCBkZWNsYXJhdGlvbk5vZGUuaW5pdGlhbGl6ZXIgPT09IHVuZGVmaW5lZCB8fFxuICAgICAgICAhdHMuaXNPYmplY3RMaXRlcmFsRXhwcmVzc2lvbihkZWNsYXJhdGlvbk5vZGUuaW5pdGlhbGl6ZXIpKSB7XG4gICAgICByZXR1cm4gbnVsbDtcbiAgICB9XG5cbiAgICByZXR1cm4gZGVjbGFyYXRpb25Ob2RlLmluaXRpYWxpemVyO1xuICB9XG5cbiAgcHJpdmF0ZSBnZXRMaXRlcmFsTmVlZGluZ01pZ3JhdGlvbihub2RlOiB0cy5WYXJpYWJsZURlY2xhcmF0aW9uKTogdHMuT2JqZWN0TGl0ZXJhbEV4cHJlc3Npb25cbiAgICAgIHxudWxsIHtcbiAgICBpZiAobm9kZS5pbml0aWFsaXplciA9PT0gdW5kZWZpbmVkKSB7XG4gICAgICByZXR1cm4gbnVsbDtcbiAgICB9XG5cbiAgICAvLyBkZWNsYXJhdGlvbiBjb3VsZCBiZSBgeDogRXh0cmFPcHRpb25zID0ge31gIG9yIGB4ID0ge30gYXMgRXh0cmFPcHRpb25zYFxuICAgIGlmICh0cy5pc0FzRXhwcmVzc2lvbihub2RlLmluaXRpYWxpemVyKSAmJlxuICAgICAgICB0cy5pc09iamVjdExpdGVyYWxFeHByZXNzaW9uKG5vZGUuaW5pdGlhbGl6ZXIuZXhwcmVzc2lvbikgJiZcbiAgICAgICAgaXNFeHRyYU9wdGlvbnModGhpcy50eXBlQ2hlY2tlciwgbm9kZS5pbml0aWFsaXplci50eXBlKSkge1xuICAgICAgcmV0dXJuIG5vZGUuaW5pdGlhbGl6ZXIuZXhwcmVzc2lvbjtcbiAgICB9IGVsc2UgaWYgKFxuICAgICAgICBub2RlLnR5cGUgIT09IHVuZGVmaW5lZCAmJiB0cy5pc09iamVjdExpdGVyYWxFeHByZXNzaW9uKG5vZGUuaW5pdGlhbGl6ZXIpICYmXG4gICAgICAgIGlzRXh0cmFPcHRpb25zKHRoaXMudHlwZUNoZWNrZXIsIG5vZGUudHlwZSkpIHtcbiAgICAgIHJldHVybiBub2RlLmluaXRpYWxpemVyO1xuICAgIH1cblxuICAgIHJldHVybiBudWxsO1xuICB9XG59XG5cbi8qKlxuICogQ2hlY2sgd2hldGhlciB0aGUgdmFsdWUgYXNzaWduZWQgdG8gYW4gYGluaXRpYWxOYXZpZ2F0aW9uYCBhc3NpZ25tZW50XG4gKiBjb25mb3JtcyB0byB0aGUgZXhwZWN0ZWQgdHlwZXMgZm9yIEV4dHJhT3B0aW9ucyNJbml0aWFsTmF2aWdhdGlvblxuICogQHBhcmFtIG5vZGUgdGhlIHByb3BlcnR5IGFzc2lnbm1lbnQgdG8gY2hlY2tcbiAqL1xuZnVuY3Rpb24gaXNWYWxpZEluaXRpYWxOYXZpZ2F0aW9uVmFsdWUobm9kZTogdHMuUHJvcGVydHlBc3NpZ25tZW50KTogYm9vbGVhbiB7XG4gIHJldHVybiB0cy5pc1N0cmluZ0xpdGVyYWxMaWtlKG5vZGUuaW5pdGlhbGl6ZXIpIHx8XG4gICAgICBub2RlLmluaXRpYWxpemVyLmtpbmQgPT09IHRzLlN5bnRheEtpbmQuRmFsc2VLZXl3b3JkIHx8XG4gICAgICBub2RlLmluaXRpYWxpemVyLmtpbmQgPT09IHRzLlN5bnRheEtpbmQuVHJ1ZUtleXdvcmQ7XG59XG4iXX0=