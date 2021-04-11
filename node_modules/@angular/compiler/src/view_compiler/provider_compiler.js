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
        define("@angular/compiler/src/view_compiler/provider_compiler", ["require", "exports", "@angular/compiler/src/identifiers", "@angular/compiler/src/lifecycle_reflector", "@angular/compiler/src/output/output_ast", "@angular/compiler/src/output/value_util", "@angular/compiler/src/template_parser/template_ast"], factory);
    }
})(function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.componentFactoryResolverProviderDef = exports.lifecycleHookToNodeFlag = exports.depDef = exports.providerDef = void 0;
    var identifiers_1 = require("@angular/compiler/src/identifiers");
    var lifecycle_reflector_1 = require("@angular/compiler/src/lifecycle_reflector");
    var o = require("@angular/compiler/src/output/output_ast");
    var value_util_1 = require("@angular/compiler/src/output/value_util");
    var template_ast_1 = require("@angular/compiler/src/template_parser/template_ast");
    function providerDef(ctx, providerAst) {
        var flags = 0 /* None */;
        if (!providerAst.eager) {
            flags |= 4096 /* LazyProvider */;
        }
        if (providerAst.providerType === template_ast_1.ProviderAstType.PrivateService) {
            flags |= 8192 /* PrivateProvider */;
        }
        if (providerAst.isModule) {
            flags |= 1073741824 /* TypeModuleProvider */;
        }
        providerAst.lifecycleHooks.forEach(function (lifecycleHook) {
            // for regular providers, we only support ngOnDestroy
            if (lifecycleHook === lifecycle_reflector_1.LifecycleHooks.OnDestroy ||
                providerAst.providerType === template_ast_1.ProviderAstType.Directive ||
                providerAst.providerType === template_ast_1.ProviderAstType.Component) {
                flags |= lifecycleHookToNodeFlag(lifecycleHook);
            }
        });
        var _a = providerAst.multiProvider ?
            multiProviderDef(ctx, flags, providerAst.providers) :
            singleProviderDef(ctx, flags, providerAst.providerType, providerAst.providers[0]), providerExpr = _a.providerExpr, providerFlags = _a.flags, depsExpr = _a.depsExpr;
        return {
            providerExpr: providerExpr,
            flags: providerFlags,
            depsExpr: depsExpr,
            tokenExpr: tokenExpr(ctx, providerAst.token),
        };
    }
    exports.providerDef = providerDef;
    function multiProviderDef(ctx, flags, providers) {
        var allDepDefs = [];
        var allParams = [];
        var exprs = providers.map(function (provider, providerIndex) {
            var expr;
            if (provider.useClass) {
                var depExprs = convertDeps(providerIndex, provider.deps || provider.useClass.diDeps);
                expr = ctx.importExpr(provider.useClass.reference).instantiate(depExprs);
            }
            else if (provider.useFactory) {
                var depExprs = convertDeps(providerIndex, provider.deps || provider.useFactory.diDeps);
                expr = ctx.importExpr(provider.useFactory.reference).callFn(depExprs);
            }
            else if (provider.useExisting) {
                var depExprs = convertDeps(providerIndex, [{ token: provider.useExisting }]);
                expr = depExprs[0];
            }
            else {
                expr = value_util_1.convertValueToOutputAst(ctx, provider.useValue);
            }
            return expr;
        });
        var providerExpr = o.fn(allParams, [new o.ReturnStatement(o.literalArr(exprs))], o.INFERRED_TYPE);
        return {
            providerExpr: providerExpr,
            flags: flags | 1024 /* TypeFactoryProvider */,
            depsExpr: o.literalArr(allDepDefs)
        };
        function convertDeps(providerIndex, deps) {
            return deps.map(function (dep, depIndex) {
                var paramName = "p" + providerIndex + "_" + depIndex;
                allParams.push(new o.FnParam(paramName, o.DYNAMIC_TYPE));
                allDepDefs.push(depDef(ctx, dep));
                return o.variable(paramName);
            });
        }
    }
    function singleProviderDef(ctx, flags, providerType, providerMeta) {
        var providerExpr;
        var deps;
        if (providerType === template_ast_1.ProviderAstType.Directive || providerType === template_ast_1.ProviderAstType.Component) {
            providerExpr = ctx.importExpr(providerMeta.useClass.reference);
            flags |= 16384 /* TypeDirective */;
            deps = providerMeta.deps || providerMeta.useClass.diDeps;
        }
        else {
            if (providerMeta.useClass) {
                providerExpr = ctx.importExpr(providerMeta.useClass.reference);
                flags |= 512 /* TypeClassProvider */;
                deps = providerMeta.deps || providerMeta.useClass.diDeps;
            }
            else if (providerMeta.useFactory) {
                providerExpr = ctx.importExpr(providerMeta.useFactory.reference);
                flags |= 1024 /* TypeFactoryProvider */;
                deps = providerMeta.deps || providerMeta.useFactory.diDeps;
            }
            else if (providerMeta.useExisting) {
                providerExpr = o.NULL_EXPR;
                flags |= 2048 /* TypeUseExistingProvider */;
                deps = [{ token: providerMeta.useExisting }];
            }
            else {
                providerExpr = value_util_1.convertValueToOutputAst(ctx, providerMeta.useValue);
                flags |= 256 /* TypeValueProvider */;
                deps = [];
            }
        }
        var depsExpr = o.literalArr(deps.map(function (dep) { return depDef(ctx, dep); }));
        return { providerExpr: providerExpr, flags: flags, depsExpr: depsExpr };
    }
    function tokenExpr(ctx, tokenMeta) {
        return tokenMeta.identifier ? ctx.importExpr(tokenMeta.identifier.reference) :
            o.literal(tokenMeta.value);
    }
    function depDef(ctx, dep) {
        // Note: the following fields have already been normalized out by provider_analyzer:
        // - isAttribute, isHost
        var expr = dep.isValue ? value_util_1.convertValueToOutputAst(ctx, dep.value) : tokenExpr(ctx, dep.token);
        var flags = 0 /* None */;
        if (dep.isSkipSelf) {
            flags |= 1 /* SkipSelf */;
        }
        if (dep.isOptional) {
            flags |= 2 /* Optional */;
        }
        if (dep.isSelf) {
            flags |= 4 /* Self */;
        }
        if (dep.isValue) {
            flags |= 8 /* Value */;
        }
        return flags === 0 /* None */ ? expr : o.literalArr([o.literal(flags), expr]);
    }
    exports.depDef = depDef;
    function lifecycleHookToNodeFlag(lifecycleHook) {
        var nodeFlag = 0 /* None */;
        switch (lifecycleHook) {
            case lifecycle_reflector_1.LifecycleHooks.AfterContentChecked:
                nodeFlag = 2097152 /* AfterContentChecked */;
                break;
            case lifecycle_reflector_1.LifecycleHooks.AfterContentInit:
                nodeFlag = 1048576 /* AfterContentInit */;
                break;
            case lifecycle_reflector_1.LifecycleHooks.AfterViewChecked:
                nodeFlag = 8388608 /* AfterViewChecked */;
                break;
            case lifecycle_reflector_1.LifecycleHooks.AfterViewInit:
                nodeFlag = 4194304 /* AfterViewInit */;
                break;
            case lifecycle_reflector_1.LifecycleHooks.DoCheck:
                nodeFlag = 262144 /* DoCheck */;
                break;
            case lifecycle_reflector_1.LifecycleHooks.OnChanges:
                nodeFlag = 524288 /* OnChanges */;
                break;
            case lifecycle_reflector_1.LifecycleHooks.OnDestroy:
                nodeFlag = 131072 /* OnDestroy */;
                break;
            case lifecycle_reflector_1.LifecycleHooks.OnInit:
                nodeFlag = 65536 /* OnInit */;
                break;
        }
        return nodeFlag;
    }
    exports.lifecycleHookToNodeFlag = lifecycleHookToNodeFlag;
    function componentFactoryResolverProviderDef(reflector, ctx, flags, entryComponents) {
        var entryComponentFactories = entryComponents.map(function (entryComponent) { return ctx.importExpr(entryComponent.componentFactory); });
        var token = identifiers_1.createTokenForExternalReference(reflector, identifiers_1.Identifiers.ComponentFactoryResolver);
        var classMeta = {
            diDeps: [
                { isValue: true, value: o.literalArr(entryComponentFactories) },
                { token: token, isSkipSelf: true, isOptional: true },
                { token: identifiers_1.createTokenForExternalReference(reflector, identifiers_1.Identifiers.NgModuleRef) },
            ],
            lifecycleHooks: [],
            reference: reflector.resolveExternalReference(identifiers_1.Identifiers.CodegenComponentFactoryResolver)
        };
        var _a = singleProviderDef(ctx, flags, template_ast_1.ProviderAstType.PrivateService, {
            token: token,
            multi: false,
            useClass: classMeta,
        }), providerExpr = _a.providerExpr, providerFlags = _a.flags, depsExpr = _a.depsExpr;
        return { providerExpr: providerExpr, flags: providerFlags, depsExpr: depsExpr, tokenExpr: tokenExpr(ctx, token) };
    }
    exports.componentFactoryResolverProviderDef = componentFactoryResolverProviderDef;
});
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoicHJvdmlkZXJfY29tcGlsZXIuanMiLCJzb3VyY2VSb290IjoiIiwic291cmNlcyI6WyIuLi8uLi8uLi8uLi8uLi8uLi8uLi9wYWNrYWdlcy9jb21waWxlci9zcmMvdmlld19jb21waWxlci9wcm92aWRlcl9jb21waWxlci50cyJdLCJuYW1lcyI6W10sIm1hcHBpbmdzIjoiQUFBQTs7Ozs7O0dBTUc7Ozs7Ozs7Ozs7Ozs7SUFLSCxpRUFBNEU7SUFDNUUsaUZBQXNEO0lBQ3RELDJEQUEwQztJQUMxQyxzRUFBNkQ7SUFDN0QsbUZBQTZFO0lBRzdFLFNBQWdCLFdBQVcsQ0FBQyxHQUFrQixFQUFFLFdBQXdCO1FBTXRFLElBQUksS0FBSyxlQUFpQixDQUFDO1FBQzNCLElBQUksQ0FBQyxXQUFXLENBQUMsS0FBSyxFQUFFO1lBQ3RCLEtBQUssMkJBQTBCLENBQUM7U0FDakM7UUFDRCxJQUFJLFdBQVcsQ0FBQyxZQUFZLEtBQUssOEJBQWUsQ0FBQyxjQUFjLEVBQUU7WUFDL0QsS0FBSyw4QkFBNkIsQ0FBQztTQUNwQztRQUNELElBQUksV0FBVyxDQUFDLFFBQVEsRUFBRTtZQUN4QixLQUFLLHVDQUFnQyxDQUFDO1NBQ3ZDO1FBQ0QsV0FBVyxDQUFDLGNBQWMsQ0FBQyxPQUFPLENBQUMsVUFBQyxhQUFhO1lBQy9DLHFEQUFxRDtZQUNyRCxJQUFJLGFBQWEsS0FBSyxvQ0FBYyxDQUFDLFNBQVM7Z0JBQzFDLFdBQVcsQ0FBQyxZQUFZLEtBQUssOEJBQWUsQ0FBQyxTQUFTO2dCQUN0RCxXQUFXLENBQUMsWUFBWSxLQUFLLDhCQUFlLENBQUMsU0FBUyxFQUFFO2dCQUMxRCxLQUFLLElBQUksdUJBQXVCLENBQUMsYUFBYSxDQUFDLENBQUM7YUFDakQ7UUFDSCxDQUFDLENBQUMsQ0FBQztRQUNHLElBQUEsS0FBaUQsV0FBVyxDQUFDLGFBQWEsQ0FBQyxDQUFDO1lBQzlFLGdCQUFnQixDQUFDLEdBQUcsRUFBRSxLQUFLLEVBQUUsV0FBVyxDQUFDLFNBQVMsQ0FBQyxDQUFDLENBQUM7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QSxFQUFFLFFBQVEsY0FLN0MsQ0FBQztRQUNQLE9BQU8sRUFBQyxZQUFZLGNBQUEsRUFBRSxLQUFLLEVBQUUsYUFBYSxFQUFFLFFBQVEsVUFBQSxFQUFFLFNBQVMsRUFBRSxTQUFTLENBQUMsR0FBRyxFQUFFLEtBQUssQ0FBQyxFQUFDLENBQUM7SUFDMUYsQ0FBQztJQTNCRCxrRkEyQkMiLCJzb3VyY2VzQ29udGVudCI6WyIvKipcbiAqIEBsaWNlbnNlXG4gKiBDb3B5cmlnaHQgR29vZ2xlIExMQyBBbGwgUmlnaHRzIFJlc2VydmVkLlxuICpcbiAqIFVzZSBvZiB0aGlzIHNvdXJjZSBjb2RlIGlzIGdvdmVybmVkIGJ5IGFuIE1JVC1zdHlsZSBsaWNlbnNlIHRoYXQgY2FuIGJlXG4gKiBmb3VuZCBpbiB0aGUgTElDRU5TRSBmaWxlIGF0IGh0dHBzOi8vYW5ndWxhci5pby9saWNlbnNlXG4gKi9cblxuaW1wb3J0IHtDb21waWxlRGlEZXBlbmRlbmN5TWV0YWRhdGEsIENvbXBpbGVFbnRyeUNvbXBvbmVudE1ldGFkYXRhLCBDb21waWxlUHJvdmlkZXJNZXRhZGF0YSwgQ29tcGlsZVRva2VuTWV0YWRhdGF9IGZyb20gJy4uL2NvbXBpbGVfbWV0YWRhdGEnO1xuaW1wb3J0IHtDb21waWxlUmVmbGVjdG9yfSBmcm9tICcuLi9jb21waWxlX3JlZmxlY3Rvcic7XG5pbXBvcnQge0RlcEZsYWdzLCBOb2RlRmxhZ3N9IGZyb20gJy4uL2NvcmUnO1xuaW1wb3J0IHtjcmVhdGVUb2tlbkZvckV4dGVybmFsUmVmZXJlbmNlLCBJZGVudGlmaWVyc30gZnJvbSAnLi4vaWRlbnRpZmllcnMnO1xuaW1wb3J0IHtMaWZlY3ljbGVIb29rc30gZnJvbSAnLi4vbGlmZWN5Y2xlX3JlZmxlY3Rvcic7XG5pbXBvcnQgKiBhcyBvIGZyb20gJy4uL291dHB1dC9vdXRwdXRfYXN0JztcbmltcG9ydCB7Y29udmVydFZhbHVlVG9PdXRwdXRBc3R9IGZyb20gJy4uL291dHB1dC92YWx1ZV91dGlsJztcbmltcG9ydCB7UHJvdmlkZXJBc3QsIFByb3ZpZGVyQXN0VHlwZX0gZnJvbSAnLi4vdGVtcGxhdGVfcGFyc2VyL3RlbXBsYXRlX2FzdCc7XG5pbXBvcnQge091dHB1dENvbnRleHR9IGZyb20gJy4uL3V0aWwnO1xuXG5leHBvcnQgZnVuY3Rpb24gcHJvdmlkZXJEZWYoY3R4OiBPdXRwdXRDb250ZXh0LCBwcm92aWRlckFzdDogUHJvdmlkZXJBc3QpOiB7XG4gIHByb3ZpZGVyRXhwcjogby5FeHByZXNzaW9uLFxuICBmbGFnczogTm9kZUZsYWdzLFxuICBkZXBzRXhwcjogby5FeHByZXNzaW9uLFxuICB0b2tlbkV4cHI6IG8uRXhwcmVzc2lvblxufSB7XG4gIGxldCBmbGFncyA9IE5vZGVGbGFncy5Ob25lO1xuICBpZiAoIXByb3ZpZGVyQXN0LmVhZ2VyKSB7XG4gICAgZmxhZ3MgfD0gTm9kZUZsYWdzLkxhenlQcm92aWRlcjtcbiAgfVxuICBpZiAocHJvdmlkZXJBc3QucHJvdmlkZXJUeXBlID09PSBQcm92aWRlckFzdFR5cGUuUHJpdmF0ZVNlcnZpY2UpIHtcbiAgICBmbGFncyB8PSBOb2RlRmxhZ3MuUHJpdmF0ZVByb3ZpZGVyO1xuICB9XG4gIGlmIChwcm92aWRlckFzdC5pc01vZHVsZSkge1xuICAgIGZsYWdzIHw9IE5vZGVGbGFncy5UeXBlTW9kdWxlUHJvdmlkZXI7XG4gIH1cbiAgcHJvdmlkZXJBc3QubGlmZWN5Y2xlSG9va3MuZm9yRWFjaCgobGlmZWN5Y2xlSG9vaykgPT4ge1xuICAgIC8vIGZvciByZWd1bGFyIHByb3ZpZGVycywgd2Ugb25seSBzdXBwb3J0IG5nT25EZXN0cm95XG4gICAgaWYgKGxpZmVjeWNsZUhvb2sgPT09IExpZmVjeWNsZUhvb2tzLk9uRGVzdHJveSB8fFxuICAgICAgICBwcm92aWRlckFzdC5wcm92aWRlclR5cGUgPT09IFByb3ZpZGVyQXN0VHlwZS5EaXJlY3RpdmUgfHxcbiAgICAgICAgcHJvdmlkZXJBc3QucHJvdmlkZXJUeXBlID09PSBQcm92aWRlckFzdFR5cGUuQ29tcG9uZW50KSB7XG4gICAgICBmbGFncyB8PSBsaWZlY3ljbGVIb29rVG9Ob2RlRmxhZyhsaWZlY3ljbGVIb29rKTtcbiAgICB9XG4gIH0pO1xuICBjb25zdCB7cHJvdmlkZXJFeHByLCBmbGFnczogcHJvdmlkZXJGbGFncywgZGVwc0V4cHJ9ID0gcHJvdmlkZXJBc3QubXVsdGlQcm92aWRlciA/XG4gICAgICBtdWx0aVByb3ZpZGVyRGVmKGN0eCwgZmxhZ3MsIHByb3ZpZGVyQXN0LnByb3ZpZGVycykgOlxuICAgICAgc2luZ2xlUHJvdmlkZXJEZWYoY3R4LCBmbGFncywgcHJvdmlkZXJBc3QucHJvdmlkZXJUeXBlLCBwcm92aWRlckFzdC5wcm92aWRlcnNbMF0pO1xuICByZXR1cm4ge1xuICAgIHByb3ZpZGVyRXhwcixcbiAgICBmbGFnczogcHJvdmlkZXJGbGFncyxcbiAgICBkZXBzRXhwcixcbiAgICB0b2tlbkV4cHI6IHRva2VuRXhwcihjdHgsIHByb3ZpZGVyQXN0LnRva2VuKSxcbiAgfTtcbn1cblxuZnVuY3Rpb24gbXVsdGlQcm92aWRlckRlZihcbiAgICBjdHg6IE91dHB1dENvbnRleHQsIGZsYWdzOiBOb2RlRmxhZ3MsIHByb3ZpZGVyczogQ29tcGlsZVByb3ZpZGVyTWV0YWRhdGFbXSk6XG4gICAge3Byb3ZpZGVyRXhwcjogby5FeHByZXNzaW9uLCBmbGFnczogTm9kZUZsYWdzLCBkZXBzRXhwcjogby5FeHByZXNzaW9ufSB7XG4gIGNvbnN0IGFsbERlcERlZnM6IG8uRXhwcmVzc2lvbltdID0gW107XG4gIGNvbnN0IGFsbFBhcmFtczogby5GblBhcmFtW10gPSBbXTtcbiAgY29uc3QgZXhwcnMgPSBwcm92aWRlcnMubWFwKChwcm92aWRlciwgcHJvdmlkZXJJbmRleCkgPT4ge1xuICAgIGxldCBleHByOiBvLkV4cHJlc3Npb247XG4gICAgaWYgKHByb3ZpZGVyLnVzZUNsYXNzKSB7XG4gICAgICBjb25zdCBkZXBFeHBycyA9IGNvbnZlcnREZXBzKHByb3ZpZGVySW5kZXgsIHByb3ZpZGVyLmRlcHMgfHwgcHJvdmlkZXIudXNlQ2xhc3MuZGlEZXBzKTtcbiAgICAgIGV4cHIgPSBjdHguaW1wb3J0RXhwcihwcm92aWRlci51c2VDbGFzcy5yZWZlcmVuY2UpLmluc3RhbnRpYXRlKGRlcEV4cHJzKTtcbiAgICB9IGVsc2UgaWYgKHByb3ZpZGVyLnVzZUZhY3RvcnkpIHtcbiAgICAgIGNvbnN0IGRlcEV4cHJzID0gY29udmVydERlcHMocHJvdmlkZXJJbmRleCwgcHJvdmlkZXIuZGVwcyB8fCBwcm92aWRlci51c2VGYWN0b3J5LmRpRGVwcyk7XG4gICAgICBleHByID0gY3R4LmltcG9ydEV4cHIocHJvdmlkZXIudXNlRmFjdG9yeS5yZWZlcmVuY2UpLmNhbGxGbihkZXBFeHBycyk7XG4gICAgfSBlbHNlIGlmIChwcm92aWRlci51c2VFeGlzdGluZykge1xuICAgICAgY29uc3QgZGVwRXhwcnMgPSBjb252ZXJ0RGVwcyhwcm92aWRlckluZGV4LCBbe3Rva2VuOiBwcm92aWRlci51c2VFeGlzdGluZ31dKTtcbiAgICAgIGV4cHIgPSBkZXBFeHByc1swXTtcbiAgICB9IGVsc2Uge1xuICAgICAgZXhwciA9IGNvbnZlcnRWYWx1ZVRvT3V0cHV0QXN0KGN0eCwgcHJvdmlkZXIudXNlVmFsdWUpO1xuICAgIH1cbiAgICByZXR1cm4gZXhwcjtcbiAgfSk7XG4gIGNvbnN0IHByb3ZpZGVyRXhwciA9XG4gICAgICBvLmZuKGFsbFBhcmFtcywgW25ldyBvLlJldHVyblN0YXRlbWVudChvLmxpdGVyYWxBcnIoZXhwcnMpKV0sIG8uSU5GRVJSRURfVFlQRSk7XG4gIHJldHVybiB7XG4gICAgcHJvdmlkZXJFeHByLFxuICAgIGZsYWdzOiBmbGFncyB8IE5vZGVGbGFncy5UeXBlRmFjdG9yeVByb3ZpZGVyLFxuICAgIGRlcHNFeHByOiBvLmxpdGVyYWxBcnIoYWxsRGVwRGVmcylcbiAgfTtcblxuICBmdW5jdGlvbiBjb252ZXJ0RGVwcyhwcm92aWRlckluZGV4OiBudW1iZXIsIGRlcHM6IENvbXBpbGVEaURlcGVuZGVuY3lNZXRhZGF0YVtdKSB7XG4gICAgcmV0dXJuIGRlcHMubWFwKChkZXAsIGRlcEluZGV4KSA9PiB7XG4gICAgICBjb25zdCBwYXJhbU5hbWUgPSBgcCR7cHJvdmlkZXJJbmRleH1fJHtkZXBJbmRleH1gO1xuICAgICAgYWxsUGFyYW1zLnB1c2gobmV3IG8uRm5QYXJhbShwYXJhbU5hbWUsIG8uRFlOQU1JQ19UWVBFKSk7XG4gICAgICBhbGxEZXBEZWZzLnB1c2goZGVwRGVmKGN0eCwgZGVwKSk7XG4gICAgICByZXR1cm4gby52YXJpYWJsZShwYXJhbU5hbWUpO1xuICAgIH0pO1xuICB9XG59XG5cbmZ1bmN0aW9uIHNpbmdsZVByb3ZpZGVyRGVmKFxuICAgIGN0eDogT3V0cHV0Q29udGV4dCwgZmxhZ3M6IE5vZGVGbGFncywgcHJvdmlkZXJUeXBlOiBQcm92aWRlckFzdFR5cGUsXG4gICAgcHJvdmlkZXJNZXRhOiBDb21waWxlUHJvdmlkZXJNZXRhZGF0YSk6XG4gICAge3Byb3ZpZGVyRXhwcjogby5FeHByZXNzaW9uLCBmbGFnczogTm9kZUZsYWdzLCBkZXBzRXhwcjogby5FeHByZXNzaW9ufSB7XG4gIGxldCBwcm92aWRlckV4cHI6IG8uRXhwcmVzc2lvbjtcbiAgbGV0IGRlcHM6IENvbXBpbGVEaURlcGVuZGVuY3lNZXRhZGF0YVtdO1xuICBpZiAocHJvdmlkZXJUeXBlID09PSBQcm92aWRlckFzdFR5cGUuRGlyZWN0aXZlIHx8IHByb3ZpZGVyVHlwZSA9PT0gUHJvdmlkZXJBc3RUeXBlLkNvbXBvbmVudCkge1xuICAgIHByb3ZpZGVyRXhwciA9IGN0eC5pbXBvcnRFeHByKHByb3ZpZGVyTWV0YS51c2VDbGFzcyEucmVmZXJlbmNlKTtcbiAgICBmbGFncyB8PSBOb2RlRmxhZ3MuVHlwZURpcmVjdGl2ZTtcbiAgICBkZXBzID0gcHJvdmlkZXJNZXRhLmRlcHMgfHwgcHJvdmlkZXJNZXRhLnVzZUNsYXNzIS5kaURlcHM7XG4gIH0gZWxzZSB7XG4gICAgaWYgKHByb3ZpZGVyTWV0YS51c2VDbGFzcykge1xuICAgICAgcHJvdmlkZXJFeHByID0gY3R4LmltcG9ydEV4cHIocHJvdmlkZXJNZXRhLnVzZUNsYXNzLnJlZmVyZW5jZSk7XG4gICAgICBmbGFncyB8PSBOb2RlRmxhZ3MuVHlwZUNsYXNzUHJvdmlkZXI7XG4gICAgICBkZXBzID0gcHJvdmlkZXJNZXRhLmRlcHMgfHwgcHJvdmlkZXJNZXRhLnVzZUNsYXNzLmRpRGVwcztcbiAgICB9IGVsc2UgaWYgKHByb3ZpZGVyTWV0YS51c2VGYWN0b3J5KSB7XG4gICAgICBwcm92aWRlckV4cHIgPSBjdHguaW1wb3J0RXhwcihwcm92aWRlck1ldGEudXNlRmFjdG9yeS5yZWZlcmVuY2UpO1xuICAgICAgZmxhZ3MgfD0gTm9kZUZsYWdzLlR5cGVGYWN0b3J5UHJvdmlkZXI7XG4gICAgICBkZXBzID0gcHJvdmlkZXJNZXRhLmRlcHMgfHwgcHJvdmlkZXJNZXRhLnVzZUZhY3RvcnkuZGlEZXBzO1xuICAgIH0gZWxzZSBpZiAocHJvdmlkZXJNZXRhLnVzZUV4aXN0aW5nKSB7XG4gICAgICBwcm92aWRlckV4cHIgPSBvLk5VTExfRVhQUjtcbiAgICAgIGZsYWdzIHw9IE5vZGVGbGFncy5UeXBlVXNlRXhpc3RpbmdQcm92aWRlcjtcbiAgICAgIGRlcHMgPSBbe3Rva2VuOiBwcm92aWRlck1ldGEudXNlRXhpc3Rpbmd9XTtcbiAgICB9IGVsc2Uge1xuICAgICAgcHJvdmlkZXJFeHByID0gY29udmVydFZhbHVlVG9PdXRwdXRBc3QoY3R4LCBwcm92aWRlck1ldGEudXNlVmFsdWUpO1xuICAgICAgZmxhZ3MgfD0gTm9kZUZsYWdzLlR5cGVWYWx1ZVByb3ZpZGVyO1xuICAgICAgZGVwcyA9IFtdO1xuICAgIH1cbiAgfVxuICBjb25zdCBkZXBzRXhwciA9IG8ubGl0ZXJhbEFycihkZXBzLm1hcChkZXAgPT4gZGVwRGVmKGN0eCwgZGVwKSkpO1xuICByZXR1cm4ge3Byb3ZpZGVyRXhwciwgZmxhZ3MsIGRlcHNFeHByfTtcbn1cblxuZnVuY3Rpb24gdG9rZW5FeHByKGN0eDogT3V0cHV0Q29udGV4dCwgdG9rZW5NZXRhOiBDb21waWxlVG9rZW5NZXRhZGF0YSk6IG8uRXhwcmVzc2lvbiB7XG4gIHJldHVybiB0b2tlbk1ldGEuaWRlbnRpZmllciA/IGN0eC5pbXBvcnRFeHByKHRva2VuTWV0YS5pZGVudGlmaWVyLnJlZmVyZW5jZSkgOlxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBvLmxpdGVyYWwodG9rZW5NZXRhLnZhbHVlKTtcbn1cblxuZXhwb3J0IGZ1bmN0aW9uIGRlcERlZihjdHg6IE91dHB1dENvbnRleHQsIGRlcDogQ29tcGlsZURpRGVwZW5kZW5jeU1ldGFkYXRhKTogby5FeHByZXNzaW9uIHtcbiAgLy8gTm90ZTogdGhlIGZvbGxvd2luZyBmaWVsZHMgaGF2ZSBhbHJlYWR5IGJlZW4gbm9ybWFsaXplZCBvdXQgYnkgcHJvdmlkZXJfYW5hbHl6ZXI6XG4gIC8vIC0gaXNBdHRyaWJ1dGUsIGlzSG9zdFxuICBjb25zdCBleHByID0gZGVwLmlzVmFsdWUgPyBjb252ZXJ0VmFsdWVUb091dHB1dEFzdChjdHgsIGRlcC52YWx1ZSkgOiB0b2tlbkV4cHIoY3R4LCBkZXAudG9rZW4hKTtcbiAgbGV0IGZsYWdzID0gRGVwRmxhZ3MuTm9uZTtcbiAgaWYgKGRlcC5pc1NraXBTZWxmKSB7XG4gICAgZmxhZ3MgfD0gRGVwRmxhZ3MuU2tpcFNlbGY7XG4gIH1cbiAgaWYgKGRlcC5pc09wdGlvbmFsKSB7XG4gICAgZmxhZ3MgfD0gRGVwRmxhZ3MuT3B0aW9uYWw7XG4gIH1cbiAgaWYgKGRlcC5pc1NlbGYpIHtcbiAgICBmbGFncyB8PSBEZXBGbGFncy5TZWxmO1xuICB9XG4gIGlmIChkZXAuaXNWYWx1ZSkge1xuICAgIGZsYWdzIHw9IERlcEZsYWdzLlZhbHVlO1xuICB9XG4gIHJldHVybiBmbGFncyA9PT0gRGVwRmxhZ3MuTm9uZSA/IGV4cHIgOiBvLmxpdGVyYWxBcnIoW28ubGl0ZXJhbChmbGFncyksIGV4cHJdKTtcbn1cblxuZXhwb3J0IGZ1bmN0aW9uIGxpZmVjeWNsZUhvb2tUb05vZGVGbGFnKGxpZmVjeWNsZUhvb2s6IExpZmVjeWNsZUhvb2tzKTogTm9kZUZsYWdzIHtcbiAgbGV0IG5vZGVGbGFnID0gTm9kZUZsYWdzLk5vbmU7XG4gIHN3aXRjaCAobGlmZWN5Y2xlSG9vaykge1xuICAgIGNhc2UgTGlmZWN5Y2xlSG9va3MuQWZ0ZXJDb250ZW50Q2hlY2tlZDpcbiAgICAgIG5vZGVGbGFnID0gTm9kZUZsYWdzLkFmdGVyQ29udGVudENoZWNrZWQ7XG4gICAgICBicmVhaztcbiAgICBjYXNlIExpZmVjeWNsZUhvb2tzLkFmdGVyQ29udGVudEluaXQ6XG4gICAgICBub2RlRmxhZyA9IE5vZGVGbGFncy5BZnRlckNvbnRlbnRJbml0O1xuICAgICAgYnJlYWs7XG4gICAgY2FzZSBMaWZlY3ljbGVIb29rcy5BZnRlclZpZXdDaGVja2VkOlxuICAgICAgbm9kZUZsYWcgPSBOb2RlRmxhZ3MuQWZ0ZXJWaWV3Q2hlY2tlZDtcbiAgICAgIGJyZWFrO1xuICAgIGNhc2UgTGlmZWN5Y2xlSG9va3MuQWZ0ZXJWaWV3SW5pdDpcbiAgICAgIG5vZGVGbGFnID0gTm9kZUZsYWdzLkFmdGVyVmlld0luaXQ7XG4gICAgICBicmVhaztcbiAgICBjYXNlIExpZmVjeWNsZUhvb2tzLkRvQ2hlY2s6XG4gICAgICBub2RlRmxhZyA9IE5vZGVGbGFncy5Eb0NoZWNrO1xuICAgICAgYnJlYWs7XG4gICAgY2FzZSBMaWZlY3ljbGVIb29rcy5PbkNoYW5nZXM6XG4gICAgICBub2RlRmxhZyA9IE5vZGVGbGFncy5PbkNoYW5nZXM7XG4gICAgICBicmVhaztcbiAgICBjYXNlIExpZmVjeWNsZUhvb2tzLk9uRGVzdHJveTpcbiAgICAgIG5vZGVGbGFnID0gTm9kZUZsYWdzLk9uRGVzdHJveTtcbiAgICAgIGJyZWFrO1xuICAgIGNhc2UgTGlmZWN5Y2xlSG9va3MuT25Jbml0OlxuICAgICAgbm9kZUZsYWcgPSBOb2RlRmxhZ3MuT25Jbml0O1xuICAgICAgYnJlYWs7XG4gIH1cbiAgcmV0dXJuIG5vZGVGbGFnO1xufVxuXG5leHBvcnQgZnVuY3Rpb24gY29tcG9uZW50RmFjdG9yeVJlc29sdmVyUHJvdmlkZXJEZWYoXG4gICAgcmVmbGVjdG9yOiBDb21waWxlUmVmbGVjdG9yLCBjdHg6IE91dHB1dENvbnRleHQsIGZsYWdzOiBOb2RlRmxhZ3MsXG4gICAgZW50cnlDb21wb25lbnRzOiBDb21waWxlRW50cnlDb21wb25lbnRNZXRhZGF0YVtdKToge1xuICBwcm92aWRlckV4cHI6IG8uRXhwcmVzc2lvbixcbiAgZmxhZ3M6IE5vZGVGbGFncyxcbiAgZGVwc0V4cHI6IG8uRXhwcmVzc2lvbixcbiAgdG9rZW5FeHByOiBvLkV4cHJlc3Npb25cbn0ge1xuICBjb25zdCBlbnRyeUNvbXBvbmVudEZhY3RvcmllcyA9XG4gICAgICBlbnRyeUNvbXBvbmVudHMubWFwKChlbnRyeUNvbXBvbmVudCkgPT4gY3R4LmltcG9ydEV4cHIoZW50cnlDb21wb25lbnQuY29tcG9uZW50RmFjdG9yeSkpO1xuICBjb25zdCB0b2tlbiA9IGNyZWF0ZVRva2VuRm9yRXh0ZXJuYWxSZWZlcmVuY2UocmVmbGVjdG9yLCBJZGVudGlmaWVycy5Db21wb25lbnRGYWN0b3J5UmVzb2x2ZXIpO1xuICBjb25zdCBjbGFzc01ldGEgPSB7XG4gICAgZGlEZXBzOiBbXG4gICAgICB7aXNWYWx1ZTogdHJ1ZSwgdmFsdWU6IG8ubGl0ZXJhbEFycihlbnRyeUNvbXBvbmVudEZhY3Rvcmllcyl9LFxuICAgICAge3Rva2VuOiB0b2tlbiwgaXNTa2lwU2VsZjogdHJ1ZSwgaXNPcHRpb25hbDogdHJ1ZX0sXG4gICAgICB7dG9rZW46IGNyZWF0ZVRva2VuRm9yRXh0ZXJuYWxSZWZlcmVuY2UocmVmbGVjdG9yLCBJZGVudGlmaWVycy5OZ01vZHVsZVJlZil9LFxuICAgIF0sXG4gICAgbGlmZWN5Y2xlSG9va3M6IFtdLFxuICAgIHJlZmVyZW5jZTogcmVmbGVjdG9yLnJlc29sdmVFeHRlcm5hbFJlZmVyZW5jZShJZGVudGlmaWVycy5Db2RlZ2VuQ29tcG9uZW50RmFjdG9yeVJlc29sdmVyKVxuICB9O1xuICBjb25zdCB7cHJvdmlkZXJFeHByLCBmbGFnczogcHJvdmlkZXJGbGFncywgZGVwc0V4cHJ9ID1cbiAgICAgIHNpbmdsZVByb3ZpZGVyRGVmKGN0eCwgZmxhZ3MsIFByb3ZpZGVyQXN0VHlwZS5Qcml2YXRlU2VydmljZSwge1xuICAgICAgICB0b2tlbixcbiAgICAgICAgbXVsdGk6IGZhbHNlLFxuICAgICAgICB1c2VDbGFzczogY2xhc3NNZXRhLFxuICAgICAgfSk7XG4gIHJldHVybiB7cHJvdmlkZXJFeHByLCBmbGFnczogcHJvdmlkZXJGbGFncywgZGVwc0V4cHIsIHRva2VuRXhwcjogdG9rZW5FeHByKGN0eCwgdG9rZW4pfTtcbn1cbiJdfQ==