(function (factory) {
    if (typeof module === "object" && typeof module.exports === "object") {
        var v = factory(require, exports);
        if (v !== undefined) module.exports = v;
    }
    else if (typeof define === "function" && define.amd) {
        define("@angular/core/schematics/migrations/initial-navigation/transform", ["require", "exports", "typescript"], factory);
    }
})(function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.InitialNavigationTransform = void 0;
    /**
     * @license
     * Copyright Google LLC All Rights Reserved.
     *
     * Use of this source code is governed by an MIT-style license that can be
     * found in the LICENSE file at https://angular.io/license
     */
    const ts = require("typescript");
    class InitialNavigationTransform {
        constructor(getUpdateRecorder) {
            this.getUpdateRecorder = getUpdateRecorder;
            this.printer = ts.createPrinter();
        }
        /** Migrate the ExtraOptions#InitialNavigation property assignments. */
        migrateInitialNavigationAssignments(literals) {
            literals.forEach(l => this.migrateAssignment(l));
        }
        /** Migrate an ExtraOptions#InitialNavigation expression to use the new options format. */
        migrateAssignment(assignment) {
            const newInitializer = getUpdatedInitialNavigationValue(assignment.initializer);
            if (newInitializer) {
                const newAssignment = ts.updatePropertyAssignment(assignment, assignment.name, newInitializer);
                this._updateNode(assignment, newAssignment);
            }
        }
        _updateNode(node, newNode) {
            const newText = this.printer.printNode(ts.EmitHint.Unspecified, newNode, node.getSourceFile());
            const recorder = this.getUpdateRecorder(node.getSourceFile());
            recorder.updateNode(node, newText);
        }
    }
    exports.InitialNavigationTransform = InitialNavigationTransform;
    /**
     * Updates the deprecated initialNavigation options to their v10 equivalents
     * (or as close as we can get).
     * @param initializer the old initializer to update
     */
    function getUpdatedInitialNavigationValue(initializer) {
        const oldText = ts.isStringLiteralLike(initializer) ?
            initializer.text :
            initializer.kind === ts.SyntaxKind.TrueKeyword;
        let newText;
        switch (oldText) {
            case false:
            case 'legacy_disabled':
                newText = 'disabled';
                break;
            case true:
            case 'legacy_enabled':
                newText = 'enabledNonBlocking';
                break;
        }
        return !!newText ? ts.createIdentifier(`'${newText}'`) : null;
    }
});
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoidHJhbnNmb3JtLmpzIiwic291cmNlUm9vdCI6IiIsInNvdXJjZXMiOlsiLi4vLi4vLi4vLi4vLi4vLi4vLi4vLi4vcGFja2FnZXMvY29yZS9zY2hlbWF0aWNzL21pZ3JhdGlvbnMvaW5pdGlhbC1uYXZpZ2F0aW9uL3RyYW5zZm9ybS50cyJdLCJuYW1lcyI6W10sIm1hcHBpbmdzIjoiOzs7Ozs7Ozs7Ozs7SUFBQTs7Ozs7O09BTUc7SUFDSCxpQ0FBaUM7SUFLakMsTUFBYSwwQkFBMEI7UUFHckMsWUFBb0IsaUJBQXdEO1lBQXhELHNCQUFpQixHQUFqQixpQkFBaUIsQ0FBdUM7WUFGcEUsWUFBTyxHQUFHLEVBQUUsQ0FBQyxhQUFhLEVBQUUsQ0FBQztRQUUwQyxDQUFDO1FBRWhGLHVFQUF1RTtRQUN2RSxtQ0FBbUMsQ0FBQyxRQUFpQztZQUNuRSxRQUFRLENBQUMsT0FBTyxDQUFDLENBQUMsQ0FBQyxFQUFFLENBQUMsSUFBSSxDQUFDLGlCQUFpQixDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUM7UUFDbkQsQ0FBQztRQUVELDBGQUEwRjtRQUMxRixpQkFBaUIsQ0FBQyxVQUFpQztZQUNqRCxNQUFNLGNBQWMsR0FBRyxnQ0FBZ0MsQ0FBQyxVQUFVLENBQUMsV0FBVyxDQUFDLENBQUM7WUFDaEYsSUFBSSxjQUFjLEVBQUU7Z0JBQ2xCLE1BQU0sYUFBYSxHQUNmLEVBQUUsQ0FBQyx3QkFBd0IsQ0FBQyxVQUFVLEVBQUUsVUFBVSxDQUFDLElBQUksRUFBRSxjQUFjLENBQUMsQ0FBQztnQkFDN0UsSUFBSSxDQUFDLFdBQVcsQ0FBQyxVQUFVLEVBQUUsYUFBYSxDQUFDLENBQUM7YUFDN0M7UUFDSCxDQUFDO1FBRU8sV0FBVyxDQUFDLElBQWEsRUFBRSxPQUFnQjtZQUNqRCxNQUFNLE9BQU8sR0FBRyxJQUFJLENBQUMsT0FBTyxDQUFDLFNBQVMsQ0FBQyxFQUFFLENBQUMsUUFBUSxDQUFDLFdBQVcsRUFBRSxPQUFPLEVBQUUsSUFBSSxDQUFDLGFBQWEsRUFBRSxDQUFDLENBQUM7WUFDL0YsTUFBTSxRQUFRLEdBQUcsSUFBSSxDQUFDLGlCQUFpQixDQUFDLElBQUksQ0FBQyxhQUFhLEVBQUUsQ0FBQyxDQUFDO1lBQzlELFFBQVEsQ0FBQyxVQUFVLENBQUMsSUFBSSxFQUFFLE9BQU8sQ0FBQyxDQUFDO1FBQ3JDLENBQUM7S0FDRjtJQXpCRCxnRUF5QkM7SUFFRDs7OztPQUlHO0lBQ0gsU0FBUyxnQ0FBZ0MsQ0FBQyxXQUEwQjtRQUNsRSxNQUFNLE9BQU8sR0FBbUIsRUFBRSxDQUFDLG1CQUFtQixDQUFDLFdBQVcsQ0FBQyxDQUFDLENBQUM7WUFDakUsV0FBVyxDQUFDLElBQUksQ0FBQyxDQUFDO1lBQ2xCLFdBQVcsQ0FBQyxJQUFJLEtBQUssRUFBRSxDQUFDLFVBQVUsQ0FBQyxXQUFXLENBQUM7UUFDbkQsSUFBSSxPQUF5QixDQUFDO1FBQzlCLFFBQVEsT0FBTyxFQUFFO1lBQ2YsS0FBSyxLQUFLLENBQUM7WUFDWCxLQUFLLGlCQUFpQjtnQkFDcEIsT0FBTyxHQUFHLFVBQVUsQ0FBQztnQkFDckIsTUFBTTtZQUNSLEtBQUssSUFBSSxDQUFDO1lBQ1YsS0FBSyxnQkFBZ0I7Z0JBQ25CLE9BQU8sR0FBRyxvQkFBb0IsQ0FBQztnQkFDL0IsTUFBTTtTQUNUO1FBRUQsT0FBTyxDQUFDLENBQUMsT0FBTyxDQUFDLENBQUMsQ0FBQyxFQUFFLENBQUMsZ0JBQWdCLENBQUMsSUFBSSxPQUFPLEdBQUcsQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFJLENBQUM7SUFDaEUsQ0FBQyIsInNvdXJjZXNDb250ZW50IjpbIi8qKlxuICogQGxpY2Vuc2VcbiAqIENvcHlyaWdodCBHb29nbGUgTExDIEFsbCBSaWdodHMgUmVzZXJ2ZWQuXG4gKlxuICogVXNlIG9mIHRoaXMgc291cmNlIGNvZGUgaXMgZ292ZXJuZWQgYnkgYW4gTUlULXN0eWxlIGxpY2Vuc2UgdGhhdCBjYW4gYmVcbiAqIGZvdW5kIGluIHRoZSBMSUNFTlNFIGZpbGUgYXQgaHR0cHM6Ly9hbmd1bGFyLmlvL2xpY2Vuc2VcbiAqL1xuaW1wb3J0ICogYXMgdHMgZnJvbSAndHlwZXNjcmlwdCc7XG5cbmltcG9ydCB7VXBkYXRlUmVjb3JkZXJ9IGZyb20gJy4vdXBkYXRlX3JlY29yZGVyJztcblxuXG5leHBvcnQgY2xhc3MgSW5pdGlhbE5hdmlnYXRpb25UcmFuc2Zvcm0ge1xuICBwcml2YXRlIHByaW50ZXIgPSB0cy5jcmVhdGVQcmludGVyKCk7XG5cbiAgY29uc3RydWN0b3IocHJpdmF0ZSBnZXRVcGRhdGVSZWNvcmRlcjogKHNmOiB0cy5Tb3VyY2VGaWxlKSA9PiBVcGRhdGVSZWNvcmRlcikge31cblxuICAvKiogTWlncmF0ZSB0aGUgRXh0cmFPcHRpb25zI0luaXRpYWxOYXZpZ2F0aW9uIHByb3BlcnR5IGFzc2lnbm1lbnRzLiAqL1xuICBtaWdyYXRlSW5pdGlhbE5hdmlnYXRpb25Bc3NpZ25tZW50cyhsaXRlcmFsczogdHMuUHJvcGVydHlBc3NpZ25tZW50W10pIHtcbiAgICBsaXRlcmFscy5mb3JFYWNoKGwgPT4gdGhpcy5taWdyYXRlQXNzaWdubWVudChsKSk7XG4gIH1cblxuICAvKiogTWlncmF0ZSBhbiBFeHRyYU9wdGlvbnMjSW5pdGlhbE5hdmlnYXRpb24gZXhwcmVzc2lvbiB0byB1c2UgdGhlIG5ldyBvcHRpb25zIGZvcm1hdC4gKi9cbiAgbWlncmF0ZUFzc2lnbm1lbnQoYXNzaWdubWVudDogdHMuUHJvcGVydHlBc3NpZ25tZW50KSB7XG4gICAgY29uc3QgbmV3SW5pdGlhbGl6ZXIgPSBnZXRVcGRhdGVkSW5pdGlhbE5hdmlnYXRpb25WYWx1ZShhc3NpZ25tZW50LmluaXRpYWxpemVyKTtcbiAgICBpZiAobmV3SW5pdGlhbGl6ZXIpIHtcbiAgICAgIGNvbnN0IG5ld0Fzc2lnbm1lbnQgPVxuICAgICAgICAgIHRzLnVwZGF0ZVByb3BlcnR5QXNzaWdubWVudChhc3NpZ25tZW50LCBhc3NpZ25tZW50Lm5hbWUsIG5ld0luaXRpYWxpemVyKTtcbiAgICAgIHRoaXMuX3VwZGF0ZU5vZGUoYXNzaWdubWVudCwgbmV3QXNzaWdubWVudCk7XG4gICAgfVxuICB9XG5cbiAgcHJpdmF0ZSBfdXBkYXRlTm9kZShub2RlOiB0cy5Ob2RlLCBuZXdOb2RlOiB0cy5Ob2RlKSB7XG4gICAgY29uc3QgbmV3VGV4dCA9IHRoaXMucHJpbnRlci5wcmludE5vZGUodHMuRW1pdEhpbnQuVW5zcGVjaWZpZWQsIG5ld05vZGUsIG5vZGUuZ2V0U291cmNlRmlsZSgpKTtcbiAgICBjb25zdCByZWNvcmRlciA9IHRoaXMuZ2V0VXBkYXRlUmVjb3JkZXIobm9kZS5nZXRTb3VyY2VGaWxlKCkpO1xuICAgIHJlY29yZGVyLnVwZGF0ZU5vZGUobm9kZSwgbmV3VGV4dCk7XG4gIH1cbn1cblxuLyoqXG4gKiBVcGRhdGVzIHRoZSBkZXByZWNhdGVkIGluaXRpYWxOYXZpZ2F0aW9uIG9wdGlvbnMgdG8gdGhlaXIgdjEwIGVxdWl2YWxlbnRzXG4gKiAob3IgYXMgY2xvc2UgYXMgd2UgY2FuIGdldCkuXG4gKiBAcGFyYW0gaW5pdGlhbGl6ZXIgdGhlIG9sZCBpbml0aWFsaXplciB0byB1cGRhdGVcbiAqL1xuZnVuY3Rpb24gZ2V0VXBkYXRlZEluaXRpYWxOYXZpZ2F0aW9uVmFsdWUoaW5pdGlhbGl6ZXI6IHRzLkV4cHJlc3Npb24pOiB0cy5FeHByZXNzaW9ufG51bGwge1xuICBjb25zdCBvbGRUZXh0OiBzdHJpbmd8Ym9vbGVhbiA9IHRzLmlzU3RyaW5nTGl0ZXJhbExpa2UoaW5pdGlhbGl6ZXIpID9cbiAgICAgIGluaXRpYWxpemVyLnRleHQgOlxuICAgICAgaW5pdGlhbGl6ZXIua2luZCA9PT0gdHMuU3ludGF4S2luZC5UcnVlS2V5d29yZDtcbiAgbGV0IG5ld1RleHQ6IHN0cmluZ3x1bmRlZmluZWQ7XG4gIHN3aXRjaCAob2xkVGV4dCkge1xuICAgIGNhc2UgZmFsc2U6XG4gICAgY2FzZSAnbGVnYWN5X2Rpc2FibGVkJzpcbiAgICAgIG5ld1RleHQgPSAnZGlzYWJsZWQnO1xuICAgICAgYnJlYWs7XG4gICAgY2FzZSB0cnVlOlxuICAgIGNhc2UgJ2xlZ2FjeV9lbmFibGVkJzpcbiAgICAgIG5ld1RleHQgPSAnZW5hYmxlZE5vbkJsb2NraW5nJztcbiAgICAgIGJyZWFrO1xuICB9XG5cbiAgcmV0dXJuICEhbmV3VGV4dCA/IHRzLmNyZWF0ZUlkZW50aWZpZXIoYCcke25ld1RleHR9J2ApIDogbnVsbDtcbn1cbiJdfQ==