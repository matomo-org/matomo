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
        define("@angular/compiler/src/output/value_util", ["require", "exports", "@angular/compiler/src/util", "@angular/compiler/src/output/output_ast"], factory);
    }
})(function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.convertValueToOutputAst = exports.QUOTED_KEYS = void 0;
    var util_1 = require("@angular/compiler/src/util");
    var o = require("@angular/compiler/src/output/output_ast");
    exports.QUOTED_KEYS = '$quoted$';
    function convertValueToOutputAst(ctx, value, type) {
        if (type === void 0) { type = null; }
        return util_1.visitValue(value, new _ValueOutputAstTransformer(ctx), type);
    }
    exports.convertValueToOutputAst = convertValueToOutputAst;
    var _ValueOutputAstTransformer = /** @class */ (function () {
        function _ValueOutputAstTransformer(ctx) {
            this.ctx = ctx;
        }
        _ValueOutputAstTransformer.prototype.visitArray = function (arr, type) {
            var values = [];
            // Note Array.map() must not be used to convert the values because it will
            // skip over empty elements in arrays constructed using `new Array(length)`,
            // resulting in `undefined` elements. This breaks the type guarantee that
            // all values in `o.LiteralArrayExpr` are of type `o.Expression`.
            // See test case in `value_util_spec.ts`.
            for (var i = 0; i < arr.length; ++i) {
                values.push(util_1.visitValue(arr[i], this, null /* context */));
            }
            return o.literalArr(values, type);
        };
        _ValueOutputAstTransformer.prototype.visitStringMap = function (map, type) {
            var _this = this;
            var entries = [];
            var quotedSet = new Set(map && map[exports.QUOTED_KEYS]);
            Object.keys(map).forEach(function (key) {
                entries.push(new o.LiteralMapEntry(key, util_1.visitValue(map[key], _this, null), quotedSet.has(key)));
            });
            return new o.LiteralMapExpr(entries, type);
        };
        _ValueOutputAstTransformer.prototype.visitPrimitive = function (value, type) {
            return o.literal(value, type);
        };
        _ValueOutputAstTransformer.prototype.visitOther = function (value, type) {
            if (value instanceof o.Expression) {
                return value;
            }
            else {
                return this.ctx.importExpr(value);
            }
        };
        return _ValueOutputAstTransformer;
    }());
});
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoidmFsdWVfdXRpbC5qcyIsInNvdXJjZVJvb3QiOiIiLCJzb3VyY2VzIjpbIi4uLy4uLy4uLy4uLy4uLy4uLy4uL3BhY2thZ2VzL2NvbXBpbGVyL3NyYy9vdXRwdXQvdmFsdWVfdXRpbC50cyJdLCJuYW1lcyI6W10sIm1hcHBpbmdzIjoiQUFBQTs7Ozs7O0dBTUc7Ozs7Ozs7Ozs7Ozs7SUFHSCxtREFBb0U7SUFFcEUsMkRBQWtDO0lBRXJCLFFBQUEsV0FBVyxHQUFHLFVBQVUsQ0FBQztJQUV0QyxTQUFnQix1QkFBdUIsQ0FDbkMsR0FBa0IsRUFBRSxLQUFVLEVBQUUsSUFBd0I7UUFBeEIscUJBQUEsRUFBQSxXQUF3QjtRQUMxRCxPQUFPLGlCQUFVLENBQUMsS0FBSyxFQUFFLElBQUksMEJBQTBCLENBQUMsR0FBRyxDQUFDLEVBQUUsSUFBSSxDQUFDLENBQUM7SUFDdEUsQ0FBQztJQUhELDBEQUdDO0lBRUQ7UUFDRSxvQ0FBb0IsR0FBa0I7WUFBbEIsUUFBRyxHQUFILEdBQUcsQ0FBZTtRQUFHLENBQUM7UUFDMUMsK0NBQVUsR0FBVixVQUFXLEdBQVUsRUFBRSxJQUFZO1lBQ2pDLElBQU0sTUFBTSxHQUFtQixFQUFFLENBQUM7WUFDbEMsMEVBQTBFO1lBQzFFLDRFQUE0RTtZQUM1RSx5RUFBeUU7WUFDekUsaUVBQWlFO1lBQ2pFLHlDQUF5QztZQUN6QyxLQUFLLElBQUksQ0FBQyxHQUFHLENBQUMsRUFBRSxDQUFDLEdBQUcsR0FBRyxDQUFDLE1BQU0sRUFBRSxFQUFFLENBQUMsRUFBRTtnQkFDbkMsTUFBTSxDQUFDLElBQUksQ0FBQyxpQkFBVSxDQUFDLEdBQUcsQ0FBQyxDQUFDLENBQUMsRUFBRSxJQUFJLEVBQUUsSUFBSSxDQUFDLGFBQWEsQ0FBQyxDQUFDLENBQUM7YUFDM0Q7WUFDRCxPQUFPLENBQUMsQ0FBQyxVQUFVLENBQUMsTUFBTSxFQUFFLElBQUksQ0FBQyxDQUFDO1FBQ3BDLENBQUM7UUFFRCxtREFBYyxHQUFkLFVBQWUsR0FBeUIsRUFBRSxJQUFlO1lBQXpELGlCQVFDO1lBUEMsSUFBTSxPQUFPLEdBQXdCLEVBQUUsQ0FBQztZQUN4QyxJQUFNLFNBQVMsR0FBRyxJQUFJLEdBQUcsQ0FBUyxHQUFHLElBQUksR0FBRyxDQUFDLG1CQUFXLENBQUMsQ0FBQyxDQUFDO1lBQzNELE1BQU0sQ0FBQyxJQUFJLENBQUMsR0FBRyxDQUFDLENBQUMsT0FBTyxDQUFDLFVBQUEsR0FBRztnQkFDMUIsT0FBTyxDQUFDLElBQUksQ0FDUixJQUFJLENBQUMsQ0FBQyxlQUFlLENBQUMsR0FBRyxFQUFFLGlCQUFVLENBQUMsR0FBRyxDQUFDLEdBQUcsQ0FBQyxFQUFFLEtBQUksRUFBRSxJQUFJLENBQUMsRUFBRSxTQUFTLENBQUMsR0FBRyxDQUFDLEdBQUcsQ0FBQyxDQUFDLENBQUMsQ0FBQztZQUN4RixDQUFDLENBQUMsQ0FBQztZQUNILE9BQU8sSUFBSSxDQUFDLENBQUMsY0FBYyxDQUFDLE9BQU8sRUFBRSxJQUFJLENBQUMsQ0FBQztRQUM3QyxDQUFDO1FBRUQsbURBQWMsR0FBZCxVQUFlLEtBQVUsRUFBRSxJQUFZO1lBQ3JDLE9BQU8sQ0FBQyxDQUFDLE9BQU8sQ0FBQyxLQUFLLEVBQUUsSUFBSSxDQUFDLENBQUM7UUFDaEMsQ0FBQztRQUVELCtDQUFVLEdBQVYsVUFBVyxLQUFVLEVBQUUsSUFBWTtZQUNqQyxJQUFJLEtBQUssWUFBWSxDQUFDLENBQUMsVUFBVSxFQUFFO2dCQUNqQyxPQUFPLEtBQUssQ0FBQzthQUNkO2lCQUFNO2dCQUNMLE9BQU8sSUFBSSxDQUFDLEdBQUcsQ0FBQyxVQUFVLENBQUMsS0FBSyxDQUFDLENBQUM7YUFDbkM7UUFDSCxDQUFDO1FBQ0gsaUNBQUM7SUFBRCxDQUFDLEFBcENELElBb0NDIiwic291cmNlc0NvbnRlbnQiOlsiLyoqXG4gKiBAbGljZW5zZVxuICogQ29weXJpZ2h0IEdvb2dsZSBMTEMgQWxsIFJpZ2h0cyBSZXNlcnZlZC5cbiAqXG4gKiBVc2Ugb2YgdGhpcyBzb3VyY2UgY29kZSBpcyBnb3Zlcm5lZCBieSBhbiBNSVQtc3R5bGUgbGljZW5zZSB0aGF0IGNhbiBiZVxuICogZm91bmQgaW4gdGhlIExJQ0VOU0UgZmlsZSBhdCBodHRwczovL2FuZ3VsYXIuaW8vbGljZW5zZVxuICovXG5cblxuaW1wb3J0IHtPdXRwdXRDb250ZXh0LCBWYWx1ZVRyYW5zZm9ybWVyLCB2aXNpdFZhbHVlfSBmcm9tICcuLi91dGlsJztcblxuaW1wb3J0ICogYXMgbyBmcm9tICcuL291dHB1dF9hc3QnO1xuXG5leHBvcnQgY29uc3QgUVVPVEVEX0tFWVMgPSAnJHF1b3RlZCQnO1xuXG5leHBvcnQgZnVuY3Rpb24gY29udmVydFZhbHVlVG9PdXRwdXRBc3QoXG4gICAgY3R4OiBPdXRwdXRDb250ZXh0LCB2YWx1ZTogYW55LCB0eXBlOiBvLlR5cGV8bnVsbCA9IG51bGwpOiBvLkV4cHJlc3Npb24ge1xuICByZXR1cm4gdmlzaXRWYWx1ZSh2YWx1ZSwgbmV3IF9WYWx1ZU91dHB1dEFzdFRyYW5zZm9ybWVyKGN0eCksIHR5cGUpO1xufVxuXG5jbGFzcyBfVmFsdWVPdXRwdXRBc3RUcmFuc2Zvcm1lciBpbXBsZW1lbnRzIFZhbHVlVHJhbnNmb3JtZXIge1xuICBjb25zdHJ1Y3Rvcihwcml2YXRlIGN0eDogT3V0cHV0Q29udGV4dCkge31cbiAgdmlzaXRBcnJheShhcnI6IGFueVtdLCB0eXBlOiBvLlR5cGUpOiBvLkV4cHJlc3Npb24ge1xuICAgIGNvbnN0IHZhbHVlczogby5FeHByZXNzaW9uW10gPSBbXTtcbiAgICAvLyBOb3RlIEFycmF5Lm1hcCgpIG11c3Qgbm90IGJlIHVzZWQgdG8gY29udmVydCB0aGUgdmFsdWVzIGJlY2F1c2UgaXQgd2lsbFxuICAgIC8vIHNraXAgb3ZlciBlbXB0eSBlbGVtZW50cyBpbiBhcnJheXMgY29uc3RydWN0ZWQgdXNpbmcgYG5ldyBBcnJheShsZW5ndGgpYCxcbiAgICAvLyByZXN1bHRpbmcgaW4gYHVuZGVmaW5lZGAgZWxlbWVudHMuIFRoaXMgYnJlYWtzIHRoZSB0eXBlIGd1YXJhbnRlZSB0aGF0XG4gICAgLy8gYWxsIHZhbHVlcyBpbiBgby5MaXRlcmFsQXJyYXlFeHByYCBhcmUgb2YgdHlwZSBgby5FeHByZXNzaW9uYC5cbiAgICAvLyBTZWUgdGVzdCBjYXNlIGluIGB2YWx1ZV91dGlsX3NwZWMudHNgLlxuICAgIGZvciAobGV0IGkgPSAwOyBpIDwgYXJyLmxlbmd0aDsgKytpKSB7XG4gICAgICB2YWx1ZXMucHVzaCh2aXNpdFZhbHVlKGFycltpXSwgdGhpcywgbnVsbCAvKiBjb250ZXh0ICovKSk7XG4gICAgfVxuICAgIHJldHVybiBvLmxpdGVyYWxBcnIodmFsdWVzLCB0eXBlKTtcbiAgfVxuXG4gIHZpc2l0U3RyaW5nTWFwKG1hcDoge1trZXk6IHN0cmluZ106IGFueX0sIHR5cGU6IG8uTWFwVHlwZSk6IG8uRXhwcmVzc2lvbiB7XG4gICAgY29uc3QgZW50cmllczogby5MaXRlcmFsTWFwRW50cnlbXSA9IFtdO1xuICAgIGNvbnN0IHF1b3RlZFNldCA9IG5ldyBTZXQ8c3RyaW5nPihtYXAgJiYgbWFwW1FVT1RFRF9LRVlTXSk7XG4gICAgT2JqZWN0LmtleXMobWFwKS5mb3JFYWNoKGtleSA9PiB7XG4gICAgICBlbnRyaWVzLnB1c2goXG4gICAgICAgICAgbmV3IG8uTGl0ZXJhbE1hcEVudHJ5KGtleSwgdmlzaXRWYWx1ZShtYXBba2V5XSwgdGhpcywgbnVsbCksIHF1b3RlZFNldC5oYXMoa2V5KSkpO1xuICAgIH0pO1xuICAgIHJldHVybiBuZXcgby5MaXRlcmFsTWFwRXhwcihlbnRyaWVzLCB0eXBlKTtcbiAgfVxuXG4gIHZpc2l0UHJpbWl0aXZlKHZhbHVlOiBhbnksIHR5cGU6IG8uVHlwZSk6IG8uRXhwcmVzc2lvbiB7XG4gICAgcmV0dXJuIG8ubGl0ZXJhbCh2YWx1ZSwgdHlwZSk7XG4gIH1cblxuICB2aXNpdE90aGVyKHZhbHVlOiBhbnksIHR5cGU6IG8uVHlwZSk6IG8uRXhwcmVzc2lvbiB7XG4gICAgaWYgKHZhbHVlIGluc3RhbmNlb2Ygby5FeHByZXNzaW9uKSB7XG4gICAgICByZXR1cm4gdmFsdWU7XG4gICAgfSBlbHNlIHtcbiAgICAgIHJldHVybiB0aGlzLmN0eC5pbXBvcnRFeHByKHZhbHVlKTtcbiAgICB9XG4gIH1cbn1cbiJdfQ==