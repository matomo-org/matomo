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
        define("@angular/compiler/src/ng_module_resolver", ["require", "exports", "@angular/compiler/src/core", "@angular/compiler/src/directive_resolver", "@angular/compiler/src/util"], factory);
    }
})(function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.NgModuleResolver = void 0;
    var core_1 = require("@angular/compiler/src/core");
    var directive_resolver_1 = require("@angular/compiler/src/directive_resolver");
    var util_1 = require("@angular/compiler/src/util");
    /**
     * Resolves types to {@link NgModule}.
     */
    var NgModuleResolver = /** @class */ (function () {
        function NgModuleResolver(_reflector) {
            this._reflector = _reflector;
        }
        NgModuleResolver.prototype.isNgModule = function (type) {
            return this._reflector.annotations(type).some(core_1.createNgModule.isTypeOf);
        };
        NgModuleResolver.prototype.resolve = function (type, throwIfNotFound) {
            if (throwIfNotFound === void 0) { throwIfNotFound = true; }
            var ngModuleMeta = directive_resolver_1.findLast(this._reflector.annotations(type), core_1.createNgModule.isTypeOf);
            if (ngModuleMeta) {
                return ngModuleMeta;
            }
            else {
                if (throwIfNotFound) {
                    throw new Error("No NgModule metadata found for '" + util_1.stringify(type) + "'.");
                }
                return null;
            }
        };
        return NgModuleResolver;
    }());
    exports.NgModuleResolver = NgModuleResolver;
});
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoibmdfbW9kdWxlX3Jlc29sdmVyLmpzIiwic291cmNlUm9vdCI6IiIsInNvdXJjZXMiOlsiLi4vLi4vLi4vLi4vLi4vLi4vcGFja2FnZXMvY29tcGlsZXIvc3JjL25nX21vZHVsZV9yZXNvbHZlci50cyJdLCJuYW1lcyI6W10sIm1hcHBpbmdzIjoiQUFBQTs7Ozs7O0dBTUc7Ozs7Ozs7Ozs7Ozs7SUFHSCxtREFBc0Q7SUFDdEQsK0VBQThDO0lBQzlDLG1EQUFpQztJQUdqQzs7T0FFRztJQUNIO1FBQ0UsMEJBQW9CLFVBQTRCO1lBQTVCLGVBQVUsR0FBVixVQUFVLENBQWtCO1FBQUcsQ0FBQztRQUVwRCxxQ0FBVSxHQUFWLFVBQVcsSUFBUztZQUNsQixPQUFPLElBQUksQ0FBQyxVQUFVLENBQUMsV0FBVyxDQUFDLElBQUksQ0FBQyxDQUFDLElBQUksQ0FBQyxxQkFBYyxDQUFDLFFBQVEsQ0FBQyxDQUFDO1FBQ3pFLENBQUM7UUFFRCxrQ0FBTyxHQUFQLFVBQVEsSUFBVSxFQUFFLGVBQXNCO1lBQXRCLGdDQUFBLEVBQUEsc0JBQXNCO1lBQ3hDLElBQU0sWUFBWSxHQUNkLDZCQUFRLENBQUMsSUFBSSxDQUFDLFVBQVUsQ0FBQyxXQUFXLENBQUMsSUFBSSxDQUFDLEVBQUUscUJBQWMsQ0FBQyxRQUFRLENBQUMsQ0FBQztZQUV6RSxJQUFJLFlBQVksRUFBRTtnQkFDaEIsT0FBTyxZQUFZLENBQUM7YUFDckI7aUJBQU07Z0JBQ0wsSUFBSSxlQUFlLEVBQUU7b0JBQ25CLE1BQU0sSUFBSSxLQUFLLENBQUMscUNBQW1DLGdCQUFTLENBQUMsSUFBSSxDQUFDLE9BQUksQ0FBQyxDQUFDO2lCQUN6RTtnQkFDRCxPQUFPLElBQUksQ0FBQzthQUNiO1FBQ0gsQ0FBQztRQUNILHVCQUFDO0lBQUQsQ0FBQyxBQXBCRCxJQW9CQztJQXBCWSw0Q0FBZ0IiLCJzb3VyY2VzQ29udGVudCI6WyIvKipcbiAqIEBsaWNlbnNlXG4gKiBDb3B5cmlnaHQgR29vZ2xlIExMQyBBbGwgUmlnaHRzIFJlc2VydmVkLlxuICpcbiAqIFVzZSBvZiB0aGlzIHNvdXJjZSBjb2RlIGlzIGdvdmVybmVkIGJ5IGFuIE1JVC1zdHlsZSBsaWNlbnNlIHRoYXQgY2FuIGJlXG4gKiBmb3VuZCBpbiB0aGUgTElDRU5TRSBmaWxlIGF0IGh0dHBzOi8vYW5ndWxhci5pby9saWNlbnNlXG4gKi9cblxuaW1wb3J0IHtDb21waWxlUmVmbGVjdG9yfSBmcm9tICcuL2NvbXBpbGVfcmVmbGVjdG9yJztcbmltcG9ydCB7Y3JlYXRlTmdNb2R1bGUsIE5nTW9kdWxlLCBUeXBlfSBmcm9tICcuL2NvcmUnO1xuaW1wb3J0IHtmaW5kTGFzdH0gZnJvbSAnLi9kaXJlY3RpdmVfcmVzb2x2ZXInO1xuaW1wb3J0IHtzdHJpbmdpZnl9IGZyb20gJy4vdXRpbCc7XG5cblxuLyoqXG4gKiBSZXNvbHZlcyB0eXBlcyB0byB7QGxpbmsgTmdNb2R1bGV9LlxuICovXG5leHBvcnQgY2xhc3MgTmdNb2R1bGVSZXNvbHZlciB7XG4gIGNvbnN0cnVjdG9yKHByaXZhdGUgX3JlZmxlY3RvcjogQ29tcGlsZVJlZmxlY3Rvcikge31cblxuICBpc05nTW9kdWxlKHR5cGU6IGFueSkge1xuICAgIHJldHVybiB0aGlzLl9yZWZsZWN0b3IuYW5ub3RhdGlvbnModHlwZSkuc29tZShjcmVhdGVOZ01vZHVsZS5pc1R5cGVPZik7XG4gIH1cblxuICByZXNvbHZlKHR5cGU6IFR5cGUsIHRocm93SWZOb3RGb3VuZCA9IHRydWUpOiBOZ01vZHVsZXxudWxsIHtcbiAgICBjb25zdCBuZ01vZHVsZU1ldGE6IE5nTW9kdWxlID1cbiAgICAgICAgZmluZExhc3QodGhpcy5fcmVmbGVjdG9yLmFubm90YXRpb25zKHR5cGUpLCBjcmVhdGVOZ01vZHVsZS5pc1R5cGVPZik7XG5cbiAgICBpZiAobmdNb2R1bGVNZXRhKSB7XG4gICAgICByZXR1cm4gbmdNb2R1bGVNZXRhO1xuICAgIH0gZWxzZSB7XG4gICAgICBpZiAodGhyb3dJZk5vdEZvdW5kKSB7XG4gICAgICAgIHRocm93IG5ldyBFcnJvcihgTm8gTmdNb2R1bGUgbWV0YWRhdGEgZm91bmQgZm9yICcke3N0cmluZ2lmeSh0eXBlKX0nLmApO1xuICAgICAgfVxuICAgICAgcmV0dXJuIG51bGw7XG4gICAgfVxuICB9XG59XG4iXX0=