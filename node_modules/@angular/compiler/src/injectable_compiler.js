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
        define("@angular/compiler/src/injectable_compiler", ["require", "exports", "@angular/compiler/src/compile_metadata", "@angular/compiler/src/identifiers", "@angular/compiler/src/output/output_ast", "@angular/compiler/src/output/value_util"], factory);
    }
})(function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.InjectableCompiler = void 0;
    var compile_metadata_1 = require("@angular/compiler/src/compile_metadata");
    var identifiers_1 = require("@angular/compiler/src/identifiers");
    var o = require("@angular/compiler/src/output/output_ast");
    var value_util_1 = require("@angular/compiler/src/output/value_util");
    function mapEntry(key, value) {
        return { key: key, value: value, quoted: false };
    }
    var InjectableCompiler = /** @class */ (function () {
        function InjectableCompiler(reflector, alwaysGenerateDef) {
            this.reflector = reflector;
            this.alwaysGenerateDef = alwaysGenerateDef;
            this.tokenInjector = reflector.resolveExternalReference(identifiers_1.Identifiers.Injector);
        }
        InjectableCompiler.prototype.depsArray = function (deps, ctx) {
            var _this = this;
            return deps.map(function (dep) {
                var token = dep;
                var args = [token];
                var flags = 0 /* Default */;
                if (Array.isArray(dep)) {
                    for (var i = 0; i < dep.length; i++) {
                        var v = dep[i];
                        if (v) {
                            if (v.ngMetadataName === 'Optional') {
                                flags |= 8 /* Optional */;
                            }
                            else if (v.ngMetadataName === 'SkipSelf') {
                                flags |= 4 /* SkipSelf */;
                            }
                            else if (v.ngMetadataName === 'Self') {
                                flags |= 2 /* Self */;
                            }
                            else if (v.ngMetadataName === 'Inject') {
                                token = v.token;
                            }
                            else {
                                token = v;
                            }
                        }
                    }
                }
                var tokenExpr;
                if (typeof token === 'string') {
                    tokenExpr = o.literal(token);
                }
                else if (token === _this.tokenInjector) {
                    tokenExpr = o.importExpr(identifiers_1.Identifiers.INJECTOR);
                }
                else {
                    tokenExpr = ctx.importExpr(token);
                }
                if (flags !== 0 /* Default */) {
                    args = [tokenExpr, o.literal(flags)];
                }
                else {
                    args = [tokenExpr];
                }
                return o.importExpr(identifiers_1.Identifiers.inject).callFn(args);
            });
        };
        InjectableCompiler.prototype.factoryFor = function (injectable, ctx) {
            var retValue;
            if (injectable.useExisting) {
                retValue = o.importExpr(identifiers_1.Identifiers.inject).callFn([ctx.importExpr(injectable.useExisting)]);
            }
            else if (injectable.useFactory) {
                var deps = injectable.deps || [];
                if (deps.length > 0) {
                    retValue = ctx.importExpr(injectable.useFactory).callFn(this.depsArray(deps, ctx));
                }
                else {
                    return ctx.importExpr(injectable.useFactory);
                }
            }
            else if (injectable.useValue) {
                retValue = value_util_1.convertValueToOutputAst(ctx, injectable.useValue);
            }
            else {
                var clazz = injectable.useClass || injectable.symbol;
                var depArgs = this.depsArray(this.reflector.parameters(clazz), ctx);
                retValue = new o.InstantiateExpr(ctx.importExpr(clazz), depArgs);
            }
            return o.fn([], [new o.ReturnStatement(retValue)], undefined, undefined, injectable.symbol.name + '_Factory');
        };
        InjectableCompiler.prototype.injectableDef = function (injectable, ctx) {
            var providedIn = o.NULL_EXPR;
            if (injectable.providedIn !== undefined) {
                if (injectable.providedIn === null) {
                    providedIn = o.NULL_EXPR;
                }
                else if (typeof injectable.providedIn === 'string') {
                    providedIn = o.literal(injectable.providedIn);
                }
                else {
                    providedIn = ctx.importExpr(injectable.providedIn);
                }
            }
            var def = [
                mapEntry('factory', this.factoryFor(injectable, ctx)),
                mapEntry('token', ctx.importExpr(injectable.type.reference)),
                mapEntry('providedIn', providedIn),
            ];
            return o.importExpr(identifiers_1.Identifiers.ɵɵdefineInjectable).callFn([o.literalMap(def)]);
        };
        InjectableCompiler.prototype.compile = function (injectable, ctx) {
            if (this.alwaysGenerateDef || injectable.providedIn !== undefined) {
                var className = compile_metadata_1.identifierName(injectable.type);
                var clazz = new o.ClassStmt(className, null, [
                    new o.ClassField('ɵprov', o.INFERRED_TYPE, [o.StmtModifier.Static], this.injectableDef(injectable, ctx)),
                ], [], new o.ClassMethod(null, [], []), []);
                ctx.statements.push(clazz);
            }
        };
        return InjectableCompiler;
    }());
    exports.InjectableCompiler = InjectableCompiler;
});
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiaW5qZWN0YWJsZV9jb21waWxlci5qcyIsInNvdXJjZVJvb3QiOiIiLCJzb3VyY2VzIjpbIi4uLy4uLy4uLy4uLy4uLy4uL3BhY2thZ2VzL2NvbXBpbGVyL3NyYy9pbmplY3RhYmxlX2NvbXBpbGVyLnRzIl0sIm5hbWVzIjpbXSwibWFwcGluZ3MiOiJBQUFBOzs7Ozs7R0FNRzs7Ozs7Ozs7Ozs7OztJQUdILDJFQUErSDtJQUcvSCxpRUFBMEM7SUFDMUMsMkRBQXlDO0lBQ3pDLHNFQUE0RDtJQWE1RCxTQUFTLFFBQVEsQ0FBQyxHQUFXLEVBQUUsS0FBbUI7UUFDaEQsT0FBTyxFQUFDLEdBQUcsS0FBQSxFQUFFLEtBQUssT0FBQSxFQUFFLE1BQU0sRUFBRSxLQUFLLEVBQUMsQ0FBQztJQUNyQyxDQUFDO0lBRUQ7UUFFRSw0QkFBb0IsU0FBMkIsRUFBVSxpQkFBMEI7WUFBL0QsY0FBUyxHQUFULFNBQVMsQ0FBa0I7WUFBVSxzQkFBaUIsR0FBakIsaUJBQWlCLENBQVM7WUFDakYsSUFBSSxDQUFDLGFBQWEsR0FBRyxTQUFTLENBQUMsd0JBQXdCLENBQUMseUJBQVcsQ0FBQyxRQUFRLENBQUMsQ0FBQztRQUNoRixDQUFDO1FBRU8sc0NBQVMsR0FBakIsVUFBa0IsSUFBVyxFQUFFLEdBQWtCO1lBQWpELGlCQXdDQztZQXZDQyxPQUFPLElBQUksQ0FBQyxHQUFHLENBQUMsVUFBQSxHQUFHO2dCQUNqQixJQUFJLEtBQUssR0FBRyxHQUFHLENBQUM7Z0JBQ2hCLElBQUksSUFBSSxHQUFHLENBQUMsS0FBSyxDQUFDLENBQUM7Z0JBQ25CLElBQUksS0FBSyxrQkFBbUMsQ0FBQztnQkFDN0MsSUFBSSxLQUFLLENBQUMsT0FBTyxDQUFDLEdBQUcsQ0FBQyxFQUFFO29CQUN0QixLQUFLLElBQUksQ0FBQyxHQUFHLENBQUMsRUFBRSxDQUFDLEdBQUcsR0FBRyxDQUFDLE1BQU0sRUFBRSxDQUFDLEVBQUUsRUFBRTt3QkFDbkMsSUFBTSxDQUFDLEdBQUcsR0FBRyxDQUFDLENBQUMsQ0FB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Q0FBQyxrQkFBa0IsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsQ0FBQyxVQUFVLENBQUMsR0FBRyxDQUFDLENBQUMsQ0FBQyxDQUFDO1FBQ2xGLENBQUM7UUFFRCxvQ0FBTyxHQUFQLFVBQVEsVUFBcUMsRUFBRSxHQUFrQjtZQUMvRCxJQUFJLElBQUksQ0FBQyxpQkFBaUIsSUFBSSxVQUFVLENBQUMsVUFBVSxLQUFLLFNBQVMsRUFBRTtnQkFDakUsSUFBTSxTQUFTLEdBQUcsaUNBQWMsQ0FBQyxVQUFVLENBQUMsSUFBSSxDQUFFLENBQUM7Z0JBQ25ELElBQU0sS0FBSyxHQUFHLElBQUksQ0FBQyxDQUFDLFNBQVMsQ0FDekIsU0FBUyxFQUFFLElBQUksRUFDZjtvQkFDRSxJQUFJLENBQUMsQ0FBQyxVQUFVLENBQ1osT0FBTyxFQUFFLENBQUMsQ0FBQyxhQUFhLEVBQUUsQ0FBQyxDQUFDLENBQUMsWUFBWSxDQUFDLE1BQU0sQ0FBQyxFQUNqRCxJQUFJLENBQUMsYUFBYSxDQUFDLFVBQVUsRUFBRSxHQUFHLENBQUMsQ0FBQztpQkFDekMsRUFDRCxFQUFFLEVBQUUsSUFBSSxDQUFDLENBQUMsV0FBVyxDQUFDLElBQUksRUFBRSxFQUFFLEVBQUUsRUFBRSxDQUFDLEVBQUUsRUFBRSxDQUFDLENBQUM7Z0JBQzdDLEdBQUcsQ0FBQyxVQUFVLENBQUMsSUFBSSxDQUFDLEtBQUssQ0FBQyxDQUFDO2FBQzVCO1FBQ0gsQ0FBQztRQUNILHlCQUFDO0lBQUQsQ0FBQyxBQXhHRCxJQXdHQztJQXhHWSxnREFBa0IiLCJzb3VyY2VzQ29udGVudCI6WyIvKipcbiAqIEBsaWNlbnNlXG4gKiBDb3B5cmlnaHQgR29vZ2xlIExMQyBBbGwgUmlnaHRzIFJlc2VydmVkLlxuICpcbiAqIFVzZSBvZiB0aGlzIHNvdXJjZSBjb2RlIGlzIGdvdmVybmVkIGJ5IGFuIE1JVC1zdHlsZSBsaWNlbnNlIHRoYXQgY2FuIGJlXG4gKiBmb3VuZCBpbiB0aGUgTElDRU5TRSBmaWxlIGF0IGh0dHBzOi8vYW5ndWxhci5pby9saWNlbnNlXG4gKi9cblxuaW1wb3J0IHtTdGF0aWNTeW1ib2x9IGZyb20gJy4vYW90L3N0YXRpY19zeW1ib2wnO1xuaW1wb3J0IHtDb21waWxlSW5qZWN0YWJsZU1ldGFkYXRhLCBDb21waWxlTmdNb2R1bGVNZXRhZGF0YSwgQ29tcGlsZVByb3ZpZGVyTWV0YWRhdGEsIGlkZW50aWZpZXJOYW1lfSBmcm9tICcuL2NvbXBpbGVfbWV0YWRhdGEnO1xuaW1wb3J0IHtDb21waWxlUmVmbGVjdG9yfSBmcm9tICcuL2NvbXBpbGVfcmVmbGVjdG9yJztcbmltcG9ydCB7SW5qZWN0RmxhZ3MsIE5vZGVGbGFnc30gZnJvbSAnLi9jb3JlJztcbmltcG9ydCB7SWRlbnRpZmllcnN9IGZyb20gJy4vaWRlbnRpZmllcnMnO1xuaW1wb3J0ICogYXMgbyBmcm9tICcuL291dHB1dC9vdXRwdXRfYXN0JztcbmltcG9ydCB7Y29udmVydFZhbHVlVG9PdXRwdXRBc3R9IGZyb20gJy4vb3V0cHV0L3ZhbHVlX3V0aWwnO1xuaW1wb3J0IHt0eXBlU291cmNlU3Bhbn0gZnJvbSAnLi9wYXJzZV91dGlsJztcbmltcG9ydCB7TmdNb2R1bGVQcm92aWRlckFuYWx5emVyfSBmcm9tICcuL3Byb3ZpZGVyX2FuYWx5emVyJztcbmltcG9ydCB7T3V0cHV0Q29udGV4dH0gZnJvbSAnLi91dGlsJztcbmltcG9ydCB7Y29tcG9uZW50RmFjdG9yeVJlc29sdmVyUHJvdmlkZXJEZWYsIGRlcERlZiwgcHJvdmlkZXJEZWZ9IGZyb20gJy4vdmlld19jb21waWxlci9wcm92aWRlcl9jb21waWxlcic7XG5cbnR5cGUgTWFwRW50cnkgPSB7XG4gIGtleTogc3RyaW5nLFxuICBxdW90ZWQ6IGJvb2xlYW4sXG4gIHZhbHVlOiBvLkV4cHJlc3Npb25cbn07XG50eXBlIE1hcExpdGVyYWwgPSBNYXBFbnRyeVtdO1xuXG5mdW5jdGlvbiBtYXBFbnRyeShrZXk6IHN0cmluZywgdmFsdWU6IG8uRXhwcmVzc2lvbik6IE1hcEVudHJ5IHtcbiAgcmV0dXJuIHtrZXksIHZhbHVlLCBxdW90ZWQ6IGZhbHNlfTtcbn1cblxuZXhwb3J0IGNsYXNzIEluamVjdGFibGVDb21waWxlciB7XG4gIHByaXZhdGUgdG9rZW5JbmplY3RvcjogU3RhdGljU3ltYm9sO1xuICBjb25zdHJ1Y3Rvcihwcml2YXRlIHJlZmxlY3RvcjogQ29tcGlsZVJlZmxlY3RvciwgcHJpdmF0ZSBhbHdheXNHZW5lcmF0ZURlZjogYm9vbGVhbikge1xuICAgIHRoaXMudG9rZW5JbmplY3RvciA9IHJlZmxlY3Rvci5yZXNvbHZlRXh0ZXJuYWxSZWZlcmVuY2UoSWRlbnRpZmllcnMuSW5qZWN0b3IpO1xuICB9XG5cbiAgcHJpdmF0ZSBkZXBzQXJyYXkoZGVwczogYW55W10sIGN0eDogT3V0cHV0Q29udGV4dCk6IG8uRXhwcmVzc2lvbltdIHtcbiAgICByZXR1cm4gZGVwcy5tYXAoZGVwID0+IHtcbiAgICAgIGxldCB0b2tlbiA9IGRlcDtcbiAgICAgIGxldCBhcmdzID0gW3Rva2VuXTtcbiAgICAgIGxldCBmbGFnczogSW5qZWN0RmxhZ3MgPSBJbmplY3RGbGFncy5EZWZhdWx0O1xuICAgICAgaWYgKEFycmF5LmlzQXJyYXkoZGVwKSkge1xuICAgICAgICBmb3IgKGxldCBpID0gMDsgaSA8IGRlcC5sZW5ndGg7IGkrKykge1xuICAgICAgICAgIGNvbnN0IHYgPSBkZXBbaV07XG4gICAgICAgICAgaWYgKHYpIHtcbiAgICAgICAgICAgIGlmICh2Lm5nTWV0YWRhdGFOYW1lID09PSAnT3B0aW9uYWwnKSB7XG4gICAgICAgICAgICAgIGZsYWdzIHw9IEluamVjdEZsYWdzLk9wdGlvbmFsO1xuICAgICAgICAgICAgfSBlbHNlIGlmICh2Lm5nTWV0YWRhdGFOYW1lID09PSAnU2tpcFNlbGYnKSB7XG4gICAgICAgICAgICAgIGZsYWdzIHw9IEluamVjdEZsYWdzLlNraXBTZWxmO1xuICAgICAgICAgICAgfSBlbHNlIGlmICh2Lm5nTWV0YWRhdGFOYW1lID09PSAnU2VsZicpIHtcbiAgICAgICAgICAgICAgZmxhZ3MgfD0gSW5qZWN0RmxhZ3MuU2VsZjtcbiAgICAgICAgICAgIH0gZWxzZSBpZiAodi5uZ01ldGFkYXRhTmFtZSA9PT0gJ0luamVjdCcpIHtcbiAgICAgICAgICAgICAgdG9rZW4gPSB2LnRva2VuO1xuICAgICAgICAgICAgfSBlbHNlIHtcbiAgICAgICAgICAgICAgdG9rZW4gPSB2O1xuICAgICAgICAgICAgfVxuICAgICAgICAgIH1cbiAgICAgICAgfVxuICAgICAgfVxuXG4gICAgICBsZXQgdG9rZW5FeHByOiBvLkV4cHJlc3Npb247XG4gICAgICBpZiAodHlwZW9mIHRva2VuID09PSAnc3RyaW5nJykge1xuICAgICAgICB0b2tlbkV4cHIgPSBvLmxpdGVyYWwodG9rZW4pO1xuICAgICAgfSBlbHNlIGlmICh0b2tlbiA9PT0gdGhpcy50b2tlbkluamVjdG9yKSB7XG4gICAgICAgIHRva2VuRXhwciA9IG8uaW1wb3J0RXhwcihJZGVudGlmaWVycy5JTkpFQ1RPUik7XG4gICAgICB9IGVsc2Uge1xuICAgICAgICB0b2tlbkV4cHIgPSBjdHguaW1wb3J0RXhwcih0b2tlbik7XG4gICAgICB9XG5cbiAgICAgIGlmIChmbGFncyAhPT0gSW5qZWN0RmxhZ3MuRGVmYXVsdCkge1xuICAgICAgICBhcmdzID0gW3Rva2VuRXhwciwgby5saXRlcmFsKGZsYWdzKV07XG4gICAgICB9IGVsc2Uge1xuICAgICAgICBhcmdzID0gW3Rva2VuRXhwcl07XG4gICAgICB9XG4gICAgICByZXR1cm4gby5pbXBvcnRFeHByKElkZW50aWZpZXJzLmluamVjdCkuY2FsbEZuKGFyZ3MpO1xuICAgIH0pO1xuICB9XG5cbiAgZmFjdG9yeUZvcihpbmplY3RhYmxlOiBDb21waWxlSW5qZWN0YWJsZU1ldGFkYXRhLCBjdHg6IE91dHB1dENvbnRleHQpOiBvLkV4cHJlc3Npb24ge1xuICAgIGxldCByZXRWYWx1ZTogby5FeHByZXNzaW9uO1xuICAgIGlmIChpbmplY3RhYmxlLnVzZUV4aXN0aW5nKSB7XG4gICAgICByZXRWYWx1ZSA9IG8uaW1wb3J0RXhwcihJZGVudGlmaWVycy5pbmplY3QpLmNhbGxGbihbY3R4LmltcG9ydEV4cHIoaW5qZWN0YWJsZS51c2VFeGlzdGluZyldKTtcbiAgICB9IGVsc2UgaWYgKGluamVjdGFibGUudXNlRmFjdG9yeSkge1xuICAgICAgY29uc3QgZGVwcyA9IGluamVjdGFibGUuZGVwcyB8fCBbXTtcbiAgICAgIGlmIChkZXBzLmxlbmd0aCA+IDApIHtcbiAgICAgICAgcmV0VmFsdWUgPSBjdHguaW1wb3J0RXhwcihpbmplY3RhYmxlLnVzZUZhY3RvcnkpLmNhbGxGbih0aGlzLmRlcHNBcnJheShkZXBzLCBjdHgpKTtcbiAgICAgIH0gZWxzZSB7XG4gICAgICAgIHJldHVybiBjdHguaW1wb3J0RXhwcihpbmplY3RhYmxlLnVzZUZhY3RvcnkpO1xuICAgICAgfVxuICAgIH0gZWxzZSBpZiAoaW5qZWN0YWJsZS51c2VWYWx1ZSkge1xuICAgICAgcmV0VmFsdWUgPSBjb252ZXJ0VmFsdWVUb091dHB1dEFzdChjdHgsIGluamVjdGFibGUudXNlVmFsdWUpO1xuICAgIH0gZWxzZSB7XG4gICAgICBjb25zdCBjbGF6eiA9IGluamVjdGFibGUudXNlQ2xhc3MgfHwgaW5qZWN0YWJsZS5zeW1ib2w7XG4gICAgICBjb25zdCBkZXBBcmdzID0gdGhpcy5kZXBzQXJyYXkodGhpcy5yZWZsZWN0b3IucGFyYW1ldGVycyhjbGF6eiksIGN0eCk7XG4gICAgICByZXRWYWx1ZSA9IG5ldyBvLkluc3RhbnRpYXRlRXhwcihjdHguaW1wb3J0RXhwcihjbGF6eiksIGRlcEFyZ3MpO1xuICAgIH1cbiAgICByZXR1cm4gby5mbihcbiAgICAgICAgW10sIFtuZXcgby5SZXR1cm5TdGF0ZW1lbnQocmV0VmFsdWUpXSwgdW5kZWZpbmVkLCB1bmRlZmluZWQsXG4gICAgICAgIGluamVjdGFibGUuc3ltYm9sLm5hbWUgKyAnX0ZhY3RvcnknKTtcbiAgfVxuXG4gIGluamVjdGFibGVEZWYoaW5qZWN0YWJsZTogQ29tcGlsZUluamVjdGFibGVNZXRhZGF0YSwgY3R4OiBPdXRwdXRDb250ZXh0KTogby5FeHByZXNzaW9uIHtcbiAgICBsZXQgcHJvdmlkZWRJbjogby5FeHByZXNzaW9uID0gby5OVUxMX0VYUFI7XG4gICAgaWYgKGluamVjdGFibGUucHJvdmlkZWRJbiAhPT0gdW5kZWZpbmVkKSB7XG4gICAgICBpZiAoaW5qZWN0YWJsZS5wcm92aWRlZEluID09PSBudWxsKSB7XG4gICAgICAgIHByb3ZpZGVkSW4gPSBvLk5VTExfRVhQUjtcbiAgICAgIH0gZWxzZSBpZiAodHlwZW9mIGluamVjdGFibGUucHJvdmlkZWRJbiA9PT0gJ3N0cmluZycpIHtcbiAgICAgICAgcHJvdmlkZWRJbiA9IG8ubGl0ZXJhbChpbmplY3RhYmxlLnByb3ZpZGVkSW4pO1xuICAgICAgfSBlbHNlIHtcbiAgICAgICAgcHJvdmlkZWRJbiA9IGN0eC5pbXBvcnRFeHByKGluamVjdGFibGUucHJvdmlkZWRJbik7XG4gICAgICB9XG4gICAgfVxuICAgIGNvbnN0IGRlZjogTWFwTGl0ZXJhbCA9IFtcbiAgICAgIG1hcEVudHJ5KCdmYWN0b3J5JywgdGhpcy5mYWN0b3J5Rm9yKGluamVjdGFibGUsIGN0eCkpLFxuICAgICAgbWFwRW50cnkoJ3Rva2VuJywgY3R4LmltcG9ydEV4cHIoaW5qZWN0YWJsZS50eXBlLnJlZmVyZW5jZSkpLFxuICAgICAgbWFwRW50cnkoJ3Byb3ZpZGVkSW4nLCBwcm92aWRlZEluKSxcbiAgICBdO1xuICAgIHJldHVybiBvLmltcG9ydEV4cHIoSWRlbnRpZmllcnMuybXJtWRlZmluZUluamVjdGFibGUpLmNhbGxGbihbby5saXRlcmFsTWFwKGRlZildKTtcbiAgfVxuXG4gIGNvbXBpbGUoaW5qZWN0YWJsZTogQ29tcGlsZUluamVjdGFibGVNZXRhZGF0YSwgY3R4OiBPdXRwdXRDb250ZXh0KTogdm9pZCB7XG4gICAgaWYgKHRoaXMuYWx3YXlzR2VuZXJhdGVEZWYgfHwgaW5qZWN0YWJsZS5wcm92aWRlZEluICE9PSB1bmRlZmluZWQpIHtcbiAgICAgIGNvbnN0IGNsYXNzTmFtZSA9IGlkZW50aWZpZXJOYW1lKGluamVjdGFibGUudHlwZSkhO1xuICAgICAgY29uc3QgY2xhenogPSBuZXcgby5DbGFzc1N0bXQoXG4gICAgICAgICAgY2xhc3NOYW1lLCBudWxsLFxuICAgICAgICAgIFtcbiAgICAgICAgICAgIG5ldyBvLkNsYXNzRmllbGQoXG4gICAgICAgICAgICAgICAgJ8m1cHJvdicsIG8uSU5GRVJSRURfVFlQRSwgW28uU3RtdE1vZGlmaWVyLlN0YXRpY10sXG4gICAgICAgICAgICAgICAgdGhpcy5pbmplY3RhYmxlRGVmKGluamVjdGFibGUsIGN0eCkpLFxuICAgICAgICAgIF0sXG4gICAgICAgICAgW10sIG5ldyBvLkNsYXNzTWV0aG9kKG51bGwsIFtdLCBbXSksIFtdKTtcbiAgICAgIGN0eC5zdGF0ZW1lbnRzLnB1c2goY2xhenopO1xuICAgIH1cbiAgfVxufVxuIl19