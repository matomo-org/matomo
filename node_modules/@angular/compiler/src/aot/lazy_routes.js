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
        define("@angular/compiler/src/aot/lazy_routes", ["require", "exports", "tslib", "@angular/compiler/src/compile_metadata"], factory);
    }
})(function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.parseLazyRoute = exports.listLazyRoutes = void 0;
    var tslib_1 = require("tslib");
    var compile_metadata_1 = require("@angular/compiler/src/compile_metadata");
    function listLazyRoutes(moduleMeta, reflector) {
        var e_1, _a, e_2, _b;
        var allLazyRoutes = [];
        try {
            for (var _c = tslib_1.__values(moduleMeta.transitiveModule.providers), _d = _c.next(); !_d.done; _d = _c.next()) {
                var _e = _d.value, provider = _e.provider, module = _e.module;
                if (compile_metadata_1.tokenReference(provider.token) === reflector.ROUTES) {
                    var loadChildren = _collectLoadChildren(provider.useValue);
                    try {
                        for (var loadChildren_1 = (e_2 = void 0, tslib_1.__values(loadChildren)), loadChildren_1_1 = loadChildren_1.next(); !loadChildren_1_1.done; loadChildren_1_1 = loadChildren_1.next()) {
                            var route = loadChildren_1_1.value;
                            allLazyRoutes.push(parseLazyRoute(route, reflector, module.reference));
                        }
                    }
                    catch (e_2_1) { e_2 = { error: e_2_1 }; }
                    finally {
                        try {
                            if (loadChildren_1_1 && !loadChildren_1_1.done && (_b = loadChildren_1.return)) _b.call(loadChildren_1);
                        }
                        finally { if (e_2) throw e_2.error; }
                    }
                }
            }
        }
        catch (e_1_1) { e_1 = { error: e_1_1 }; }
        finally {
            try {
                if (_d && !_d.done && (_a = _c.return)) _a.call(_c);
            }
            finally { if (e_1) throw e_1.error; }
        }
        return allLazyRoutes;
    }
    exports.listLazyRoutes = listLazyRoutes;
    function _collectLoadChildren(routes, target) {
        var e_3, _a;
        if (target === void 0) { target = []; }
        if (typeof routes === 'string') {
            target.push(routes);
        }
        else if (Array.isArray(routes)) {
            try {
                for (var routes_1 = tslib_1.__values(routes), routes_1_1 = routes_1.next(); !routes_1_1.done; routes_1_1 = routes_1.next()) {
                    var route = routes_1_1.value;
                    _collectLoadChildren(route, target);
                }
            }
            catch (e_3_1) { e_3 = { error: e_3_1 }; }
            finally {
                try {
                    if (routes_1_1 && !routes_1_1.done && (_a = routes_1.return)) _a.call(routes_1);
                }
                finally { if (e_3) throw e_3.error; }
            }
        }
        else if (routes.loadChildren) {
            _collectLoadChildren(routes.loadChildren, target);
        }
        else if (routes.children) {
            _collectLoadChildren(routes.children, target);
        }
        return target;
    }
    function parseLazyRoute(route, reflector, module) {
        var _a = tslib_1.__read(route.split('#'), 2), routePath = _a[0], routeName = _a[1];
        var referencedModule = reflector.resolveExternalReference({
            moduleName: routePath,
            name: routeName,
        }, module ? module.filePath : undefined);
        return { route: route, module: module || referencedModule, referencedModule: referencedModule };
    }
    exports.parseLazyRoute = parseLazyRoute;
});
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoibGF6eV9yb3V0ZXMuanMiLCJzb3VyY2VSb290IjoiIiwic291cmNlcyI6WyIuLi8uLi8uLi8uLi8uLi8uLi8uLi9wYWNrYWdlcy9jb21waWxlci9zcmMvYW90L2xhenlfcm91dGVzLnRzIl0sIm5hbWVzIjpbXSwibWFwcGluZ3MiOiJBQUFBOzs7Ozs7R0FNRzs7Ozs7Ozs7Ozs7Ozs7SUFFSCwyRUFBNEU7SUFZNUUsU0FBZ0IsY0FBYyxDQUMxQixVQUFtQyxFQUFFLFNBQTBCOztRQUNqRSxJQUFNLGFBQWEsR0FBZ0IsRUFBRSxDQUFDOztZQUN0QyxLQUFpQyxJQUFBLEtBQUEsaUJBQUEsVUFBVSxDQUFDLGdCQUFnQixDQUFDLFNBQVMsQ0FBQSxnQkFBQSw0QkFBRTtnQkFBN0QsSUFBQSxhQUFrQixFQUFqQixRQUFRLGNBQUEsRUFBRSxNQUFNLFlBQUE7Z0JBQzFCLElBQUksaUNBQWMsQ0FBQyxRQUFRLENBQUMsS0FBSyxDQUFDLEtBQUssU0FBUyxDQUFDLE1BQU0sRUFBRTtvQkFDdkQsSUFBTSxZQUFZLEdBQUcsb0JBQW9CLENBQUMsUUFBUSxDQUFDLFFBQVEsQ0FBQyxDQUFDOzt3QkFDN0QsS0FBb0IsSUFBQSxnQ0FBQSxpQkFBQSxZQUFZLENBQUEsQ0FBQSwwQ0FBQSxvRUFBRTs0QkFBN0IsSUFBTSxLQUFLLHlCQUFBOzRCQUNkLGFBQWEsQ0FBQyxJQUFJLENBQUMsY0FBYyxDQUFDLEtBQUssRUFBRSxTQUFTLEVBQUUsTUFBTSxDQUFDLFNBQVMsQ0FBQyxDQUFDLENBQUM7eUJBQ3hFOzs7Ozs7Ozs7aUJBQ0Y7YUFDRjs7Ozs7Ozs7O1FBQ0QsT0FBTyxhQUFhLENBQUM7SUFDdkIsQ0FBQztJQVpELHdDQVlDO0lBRUQsU0FBUyxvQkFBb0IsQ0FBQyxNQUE0QixFQUFFLE1BQXFCOztRQUFyQix1QkFBQSxFQUFBLFdBQXFCO1FBQy9FLElBQUksT0FBTyxNQUFNLEtBQUssUUFBUSxFQUFFO1lBQzlCLE1BQU0sQ0FBQyxJQUFJLENBQUMsTUFBTSxDQUFDLENBQUM7U0FDckI7YUFBTSxJQUFJLEtBQUssQ0FBQyxPQUFPLENBQUMsTUFBTSxDQUFDLEVBQUU7O2dCQUNoQyxLQUFvQixJQUFBLFdBQUEsaUJBQUEsTUFBTSxDQUFBLDhCQUFBLGtEQUFFO29CQUF2QixJQUFNLEtBQUssbUJBQUE7b0JBQ2Qsb0JBQW9CLENBQUMsS0FBSyxFQUFFLE1BQU0sQ0FBQyxDQUFDO2lCQUNyQzs7Ozs7Ozs7O1NBQ0Y7YUFBTSxJQUFJLE1BQU0sQ0FBQyxZQUFZLEVBQUU7WUFDOUIsb0JBQW9CLENBQUMsTUFBTSxDQUFDLFlBQVksRUFBRSxNQUFNLENBQUMsQ0FBQztTQUNuRDthQUFNLElBQUksTUFBTSxDQUFDLFFBQVEsRUFBRTtZQUMxQixvQkFBb0IsQ0FBQyxNQUFNLENBQUMsUUFBUSxFQUFFLE1BQU0sQ0FBQyxDQUFDO1NBQy9DO1FBQ0QsT0FBTyxNQUFNLENBQUM7SUFDaEIsQ0FBQztJQUVELFNBQWdCLGNBQWMsQ0FDMUIsS0FBYSxFQUFFLFNBQTBCLEVBQUUsTUFBcUI7UUFDNUQsSUFBQSxLQUFBLGVBQXlCLEtBQUssQ0FBQyxLQUFLLENBQUMsR0FBRyxDQUFDLElBQUEsRUFBeEMsU0FBUyxRQUFBLEVBQUUsU0FBUyxRQUFvQixDQUFDO1FBQ2hELElBQU0sZ0JBQWdCLEdBQUcsU0FBUyxDQUFDLHdCQUF3QixDQUN2RDtZQUNFLFVBQVUsRUFBRSxTQUFTO1lBQ3JCLElBQUksRUFBRSxTQUFTO1NBQ2hCLEVBQ0QsTUFBTSxDQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsUUFBUSxDQUFDLENBQUMsQ0FBQyxTQUFTLENBQUMsQ0FBQztRQUMxQyxPQUFPLEVBQUMsS0FBSyxFQUFFLEtBQUssRUFBRSxNQUFNLEVBQUUsTUFBTSxJQUFJLGdCQUFnQixFQUFFLGdCQUFnQixrQkFBQSxFQUFDLENBQUM7SUFDOUUsQ0FBQztJQVZELHdDQVVDIiwic291cmNlc0NvbnRlbnQiOlsiLyoqXG4gKiBAbGljZW5zZVxuICogQ29weXJpZ2h0IEdvb2dsZSBMTEMgQWxsIFJpZ2h0cyBSZXNlcnZlZC5cbiAqXG4gKiBVc2Ugb2YgdGhpcyBzb3VyY2UgY29kZSBpcyBnb3Zlcm5lZCBieSBhbiBNSVQtc3R5bGUgbGljZW5zZSB0aGF0IGNhbiBiZVxuICogZm91bmQgaW4gdGhlIExJQ0VOU0UgZmlsZSBhdCBodHRwczovL2FuZ3VsYXIuaW8vbGljZW5zZVxuICovXG5cbmltcG9ydCB7Q29tcGlsZU5nTW9kdWxlTWV0YWRhdGEsIHRva2VuUmVmZXJlbmNlfSBmcm9tICcuLi9jb21waWxlX21ldGFkYXRhJztcbmltcG9ydCB7Um91dGV9IGZyb20gJy4uL2NvcmUnO1xuXG5pbXBvcnQge1N0YXRpY1JlZmxlY3Rvcn0gZnJvbSAnLi9zdGF0aWNfcmVmbGVjdG9yJztcbmltcG9ydCB7U3RhdGljU3ltYm9sfSBmcm9tICcuL3N0YXRpY19zeW1ib2wnO1xuXG5leHBvcnQgaW50ZXJmYWNlIExhenlSb3V0ZSB7XG4gIG1vZHVsZTogU3RhdGljU3ltYm9sO1xuICByb3V0ZTogc3RyaW5nO1xuICByZWZlcmVuY2VkTW9kdWxlOiBTdGF0aWNTeW1ib2w7XG59XG5cbmV4cG9ydCBmdW5jdGlvbiBsaXN0TGF6eVJvdXRlcyhcbiAgICBtb2R1bGVNZXRhOiBDb21waWxlTmdNb2R1bGVNZXRhZGF0YSwgcmVmbGVjdG9yOiBTdGF0aWNSZWZsZWN0b3IpOiBMYXp5Um91dGVbXSB7XG4gIGNvbnN0IGFsbExhenlSb3V0ZXM6IExhenlSb3V0ZVtdID0gW107XG4gIGZvciAoY29uc3Qge3Byb3ZpZGVyLCBtb2R1bGV9IG9mIG1vZHVsZU1ldGEudHJhbnNpdGl2ZU1vZHVsZS5wcm92aWRlcnMpIHtcbiAgICBpZiAodG9rZW5SZWZlcmVuY2UocHJvdmlkZXIudG9rZW4pID09PSByZWZsZWN0b3IuUk9VVEVTKSB7XG4gICAgICBjb25zdCBsb2FkQ2hpbGRyZW4gPSBfY29sbGVjdExvYWRDaGlsZHJlbihwcm92aWRlci51c2VWYWx1ZSk7XG4gICAgICBmb3IgKGNvbnN0IHJvdXRlIG9mIGxvYWRDaGlsZHJlbikge1xuICAgICAgICBhbGxMYXp5Um91dGVzLnB1c2gocGFyc2VMYXp5Um91dGUocm91dGUsIHJlZmxlY3RvciwgbW9kdWxlLnJlZmVyZW5jZSkpO1xuICAgICAgfVxuICAgIH1cbiAgfVxuICByZXR1cm4gYWxsTGF6eVJvdXRlcztcbn1cblxuZnVuY3Rpb24gX2NvbGxlY3RMb2FkQ2hpbGRyZW4ocm91dGVzOiBzdHJpbmd8Um91dGV8Um91dGVbXSwgdGFyZ2V0OiBzdHJpbmdbXSA9IFtdKTogc3RyaW5nW10ge1xuICBpZiAodHlwZW9mIHJvdXRlcyA9PT0gJ3N0cmluZycpIHtcbiAgICB0YXJnZXQucHVzaChyb3V0ZXMpO1xuICB9IGVsc2UgaWYgKEFycmF5LmlzQXJyYXkocm91dGVzKSkge1xuICAgIGZvciAoY29uc3Qgcm91dGUgb2Ygcm91dGVzKSB7XG4gICAgICBfY29sbGVjdExvYWRDaGlsZHJlbihyb3V0ZSwgdGFyZ2V0KTtcbiAgICB9XG4gIH0gZWxzZSBpZiAocm91dGVzLmxvYWRDaGlsZHJlbikge1xuICAgIF9jb2xsZWN0TG9hZENoaWxkcmVuKHJvdXRlcy5sb2FkQ2hpbGRyZW4sIHRhcmdldCk7XG4gIH0gZWxzZSBpZiAocm91dGVzLmNoaWxkcmVuKSB7XG4gICAgX2NvbGxlY3RMb2FkQ2hpbGRyZW4ocm91dGVzLmNoaWxkcmVuLCB0YXJnZXQpO1xuICB9XG4gIHJldHVybiB0YXJnZXQ7XG59XG5cbmV4cG9ydCBmdW5jdGlvbiBwYXJzZUxhenlSb3V0ZShcbiAgICByb3V0ZTogc3RyaW5nLCByZWZsZWN0b3I6IFN0YXRpY1JlZmxlY3RvciwgbW9kdWxlPzogU3RhdGljU3ltYm9sKTogTGF6eVJvdXRlIHtcbiAgY29uc3QgW3JvdXRlUGF0aCwgcm91dGVOYW1lXSA9IHJvdXRlLnNwbGl0KCcjJyk7XG4gIGNvbnN0IHJlZmVyZW5jZWRNb2R1bGUgPSByZWZsZWN0b3IucmVzb2x2ZUV4dGVybmFsUmVmZXJlbmNlKFxuICAgICAge1xuICAgICAgICBtb2R1bGVOYW1lOiByb3V0ZVBhdGgsXG4gICAgICAgIG5hbWU6IHJvdXRlTmFtZSxcbiAgICAgIH0sXG4gICAgICBtb2R1bGUgPyBtb2R1bGUuZmlsZVBhdGggOiB1bmRlZmluZWQpO1xuICByZXR1cm4ge3JvdXRlOiByb3V0ZSwgbW9kdWxlOiBtb2R1bGUgfHwgcmVmZXJlbmNlZE1vZHVsZSwgcmVmZXJlbmNlZE1vZHVsZX07XG59XG4iXX0=