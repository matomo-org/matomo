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
        define("@angular/core/schematics/utils/ng_decorators", ["require", "exports", "@angular/core/schematics/utils/typescript/decorators"], factory);
    }
})(function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.getAngularDecorators = void 0;
    const decorators_1 = require("@angular/core/schematics/utils/typescript/decorators");
    /**
     * Gets all decorators which are imported from an Angular package (e.g. "@angular/core")
     * from a list of decorators.
     */
    function getAngularDecorators(typeChecker, decorators) {
        return decorators.map(node => ({ node, importData: decorators_1.getCallDecoratorImport(typeChecker, node) }))
            .filter(({ importData }) => importData && importData.importModule.startsWith('@angular/'))
            .map(({ node, importData }) => ({
            node: node,
            name: importData.name,
            moduleName: importData.importModule,
            importNode: importData.node
        }));
    }
    exports.getAngularDecorators = getAngularDecorators;
});
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoibmdfZGVjb3JhdG9ycy5qcyIsInNvdXJjZVJvb3QiOiIiLCJzb3VyY2VzIjpbIi4uLy4uLy4uLy4uLy4uLy4uLy4uL3BhY2thZ2VzL2NvcmUvc2NoZW1hdGljcy91dGlscy9uZ19kZWNvcmF0b3JzLnRzIl0sIm5hbWVzIjpbXSwibWFwcGluZ3MiOiJBQUFBOzs7Ozs7R0FNRzs7Ozs7Ozs7Ozs7OztJQUdILHFGQUErRDtJQWEvRDs7O09BR0c7SUFDSCxTQUFnQixvQkFBb0IsQ0FDaEMsV0FBMkIsRUFBRSxVQUF1QztRQUN0RSxPQUFPLFVBQVUsQ0FBQyxHQUFHLENBQUMsSUFBSSxDQUFDLEVBQUUsQ0FBQyxDQUFDLEVBQUMsSUFBSSxFQUFFLFVBQVUsRUFBRSxtQ0FBc0IsQ0FBQyxXQUFXLEVBQUUsSUFBSSxDQUFDLEVBQUMsQ0FBQyxDQUFDO2FBQ3pGLE1BQU0sQ0FBQyxDQUFDLEVBQUMsVUFBVSxFQUFDLEVBQUUsRUFBRSxDQUFDLFVBQVUsSUFBSSxVQUFVLENBQUMsWUFBWSxDQUFDLFVBQVUsQ0FBQyxXQUFXLENBQUMsQ0FBQzthQUN2RixHQUFHLENBQUMsQ0FBQyxFQUFDLElBQUksRUFBRSxVQUFVLEVBQUMsRUFBRSxFQUFFLENBQUMsQ0FBQztZQUN2QixJQUFJLEVBQUUsSUFBK0I7WUFDckMsSUFBSSxFQUFFLFVBQVcsQ0FBQyxJQUFJO1lBQ3RCLFVBQVUsRUFBRSxVQUFXLENBQUMsWUFBWTtZQUNwQyxVQUFVLEVBQUUsVUFBVyxDQUFDLElBQUk7U0FDN0IsQ0FBQyxDQUFDLENBQUM7SUFDZixDQUFDO0lBVkQsb0RBVUMiLCJzb3VyY2VzQ29udGVudCI6WyIvKipcbiAqIEBsaWNlbnNlXG4gKiBDb3B5cmlnaHQgR29vZ2xlIExMQyBBbGwgUmlnaHRzIFJlc2VydmVkLlxuICpcbiAqIFVzZSBvZiB0aGlzIHNvdXJjZSBjb2RlIGlzIGdvdmVybmVkIGJ5IGFuIE1JVC1zdHlsZSBsaWNlbnNlIHRoYXQgY2FuIGJlXG4gKiBmb3VuZCBpbiB0aGUgTElDRU5TRSBmaWxlIGF0IGh0dHBzOi8vYW5ndWxhci5pby9saWNlbnNlXG4gKi9cblxuaW1wb3J0ICogYXMgdHMgZnJvbSAndHlwZXNjcmlwdCc7XG5pbXBvcnQge2dldENhbGxEZWNvcmF0b3JJbXBvcnR9IGZyb20gJy4vdHlwZXNjcmlwdC9kZWNvcmF0b3JzJztcblxuZXhwb3J0IHR5cGUgQ2FsbEV4cHJlc3Npb25EZWNvcmF0b3IgPSB0cy5EZWNvcmF0b3Ime1xuICBleHByZXNzaW9uOiB0cy5DYWxsRXhwcmVzc2lvbjtcbn07XG5cbmV4cG9ydCBpbnRlcmZhY2UgTmdEZWNvcmF0b3Ige1xuICBuYW1lOiBzdHJpbmc7XG4gIG1vZHVsZU5hbWU6IHN0cmluZztcbiAgbm9kZTogQ2FsbEV4cHJlc3Npb25EZWNvcmF0b3I7XG4gIGltcG9ydE5vZGU6IHRzLkltcG9ydERlY2xhcmF0aW9uO1xufVxuXG4vKipcbiAqIEdldHMgYWxsIGRlY29yYXRvcnMgd2hpY2ggYXJlIGltcG9ydGVkIGZyb20gYW4gQW5ndWxhciBwYWNrYWdlIChlLmcuIFwiQGFuZ3VsYXIvY29yZVwiKVxuICogZnJvbSBhIGxpc3Qgb2YgZGVjb3JhdG9ycy5cbiAqL1xuZXhwb3J0IGZ1bmN0aW9uIGdldEFuZ3VsYXJEZWNvcmF0b3JzKFxuICAgIHR5cGVDaGVja2VyOiB0cy5UeXBlQ2hlY2tlciwgZGVjb3JhdG9yczogUmVhZG9ubHlBcnJheTx0cy5EZWNvcmF0b3I+KTogTmdEZWNvcmF0b3JbXSB7XG4gIHJldHVybiBkZWNvcmF0b3JzLm1hcChub2RlID0+ICh7bm9kZSwgaW1wb3J0RGF0YTogZ2V0Q2FsbERlY29yYXRvckltcG9ydCh0eXBlQ2hlY2tlciwgbm9kZSl9KSlcbiAgICAgIC5maWx0ZXIoKHtpbXBvcnREYXRhfSkgPT4gaW1wb3J0RGF0YSAmJiBpbXBvcnREYXRhLmltcG9ydE1vZHVsZS5zdGFydHNXaXRoKCdAYW5ndWxhci8nKSlcbiAgICAgIC5tYXAoKHtub2RlLCBpbXBvcnREYXRhfSkgPT4gKHtcbiAgICAgICAgICAgICBub2RlOiBub2RlIGFzIENhbGxFeHByZXNzaW9uRGVjb3JhdG9yLFxuICAgICAgICAgICAgIG5hbWU6IGltcG9ydERhdGEhLm5hbWUsXG4gICAgICAgICAgICAgbW9kdWxlTmFtZTogaW1wb3J0RGF0YSEuaW1wb3J0TW9kdWxlLFxuICAgICAgICAgICAgIGltcG9ydE5vZGU6IGltcG9ydERhdGEhLm5vZGVcbiAgICAgICAgICAgfSkpO1xufVxuIl19