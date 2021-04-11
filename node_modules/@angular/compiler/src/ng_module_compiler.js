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
        define("@angular/compiler/src/ng_module_compiler", ["require", "exports", "@angular/compiler/src/compile_metadata", "@angular/compiler/src/identifiers", "@angular/compiler/src/output/output_ast", "@angular/compiler/src/parse_util", "@angular/compiler/src/provider_analyzer", "@angular/compiler/src/view_compiler/provider_compiler"], factory);
    }
})(function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.NgModuleCompiler = exports.NgModuleCompileResult = void 0;
    var compile_metadata_1 = require("@angular/compiler/src/compile_metadata");
    var identifiers_1 = require("@angular/compiler/src/identifiers");
    var o = require("@angular/compiler/src/output/output_ast");
    var parse_util_1 = require("@angular/compiler/src/parse_util");
    var provider_analyzer_1 = require("@angular/compiler/src/provider_analyzer");
    var provider_compiler_1 = require("@angular/compiler/src/view_compiler/provider_compiler");
    var NgModuleCompileResult = /** @class */ (function () {
        function NgModuleCompileResult(ngModuleFactoryVar) {
            this.ngModuleFactoryVar = ngModuleFactoryVar;
        }
        return NgModuleCompileResult;
    }());
    exports.NgModuleCompileResult = NgModuleCompileResult;
    var LOG_VAR = o.variable('_l');
    var NgModuleCompiler = /** @class */ (function () {
        function NgModuleCompiler(reflector) {
            this.reflector = reflector;
        }
        NgModuleCompiler.prototype.compile = function (ctx, ngModuleMeta, extraProviders) {
            var sourceSpan = parse_util_1.typeSourceSpan('NgModule', ngModuleMeta.type);
            var entryComponentFactories = ngModuleMeta.transitiveModule.entryComponents;
            var bootstrapComponents = ngModuleMeta.bootstrapComponents;
            var providerParser = new provider_analyzer_1.NgModuleProviderAnalyzer(this.reflector, ngModuleMeta, extraProviders, sourceSpan);
            var providerDefs = [provider_compiler_1.componentFactoryResolverProviderDef(this.reflector, ctx, 0 /* None */, entryComponentFactories)]
                .concat(providerParser.parse().map(function (provider) { return provider_compiler_1.providerDef(ctx, provider); }))
                .map(function (_a) {
                var providerExpr = _a.providerExpr, depsExpr = _a.depsExpr, flags = _a.flags, tokenExpr = _a.tokenExpr;
                return o.importExpr(identifiers_1.Identifiers.moduleProviderDef).callFn([
                    o.literal(flags), tokenExpr, providerExpr, depsExpr
                ]);
            });
            var ngModuleDef = o.importExpr(identifiers_1.Identifiers.moduleDef).callFn([o.literalArr(providerDefs)]);
            var ngModuleDefFactory = o.fn([new o.FnParam(LOG_VAR.name)], [new o.ReturnStatement(ngModuleDef)], o.INFERRED_TYPE);
            var ngModuleFactoryVar = compile_metadata_1.identifierName(ngModuleMeta.type) + "NgFactory";
            this._createNgModuleFactory(ctx, ngModuleMeta.type.reference, o.importExpr(identifiers_1.Identifiers.createModuleFactory).callFn([
                ctx.importExpr(ngModuleMeta.type.reference),
                o.literalArr(bootstrapComponents.map(function (id) { return ctx.importExpr(id.reference); })),
                ngModuleDefFactory
            ]));
            if (ngModuleMeta.id) {
                var id = typeof ngModuleMeta.id === 'string' ? o.literal(ngModuleMeta.id) :
                    ctx.importExpr(ngModuleMeta.id);
                var registerFactoryStmt = o.importExpr(identifiers_1.Identifiers.RegisterModuleFactoryFn)
                    .callFn([id, o.variable(ngModuleFactoryVar)])
                    .toStmt();
                ctx.statements.push(registerFactoryStmt);
            }
            return new NgModuleCompileResult(ngModuleFactoryVar);
        };
        NgModuleCompiler.prototype.createStub = function (ctx, ngModuleReference) {
            this._createNgModuleFactory(ctx, ngModuleReference, o.NULL_EXPR);
        };
        NgModuleCompiler.prototype._createNgModuleFactory = function (ctx, reference, value) {
            var ngModuleFactoryVar = compile_metadata_1.identifierName({ reference: reference }) + "NgFactory";
            var ngModuleFactoryStmt = o.variable(ngModuleFactoryVar)
                .set(value)
                .toDeclStmt(o.importType(identifiers_1.Identifiers.NgModuleFactory, [o.expressionType(ctx.importExpr(reference))], [o.TypeModifier.Const]), [o.StmtModifier.Final, o.StmtModifier.Exported]);
            ctx.statements.push(ngModuleFactoryStmt);
        };
        return NgModuleCompiler;
    }());
    exports.NgModuleCompiler = NgModuleCompiler;
});
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoibmdfbW9kdWxlX2NvbXBpbGVyLmpzIiwic291cmNlUm9vdCI6IiIsInNvdXJjZXMiOlsiLi4vLi4vLi4vLi4vLi4vLi4vcGFja2FnZXMvY29tcGlsZXIvc3JjL25nX21vZHVsZV9jb21waWxlci50cyJdLCJuYW1lcyI6W10sIm1hcHBpbmdzIjoiQUFBQTs7Ozs7O0dBTUc7Ozs7Ozs7Ozs7Ozs7SUFFSCwyRUFBb0c7SUFHcEcsaUVBQTBDO0lBQzFDLDJEQUF5QztJQUN6QywrREFBNEM7SUFDNUMsNkVBQTZEO0lBRTdELDJGQUEyRztJQUUzRztRQUNFLCtCQUFtQixrQkFBMEI7WUFBMUIsdUJBQWtCLEdBQWxCLGtCQUFrQixDQUFRO1FBQUcsQ0FBQztRQUNuRCw0QkFBQztJQUFELENBQUMsQUFGRCxJQUVDO0lBRlksc0RBQXFCO0lBSWxDLElBQU0sT0FBTyxHQUFHLENBQUMsQ0FBQyxRQUFRLENBQUMsSUFBSSxDQUFDLENBQUM7SUFFakM7UUFDRSwwQkFBb0IsU0FBMkI7WUFBM0IsY0FBUyxHQUFULFNBQVMsQ0FBa0I7UUFBRyxDQUFDO1FBQ25ELGtDQUFPLEdBQVAsVUFDSSxHQUFrQixFQUFFLFlBQXFDLEVBQ3pELGNBQXlDO1lBQzNDLElBQU0sVUFBVSxHQUFHLDJCQUFjLENBQUMsVUFBVSxFQUFFLFlBQVksQ0FBQyxJQUFJLENBQUMsQ0FBQztZQUNqRSxJQUFNLHVCQUF1QixHQUFHLFlBQVksQ0FBQyxnQkFBZ0IsQ0FBQyxlQUFlLENBQUM7WUFDOUUsSUFBTSxtQkFBbUIsR0FBRyxZQUFZLENBQUMsbUJBQW1CLENBQUM7WUFDN0QsSUFBTSxjQUFjLEdBQ2hCLElBQUksNENBQXdCLENBQUMsSUFBSSxDQUFDLFNBQVMsRUFBRSxZQUFZLEVBQUUsY0FBYyxFQUFFLFVBQVUsQ0FBQyxDQUFDO1lBQzNGLElBQU0sWUFBWSxHQUNkLENBQUMsdURBQW1DLENBQy9CLElBQUksQ0FBQyxTQUFTLEVBQUUsR0FBRyxnQkFBa0IsdUJBQXVCLENBQUMsQ0FBQztpQkFDOUQsTUFB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LFNBQVMsRUFBQyxDQUFDLGNBQVcsQ0FBQztZQUNoRixJQUFNLG1CQUFtQixHQUNyQixDQUFDLENBQUMsUUFBUSxDQUFDLGtCQUFrQixDQUFDO2lCQUN6QixHQUFHLENBQUMsS0FBSyxDQUFDO2lCQUNWLFVBQVUsQ0FDUCxDQUFDLENBQUMsVUFBVSxDQUNSLHlCQUFXLENBQUMsZUFBZSxFQUFFLENBQUMsQ0FBQyxDQUFDLGNBQWMsQ0FBQyxHQUFHLENBQUMsVUFBVSxDQUFDLFNBQVMsQ0FBQyxDQUFFLENBQUMsRUFDM0UsQ0FBQyxDQUFDLENBQUMsWUFBWSxDQUFDLEtBQUssQ0FBQyxDQUFDLEVBQzNCLENBQUMsQ0FBQyxDQUFDLFlBQVksQ0FBQyxLQUFLLEVBQUUsQ0FBQyxDQUFDLFlBQVksQ0FBQyxRQUFRLENBQUMsQ0FBQyxDQUFDO1lBRTdELEdBQUcsQ0FBQyxVQUFVLENBQUMsSUFBSSxDQUFDLG1CQUFtQixDQUFDLENBQUM7UUFDM0MsQ0FBQztRQUNILHVCQUFDO0lBQUQsQ0FBQyxBQTdERCxJQTZEQztJQTdEWSw0Q0FBZ0IiLCJzb3VyY2VzQ29udGVudCI6WyIvKipcbiAqIEBsaWNlbnNlXG4gKiBDb3B5cmlnaHQgR29vZ2xlIExMQyBBbGwgUmlnaHRzIFJlc2VydmVkLlxuICpcbiAqIFVzZSBvZiB0aGlzIHNvdXJjZSBjb2RlIGlzIGdvdmVybmVkIGJ5IGFuIE1JVC1zdHlsZSBsaWNlbnNlIHRoYXQgY2FuIGJlXG4gKiBmb3VuZCBpbiB0aGUgTElDRU5TRSBmaWxlIGF0IGh0dHBzOi8vYW5ndWxhci5pby9saWNlbnNlXG4gKi9cblxuaW1wb3J0IHtDb21waWxlTmdNb2R1bGVNZXRhZGF0YSwgQ29tcGlsZVByb3ZpZGVyTWV0YWRhdGEsIGlkZW50aWZpZXJOYW1lfSBmcm9tICcuL2NvbXBpbGVfbWV0YWRhdGEnO1xuaW1wb3J0IHtDb21waWxlUmVmbGVjdG9yfSBmcm9tICcuL2NvbXBpbGVfcmVmbGVjdG9yJztcbmltcG9ydCB7Tm9kZUZsYWdzfSBmcm9tICcuL2NvcmUnO1xuaW1wb3J0IHtJZGVudGlmaWVyc30gZnJvbSAnLi9pZGVudGlmaWVycyc7XG5pbXBvcnQgKiBhcyBvIGZyb20gJy4vb3V0cHV0L291dHB1dF9hc3QnO1xuaW1wb3J0IHt0eXBlU291cmNlU3Bhbn0gZnJvbSAnLi9wYXJzZV91dGlsJztcbmltcG9ydCB7TmdNb2R1bGVQcm92aWRlckFuYWx5emVyfSBmcm9tICcuL3Byb3ZpZGVyX2FuYWx5emVyJztcbmltcG9ydCB7T3V0cHV0Q29udGV4dH0gZnJvbSAnLi91dGlsJztcbmltcG9ydCB7Y29tcG9uZW50RmFjdG9yeVJlc29sdmVyUHJvdmlkZXJEZWYsIGRlcERlZiwgcHJvdmlkZXJEZWZ9IGZyb20gJy4vdmlld19jb21waWxlci9wcm92aWRlcl9jb21waWxlcic7XG5cbmV4cG9ydCBjbGFzcyBOZ01vZHVsZUNvbXBpbGVSZXN1bHQge1xuICBjb25zdHJ1Y3RvcihwdWJsaWMgbmdNb2R1bGVGYWN0b3J5VmFyOiBzdHJpbmcpIHt9XG59XG5cbmNvbnN0IExPR19WQVIgPSBvLnZhcmlhYmxlKCdfbCcpO1xuXG5leHBvcnQgY2xhc3MgTmdNb2R1bGVDb21waWxlciB7XG4gIGNvbnN0cnVjdG9yKHByaXZhdGUgcmVmbGVjdG9yOiBDb21waWxlUmVmbGVjdG9yKSB7fVxuICBjb21waWxlKFxuICAgICAgY3R4OiBPdXRwdXRDb250ZXh0LCBuZ01vZHVsZU1ldGE6IENvbXBpbGVOZ01vZHVsZU1ldGFkYXRhLFxuICAgICAgZXh0cmFQcm92aWRlcnM6IENvbXBpbGVQcm92aWRlck1ldGFkYXRhW10pOiBOZ01vZHVsZUNvbXBpbGVSZXN1bHQge1xuICAgIGNvbnN0IHNvdXJjZVNwYW4gPSB0eXBlU291cmNlU3BhbignTmdNb2R1bGUnLCBuZ01vZHVsZU1ldGEudHlwZSk7XG4gICAgY29uc3QgZW50cnlDb21wb25lbnRGYWN0b3JpZXMgPSBuZ01vZHVsZU1ldGEudHJhbnNpdGl2ZU1vZHVsZS5lbnRyeUNvbXBvbmVudHM7XG4gICAgY29uc3QgYm9vdHN0cmFwQ29tcG9uZW50cyA9IG5nTW9kdWxlTWV0YS5ib290c3RyYXBDb21wb25lbnRzO1xuICAgIGNvbnN0IHByb3ZpZGVyUGFyc2VyID1cbiAgICAgICAgbmV3IE5nTW9kdWxlUHJvdmlkZXJBbmFseXplcih0aGlzLnJlZmxlY3RvciwgbmdNb2R1bGVNZXRhLCBleHRyYVByb3ZpZGVycywgc291cmNlU3Bhbik7XG4gICAgY29uc3QgcHJvdmlkZXJEZWZzID1cbiAgICAgICAgW2NvbXBvbmVudEZhY3RvcnlSZXNvbHZlclByb3ZpZGVyRGVmKFxuICAgICAgICAgICAgIHRoaXMucmVmbGVjdG9yLCBjdHgsIE5vZGVGbGFncy5Ob25lLCBlbnRyeUNvbXBvbmVudEZhY3RvcmllcyldXG4gICAgICAgICAgICAuY29uY2F0KHByb3ZpZGVyUGFyc2VyLnBhcnNlKCkubWFwKChwcm92aWRlcikgPT4gcHJvdmlkZXJEZWYoY3R4LCBwcm92aWRlcikpKVxuICAgICAgICAgICAgLm1hcCgoe3Byb3ZpZGVyRXhwciwgZGVwc0V4cHIsIGZsYWdzLCB0b2tlbkV4cHJ9KSA9PiB7XG4gICAgICAgICAgICAgIHJldHVybiBvLmltcG9ydEV4cHIoSWRlbnRpZmllcnMubW9kdWxlUHJvdmlkZXJEZWYpLmNhbGxGbihbXG4gICAgICAgICAgICAgICAgby5saXRlcmFsKGZsYWdzKSwgdG9rZW5FeHByLCBwcm92aWRlckV4cHIsIGRlcHNFeHByXG4gICAgICAgICAgICAgIF0pO1xuICAgICAgICAgICAgfSk7XG5cbiAgICBjb25zdCBuZ01vZHVsZURlZiA9IG8uaW1wb3J0RXhwcihJZGVudGlmaWVycy5tb2R1bGVEZWYpLmNhbGxGbihbby5saXRlcmFsQXJyKHByb3ZpZGVyRGVmcyldKTtcbiAgICBjb25zdCBuZ01vZHVsZURlZkZhY3RvcnkgPVxuICAgICAgICBvLmZuKFtuZXcgby5GblBhcmFtKExPR19WQVIubmFtZSEpXSwgW25ldyBvLlJldHVyblN0YXRlbWVudChuZ01vZHVsZURlZildLCBvLklORkVSUkVEX1RZUEUpO1xuXG4gICAgY29uc3QgbmdNb2R1bGVGYWN0b3J5VmFyID0gYCR7aWRlbnRpZmllck5hbWUobmdNb2R1bGVNZXRhLnR5cGUpfU5nRmFjdG9yeWA7XG4gICAgdGhpcy5fY3JlYXRlTmdNb2R1bGVGYWN0b3J5KFxuICAgICAgICBjdHgsIG5nTW9kdWxlTWV0YS50eXBlLnJlZmVyZW5jZSwgby5pbXBvcnRFeHByKElkZW50aWZpZXJzLmNyZWF0ZU1vZHVsZUZhY3RvcnkpLmNhbGxGbihbXG4gICAgICAgICAgY3R4LmltcG9ydEV4cHIobmdNb2R1bGVNZXRhLnR5cGUucmVmZXJlbmNlKSxcbiAgICAgICAgICBvLmxpdGVyYWxBcnIoYm9vdHN0cmFwQ29tcG9uZW50cy5tYXAoaWQgPT4gY3R4LmltcG9ydEV4cHIoaWQucmVmZXJlbmNlKSkpLFxuICAgICAgICAgIG5nTW9kdWxlRGVmRmFjdG9yeVxuICAgICAgICBdKSk7XG5cbiAgICBpZiAobmdNb2R1bGVNZXRhLmlkKSB7XG4gICAgICBjb25zdCBpZCA9IHR5cGVvZiBuZ01vZHVsZU1ldGEuaWQgPT09ICdzdHJpbmcnID8gby5saXRlcmFsKG5nTW9kdWxlTWV0YS5pZCkgOlxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIGN0eC5pbXBvcnRFeHByKG5nTW9kdWxlTWV0YS5pZCk7XG4gICAgICBjb25zdCByZWdpc3RlckZhY3RvcnlTdG10ID0gby5pbXBvcnRFeHByKElkZW50aWZpZXJzLlJlZ2lzdGVyTW9kdWxlRmFjdG9yeUZuKVxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAuY2FsbEZuKFtpZCwgby52YXJpYWJsZShuZ01vZHVsZUZhY3RvcnlWYXIpXSlcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgLnRvU3RtdCgpO1xuICAgICAgY3R4LnN0YXRlbWVudHMucHVzaChyZWdpc3RlckZhY3RvcnlTdG10KTtcbiAgICB9XG5cbiAgICByZXR1cm4gbmV3IE5nTW9kdWxlQ29tcGlsZVJlc3VsdChuZ01vZHVsZUZhY3RvcnlWYXIpO1xuICB9XG5cbiAgY3JlYXRlU3R1YihjdHg6IE91dHB1dENvbnRleHQsIG5nTW9kdWxlUmVmZXJlbmNlOiBhbnkpIHtcbiAgICB0aGlzLl9jcmVhdGVOZ01vZHVsZUZhY3RvcnkoY3R4LCBuZ01vZHVsZVJlZmVyZW5jZSwgby5OVUxMX0VYUFIpO1xuICB9XG5cbiAgcHJpdmF0ZSBfY3JlYXRlTmdNb2R1bGVGYWN0b3J5KGN0eDogT3V0cHV0Q29udGV4dCwgcmVmZXJlbmNlOiBhbnksIHZhbHVlOiBvLkV4cHJlc3Npb24pIHtcbiAgICBjb25zdCBuZ01vZHVsZUZhY3RvcnlWYXIgPSBgJHtpZGVudGlmaWVyTmFtZSh7cmVmZXJlbmNlOiByZWZlcmVuY2V9KX1OZ0ZhY3RvcnlgO1xuICAgIGNvbnN0IG5nTW9kdWxlRmFjdG9yeVN0bXQgPVxuICAgICAgICBvLnZhcmlhYmxlKG5nTW9kdWxlRmFjdG9yeVZhcilcbiAgICAgICAgICAgIC5zZXQodmFsdWUpXG4gICAgICAgICAgICAudG9EZWNsU3RtdChcbiAgICAgICAgICAgICAgICBvLmltcG9ydFR5cGUoXG4gICAgICAgICAgICAgICAgICAgIElkZW50aWZpZXJzLk5nTW9kdWxlRmFjdG9yeSwgW28uZXhwcmVzc2lvblR5cGUoY3R4LmltcG9ydEV4cHIocmVmZXJlbmNlKSkhXSxcbiAgICAgICAgICAgICAgICAgICAgW28uVHlwZU1vZGlmaWVyLkNvbnN0XSksXG4gICAgICAgICAgICAgICAgW28uU3RtdE1vZGlmaWVyLkZpbmFsLCBvLlN0bXRNb2RpZmllci5FeHBvcnRlZF0pO1xuXG4gICAgY3R4LnN0YXRlbWVudHMucHVzaChuZ01vZHVsZUZhY3RvcnlTdG10KTtcbiAgfVxufVxuIl19