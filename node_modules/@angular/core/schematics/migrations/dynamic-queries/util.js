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
        define("@angular/core/schematics/migrations/dynamic-queries/util", ["require", "exports", "typescript", "@angular/core/schematics/utils/ng_decorators"], factory);
    }
})(function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.removeStaticFlag = exports.removeOptionsParameter = exports.identifyDynamicQueryNodes = void 0;
    const ts = require("typescript");
    const ng_decorators_1 = require("@angular/core/schematics/utils/ng_decorators");
    /**
     * Identifies the nodes that should be migrated by the dynamic
     * queries schematic. Splits the nodes into the following categories:
     * - `removeProperty` - queries from which we should only remove the `static` property of the
     *  `options` parameter (e.g. `@ViewChild('child', {static: false, read: ElementRef})`).
     * - `removeParameter` - queries from which we should drop the entire `options` parameter.
     *  (e.g. `@ViewChild('child', {static: false})`).
     */
    function identifyDynamicQueryNodes(typeChecker, sourceFile) {
        const removeProperty = [];
        const removeParameter = [];
        sourceFile.forEachChild(function walk(node) {
            if (ts.isClassDeclaration(node)) {
                node.members.forEach(member => {
                    const angularDecorators = member.decorators && ng_decorators_1.getAngularDecorators(typeChecker, member.decorators);
                    if (angularDecorators) {
                        angularDecorators
                            // Filter out the queries that can have the `static` flag.
                            .filter(decorator => {
                            return decorator.name === 'ViewChild' || decorator.name === 'ContentChild';
                        })
                            // Filter out the queries where the `static` flag is explicitly set to `false`.
                            .filter(decorator => {
                            const options = decorator.node.expression.arguments[1];
                            return options && ts.isObjectLiteralExpression(options) &&
                                options.properties.some(property => ts.isPropertyAssignment(property) &&
                                    property.initializer.kind === ts.SyntaxKind.FalseKeyword);
                        })
                            .forEach(decorator => {
                            const options = decorator.node.expression.arguments[1];
                            // At this point we know that at least one property is the `static` flag. If this is
                            // the only property we can drop the entire object literal, otherwise we have to
                            // drop only the property.
                            if (options.properties.length === 1) {
                                removeParameter.push(decorator.node.expression);
                            }
                            else {
                                removeProperty.push(options);
                            }
                        });
                    }
                });
            }
            node.forEachChild(walk);
        });
        return { removeProperty, removeParameter };
    }
    exports.identifyDynamicQueryNodes = identifyDynamicQueryNodes;
    /** Removes the `options` parameter from the call expression of a query decorator. */
    function removeOptionsParameter(node) {
        return ts.updateCall(node, node.expression, node.typeArguments, [node.arguments[0]]);
    }
    exports.removeOptionsParameter = removeOptionsParameter;
    /** Removes the `static` property from an object literal expression. */
    function removeStaticFlag(node) {
        return ts.updateObjectLiteral(node, node.properties.filter(property => property.name && property.name.getText() !== 'static'));
    }
    exports.removeStaticFlag = removeStaticFlag;
});
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoidXRpbC5qcyIsInNvdXJjZVJvb3QiOiIiLCJzb3VyY2VzIjpbIi4uLy4uLy4uLy4uLy4uLy4uLy4uLy4uL3BhY2thZ2VzL2NvcmUvc2NoZW1hdGljcy9taWdyYXRpb25zL2R5bmFtaWMtcXVlcmllcy91dGlsLnRzIl0sIm5hbWVzIjpbXSwibWFwcGluZ3MiOiJBQUFBOzs7Ozs7R0FNRzs7Ozs7Ozs7Ozs7OztJQUVILGlDQUFpQztJQUNqQyxnRkFBK0Q7SUFFL0Q7Ozs7Ozs7T0FPRztJQUNILFNBQWdCLHlCQUF5QixDQUFDLFdBQTJCLEVBQUUsVUFBeUI7UUFDOUYsTUFBTSxjQUFjLEdBQWlDLEVBQUUsQ0FBQztRQUN4RCxNQUFNLGVBQWUsR0FBd0IsRUFBRSxDQUFDO1FBRWhELFVBQVUsQ0FBQyxZQUFZLENBQUMsU0FBUyxJQUFJLENBQUMsSUFBYTtZQUNqRCxJQUFJLEVBQUUsQ0FBQyxrQkFBa0IsQ0FBQyxJQUFJLENBQUMsRUFBRTtnQkFDL0IsSUFBSSxDQUFDLE9BQU8sQ0FBQyxPQUFPLENBQUMsTUFBTSxDQUFDLEVBQUU7b0JBQzVCLE1BQU0saUJBQWlCLEdBQ25CLE1BQU0sQ0FBQyxVQUFVLElBQUksb0NBQW9CLENBQUMsV0FBVyxFQUFFLE1BQU0sQ0FBQyxVQUFVLENBQUMsQ0FBQztvQkFFOUUsSUFBSSxpQkFBaUIsRUFBRTt3QkFDckIsaUJBQWlCOzRCQUNiLDBEQUEwRDs2QkFDekQsTUFBTSxDQUFDLFNBQVMsQ0FBQyxFQUFFOzRCQUNsQixPQUFPLFNBQVMsQ0FBQyxJQUFJLEtBQUssV0FBVyxJQUFJLFNBQVMsQ0FBQyxJQUFJLEtBQUssY0FBYyxDQUFDO3dCQUM3RSxDQUFDLENBQUM7NEJBQ0YsK0VBQStFOzZCQUM5RSxNQUFNLENBQUMsU0FBUyxDQUFDLEVBQUU7NEJBQ2xCLE1BQU0sT0FBTyxHQUFHLFNBQVMsQ0FBQyxJQUFJLENBQUMsVUFBVSxDQUFDLFNBQVMsQ0FBQyxDQUFDLENBQUMsQ0FBQzs0QkFDdkQsT0FBTyxPQUFPLElBQUksRUFBRSxDQUFDLHlCQUF5QixDQUFDLE9BQU8sQ0FBQztnQ0FDbkQsT0FBTyxDQUFDLFVBQVUsQ0FBQyxJQUFJLENBQ25CLFFBQVEsQ0FBQyxFQUFFLENBQUMsRUFBRSxDQUFDLG9CQUFvQixDQUFDLFFBQVEsQ0FBQztvQ0FDekMsUUFBUSxDQUFDLFdBQVcsQ0FBQyxJQUFJLEtBQUssRUFBRSxDQUFDLFVBQVUsQ0FBQyxZQUFZLENBQUMsQ0FBQzt3QkFDeEUsQ0FBQyxDQUFDOzZCQUNELE9BQU8sQ0FBQyxTQUFTLENBQUMsRUFBRTs0QkFDbkIsTUFBTSxPQUFPLEdBQ1QsU0FBUyxDQUFDLElBQUksQ0FBQyxVQUFVLENBQUMsU0FBUyxDQUFDLENBQUMsQ0FBK0IsQ0FBQzs0QkFFekUsb0ZBQW9GOzRCQUNwRixnRkFBZ0Y7NEJBQ2hGLDBCQUEwQjs0QkFDMUIsSUFBSSxPQUFPLENBQUMsVUFBVSxDQUFDLE1BQU0sS0FBSyxDQUFDLEVBQUU7Z0NBQ25DLGVBQWUsQ0FBQyxJQUFJLENBQUMsU0FBUyxDQUFDLElBQUksQ0FBQyxVQUFVLENBQUMsQ0FBQzs2QkFDakQ7aUNBQU07Z0NBQ0wsY0FBYyxDQUFDLElBQUksQ0FBQyxPQUFPLENBQUMsQ0FBQzs2QkFDOUI7d0JBQ0gsQ0FBQyxDQUFDLENBQUM7cUJBQ1I7Z0JBQ0gsQ0FBQyxDQUFDLENBQUM7YUFDSjtZQUVELElBQUksQ0FBQyxZQUFZLENBQUMsSUFBSSxDQUFDLENBQUM7UUFDMUIsQ0FBQyxDQUFDLENBQUM7UUFFSCxPQUFPLEVBQUMsY0FBYyxFQUFFLGVBQWUsRUFBQyxDQUFDO0lBQzNDLENBQUM7SUE3Q0QsOERBNkNDO0lBRUQscUZBQXFGO0lBQ3JGLFNBQWdCLHNCQUFzQixDQUFDLElBQXVCO1FBQzVELE9BQU8sRUFBRSxDQUFDLFVBQVUsQ0FBQyxJQUFJLEVBQUUsSUFBSSxDQUFDLFVBQVUsRUFBRSxJQUFJLENBQUMsYUFBYSxFQUFFLENBQUMsSUFBSSxDQUFDLFNBQVMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUM7SUFDdkYsQ0FBQztJQUZELHdEQUVDO0lBRUQsdUVBQXVFO0lBQ3ZFLFNBQWdCLGdCQUFnQixDQUFDLElBQWdDO1FBQy9ELE9BQU8sRUFBRSxDQUFDLG1CQUFtQixDQUN6QixJQUFJLEVBQ0osSUFBSSxDQUFDLFVBQVUsQ0FBQyxNQUFNLENBQUMsUUFBUSxDQUFDLEVBQUUsQ0FBQyxRQUFRLENBQUMsSUFBSSxJQUFJLFFBQVEsQ0FBQyxJQUFJLENBQUMsT0FBTyxFQUFFLEtBQUssUUFBUSxDQUFDLENBQUMsQ0FBQztJQUNqRyxDQUFDO0lBSkQsNENBSUMiLCJzb3VyY2VzQ29udGVudCI6WyIvKipcbiAqIEBsaWNlbnNlXG4gKiBDb3B5cmlnaHQgR29vZ2xlIExMQyBBbGwgUmlnaHRzIFJlc2VydmVkLlxuICpcbiAqIFVzZSBvZiB0aGlzIHNvdXJjZSBjb2RlIGlzIGdvdmVybmVkIGJ5IGFuIE1JVC1zdHlsZSBsaWNlbnNlIHRoYXQgY2FuIGJlXG4gKiBmb3VuZCBpbiB0aGUgTElDRU5TRSBmaWxlIGF0IGh0dHBzOi8vYW5ndWxhci5pby9saWNlbnNlXG4gKi9cblxuaW1wb3J0ICogYXMgdHMgZnJvbSAndHlwZXNjcmlwdCc7XG5pbXBvcnQge2dldEFuZ3VsYXJEZWNvcmF0b3JzfSBmcm9tICcuLi8uLi91dGlscy9uZ19kZWNvcmF0b3JzJztcblxuLyoqXG4gKiBJZGVudGlmaWVzIHRoZSBub2RlcyB0aGF0IHNob3VsZCBiZSBtaWdyYXRlZCBieSB0aGUgZHluYW1pY1xuICogcXVlcmllcyBzY2hlbWF0aWMuIFNwbGl0cyB0aGUgbm9kZXMgaW50byB0aGUgZm9sbG93aW5nIGNhdGVnb3JpZXM6XG4gKiAtIGByZW1vdmVQcm9wZXJ0eWAgLSBxdWVyaWVzIGZyb20gd2hpY2ggd2Ugc2hvdWxkIG9ubHkgcmVtb3ZlIHRoZSBgc3RhdGljYCBwcm9wZXJ0eSBvZiB0aGVcbiAqICBgb3B0aW9uc2AgcGFyYW1ldGVyIChlLmcuIGBAVmlld0NoaWxkKCdjaGlsZCcsIHtzdGF0aWM6IGZhbHNlLCByZWFkOiBFbGVtZW50UmVmfSlgKS5cbiAqIC0gYHJlbW92ZVBhcmFtZXRlcmAgLSBxdWVyaWVzIGZyb20gd2hpY2ggd2Ugc2hvdWxkIGRyb3AgdGhlIGVudGlyZSBgb3B0aW9uc2AgcGFyYW1ldGVyLlxuICogIChlLmcuIGBAVmlld0NoaWxkKCdjaGlsZCcsIHtzdGF0aWM6IGZhbHNlfSlgKS5cbiAqL1xuZXhwb3J0IGZ1bmN0aW9uIGlkZW50aWZ5RHluYW1pY1F1ZXJ5Tm9kZXModHlwZUNoZWNrZXI6IHRzLlR5cGVDaGVja2VyLCBzb3VyY2VGaWxlOiB0cy5Tb3VyY2VGaWxlKSB7XG4gIGNvbnN0IHJlbW92ZVByb3BlcnR5OiB0cy5PYmplY3RMaXRlcmFsRXhwcmVzc2lvbltdID0gW107XG4gIGNvbnN0IHJlbW92ZVBhcmFtZXRlcjogdHMuQ2FsbEV4cHJlc3Npb25bXSA9IFtdO1xuXG4gIHNvdXJjZUZpbGUuZm9yRWFjaENoaWxkKGZ1bmN0aW9uIHdhbGsobm9kZTogdHMuTm9kZSkge1xuICAgIGlmICh0cy5pc0NsYXNzRGVjbGFyYXRpb24obm9kZSkpIHtcbiAgICAgIG5vZGUubWVtYmVycy5mb3JFYWNoKG1lbWJlciA9PiB7XG4gICAgICAgIGNvbnN0IGFuZ3VsYXJEZWNvcmF0b3JzID1cbiAgICAgICAgICAgIG1lbWJlci5kZWNvcmF0b3JzICYmIGdldEFuZ3VsYXJEZWNvcmF0b3JzKHR5cGVDaGVja2VyLCBtZW1iZXIuZGVjb3JhdG9ycyk7XG5cbiAgICAgICAgaWYgKGFuZ3VsYXJEZWNvcmF0b3JzKSB7XG4gICAgICAgICAgYW5ndWxhckRlY29yYXRvcnNcbiAgICAgICAgICAgICAgLy8gRmlsdGVyIG91dCB0aGUgcXVlcmllcyB0aGF0IGNhbiBoYXZlIHRoZSBgc3RhdGljYCBmbGFnLlxuICAgICAgICAgICAgICAuZmlsdGVyKGRlY29yYXRvciA9PiB7XG4gICAgICAgICAgICAgICAgcmV0dXJuIGRlY29yYXRvci5uYW1lID09PSAnVmlld0NoaWxkJyB8fCBkZWNvcmF0b3IubmFtZSA9PT0gJ0NvbnRlbnRDaGlsZCc7XG4gICAgICAgICAgICAgIH0pXG4gICAgICAgICAgICAgIC8vIEZpbHRlciBvdXQgdGhlIHF1ZXJpZXMgd2hlcmUgdGhlIGBzdGF0aWNgIGZsYWcgaXMgZXhwbGljaXRseSBzZXQgdG8gYGZhbHNlYC5cbiAgICAgICAgICAgICAgLmZpbHRlcihkZWNvcmF0b3IgPT4ge1xuICAgICAgICAgICAgICAgIGNvbnN0IG9wdGlvbnMgPSBkZWNvcmF0b3Iubm9kZS5leHByZXNzaW9uLmFyZ3VtZW50c1sxXTtcbiAgICAgICAgICAgICAgICByZXR1cm4gb3B0aW9ucyAmJiB0cy5pc09iamVjdExpdGVyYWxFeHByZXNzaW9uKG9wdGlvbnMpICYmXG4gICAgICAgICAgICAgICAgICAgIG9wdGlvbnMucHJvcGVydGllcy5zb21lKFxuICAgICAgICAgICAgICAgICAgICAgICAgcHJvcGVydHkgPT4gdHMuaXNQcm9wZXJ0eUFzc2lnbm1lbnQocHJvcGVydHkpICYmXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgcHJvcGVydHkuaW5pdGlhbGl6ZXIua2luZCA9PT0gdHMuU3ludGF4S2luZC5GYWxzZUtleXdvcmQpO1xuICAgICAgICAgICAgICB9KVxuICAgICAgICAgICAgICAuZm9yRWFjaChkZWNvcmF0b3IgPT4ge1xuICAgICAgICAgICAgICAgIGNvbnN0IG9wdGlvbnMgPVxuICAgICAgICAgICAgICAgICAgICBkZWNvcmF0b3Iubm9kZS5leHByZXNzaW9uLmFyZ3VtZW50c1sxXSBhcyB0cy5PYmplY3RMaXRlcmFsRXhwcmVzc2lvbjtcblxuICAgICAgICAgICAgICAgIC8vIEF0IHRoaXMgcG9pbnQgd2Uga25vdyB0aGF0IGF0IGxlYXN0IG9uZSBwcm9wZXJ0eSBpcyB0aGUgYHN0YXRpY2AgZmxhZy4gSWYgdGhpcyBpc1xuICAgICAgICAgICAgICAgIC8vIHRoZSBvbmx5IHByb3BlcnR5IHdlIGNhbiBkcm9wIHRoZSBlbnRpcmUgb2JqZWN0IGxpdGVyYWwsIG90aGVyd2lzZSB3ZSBoYXZlIHRvXG4gICAgICAgICAgICAgICAgLy8gZHJvcCBvbmx5IHRoZSBwcm9wZXJ0eS5cbiAgICAgICAgICAgICAgICBpZiAob3B0aW9ucy5wcm9wZXJ0aWVzLmxlbmd0aCA9PT0gMSkge1xuICAgICAgICAgICAgICAgICAgcmVtb3ZlUGFyYW1ldGVyLnB1c2goZGVjb3JhdG9yLm5vZGUuZXhwcmVzc2lvbik7XG4gICAgICAgICAgICAgICAgfSBlbHNlIHtcbiAgICAgICAgICAgICAgICAgIHJlbW92ZVByb3BlcnR5LnB1c2gob3B0aW9ucyk7XG4gICAgICAgICAgICAgICAgfVxuICAgICAgICAgICAgICB9KTtcbiAgICAgICAgfVxuICAgICAgfSk7XG4gICAgfVxuXG4gICAgbm9kZS5mb3JFYWNoQ2hpbGQod2Fsayk7XG4gIH0pO1xuXG4gIHJldHVybiB7cmVtb3ZlUHJvcGVydHksIHJlbW92ZVBhcmFtZXRlcn07XG59XG5cbi8qKiBSZW1vdmVzIHRoZSBgb3B0aW9uc2AgcGFyYW1ldGVyIGZyb20gdGhlIGNhbGwgZXhwcmVzc2lvbiBvZiBhIHF1ZXJ5IGRlY29yYXRvci4gKi9cbmV4cG9ydCBmdW5jdGlvbiByZW1vdmVPcHRpb25zUGFyYW1ldGVyKG5vZGU6IHRzLkNhbGxFeHByZXNzaW9uKTogdHMuQ2FsbEV4cHJlc3Npb24ge1xuICByZXR1cm4gdHMudXBkYXRlQ2FsbChub2RlLCBub2RlLmV4cHJlc3Npb24sIG5vZGUudHlwZUFyZ3VtZW50cywgW25vZGUuYXJndW1lbnRzWzBdXSk7XG59XG5cbi8qKiBSZW1vdmVzIHRoZSBgc3RhdGljYCBwcm9wZXJ0eSBmcm9tIGFuIG9iamVjdCBsaXRlcmFsIGV4cHJlc3Npb24uICovXG5leHBvcnQgZnVuY3Rpb24gcmVtb3ZlU3RhdGljRmxhZyhub2RlOiB0cy5PYmplY3RMaXRlcmFsRXhwcmVzc2lvbik6IHRzLk9iamVjdExpdGVyYWxFeHByZXNzaW9uIHtcbiAgcmV0dXJuIHRzLnVwZGF0ZU9iamVjdExpdGVyYWwoXG4gICAgICBub2RlLFxuICAgICAgbm9kZS5wcm9wZXJ0aWVzLmZpbHRlcihwcm9wZXJ0eSA9PiBwcm9wZXJ0eS5uYW1lICYmIHByb3BlcnR5Lm5hbWUuZ2V0VGV4dCgpICE9PSAnc3RhdGljJykpO1xufVxuIl19