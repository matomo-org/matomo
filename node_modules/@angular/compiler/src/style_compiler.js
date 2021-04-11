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
        define("@angular/compiler/src/style_compiler", ["require", "exports", "@angular/compiler/src/compile_metadata", "@angular/compiler/src/core", "@angular/compiler/src/output/output_ast", "@angular/compiler/src/shadow_css"], factory);
    }
})(function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.StyleCompiler = exports.CompiledStylesheet = exports.StylesCompileDependency = exports.CONTENT_ATTR = exports.HOST_ATTR = void 0;
    var compile_metadata_1 = require("@angular/compiler/src/compile_metadata");
    var core_1 = require("@angular/compiler/src/core");
    var o = require("@angular/compiler/src/output/output_ast");
    var shadow_css_1 = require("@angular/compiler/src/shadow_css");
    var COMPONENT_VARIABLE = '%COMP%';
    exports.HOST_ATTR = "_nghost-" + COMPONENT_VARIABLE;
    exports.CONTENT_ATTR = "_ngcontent-" + COMPONENT_VARIABLE;
    var StylesCompileDependency = /** @class */ (function () {
        function StylesCompileDependency(name, moduleUrl, setValue) {
            this.name = name;
            this.moduleUrl = moduleUrl;
            this.setValue = setValue;
        }
        return StylesCompileDependency;
    }());
    exports.StylesCompileDependency = StylesCompileDependency;
    var CompiledStylesheet = /** @class */ (function () {
        function CompiledStylesheet(outputCtx, stylesVar, dependencies, isShimmed, meta) {
            this.outputCtx = outputCtx;
            this.stylesVar = stylesVar;
            this.dependencies = dependencies;
            this.isShimmed = isShimmed;
            this.meta = meta;
        }
        return CompiledStylesheet;
    }());
    exports.CompiledStylesheet = CompiledStylesheet;
    var StyleCompiler = /** @class */ (function () {
        function StyleCompiler(_urlResolver) {
            this._urlResolver = _urlResolver;
            this._shadowCss = new shadow_css_1.ShadowCss();
        }
        StyleCompiler.prototype.compileComponent = function (outputCtx, comp) {
            var template = comp.template;
            return this._compileStyles(outputCtx, comp, new compile_metadata_1.CompileStylesheetMetadata({
                styles: template.styles,
                styleUrls: template.styleUrls,
                moduleUrl: compile_metadata_1.identifierModuleUrl(comp.type)
            }), this.needsStyleShim(comp), true);
        };
        StyleCompiler.prototype.compileStyles = function (outputCtx, comp, stylesheet, shim) {
            if (shim === void 0) { shim = this.needsStyleShim(comp); }
            return this._compileStyles(outputCtx, comp, stylesheet, shim, false);
        };
        StyleCompiler.prototype.needsStyleShim = function (comp) {
            return comp.template.encapsulation === core_1.ViewEncapsulation.Emulated;
        };
        StyleCompiler.prototype._compileStyles = function (outputCtx, comp, stylesheet, shim, isComponentStylesheet) {
            var _this = this;
            var styleExpressions = stylesheet.styles.map(function (plainStyle) { return o.literal(_this._shimIfNeeded(plainStyle, shim)); });
            var dependencies = [];
            stylesheet.styleUrls.forEach(function (styleUrl) {
                var exprIndex = styleExpressions.length;
                // Note: This placeholder will be filled later.
                styleExpressions.push(null);
                dependencies.push(new StylesCompileDependency(getStylesVarName(null), styleUrl, function (value) { return styleExpressions[exprIndex] = outputCtx.importExpr(value); }));
            });
            // styles variable contains plain strings and arrays of other styles arrays (recursive),
            // so we set its type to dynamic.
            var stylesVar = getStylesVarName(isComponentStylesheet ? comp : null);
            var stmt = o.variable(stylesVar)
                .set(o.literalArr(styleExpressions, new o.ArrayType(o.DYNAMIC_TYPE, [o.TypeModifier.Const])))
                .toDeclStmt(null, isComponentStylesheet ? [o.StmtModifier.Final] : [
                o.StmtModifier.Final, o.StmtModifier.Exported
            ]);
            outputCtx.statements.push(stmt);
            return new CompiledStylesheet(outputCtx, stylesVar, dependencies, shim, stylesheet);
        };
        StyleCompiler.prototype._shimIfNeeded = function (style, shim) {
            return shim ? this._shadowCss.shimCssText(style, exports.CONTENT_ATTR, exports.HOST_ATTR) : style;
        };
        return StyleCompiler;
    }());
    exports.StyleCompiler = StyleCompiler;
    function getStylesVarName(component) {
        var result = "styles";
        if (component) {
            result += "_" + compile_metadata_1.identifierName(component.type);
        }
        return result;
    }
});
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoic3R5bGVfY29tcGlsZXIuanMiLCJzb3VyY2VSb290IjoiIiwic291cmNlcyI6WyIuLi8uLi8uLi8uLi8uLi8uLi9wYWNrYWdlcy9jb21waWxlci9zcmMvc3R5bGVfY29tcGlsZXIudHMiXSwibmFtZXMiOltdLCJtYXBwaW5ncyI6IkFBQUE7Ozs7OztHQU1HOzs7Ozs7Ozs7Ozs7O0lBRUgsMkVBQXVKO0lBQ3ZKLG1EQUF5QztJQUN6QywyREFBeUM7SUFDekMsK0RBQXVDO0lBSXZDLElBQU0sa0JBQWtCLEdBQUcsUUFBUSxDQUFDO0lBQ3ZCLFFBQUEsU0FBUyxHQUFHLGFBQVcsa0JBQW9CLENBQUM7SUFDNUMsUUFBQSxZQUFZLEdBQUcsZ0JBQWMsa0JBQW9CLENBQUM7SUFFL0Q7UUFDRSxpQ0FDVyxJQUFZLEVBQVMsU0FBaUIsRUFBUyxRQUE4QjtZQUE3RSxTQUFJLEdBQUosSUFBSSxDQUFRO1lBQVMsY0FBUyxHQUFULFNBQVMsQ0FBUTtZQUFTLGFBQVEsR0FBUixRQUFRLENBQXNCO1FBQUcsQ0FBQztRQUM5Riw4QkFBQztJQUFELENBQUMsQUFIRCxJQUdDO0lBSFksMERBQXVCO0lBS3BDO1FBQ0UsNEJBQ1csU0FBd0IsRUFBUyxTQUFpQixFQUNsRCxZQUF1QyxFQUFTLFNBQWtCLEVBQ2xFLElBQStCO1lBRi9CLGNBQVMsR0FBVCxTQUFTLENBQWU7WUFBUyxjQUFTLEdBQVQsU0FBUyxDQUFRO1lBQ2xELGlCQUFZLEdBQVosWUFBWSxDQUEyQjtZQUFTLGNBQVMsR0FBVCxTQUFTLENBQVM7WUFDbEUsU0FBSSxHQUFKLElBQUksQ0FBMkI7UUFBRyxDQUFDO1FBQ2hELHlCQUFDO0lBQUQsQ0FBQyxBQUxELElBS0M7SUFMWSxnREFBa0I7SUFPL0I7UUFHRSx1QkFBb0IsWUFBeUI7WUFBekIsaUJBQVksR0FBWixZQUFZLENBQWE7WUFGckMsZUFBVSxHQUFjLElBQUksc0JBQVMsRUFBRSxDQUFDO1FBRUEsQ0FBQztRQUVqRCx3Q0FBZ0IsR0FBaEIsVUFBaUIsU0FBd0IsRUFBRSxJQUE4QjtZQUN2RSxJQUFNLFFBQVEsR0FBRyxJQUFJLENBQUMsUUFBVSxDQUFDO1lBQ2pDLE9BQU8sSUFBSSxDQUFDLGNBQWMsQ0FDdEIsU0FBUyxFQUFFLElBQUksRUFBRSxJQUFJLDRDQUF5QixDQUFDO2dCQUM3QyxNQUFNLEVBQUUsUUFBUSxDQUFDLE1BQU07Z0JBQ3ZCLFNBQVMsRUFBRSxRQUFRLENBQUMsU0FBUztnQkFDN0IsU0FBUyxFQUFFLHNDQUFtQixDQUFDLElBQUksQ0FBQyxJQUFJLENBQUM7YUFDMUMsQ0FBQyxFQUNGLElBQUksQ0FBQyxjQUFjLENBQUMsSUFBSSxDQUFDLEVBQUUsSUFBSSxDQUFDLENBQUM7UUFDdkMsQ0FBQztRQUVELHFDQUFhLEdBQWIsVUFDSSxTQUF3QixFQUFFLElBQThCLEVBQ3hELFVBQXFDLEVBQ3JDLElBQXlDO1lBQXpDLHFCQUFBLEVBQUEsT0FBZ0IsSUFBSSxDQUFDLGNBQWMsQ0FBQyxJQUFJLENBQUM7WUFDM0MsT0FBTyxJQUFJLENBQUMsY0FBYyxDQUFDLFNBQVMsRUFBRSxJQUFJLEVBQUUsVUFBVSxFQUFFLElBQUksRUFBRSxLQUFLLENBQUMsQ0FBQztRQUN2RSxDQUFDO1FBRUQsc0NBQWMsR0FBZCxVQUFlLElBQThCO1lBQzNDLE9BQU8sSUFBSSxDQUFDLFFBQVUsQ0FBQyxhQUFhLEtBQUssd0JBQWlCLENBQUMsUUFBUSxDQUFDO1FBQ3RFLENBQUM7UUFFTyxzQ0FBYyxHQUF0QixVQUNJLFNBQXdCLEVBQUUsSUFBOEIsRUFDeEQsVUFBcUMsRUFBRSxJQUFhLEVBQ3BELHFCQUE4QjtZQUhsQyxpQkEwQkM7WUF0QkMsSUFBTSxnQkFBZ0IsR0FDbEIsVUFBVSxDQUFDLE1BQU0sQ0FBQyxHQUFHLENBQUMsVUFBQSxVQUFVLElBQUksT0FBQSxDQUFDLENBQUMsT0FBTyxDQUFDLEtBQUksQ0FBQyxhQUFhLENBQUMsVUFBVSxFQUFFLElBQUksQ0FBQyxDQUFDLEVBQS9DLENBQStDLENBQUMsQ0FBQztZQUN6RixJQUFNLFlBQVksR0FBOEIsRUFBRSxDQUFDO1lBQ25ELFVBQVUsQ0FBQyxTQUFTLENBQUMsT0FBTyxDQUFDLFVBQUMsUUFBUTtnQkFDcEMsSUFBTSxTQUFTLEdBQUcsZ0JBQWdCLENBQUMsTUFBTSxDQUFDO2dCQUMxQywrQ0FBK0M7Z0JBQy9DLGdCQUFnQixDQUFDLElBQUksQ0FBQyxJQUFLLENBQUMsQ0FBQztnQkFDN0IsWUFBWSxDQUFDLElBQUksQ0FBQyxJQUFJLHVCQUF1QixDQUN6QyxnQkFBZ0IsQ0FBQyxJQUFJLENBQUMsRUFBRSxRQUFRLEVBQ2hDLFVBQUMsS0FBSyxJQUFLLE9BQUEsZ0JBQWdCLENBQUMsU0FBUyxDQUFDLEdBQUcsU0FBUyxDQUFDLFVBQVUsQ0FBQyxLQUFLLENBQUMsRUFBekQsQ0FBeUQsQ0FBQyxDQUFDLENBQUM7WUFDN0UsQ0FBQyxDQUFDLENBQUM7WUFDSCx3RkFBd0Y7WUFDeEYsaUNBQWlDO1lBQ2pDLElBQU0sU0FBUyxHQUFHLGdCQUFnQixDQUFDLHFCQUFxQixDQUFDLENBQUMsQ0FBQyxJQUFJLENBQUMsQ0FBQyxDQUFDLElBQUksQ0FBQyxDQUFDO1lBQ3hFLElBQU0sSUFBSSxHQUFHLENBQUMsQ0FBQyxRQUFRLENBQUMsU0FBUyxDQUFDO2lCQUNoQixHQUFHLENBQUMsQ0FBQyxDQUFDLFVBQVUsQ0FDYixnQkFBZ0IsRUFBRSxJQUFJLENBQUMsQ0FBQyxTQUFTLENBQUMsQ0FBQyxDQUFDLFlBQVksRUFBRSxDQUFDLENBQUMsQ0FBQyxZQUFZLENBQUMsS0FBSyxDQUFDLENBQUMsQ0FBQyxDQUFDO2lCQUM5RSxVQUFVLENBQUMsSUFBSSxFQUFFLHFCQUFxQixDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxZQUFZLENBQUMsS0FBSyxDQUFDLENBQUMsQ0FBQyxDQUFDO2dCQUNqRSxDQUFDLENBQUMsWUFBWSxDQUFDLEtBQUssRUFBRSxDQUFDLENBQUMsWUFBWSxDQUFDLFFBQVE7YUFDOUMsQ0FBQyxDQUFDO1lBQ3BCLFNBQVMsQ0FBQyxVQUFVLENBQUMsSUFBSSxDQUFDLElBQUksQ0FBQyxDQUFDO1lBQ2hDLE9BQU8sSUFBSSxrQkFBa0IsQ0FBQyxTQUFTLEVBQUUsU0FBUyxFQUFFLFlBQVksRUFBRSxJQUFJLEVBQUUsVUFBVSxDQUFDLENBQUM7UUFDdEYsQ0FBQztRQUVPLHFDQUFhLEdBQXJCLFVBQXNCLEtBQWEsRUFBRSxJQUFhO1lBQ2hELE9BQU8sSUFBSSxDQUFDLENBQUMsQ0FBQyxJQUFJLENBQUMsVUFBVSxDQUFDLFdBQVcsQ0FBQyxLQUFLLEVBQUUsb0JBQVksRUFBRSxpQkFBUyxDQUFDLENBQUMsQ0FBQyxDQUFDLEtBQUssQ0FBQztRQUNwRixDQUFDO1FBQ0gsb0JBQUM7SUFBRCxDQUFDLEFBMURELElBMERDO0lBMURZLHNDQUFhO0lBNEQxQixTQUFTLGdCQUFnQixDQUFDLFNBQXdDO1FBQ2hFLElBQUksTUFBTSxHQUFHLFFBQVEsQ0FBQztRQUN0QixJQUFJLFNBQVMsRUFBRTtZQUNiLE1BQU0sSUFBSSxNQUFJLGlDQUFjLENBQUMsU0FBUyxDQUFDLElBQUksQ0FBRyxDQUFDO1NBQ2hEO1FBQ0QsT0FBTyxNQUFNLENBQUM7SUFDaEIsQ0FBQyIsInNvdXJjZXNDb250ZW50IjpbIi8qKlxuICogQGxpY2Vuc2VcbiAqIENvcHlyaWdodCBHb29nbGUgTExDIEFsbCBSaWdodHMgUmVzZXJ2ZWQuXG4gKlxuICogVXNlIG9mIHRoaXMgc291cmNlIGNvZGUgaXMgZ292ZXJuZWQgYnkgYW4gTUlULXN0eWxlIGxpY2Vuc2UgdGhhdCBjYW4gYmVcbiAqIGZvdW5kIGluIHRoZSBMSUNFTlNFIGZpbGUgYXQgaHR0cHM6Ly9hbmd1bGFyLmlvL2xpY2Vuc2VcbiAqL1xuXG5pbXBvcnQge0NvbXBpbGVEaXJlY3RpdmVNZXRhZGF0YSwgQ29tcGlsZUlkZW50aWZpZXJNZXRhZGF0YSwgQ29tcGlsZVN0eWxlc2hlZXRNZXRhZGF0YSwgaWRlbnRpZmllck1vZHVsZVVybCwgaWRlbnRpZmllck5hbWV9IGZyb20gJy4vY29tcGlsZV9tZXRhZGF0YSc7XG5pbXBvcnQge1ZpZXdFbmNhcHN1bGF0aW9ufSBmcm9tICcuL2NvcmUnO1xuaW1wb3J0ICogYXMgbyBmcm9tICcuL291dHB1dC9vdXRwdXRfYXN0JztcbmltcG9ydCB7U2hhZG93Q3NzfSBmcm9tICcuL3NoYWRvd19jc3MnO1xuaW1wb3J0IHtVcmxSZXNvbHZlcn0gZnJvbSAnLi91cmxfcmVzb2x2ZXInO1xuaW1wb3J0IHtPdXRwdXRDb250ZXh0fSBmcm9tICcuL3V0aWwnO1xuXG5jb25zdCBDT01QT05FTlRfVkFSSUFCTEUgPSAnJUNPTVAlJztcbmV4cG9ydCBjb25zdCBIT1NUX0FUVFIgPSBgX25naG9zdC0ke0NPTVBPTkVOVF9WQVJJQUJMRX1gO1xuZXhwb3J0IGNvbnN0IENPTlRFTlRfQVRUUiA9IGBfbmdjb250ZW50LSR7Q09NUE9ORU5UX1ZBUklBQkxFfWA7XG5cbmV4cG9ydCBjbGFzcyBTdHlsZXNDb21waWxlRGVwZW5kZW5jeSB7XG4gIGNvbnN0cnVjdG9yKFxuICAgICAgcHVibGljIG5hbWU6IHN0cmluZywgcHVibGljIG1vZHVsZVVybDogc3RyaW5nLCBwdWJsaWMgc2V0VmFsdWU6ICh2YWx1ZTogYW55KSA9PiB2b2lkKSB7fVxufVxuXG5leHBvcnQgY2xhc3MgQ29tcGlsZWRTdHlsZXNoZWV0IHtcbiAgY29uc3RydWN0b3IoXG4gICAgICBwdWJsaWMgb3V0cHV0Q3R4OiBPdXRwdXRDb250ZXh0LCBwdWJsaWMgc3R5bGVzVmFyOiBzdHJpbmcsXG4gICAgICBwdWJsaWMgZGVwZW5kZW5jaWVzOiBTdHlsZXNDb21waWxlRGVwZW5kZW5jeVtdLCBwdWJsaWMgaXNTaGltbWVkOiBib29sZWFuLFxuICAgICAgcHVibGljIG1ldGE6IENvbXBpbGVTdHlsZXNoZWV0TWV0YWRhdGEpIHt9XG59XG5cbmV4cG9ydCBjbGFzcyBTdHlsZUNvbXBpbGVyIHtcbiAgcHJpdmF0ZSBfc2hhZG93Q3NzOiBTaGFkb3dDc3MgPSBuZXcgU2hhZG93Q3NzKCk7XG5cbiAgY29uc3RydWN0b3IocHJpdmF0ZSBfdXJsUmVzb2x2ZXI6IFVybFJlc29sdmVyKSB7fVxuXG4gIGNvbXBpbGVDb21wb25lbnQob3V0cHV0Q3R4OiBPdXRwdXRDb250ZXh0LCBjb21wOiBDb21waWxlRGlyZWN0aXZlTWV0YWRhdGEpOiBDb21waWxlZFN0eWxlc2hlZXQge1xuICAgIGNvbnN0IHRlbXBsYXRlID0gY29tcC50ZW1wbGF0ZSAhO1xuICAgIHJldHVybiB0aGlzLl9jb21waWxlU3R5bGVzKFxuICAgICAgICBvdXRwdXRDdHgsIGNvbXAsIG5ldyBDb21waWxlU3R5bGVzaGVldE1ldGFkYXRhKHtcbiAgICAgICAgICBzdHlsZXM6IHRlbXBsYXRlLnN0eWxlcyxcbiAgICAgICAgICBzdHlsZVVybHM6IHRlbXBsYXRlLnN0eWxlVXJscyxcbiAgICAgICAgICBtb2R1bGVVcmw6IGlkZW50aWZpZXJNb2R1bGVVcmwoY29tcC50eXBlKVxuICAgICAgICB9KSxcbiAgICAgICAgdGhpcy5uZWVkc1N0eWxlU2hpbShjb21wKSwgdHJ1ZSk7XG4gIH1cblxuICBjb21waWxlU3R5bGVzKFxuICAgICAgb3V0cHV0Q3R4OiBPdXRwdXRDb250ZXh0LCBjb21wOiBDb21waWxlRGlyZWN0aXZlTWV0YWRhdGEsXG4gICAgICBzdHlsZXNoZWV0OiBDb21waWxlU3R5bGVzaGVldE1ldGFkYXRhLFxuICAgICAgc2hpbTogYm9vbGVhbiA9IHRoaXMubmVlZHNTdHlsZVNoaW0oY29tcCkpOiBDb21waWxlZFN0eWxlc2hlZXQge1xuICAgIHJldHVybiB0aGlzLl9jb21waWxlU3R5bGVzKG91dHB1dEN0eCwgY29tcCwgc3R5bGVzaGVldCwgc2hpbSwgZmFsc2UpO1xuICB9XG5cbiAgbmVlZHNTdHlsZVNoaW0oY29tcDogQ29tcGlsZURpcmVjdGl2ZU1ldGFkYXRhKTogYm9vbGVhbiB7XG4gICAgcmV0dXJuIGNvbXAudGVtcGxhdGUgIS5lbmNhcHN1bGF0aW9uID09PSBWaWV3RW5jYXBzdWxhdGlvbi5FbXVsYXRlZDtcbiAgfVxuXG4gIHByaXZhdGUgX2NvbXBpbGVTdHlsZXMoXG4gICAgICBvdXRwdXRDdHg6IE91dHB1dENvbnRleHQsIGNvbXA6IENvbXBpbGVEaXJlY3RpdmVNZXRhZGF0YSxcbiAgICAgIHN0eWxlc2hlZXQ6IENvbXBpbGVTdHlsZXNoZWV0TWV0YWRhdGEsIHNoaW06IGJvb2xlYW4sXG4gICAgICBpc0NvbXBvbmVudFN0eWxlc2hlZXQ6IGJvb2xlYW4pOiBDb21waWxlZFN0eWxlc2hlZXQge1xuICAgIGNvbnN0IHN0eWxlRXhwcmVzc2lvbnM6IG8uRXhwcmVzc2lvbltdID1cbiAgICAgICAgc3R5bGVzaGVldC5zdHlsZXMubWFwKHBsYWluU3R5bGUgPT4gby5saXRlcmFsKHRoaXMuX3NoaW1JZk5lZWRlZChwbGFpblN0eWxlLCBzaGltKSkpO1xuICAgIGNvbnN0IGRlcGVuZGVuY2llczogU3R5bGVzQ29tcGlsZURlcGVuZGVuY3lbXSA9IFtdO1xuICAgIHN0eWxlc2hlZXQuc3R5bGVVcmxzLmZvckVhY2goKHN0eWxlVXJsKSA9PiB7XG4gICAgICBjb25zdCBleHBySW5kZXggPSBzdHlsZUV4cHJlc3Npb25zLmxlbmd0aDtcbiAgICAgIC8vIE5vdGU6IFRoaXMgcGxhY2Vob2xkZXIgd2lsbCBiZSBmaWxsZWQgbGF0ZXIuXG4gICAgICBzdHlsZUV4cHJlc3Npb25zLnB1c2gobnVsbCEpO1xuICAgICAgZGVwZW5kZW5jaWVzLnB1c2gobmV3IFN0eWxlc0NvbXBpbGVEZXBlbmRlbmN5KFxuICAgICAgICAgIGdldFN0eWxlc1Zhck5hbWUobnVsbCksIHN0eWxlVXJsLFxuICAgICAgICAgICh2YWx1ZSkgPT4gc3R5bGVFeHByZXNzaW9uc1tleHBySW5kZXhdID0gb3V0cHV0Q3R4LmltcG9ydEV4cHIodmFsdWUpKSk7XG4gICAgfSk7XG4gICAgLy8gc3R5bGVzIHZhcmlhYmxlIGNvbnRhaW5zIHBsYWluIHN0cmluZ3MgYW5kIGFycmF5cyBvZiBvdGhlciBzdHlsZXMgYXJyYXlzIChyZWN1cnNpdmUpLFxuICAgIC8vIHNvIHdlIHNldCBpdHMgdHlwZSB0byBkeW5hbWljLlxuICAgIGNvbnN0IHN0eWxlc1ZhciA9IGdldFN0eWxlc1Zhck5hbWUoaXNDb21wb25lbnRTdHlsZXNoZWV0ID8gY29tcCA6IG51bGwpO1xuICAgIGNvbnN0IHN0bXQgPSBvLnZhcmlhYmxlKHN0eWxlc1ZhcilcbiAgICAgICAgICAgICAgICAgICAgIC5zZXQoby5saXRlcmFsQXJyKFxuICAgICAgICAgICAgICAgICAgICAgICAgIHN0eWxlRXhwcmVzc2lvbnMsIG5ldyBvLkFycmF5VHlwZShvLkRZTkFNSUNfVFlQRSwgW28uVHlwZU1vZGlmaWVyLkNvbnN0XSkpKVxuICAgICAgICAgICAgICAgICAgICAgLnRvRGVjbFN0bXQobnVsbCwgaXNDb21wb25lbnRTdHlsZXNoZWV0ID8gW28uU3RtdE1vZGlmaWVyLkZpbmFsXSA6IFtcbiAgICAgICAgICAgICAgICAgICAgICAgby5TdG10TW9kaWZpZXIuRmluYWwsIG8uU3RtdE1vZGlmaWVyLkV4cG9ydGVkXG4gICAgICAgICAgICAgICAgICAgICBdKTtcbiAgICBvdXRwdXRDdHguc3RhdGVtZW50cy5wdXNoKHN0bXQpO1xuICAgIHJldHVybiBuZXcgQ29tcGlsZWRTdHlsZXNoZWV0KG91dHB1dEN0eCwgc3R5bGVzVmFyLCBkZXBlbmRlbmNpZXMsIHNoaW0sIHN0eWxlc2hlZXQpO1xuICB9XG5cbiAgcHJpdmF0ZSBfc2hpbUlmTmVlZGVkKHN0eWxlOiBzdHJpbmcsIHNoaW06IGJvb2xlYW4pOiBzdHJpbmcge1xuICAgIHJldHVybiBzaGltID8gdGhpcy5fc2hhZG93Q3NzLnNoaW1Dc3NUZXh0KHN0eWxlLCBDT05URU5UX0FUVFIsIEhPU1RfQVRUUikgOiBzdHlsZTtcbiAgfVxufVxuXG5mdW5jdGlvbiBnZXRTdHlsZXNWYXJOYW1lKGNvbXBvbmVudDogQ29tcGlsZURpcmVjdGl2ZU1ldGFkYXRhfG51bGwpOiBzdHJpbmcge1xuICBsZXQgcmVzdWx0ID0gYHN0eWxlc2A7XG4gIGlmIChjb21wb25lbnQpIHtcbiAgICByZXN1bHQgKz0gYF8ke2lkZW50aWZpZXJOYW1lKGNvbXBvbmVudC50eXBlKX1gO1xuICB9XG4gIHJldHVybiByZXN1bHQ7XG59XG4iXX0=