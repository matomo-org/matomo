(function (factory) {
    if (typeof module === "object" && typeof module.exports === "object") {
        var v = factory(require, exports);
        if (v !== undefined) module.exports = v;
    }
    else if (typeof define === "function" && define.amd) {
        define("@angular/core/schematics/migrations/relative-link-resolution/transform", ["require", "exports", "typescript"], factory);
    }
})(function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.RelativeLinkResolutionTransform = void 0;
    /**
     * @license
     * Copyright Google LLC All Rights Reserved.
     *
     * Use of this source code is governed by an MIT-style license that can be
     * found in the LICENSE file at https://angular.io/license
     */
    const ts = require("typescript");
    const RELATIVE_LINK_RESOLUTION = 'relativeLinkResolution';
    class RelativeLinkResolutionTransform {
        constructor(getUpdateRecorder) {
            this.getUpdateRecorder = getUpdateRecorder;
            this.printer = ts.createPrinter();
        }
        /** Migrate the ExtraOptions#RelativeLinkResolution property assignments. */
        migrateRouterModuleForRootCalls(calls) {
            calls.forEach(c => {
                this._updateCallExpressionWithoutExtraOptions(c);
            });
        }
        migrateObjectLiterals(vars) {
            vars.forEach(v => this._maybeUpdateLiteral(v));
        }
        _updateCallExpressionWithoutExtraOptions(callExpression) {
            const args = callExpression.arguments;
            const emptyLiteral = ts.createObjectLiteral();
            const newNode = ts.updateCall(callExpression, callExpression.expression, callExpression.typeArguments, [args[0], this._getMigratedLiteralExpression(emptyLiteral)]);
            this._updateNode(callExpression, newNode);
        }
        _getMigratedLiteralExpression(literal) {
            if (literal.properties.some(prop => ts.isPropertyAssignment(prop) &&
                prop.name.getText() === RELATIVE_LINK_RESOLUTION)) {
                // literal already defines a value for relativeLinkResolution. Skip it
                return literal;
            }
            const legacyExpression = ts.createPropertyAssignment(RELATIVE_LINK_RESOLUTION, ts.createIdentifier(`'legacy'`));
            return ts.updateObjectLiteral(literal, [...literal.properties, legacyExpression]);
        }
        _maybeUpdateLiteral(literal) {
            const updatedLiteral = this._getMigratedLiteralExpression(literal);
            if (updatedLiteral !== literal) {
                this._updateNode(literal, updatedLiteral);
            }
        }
        _updateNode(node, newNode) {
            const newText = this.printer.printNode(ts.EmitHint.Unspecified, newNode, node.getSourceFile());
            const recorder = this.getUpdateRecorder(node.getSourceFile());
            recorder.updateNode(node, newText);
        }
    }
    exports.RelativeLinkResolutionTransform = RelativeLinkResolutionTransform;
});
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoidHJhbnNmb3JtLmpzIiwic291cmNlUm9vdCI6IiIsInNvdXJjZXMiOlsiLi4vLi4vLi4vLi4vLi4vLi4vLi4vLi4vcGFja2FnZXMvY29yZS9zY2hlbWF0aWNzL21pZ3JhdGlvbnMvcmVsYXRpdmUtbGluay1yZXNvbHV0aW9uL3RyYW5zZm9ybS50cyJdLCJuYW1lcyI6W10sIm1hcHBpbmdzIjoiOzs7Ozs7Ozs7Ozs7SUFBQTs7Ozs7O09BTUc7SUFDSCxpQ0FBaUM7SUFLakMsTUFBTSx3QkFBd0IsR0FBRyx3QkFBd0IsQ0FBQztJQUUxRCxNQUFhLCtCQUErQjtRQUcxQyxZQUFvQixpQkFBd0Q7WUFBeEQsc0JBQWlCLEdBQWpCLGlCQUFpQixDQUF1QztZQUZwRSxZQUFPLEdBQUcsRUFBRSxDQUFDLGFBQWEsRUFBRSxDQUFDO1FBRTBDLENBQUM7UUFFaEYsNEVBQTRFO1FBQzVFLCtCQUErQixDQUFDLEtBQTBCO1lBQ3hELEtBQUssQ0FBQyxPQUFPLENBQUMsQ0FBQyxDQUFDLEVBQUU7Z0JBQ2hCLElBQUksQ0FBQyx3Q0FBd0MsQ0FBQyxDQUFDLENBQUMsQ0FBQztZQUNuRCxDQUFDLENBQUMsQ0FBQztRQUNMLENBQUM7UUFFRCxxQkFBcUIsQ0FBQyxJQUFrQztZQUN0RCxJQUFJLENBQUMsT0FBTyxDQUFDLENBQUMsQ0FBQyxFQUFFLENBQUMsSUFBSSxDQUFDLG1CQUFtQixDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUM7UUFDakQsQ0FBQztRQUVPLHdDQUF3QyxDQUFDLGNBQWlDO1lBQ2hGLE1BQU0sSUFBSSxHQUFHLGNBQWMsQ0FBQyxTQUFTLENBQUM7WUFDdEMsTUFBTSxZQUFZLEdBQUcsRUFBRSxDQUFDLG1CQUFtQixFQUFFLENBQUM7WUFDOUMsTUFBTSxPQUFPLEdBQUcsRUFBRSxDQUFDLFVBQVUsQ0FDekIsY0FBYyxFQUFFLGNBQWMsQ0FBQyxVQUFVLEVBQUUsY0FBYyxDQUFDLGFBQWEsRUFDdkUsQ0FBQyxJQUFJLENBQUMsQ0FBQyxDQUFDLEVBQUUsSUFBSSxDQUFDLDZCQUE2QixDQUFDLFlBQVksQ0FBQyxDQUFDLENBQUMsQ0FBQztZQUNqRSxJQUFJLENBQUMsV0FBVyxDQUFDLGNBQWMsRUFBRSxPQUFPLENBQUMsQ0FBQztRQUM1QyxDQUFDO1FBRU8sNkJBQTZCLENBQUMsT0FBbUM7WUFDdkUsSUFBSSxPQUFPLENBQUMsVUFBVSxDQUFDLElBQUksQ0FDbkIsSUFBSSxDQUFDLEVBQUUsQ0FBQyxFQUFFLENBQUMsb0JBQW9CLENBQUMsSUFBSSxDQUFDO2dCQUNqQyxJQUFJLENBQUMsSUFBSSxDQUFDLE9BQU8sRUFBRSxLQUFLLHdCQUF3QixDQUFDLEVBQUU7Z0JBQzdELHNFQUFzRTtnQkFDdEUsT0FBTyxPQUFPLENBQUM7YUFDaEI7WUFDRCxNQUFNLGdCQUFnQixHQUNsQixFQUFFLENBQUMsd0JBQXdCLENBQUMsd0JBQXdCLEVBQUUsRUFBRSxDQUFDLGdCQUFnQixDQUFDLFVBQVUsQ0FBQyxDQUFDLENBQUM7WUFDM0YsT0FBTyxFQUFFLENBQUMsbUJBQW1CLENBQUMsT0FBTyxFQUFFLENBQUMsR0FBRyxPQUFPLENBQUMsVUFBVSxFQUFFLGdCQUFnQixDQUFDLENBQUMsQ0FBQztRQUNwRixDQUFDO1FBRU8sbUJBQW1CLENBQUMsT0FBbUM7WUFDN0QsTUFBTSxjQUFjLEdBQUcsSUFBSSxDQUFDLDZCQUE2QixDQUFDLE9BQU8sQ0FBQyxDQUFDO1lBQ25FLElBQUksY0FBYyxLQUFLLE9BQU8sRUFBRTtnQkFDOUIsSUFBSSxDQUFDLFdBQVcsQ0FBQyxPQUFPLEVBQUUsY0FBYyxDQUFDLENBQUM7YUFDM0M7UUFDSCxDQUFDO1FBRU8sV0FBVyxDQUFDLElBQWEsRUFBRSxPQUFnQjtZQUNqRCxNQUFNLE9BQU8sR0FBRyxJQUFJLENBQUMsT0FBTyxDQUFDLFNBQVMsQ0FBQyxFQUFFLENBQUMsUUFBUSxDQUFDLFdBQVcsRUFBRSxPQUFPLEVBQUUsSUFBSSxDQUFDLGFBQWEsRUFBRSxDQUFDLENBQUM7WUFDL0YsTUFBTSxRQUFRLEdBQUcsSUFBSSxDQUFDLGlCQUFpQixDQUFDLElBQUksQ0FBQyxhQUFhLEVBQUUsQ0FBQyxDQUFDO1lBQzlELFFBQVEsQ0FBQyxVQUFVLENBQUMsSUFBSSxFQUFFLE9BQU8sQ0FBQyxDQUFDO1FBQ3JDLENBQUM7S0FDRjtJQWpERCwwRUFpREMiLCJzb3VyY2VzQ29udGVudCI6WyIvKipcbiAqIEBsaWNlbnNlXG4gKiBDb3B5cmlnaHQgR29vZ2xlIExMQyBBbGwgUmlnaHRzIFJlc2VydmVkLlxuICpcbiAqIFVzZSBvZiB0aGlzIHNvdXJjZSBjb2RlIGlzIGdvdmVybmVkIGJ5IGFuIE1JVC1zdHlsZSBsaWNlbnNlIHRoYXQgY2FuIGJlXG4gKiBmb3VuZCBpbiB0aGUgTElDRU5TRSBmaWxlIGF0IGh0dHBzOi8vYW5ndWxhci5pby9saWNlbnNlXG4gKi9cbmltcG9ydCAqIGFzIHRzIGZyb20gJ3R5cGVzY3JpcHQnO1xuXG5pbXBvcnQge1VwZGF0ZVJlY29yZGVyfSBmcm9tICcuL3VwZGF0ZV9yZWNvcmRlcic7XG5cblxuY29uc3QgUkVMQVRJVkVfTElOS19SRVNPTFVUSU9OID0gJ3JlbGF0aXZlTGlua1Jlc29sdXRpb24nO1xuXG5leHBvcnQgY2xhc3MgUmVsYXRpdmVMaW5rUmVzb2x1dGlvblRyYW5zZm9ybSB7XG4gIHByaXZhdGUgcHJpbnRlciA9IHRzLmNyZWF0ZVByaW50ZXIoKTtcblxuICBjb25zdHJ1Y3Rvcihwcml2YXRlIGdldFVwZGF0ZVJlY29yZGVyOiAoc2Y6IHRzLlNvdXJjZUZpbGUpID0+IFVwZGF0ZVJlY29yZGVyKSB7fVxuXG4gIC8qKiBNaWdyYXRlIHRoZSBFeHRyYU9wdGlvbnMjUmVsYXRpdmVMaW5rUmVzb2x1dGlvbiBwcm9wZXJ0eSBhc3NpZ25tZW50cy4gKi9cbiAgbWlncmF0ZVJvdXRlck1vZHVsZUZvclJvb3RDYWxscyhjYWxsczogdHMuQ2FsbEV4cHJlc3Npb25bXSkge1xuICAgIGNhbGxzLmZvckVhY2goYyA9PiB7XG4gICAgICB0aGlzLl91cGRhdGVDYWxsRXhwcmVzc2lvbldpdGhvdXRFeHRyYU9wdGlvbnMoYyk7XG4gICAgfSk7XG4gIH1cblxuICBtaWdyYXRlT2JqZWN0TGl0ZXJhbHModmFyczogdHMuT2JqZWN0TGl0ZXJhbEV4cHJlc3Npb25bXSkge1xuICAgIHZhcnMuZm9yRWFjaCh2ID0+IHRoaXMuX21heWJlVXBkYXRlTGl0ZXJhbCh2KSk7XG4gIH1cblxuICBwcml2YXRlIF91cGRhdGVDYWxsRXhwcmVzc2lvbldpdGhvdXRFeHRyYU9wdGlvbnMoY2FsbEV4cHJlc3Npb246IHRzLkNhbGxFeHByZXNzaW9uKSB7XG4gICAgY29uc3QgYXJncyA9IGNhbGxFeHByZXNzaW9uLmFyZ3VtZW50cztcbiAgICBjb25zdCBlbXB0eUxpdGVyYWwgPSB0cy5jcmVhdGVPYmplY3RMaXRlcmFsKCk7XG4gICAgY29uc3QgbmV3Tm9kZSA9IHRzLnVwZGF0ZUNhbGwoXG4gICAgICAgIGNhbGxFeHByZXNzaW9uLCBjYWxsRXhwcmVzc2lvbi5leHByZXNzaW9uLCBjYWxsRXhwcmVzc2lvbi50eXBlQXJndW1lbnRzLFxuICAgICAgICBbYXJnc1swXSwgdGhpcy5fZ2V0TWlncmF0ZWRMaXRlcmFsRXhwcmVzc2lvbihlbXB0eUxpdGVyYWwpXSk7XG4gICAgdGhpcy5fdXBkYXRlTm9kZShjYWxsRXhwcmVzc2lvbiwgbmV3Tm9kZSk7XG4gIH1cblxuICBwcml2YXRlIF9nZXRNaWdyYXRlZExpdGVyYWxFeHByZXNzaW9uKGxpdGVyYWw6IHRzLk9iamVjdExpdGVyYWxFeHByZXNzaW9uKSB7XG4gICAgaWYgKGxpdGVyYWwucHJvcGVydGllcy5zb21lKFxuICAgICAgICAgICAgcHJvcCA9PiB0cy5pc1Byb3BlcnR5QXNzaWdubWVudChwcm9wKSAmJlxuICAgICAgICAgICAgICAgIHByb3AubmFtZS5nZXRUZXh0KCkgPT09IFJFTEFUSVZFX0xJTktfUkVTT0xVVElPTikpIHtcbiAgICAgIC8vIGxpdGVyYWwgYWxyZWFkeSBkZWZpbmVzIGEgdmFsdWUgZm9yIHJlbGF0aXZlTGlua1Jlc29sdXRpb24uIFNraXAgaXRcbiAgICAgIHJldHVybiBsaXRlcmFsO1xuICAgIH1cbiAgICBjb25zdCBsZWdhY3lFeHByZXNzaW9uID1cbiAgICAgICAgdHMuY3JlYXRlUHJvcGVydHlBc3NpZ25tZW50KFJFTEFUSVZFX0xJTktfUkVTT0xVVElPTiwgdHMuY3JlYXRlSWRlbnRpZmllcihgJ2xlZ2FjeSdgKSk7XG4gICAgcmV0dXJuIHRzLnVwZGF0ZU9iamVjdExpdGVyYWwobGl0ZXJhbCwgWy4uLmxpdGVyYWwucHJvcGVydGllcywgbGVnYWN5RXhwcmVzc2lvbl0pO1xuICB9XG5cbiAgcHJpdmF0ZSBfbWF5YmVVcGRhdGVMaXRlcmFsKGxpdGVyYWw6IHRzLk9iamVjdExpdGVyYWxFeHByZXNzaW9uKSB7XG4gICAgY29uc3QgdXBkYXRlZExpdGVyYWwgPSB0aGlzLl9nZXRNaWdyYXRlZExpdGVyYWxFeHByZXNzaW9uKGxpdGVyYWwpO1xuICAgIGlmICh1cGRhdGVkTGl0ZXJhbCAhPT0gbGl0ZXJhbCkge1xuICAgICAgdGhpcy5fdXBkYXRlTm9kZShsaXRlcmFsLCB1cGRhdGVkTGl0ZXJhbCk7XG4gICAgfVxuICB9XG5cbiAgcHJpdmF0ZSBfdXBkYXRlTm9kZShub2RlOiB0cy5Ob2RlLCBuZXdOb2RlOiB0cy5Ob2RlKSB7XG4gICAgY29uc3QgbmV3VGV4dCA9IHRoaXMucHJpbnRlci5wcmludE5vZGUodHMuRW1pdEhpbnQuVW5zcGVjaWZpZWQsIG5ld05vZGUsIG5vZGUuZ2V0U291cmNlRmlsZSgpKTtcbiAgICBjb25zdCByZWNvcmRlciA9IHRoaXMuZ2V0VXBkYXRlUmVjb3JkZXIobm9kZS5nZXRTb3VyY2VGaWxlKCkpO1xuICAgIHJlY29yZGVyLnVwZGF0ZU5vZGUobm9kZSwgbmV3VGV4dCk7XG4gIH1cbn1cbiJdfQ==