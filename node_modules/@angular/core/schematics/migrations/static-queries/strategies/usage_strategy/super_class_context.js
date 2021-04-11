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
        define("@angular/core/schematics/migrations/static-queries/strategies/usage_strategy/super_class_context", ["require", "exports", "typescript", "@angular/core/schematics/utils/typescript/functions", "@angular/core/schematics/utils/typescript/nodes", "@angular/core/schematics/utils/typescript/property_name", "@angular/core/schematics/migrations/static-queries/angular/super_class"], factory);
    }
})(function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.updateSuperClassAbstractMembersContext = void 0;
    const ts = require("typescript");
    const functions_1 = require("@angular/core/schematics/utils/typescript/functions");
    const nodes_1 = require("@angular/core/schematics/utils/typescript/nodes");
    const property_name_1 = require("@angular/core/schematics/utils/typescript/property_name");
    const super_class_1 = require("@angular/core/schematics/migrations/static-queries/angular/super_class");
    /**
     * Updates the specified function context to map abstract super-class class members
     * to their implementation TypeScript nodes. This allows us to run the declaration visitor
     * for the super class with the context of the "baseClass" (e.g. with implemented abstract
     * class members)
     */
    function updateSuperClassAbstractMembersContext(baseClass, context, classMetadataMap) {
        super_class_1.getSuperClassDeclarations(baseClass, classMetadataMap).forEach(superClassDecl => {
            superClassDecl.members.forEach(superClassMember => {
                if (!superClassMember.name || !nodes_1.hasModifier(superClassMember, ts.SyntaxKind.AbstractKeyword)) {
                    return;
                }
                // Find the matching implementation of the abstract declaration from the super class.
                const baseClassImpl = baseClass.members.find(baseClassMethod => !!baseClassMethod.name &&
                    property_name_1.getPropertyNameText(baseClassMethod.name) ===
                        property_name_1.getPropertyNameText(superClassMember.name));
                if (!baseClassImpl || !functions_1.isFunctionLikeDeclaration(baseClassImpl) || !baseClassImpl.body) {
                    return;
                }
                if (!context.has(superClassMember)) {
                    context.set(superClassMember, baseClassImpl);
                }
            });
        });
    }
    exports.updateSuperClassAbstractMembersContext = updateSuperClassAbstractMembersContext;
});
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoic3VwZXJfY2xhc3NfY29udGV4dC5qcyIsInNvdXJjZVJvb3QiOiIiLCJzb3VyY2VzIjpbIi4uLy4uLy4uLy4uLy4uLy4uLy4uLy4uLy4uLy4uL3BhY2thZ2VzL2NvcmUvc2NoZW1hdGljcy9taWdyYXRpb25zL3N0YXRpYy1xdWVyaWVzL3N0cmF0ZWdpZXMvdXNhZ2Vfc3RyYXRlZ3kvc3VwZXJfY2xhc3NfY29udGV4dC50cyJdLCJuYW1lcyI6W10sIm1hcHBpbmdzIjoiQUFBQTs7Ozs7O0dBTUc7Ozs7Ozs7Ozs7Ozs7SUFFSCxpQ0FBaUM7SUFFakMsbUZBQWlGO0lBQ2pGLDJFQUErRDtJQUMvRCwyRkFBK0U7SUFFL0Usd0dBQW9FO0lBS3BFOzs7OztPQUtHO0lBQ0gsU0FBZ0Isc0NBQXNDLENBQ2xELFNBQThCLEVBQUUsT0FBd0IsRUFBRSxnQkFBa0M7UUFDOUYsdUNBQXlCLENBQUMsU0FBUyxFQUFFLGdCQUFnQixDQUFDLENBQUMsT0FBTyxDQUFDLGNBQWMsQ0FBQyxFQUFFO1lBQzlFLGNBQWMsQ0FBQyxPQUFPLENBQUMsT0FBTyxDQUFDLGdCQUFnQixDQUFDLEVBQUU7Z0JBQ2hELElBQUksQ0FBQyxnQkFBZ0IsQ0FBQyxJQUFJLElBQUksQ0FBQyxtQkFBVyxDQUFDLGdCQUFnQixFQUFFLEVBQUUsQ0FBQyxVQUFVLENBQUMsZUFBZSxDQUFDLEVBQUU7b0JBQzNGLE9BQU87aUJBQ1I7Z0JBRUQscUZBQXFGO2dCQUNyRixNQUFNLGFBQWEsR0FBRyxTQUFTLENBQUMsT0FBTyxDQUFDLElBQUksQ0FDeEMsZUFBZSxDQUFDLEVBQUUsQ0FBQyxDQUFDLENBQUMsZUFBZSxDQUFDLElBQUk7b0JBQ3JDLG1DQUFtQixDQUFDLGVBQWUsQ0FBQyxJQUFJLENBQUM7d0JBQ3JDLG1DQUFtQixDQUFDLGdCQUFnQixDQUFDLElBQUssQ0FBQyxDQUFDLENBQUM7Z0JBRXpELElBQUksQ0FBQyxhQUFhLElBQUksQ0FBQyxxQ0FBeUIsQ0FBQyxhQUFhLENBQUMsSUFBSSxDQUFDLGFBQWEsQ0FBQyxJQUFJLEVBQUU7b0JBQ3RGLE9BQU87aUJBQ1I7Z0JBRUQsSUFBSSxDQUFDLE9BQU8sQ0FBQyxHQUFHLENBQUMsZ0JBQWdCLENBQUMsRUFBRTtvQkFDbEMsT0FBTyxDQUFDLEdBQUcsQ0FBQyxnQkFBZ0IsRUFBRSxhQUFhLENBQUMsQ0FBQztpQkFDOUM7WUFDSCxDQUFDLENBQUMsQ0FBQztRQUNMLENBQUMsQ0FBQyxDQUFDO0lBQ0wsQ0FBQztJQXZCRCx3RkF1QkMiLCJzb3VyY2VzQ29udGVudCI6WyIvKipcbiAqIEBsaWNlbnNlXG4gKiBDb3B5cmlnaHQgR29vZ2xlIExMQyBBbGwgUmlnaHRzIFJlc2VydmVkLlxuICpcbiAqIFVzZSBvZiB0aGlzIHNvdXJjZSBjb2RlIGlzIGdvdmVybmVkIGJ5IGFuIE1JVC1zdHlsZSBsaWNlbnNlIHRoYXQgY2FuIGJlXG4gKiBmb3VuZCBpbiB0aGUgTElDRU5TRSBmaWxlIGF0IGh0dHBzOi8vYW5ndWxhci5pby9saWNlbnNlXG4gKi9cblxuaW1wb3J0ICogYXMgdHMgZnJvbSAndHlwZXNjcmlwdCc7XG5cbmltcG9ydCB7aXNGdW5jdGlvbkxpa2VEZWNsYXJhdGlvbn0gZnJvbSAnLi4vLi4vLi4vLi4vdXRpbHMvdHlwZXNjcmlwdC9mdW5jdGlvbnMnO1xuaW1wb3J0IHtoYXNNb2RpZmllcn0gZnJvbSAnLi4vLi4vLi4vLi4vdXRpbHMvdHlwZXNjcmlwdC9ub2Rlcyc7XG5pbXBvcnQge2dldFByb3BlcnR5TmFtZVRleHR9IGZyb20gJy4uLy4uLy4uLy4uL3V0aWxzL3R5cGVzY3JpcHQvcHJvcGVydHlfbmFtZSc7XG5pbXBvcnQge0NsYXNzTWV0YWRhdGFNYXB9IGZyb20gJy4uLy4uL2FuZ3VsYXIvbmdfcXVlcnlfdmlzaXRvcic7XG5pbXBvcnQge2dldFN1cGVyQ2xhc3NEZWNsYXJhdGlvbnN9IGZyb20gJy4uLy4uL2FuZ3VsYXIvc3VwZXJfY2xhc3MnO1xuXG5pbXBvcnQge0Z1bmN0aW9uQ29udGV4dH0gZnJvbSAnLi9kZWNsYXJhdGlvbl91c2FnZV92aXNpdG9yJztcblxuXG4vKipcbiAqIFVwZGF0ZXMgdGhlIHNwZWNpZmllZCBmdW5jdGlvbiBjb250ZXh0IHRvIG1hcCBhYnN0cmFjdCBzdXBlci1jbGFzcyBjbGFzcyBtZW1iZXJzXG4gKiB0byB0aGVpciBpbXBsZW1lbnRhdGlvbiBUeXBlU2NyaXB0IG5vZGVzLiBUaGlzIGFsbG93cyB1cyB0byBydW4gdGhlIGRlY2xhcmF0aW9uIHZpc2l0b3JcbiAqIGZvciB0aGUgc3VwZXIgY2xhc3Mgd2l0aCB0aGUgY29udGV4dCBvZiB0aGUgXCJiYXNlQ2xhc3NcIiAoZS5nLiB3aXRoIGltcGxlbWVudGVkIGFic3RyYWN0XG4gKiBjbGFzcyBtZW1iZXJzKVxuICovXG5leHBvcnQgZnVuY3Rpb24gdXBkYXRlU3VwZXJDbGFzc0Fic3RyYWN0TWVtYmVyc0NvbnRleHQoXG4gICAgYmFzZUNsYXNzOiB0cy5DbGFzc0RlY2xhcmF0aW9uLCBjb250ZXh0OiBGdW5jdGlvbkNvbnRleHQsIGNsYXNzTWV0YWRhdGFNYXA6IENsYXNzTWV0YWRhdGFNYXApIHtcbiAgZ2V0U3VwZXJDbGFzc0RlY2xhcmF0aW9ucyhiYXNlQ2xhc3MsIGNsYXNzTWV0YWRhdGFNYXApLmZvckVhY2goc3VwZXJDbGFzc0RlY2wgPT4ge1xuICAgIHN1cGVyQ2xhc3NEZWNsLm1lbWJlcnMuZm9yRWFjaChzdXBlckNsYXNzTWVtYmVyID0+IHtcbiAgICAgIGlmICghc3VwZXJDbGFzc01lbWJlci5uYW1lIHx8ICFoYXNNb2RpZmllcihzdXBlckNsYXNzTWVtYmVyLCB0cy5TeW50YXhLaW5kLkFic3RyYWN0S2V5d29yZCkpIHtcbiAgICAgICAgcmV0dXJuO1xuICAgICAgfVxuXG4gICAgICAvLyBGaW5kIHRoZSBtYXRjaGluZyBpbXBsZW1lbnRhdGlvbiBvZiB0aGUgYWJzdHJhY3QgZGVjbGFyYXRpb24gZnJvbSB0aGUgc3VwZXIgY2xhc3MuXG4gICAgICBjb25zdCBiYXNlQ2xhc3NJbXBsID0gYmFzZUNsYXNzLm1lbWJlcnMuZmluZChcbiAgICAgICAgICBiYXNlQ2xhc3NNZXRob2QgPT4gISFiYXNlQ2xhc3NNZXRob2QubmFtZSAmJlxuICAgICAgICAgICAgICBnZXRQcm9wZXJ0eU5hbWVUZXh0KGJhc2VDbGFzc01ldGhvZC5uYW1lKSA9PT1cbiAgICAgICAgICAgICAgICAgIGdldFByb3BlcnR5TmFtZVRleHQoc3VwZXJDbGFzc01lbWJlci5uYW1lISkpO1xuXG4gICAgICBpZiAoIWJhc2VDbGFzc0ltcGwgfHwgIWlzRnVuY3Rpb25MaWtlRGVjbGFyYXRpb24oYmFzZUNsYXNzSW1wbCkgfHwgIWJhc2VDbGFzc0ltcGwuYm9keSkge1xuICAgICAgICByZXR1cm47XG4gICAgICB9XG5cbiAgICAgIGlmICghY29udGV4dC5oYXMoc3VwZXJDbGFzc01lbWJlcikpIHtcbiAgICAgICAgY29udGV4dC5zZXQoc3VwZXJDbGFzc01lbWJlciwgYmFzZUNsYXNzSW1wbCk7XG4gICAgICB9XG4gICAgfSk7XG4gIH0pO1xufVxuIl19