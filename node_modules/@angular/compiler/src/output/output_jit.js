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
        define("@angular/compiler/src/output/output_jit", ["require", "exports", "tslib", "@angular/compiler/src/compile_metadata", "@angular/compiler/src/output/abstract_emitter", "@angular/compiler/src/output/abstract_js_emitter", "@angular/compiler/src/output/output_ast", "@angular/compiler/src/output/output_jit_trusted_types"], factory);
    }
})(function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.JitEmitterVisitor = exports.JitEvaluator = void 0;
    var tslib_1 = require("tslib");
    var compile_metadata_1 = require("@angular/compiler/src/compile_metadata");
    var abstract_emitter_1 = require("@angular/compiler/src/output/abstract_emitter");
    var abstract_js_emitter_1 = require("@angular/compiler/src/output/abstract_js_emitter");
    var o = require("@angular/compiler/src/output/output_ast");
    var output_jit_trusted_types_1 = require("@angular/compiler/src/output/output_jit_trusted_types");
    /**
     * A helper class to manage the evaluation of JIT generated code.
     */
    var JitEvaluator = /** @class */ (function () {
        function JitEvaluator() {
        }
        /**
         *
         * @param sourceUrl The URL of the generated code.
         * @param statements An array of Angular statement AST nodes to be evaluated.
         * @param reflector A helper used when converting the statements to executable code.
         * @param createSourceMaps If true then create a source-map for the generated code and include it
         * inline as a source-map comment.
         * @returns A map of all the variables in the generated code.
         */
        JitEvaluator.prototype.evaluateStatements = function (sourceUrl, statements, reflector, createSourceMaps) {
            var converter = new JitEmitterVisitor(reflector);
            var ctx = abstract_emitter_1.EmitterVisitorContext.createRoot();
            // Ensure generated code is in strict mode
            if (statements.length > 0 && !isUseStrictStatement(statements[0])) {
                statements = tslib_1.__spread([
                    o.literal('use strict').toStmt()
                ], statements);
            }
            converter.visitAllStatements(statements, ctx);
            converter.createReturnStmt(ctx);
            return this.evaluateCode(sourceUrl, ctx, converter.getArgs(), createSourceMaps);
        };
        /**
         * Evaluate a piece of JIT generated code.
         * @param sourceUrl The URL of this generated code.
         * @param ctx A context object that contains an AST of the code to be evaluated.
         * @param vars A map containing the names and values of variables that the evaluated code might
         * reference.
         * @param createSourceMap If true then create a source-map for the generated code and include it
         * inline as a source-map comment.
         * @returns The result of evaluating the code.
         */
        JitEvaluator.prototype.evaluateCode = function (sourceUrl, ctx, vars, createSourceMap) {
            var fnBody = "\"use strict\";" + ctx.toSource() + "\n//# sourceURL=" + sourceUrl;
            var fnArgNames = [];
            var fnArgValues = [];
            for (var argName in vars) {
                fnArgValues.push(vars[argName]);
                fnArgNames.push(argName);
            }
            if (createSourceMap) {
                // using `new Function(...)` generates a header, 1 line of no arguments, 2 lines otherwise
                // E.g. ```
                // function anonymous(a,b,c
                // /**/) { ... }```
                // We don't want to hard code this fact, so we auto detect it via an empty function first.
                var emptyFn = output_jit_trusted_types_1.newTrustedFunctionForJIT.apply(void 0, tslib_1.__spread(fnArgNames.concat('return null;'))).toString();
                var headerLines = emptyFn.slice(0, emptyFn.indexOf('return null;')).split('\n').length - 1;
                fnBody += "\n" + ctx.toSourceMapGenerator(sourceUrl, headerLines).toJsComment();
            }
            var fn = output_jit_trusted_types_1.newTrustedFunctionForJIT.apply(void 0, tslib_1.__spread(fnArgNames.concat(fnBody)));
            return this.executeFunction(fn, fnArgValues);
        };
        /**
         * Execute a JIT generated function by calling it.
         *
         * This method can be overridden in tests to capture the functions that are generated
         * by this `JitEvaluator` class.
         *
         * @param fn A function to execute.
         * @param args The arguments to pass to the function being executed.
         * @returns The return value of the executed function.
         */
        JitEvaluator.prototype.executeFunction = function (fn, args) {
            return fn.apply(void 0, tslib_1.__spread(args));
        };
        return JitEvaluator;
    }());
    exports.JitEvaluator = JitEvaluator;
    /**
     * An Angular AST visitor that converts AST nodes into executable JavaScript code.
     */
    var JitEmitterVisitor = /** @class */ (function (_super) {
        tslib_1.__extends(JitEmitterVisitor, _super);
        function JitEmitterVisitor(reflector) {
            var _this = _super.call(this) || this;
            _this.reflector = reflector;
            _this._evalArgNames = [];
            _this._evalArgValues = [];
            _this._evalExportedVars = [];
            return _this;
        }
        JitEmitterVisitor.prototype.createReturnStmt = function (ctx) {
            var stmt = new o.ReturnStatement(new o.LiteralMapExpr(this._evalExportedVars.map(function (resultVar) { return new o.LiteralMapEntry(resultVar, o.variable(resultVar), false); })));
            stmt.visitStatement(this, ctx);
        };
        JitEmitterVisitor.prototype.getArgs = function () {
            var result = {};
            for (var i = 0; i < this._evalArgNames.length; i++) {
                result[this._evalArgNames[i]] = this._evalArgValues[i];
            }
            return result;
        };
        JitEmitterVisitor.prototype.visitExternalExpr = function (ast, ctx) {
            this._emitReferenceToExternal(ast, this.reflector.resolveExternalReference(ast.value), ctx);
            return null;
        };
        JitEmitterVisitor.prototype.visitWrappedNodeExpr = function (ast, ctx) {
            this._emitReferenceToExternal(ast, ast.node, ctx);
            return null;
        };
        JitEmitterVisitor.prototype.visitDeclareVarStmt = function (stmt, ctx) {
            if (stmt.hasModifier(o.StmtModifier.Exported)) {
                this._evalExportedVars.push(stmt.name);
            }
            return _super.prototype.visitDeclareVarStmt.call(this, stmt, ctx);
        };
        JitEmitterVisitor.prototype.visitDeclareFunctionStmt = function (stmt, ctx) {
            if (stmt.hasModifier(o.StmtModifier.Exported)) {
                this._evalExportedVars.push(stmt.name);
            }
            return _super.prototype.visitDeclareFunctionStmt.call(this, stmt, ctx);
        };
        JitEmitterVisitor.prototype.visitDeclareClassStmt = function (stmt, ctx) {
            if (stmt.hasModifier(o.StmtModifier.Exported)) {
                this._evalExportedVars.push(stmt.name);
            }
            return _super.prototype.visitDeclareClassStmt.call(this, stmt, ctx);
        };
        JitEmitterVisitor.prototype._emitReferenceToExternal = function (ast, value, ctx) {
            var id = this._evalArgValues.indexOf(value);
            if (id === -1) {
                id = this._evalArgValues.length;
                this._evalArgValues.push(value);
                var name_1 = compile_metadata_1.identifierName({ reference: value }) || 'val';
                this._evalArgNames.push("jit_" + name_1 + "_" + id);
            }
            ctx.print(ast, this._evalArgNames[id]);
        };
        return JitEmitterVisitor;
    }(abstract_js_emitter_1.AbstractJsEmitterVisitor));
    exports.JitEmitterVisitor = JitEmitterVisitor;
    function isUseStrictStatement(statement) {
        return statement.isEquivalent(o.literal('use strict').toStmt());
    }
});
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoib3V0cHV0X2ppdC5qcyIsInNvdXJjZVJvb3QiOiIiLCJzb3VyY2VzIjpbIi4uLy4uLy4uLy4uLy4uLy4uLy4uL3BhY2thZ2VzL2NvbXBpbGVyL3NyYy9vdXRwdXQvb3V0cHV0X2ppdC50cyJdLCJuYW1lcyI6W10sIm1hcHBpbmdzIjoiQUFBQTs7Ozs7O0dBTUc7Ozs7Ozs7Ozs7Ozs7O0lBRUgsMkVBQW1EO0lBR25ELGtGQUF5RDtJQUN6RCx3RkFBK0Q7SUFDL0QsMkRBQWtDO0lBQ2xDLGtHQUFvRTtJQUVwRTs7T0FFRztJQUNIO1FBQUE7UUEwRUEsQ0FBQztRQXpFQzs7Ozs7Ozs7V0FRRztRQUNILHlDQUFrQixHQUFsQixVQUNJLFNBQWlCLEVBQUUsVUFBeUIsRUFBRSxTQUEyQixFQUN6RSxnQkFBeUI7WUFDM0IsSUFBTSxTQUFTLEdBQUcsSUFBSSxpQkFBaUIsQ0FBQyxTQUFTLENBQUMsQ0FBQztZQUNuRCxJQUFNLEdBQUcsR0FBRyx3Q0FBcUIsQ0FBQyxVQUFVLEVBQUUsQ0FBQztZQUMvQywwQ0FBMEM7WUFDMUMsSUFBSSxVQUFVLENBQUMsTUFBTSxHQUFHLENBQUMsSUFBSSxDQUFDLG9CQUFvQixDQUFDLFVBQVUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFFO2dCQUNqRSxVQUFVO29CQUNSLENBQUMsQ0FBQyxPQUFPLENBQUMsWUFBWSxDQUFDLENBQUMsTUFBTSxFQUFFO21CQUM3QixVQUFVLENBQ2QsQ0FBQzthQUNIO1lBQ0QsU0FBUyxDQUFDLGtCQUFrQixDQUFDLFVBQVUsRUFBRSxHQUFHLENBQUMsQ0FBQztZQUM5QyxTQUFTLENBQUMsZ0JBQWdCLENBQUMsR0FBRyxDQUFDLENBQUM7WUFDaEMsT0FBTyxJQUFJLENBQUMsWUFBWSxDQUFDLFNBQVMsRUFBRSxHQUFHLEVBQUUsU0FBUyxDQUFDLE9BQU8sRUFBRSxFQUFFLGdCQUFnQixDQUFDLENBQUM7UUFDbEYsQ0FBQztRQUVEOzs7Ozs7Ozs7V0FTRztRQUNILG1DQUFZLEdBQVosVUFDSSxTQUFpQixFQUFF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LGlCQUFpQixDQUFDLElBQUksQ0FBQyxJQUFJLENBQUMsSUFBSSxDQUFDLENBQUM7YUFDeEM7WUFDRCxPQUFPLGlCQUFNLHFCQUFxQixZQUFDLElBQUksRUFBRSxHQUFHLENBQUMsQ0FBQztRQUNoRCxDQUFDO1FBRU8sb0RBQXdCLEdBQWhDLFVBQWlDLEdBQWlCLEVBQUUsS0FBVSxFQUFFLEdBQTBCO1lBRXhGLElBQUksRUFBRSxHQUFHLElBQUksQ0FBQyxjQUFjLENBQUMsT0FBTyxDQUFDLEtBQUssQ0FBQyxDQUFDO1lBQzVDLElBQUksRUFBRSxLQUFLLENBQUMsQ0FBQyxFQUFFO2dCQUNiLEVBQUUsR0FBRyxJQUFJLENBQUMsY0FBYyxDQUFDLE1BQU0sQ0FBQztnQkFDaEMsSUFBSSxDQUFDLGNBQWMsQ0FBQyxJQUFJLENBQUMsS0FBSyxDQUFDLENBQUM7Z0JBQ2hDLElBQU0sTUFBSSxHQUFHLGlDQUFjLENBQUMsRUFBQyxTQUFTLEVBQUUsS0FBSyxFQUFDLENBQUMsSUFBSSxLQUFLLENBQUM7Z0JBQ3pELElBQUksQ0FBQyxhQUFhLENBQUMsSUFBSSxDQUFDLFNBQU8sTUFBSSxTQUFJLEVBQUksQ0FBQyxDQUFDO2FBQzlDO1lBQ0QsR0FBRyxDQUFDLEtBQUssQ0FBQyxHQUFHLEVBQUUsSUFBSSxDQUFDLGFBQWEsQ0FBQyxFQUFFLENBQUMsQ0FBQyxDQUFDO1FBQ3pDLENBQUM7UUFDSCx3QkFBQztJQUFELENBQUMsQUFqRUQsQ0FBdUMsOENBQXdCLEdBaUU5RDtJQWpFWSw4Q0FBaUI7SUFvRTlCLFNBQVMsb0JBQW9CLENBQUMsU0FBc0I7UUFDbEQsT0FBTyxTQUFTLENBQUMsWUFBWSxDQUFDLENBQUMsQ0FBQyxPQUFPLENBQUMsWUFBWSxDQUFDLENBQUMsTUFBTSxFQUFFLENBQUMsQ0FBQztJQUNsRSxDQUFDIiwic291cmNlc0NvbnRlbnQiOlsiLyoqXG4gKiBAbGljZW5zZVxuICogQ29weXJpZ2h0IEdvb2dsZSBMTEMgQWxsIFJpZ2h0cyBSZXNlcnZlZC5cbiAqXG4gKiBVc2Ugb2YgdGhpcyBzb3VyY2UgY29kZSBpcyBnb3Zlcm5lZCBieSBhbiBNSVQtc3R5bGUgbGljZW5zZSB0aGF0IGNhbiBiZVxuICogZm91bmQgaW4gdGhlIExJQ0VOU0UgZmlsZSBhdCBodHRwczovL2FuZ3VsYXIuaW8vbGljZW5zZVxuICovXG5cbmltcG9ydCB7aWRlbnRpZmllck5hbWV9IGZyb20gJy4uL2NvbXBpbGVfbWV0YWRhdGEnO1xuaW1wb3J0IHtDb21waWxlUmVmbGVjdG9yfSBmcm9tICcuLi9jb21waWxlX3JlZmxlY3Rvcic7XG5cbmltcG9ydCB7RW1pdHRlclZpc2l0b3JDb250ZXh0fSBmcm9tICcuL2Fic3RyYWN0X2VtaXR0ZXInO1xuaW1wb3J0IHtBYnN0cmFjdEpzRW1pdHRlclZpc2l0b3J9IGZyb20gJy4vYWJzdHJhY3RfanNfZW1pdHRlcic7XG5pbXBvcnQgKiBhcyBvIGZyb20gJy4vb3V0cHV0X2FzdCc7XG5pbXBvcnQge25ld1RydXN0ZWRGdW5jdGlvbkZvckpJVH0gZnJvbSAnLi9vdXRwdXRfaml0X3RydXN0ZWRfdHlwZXMnO1xuXG4vKipcbiAqIEEgaGVscGVyIGNsYXNzIHRvIG1hbmFnZSB0aGUgZXZhbHVhdGlvbiBvZiBKSVQgZ2VuZXJhdGVkIGNvZGUuXG4gKi9cbmV4cG9ydCBjbGFzcyBKaXRFdmFsdWF0b3Ige1xuICAvKipcbiAgICpcbiAgICogQHBhcmFtIHNvdXJjZVVybCBUaGUgVVJMIG9mIHRoZSBnZW5lcmF0ZWQgY29kZS5cbiAgICogQHBhcmFtIHN0YXRlbWVudHMgQW4gYXJyYXkgb2YgQW5ndWxhciBzdGF0ZW1lbnQgQVNUIG5vZGVzIHRvIGJlIGV2YWx1YXRlZC5cbiAgICogQHBhcmFtIHJlZmxlY3RvciBBIGhlbHBlciB1c2VkIHdoZW4gY29udmVydGluZyB0aGUgc3RhdGVtZW50cyB0byBleGVjdXRhYmxlIGNvZGUuXG4gICAqIEBwYXJhbSBjcmVhdGVTb3VyY2VNYXBzIElmIHRydWUgdGhlbiBjcmVhdGUgYSBzb3VyY2UtbWFwIGZvciB0aGUgZ2VuZXJhdGVkIGNvZGUgYW5kIGluY2x1ZGUgaXRcbiAgICogaW5saW5lIGFzIGEgc291cmNlLW1hcCBjb21tZW50LlxuICAgKiBAcmV0dXJucyBBIG1hcCBvZiBhbGwgdGhlIHZhcmlhYmxlcyBpbiB0aGUgZ2VuZXJhdGVkIGNvZGUuXG4gICAqL1xuICBldmFsdWF0ZVN0YXRlbWVudHMoXG4gICAgICBzb3VyY2VVcmw6IHN0cmluZywgc3RhdGVtZW50czogby5TdGF0ZW1lbnRbXSwgcmVmbGVjdG9yOiBDb21waWxlUmVmbGVjdG9yLFxuICAgICAgY3JlYXRlU291cmNlTWFwczogYm9vbGVhbik6IHtba2V5OiBzdHJpbmddOiBhbnl9IHtcbiAgICBjb25zdCBjb252ZXJ0ZXIgPSBuZXcgSml0RW1pdHRlclZpc2l0b3IocmVmbGVjdG9yKTtcbiAgICBjb25zdCBjdHggPSBFbWl0dGVyVmlzaXRvckNvbnRleHQuY3JlYXRlUm9vdCgpO1xuICAgIC8vIEVuc3VyZSBnZW5lcmF0ZWQgY29kZSBpcyBpbiBzdHJpY3QgbW9kZVxuICAgIGlmIChzdGF0ZW1lbnRzLmxlbmd0aCA+IDAgJiYgIWlzVXNlU3RyaWN0U3RhdGVtZW50KHN0YXRlbWVudHNbMF0pKSB7XG4gICAgICBzdGF0ZW1lbnRzID0gW1xuICAgICAgICBvLmxpdGVyYWwoJ3VzZSBzdHJpY3QnKS50b1N0bXQoKSxcbiAgICAgICAgLi4uc3RhdGVtZW50cyxcbiAgICAgIF07XG4gICAgfVxuICAgIGNvbnZlcnRlci52aXNpdEFsbFN0YXRlbWVudHMoc3RhdGVtZW50cywgY3R4KTtcbiAgICBjb252ZXJ0ZXIuY3JlYXRlUmV0dXJuU3RtdChjdHgpO1xuICAgIHJldHVybiB0aGlzLmV2YWx1YXRlQ29kZShzb3VyY2VVcmwsIGN0eCwgY29udmVydGVyLmdldEFyZ3MoKSwgY3JlYXRlU291cmNlTWFwcyk7XG4gIH1cblxuICAvKipcbiAgICogRXZhbHVhdGUgYSBwaWVjZSBvZiBKSVQgZ2VuZXJhdGVkIGNvZGUuXG4gICAqIEBwYXJhbSBzb3VyY2VVcmwgVGhlIFVSTCBvZiB0aGlzIGdlbmVyYXRlZCBjb2RlLlxuICAgKiBAcGFyYW0gY3R4IEEgY29udGV4dCBvYmplY3QgdGhhdCBjb250YWlucyBhbiBBU1Qgb2YgdGhlIGNvZGUgdG8gYmUgZXZhbHVhdGVkLlxuICAgKiBAcGFyYW0gdmFycyBBIG1hcCBjb250YWluaW5nIHRoZSBuYW1lcyBhbmQgdmFsdWVzIG9mIHZhcmlhYmxlcyB0aGF0IHRoZSBldmFsdWF0ZWQgY29kZSBtaWdodFxuICAgKiByZWZlcmVuY2UuXG4gICAqIEBwYXJhbSBjcmVhdGVTb3VyY2VNYXAgSWYgdHJ1ZSB0aGVuIGNyZWF0ZSBhIHNvdXJjZS1tYXAgZm9yIHRoZSBnZW5lcmF0ZWQgY29kZSBhbmQgaW5jbHVkZSBpdFxuICAgKiBpbmxpbmUgYXMgYSBzb3VyY2UtbWFwIGNvbW1lbnQuXG4gICAqIEByZXR1cm5zIFRoZSByZXN1bHQgb2YgZXZhbHVhdGluZyB0aGUgY29kZS5cbiAgICovXG4gIGV2YWx1YXRlQ29kZShcbiAgICAgIHNvdXJjZVVybDogc3RyaW5nLCBjdHg6IEVtaXR0ZXJWaXNpdG9yQ29udGV4dCwgdmFyczoge1trZXk6IHN0cmluZ106IGFueX0sXG4gICAgICBjcmVhdGVTb3VyY2VNYXA6IGJvb2xlYW4pOiBhbnkge1xuICAgIGxldCBmbkJvZHkgPSBgXCJ1c2Ugc3RyaWN0XCI7JHtjdHgudG9Tb3VyY2UoKX1cXG4vLyMgc291cmNlVVJMPSR7c291cmNlVXJsfWA7XG4gICAgY29uc3QgZm5BcmdOYW1lczogc3RyaW5nW10gPSBbXTtcbiAgICBjb25zdCBmbkFyZ1ZhbHVlczogYW55W10gPSBbXTtcbiAgICBmb3IgKGNvbnN0IGFyZ05hbWUgaW4gdmFycykge1xuICAgICAgZm5BcmdWYWx1ZXMucHVzaCh2YXJzW2FyZ05hbWVdKTtcbiAgICAgIGZuQXJnTmFtZXMucHVzaChhcmdOYW1lKTtcbiAgICB9XG4gICAgaWYgKGNyZWF0ZVNvdXJjZU1hcCkge1xuICAgICAgLy8gdXNpbmcgYG5ldyBGdW5jdGlvbiguLi4pYCBnZW5lcmF0ZXMgYSBoZWFkZXIsIDEgbGluZSBvZiBubyBhcmd1bWVudHMsIDIgbGluZXMgb3RoZXJ3aXNlXG4gICAgICAvLyBFLmcuIGBgYFxuICAgICAgLy8gZnVuY3Rpb24gYW5vbnltb3VzKGEsYixjXG4gICAgICAvLyAvKiovKSB7IC4uLiB9YGBgXG4gICAgICAvLyBXZSBkb24ndCB3YW50IHRvIGhhcmQgY29kZSB0aGlzIGZhY3QsIHNvIHdlIGF1dG8gZGV0ZWN0IGl0IHZpYSBhbiBlbXB0eSBmdW5jdGlvbiBmaXJzdC5cbiAgICAgIGNvbnN0IGVtcHR5Rm4gPSBuZXdUcnVzdGVkRnVuY3Rpb25Gb3JKSVQoLi4uZm5BcmdOYW1lcy5jb25jYXQoJ3JldHVybiBudWxsOycpKS50b1N0cmluZygpO1xuICAgICAgY29uc3QgaGVhZGVyTGluZXMgPSBlbXB0eUZuLnNsaWNlKDAsIGVtcHR5Rm4uaW5kZXhPZigncmV0dXJuIG51bGw7JykpLnNwbGl0KCdcXG4nKS5sZW5ndGggLSAxO1xuICAgICAgZm5Cb2R5ICs9IGBcXG4ke2N0eC50b1NvdXJjZU1hcEdlbmVyYXRvcihzb3VyY2VVcmwsIGhlYWRlckxpbmVzKS50b0pzQ29tbWVudCgpfWA7XG4gICAgfVxuICAgIGNvbnN0IGZuID0gbmV3VHJ1c3RlZEZ1bmN0aW9uRm9ySklUKC4uLmZuQXJnTmFtZXMuY29uY2F0KGZuQm9keSkpO1xuICAgIHJldHVybiB0aGlzLmV4ZWN1dGVGdW5jdGlvbihmbiwgZm5BcmdWYWx1ZXMpO1xuICB9XG5cbiAgLyoqXG4gICAqIEV4ZWN1dGUgYSBKSVQgZ2VuZXJhdGVkIGZ1bmN0aW9uIGJ5IGNhbGxpbmcgaXQuXG4gICAqXG4gICAqIFRoaXMgbWV0aG9kIGNhbiBiZSBvdmVycmlkZGVuIGluIHRlc3RzIHRvIGNhcHR1cmUgdGhlIGZ1bmN0aW9ucyB0aGF0IGFyZSBnZW5lcmF0ZWRcbiAgICogYnkgdGhpcyBgSml0RXZhbHVhdG9yYCBjbGFzcy5cbiAgICpcbiAgICogQHBhcmFtIGZuIEEgZnVuY3Rpb24gdG8gZXhlY3V0ZS5cbiAgICogQHBhcmFtIGFyZ3MgVGhlIGFyZ3VtZW50cyB0byBwYXNzIHRvIHRoZSBmdW5jdGlvbiBiZWluZyBleGVjdXRlZC5cbiAgICogQHJldHVybnMgVGhlIHJldHVybiB2YWx1ZSBvZiB0aGUgZXhlY3V0ZWQgZnVuY3Rpb24uXG4gICAqL1xuICBleGVjdXRlRnVuY3Rpb24oZm46IEZ1bmN0aW9uLCBhcmdzOiBhbnlbXSkge1xuICAgIHJldHVybiBmbiguLi5hcmdzKTtcbiAgfVxufVxuXG4vKipcbiAqIEFuIEFuZ3VsYXIgQVNUIHZpc2l0b3IgdGhhdCBjb252ZXJ0cyBBU1Qgbm9kZXMgaW50byBleGVjdXRhYmxlIEphdmFTY3JpcHQgY29kZS5cbiAqL1xuZXhwb3J0IGNsYXNzIEppdEVtaXR0ZXJWaXNpdG9yIGV4dGVuZHMgQWJzdHJhY3RKc0VtaXR0ZXJWaXNpdG9yIHtcbiAgcHJpdmF0ZSBfZXZhbEFyZ05hbWVzOiBzdHJpbmdbXSA9IFtdO1xuICBwcml2YXRlIF9ldmFsQXJnVmFsdWVzOiBhbnlbXSA9IFtdO1xuICBwcml2YXRlIF9ldmFsRXhwb3J0ZWRWYXJzOiBzdHJpbmdbXSA9IFtdO1xuXG4gIGNvbnN0cnVjdG9yKHByaXZhdGUgcmVmbGVjdG9yOiBDb21waWxlUmVmbGVjdG9yKSB7XG4gICAgc3VwZXIoKTtcbiAgfVxuXG4gIGNyZWF0ZVJldHVyblN0bXQoY3R4OiBFbWl0dGVyVmlzaXRvckNvbnRleHQpIHtcbiAgICBjb25zdCBzdG10ID0gbmV3IG8uUmV0dXJuU3RhdGVtZW50KG5ldyBvLkxpdGVyYWxNYXBFeHByKHRoaXMuX2V2YWxFeHBvcnRlZFZhcnMubWFwKFxuICAgICAgICByZXN1bHRWYXIgPT4gbmV3IG8uTGl0ZXJhbE1hcEVudHJ5KHJlc3VsdFZhciwgby52YXJpYWJsZShyZXN1bHRWYXIpLCBmYWxzZSkpKSk7XG4gICAgc3RtdC52aXNpdFN0YXRlbWVudCh0aGlzLCBjdHgpO1xuICB9XG5cbiAgZ2V0QXJncygpOiB7W2tleTogc3RyaW5nXTogYW55fSB7XG4gICAgY29uc3QgcmVzdWx0OiB7W2tleTogc3RyaW5nXTogYW55fSA9IHt9O1xuICAgIGZvciAobGV0IGkgPSAwOyBpIDwgdGhpcy5fZXZhbEFyZ05hbWVzLmxlbmd0aDsgaSsrKSB7XG4gICAgICByZXN1bHRbdGhpcy5fZXZhbEFyZ05hbWVzW2ldXSA9IHRoaXMuX2V2YWxBcmdWYWx1ZXNbaV07XG4gICAgfVxuICAgIHJldHVybiByZXN1bHQ7XG4gIH1cblxuICB2aXNpdEV4dGVybmFsRXhwcihhc3Q6IG8uRXh0ZXJuYWxFeHByLCBjdHg6IEVtaXR0ZXJWaXNpdG9yQ29udGV4dCk6IGFueSB7XG4gICAgdGhpcy5fZW1pdFJlZmVyZW5jZVRvRXh0ZXJuYWwoYXN0LCB0aGlzLnJlZmxlY3Rvci5yZXNvbHZlRXh0ZXJuYWxSZWZlcmVuY2UoYXN0LnZhbHVlKSwgY3R4KTtcbiAgICByZXR1cm4gbnVsbDtcbiAgfVxuXG4gIHZpc2l0V3JhcHBlZE5vZGVFeHByKGFzdDogby5XcmFwcGVkTm9kZUV4cHI8YW55PiwgY3R4OiBFbWl0dGVyVmlzaXRvckNvbnRleHQpOiBhbnkge1xuICAgIHRoaXMuX2VtaXRSZWZlcmVuY2VUb0V4dGVybmFsKGFzdCwgYXN0Lm5vZGUsIGN0eCk7XG4gICAgcmV0dXJuIG51bGw7XG4gIH1cblxuICB2aXNpdERlY2xhcmVWYXJTdG10KHN0bXQ6IG8uRGVjbGFyZVZhclN0bXQsIGN0eDogRW1pdHRlclZpc2l0b3JDb250ZXh0KTogYW55IHtcbiAgICBpZiAoc3RtdC5oYXNNb2RpZmllcihvLlN0bXRNb2RpZmllci5FeHBvcnRlZCkpIHtcbiAgICAgIHRoaXMuX2V2YWxFeHBvcnRlZFZhcnMucHVzaChzdG10Lm5hbWUpO1xuICAgIH1cbiAgICByZXR1cm4gc3VwZXIudmlzaXREZWNsYXJlVmFyU3RtdChzdG10LCBjdHgpO1xuICB9XG5cbiAgdmlzaXREZWNsYXJlRnVuY3Rpb25TdG10KHN0bXQ6IG8uRGVjbGFyZUZ1bmN0aW9uU3RtdCwgY3R4OiBFbWl0dGVyVmlzaXRvckNvbnRleHQpOiBhbnkge1xuICAgIGlmIChzdG10Lmhhc01vZGlmaWVyKG8uU3RtdE1vZGlmaWVyLkV4cG9ydGVkKSkge1xuICAgICAgdGhpcy5fZXZhbEV4cG9ydGVkVmFycy5wdXNoKHN0bXQubmFtZSk7XG4gICAgfVxuICAgIHJldHVybiBzdXBlci52aXNpdERlY2xhcmVGdW5jdGlvblN0bXQoc3RtdCwgY3R4KTtcbiAgfVxuXG4gIHZpc2l0RGVjbGFyZUNsYXNzU3RtdChzdG10OiBvLkNsYXNzU3RtdCwgY3R4OiBFbWl0dGVyVmlzaXRvckNvbnRleHQpOiBhbnkge1xuICAgIGlmIChzdG10Lmhhc01vZGlmaWVyKG8uU3RtdE1vZGlmaWVyLkV4cG9ydGVkKSkge1xuICAgICAgdGhpcy5fZXZhbEV4cG9ydGVkVmFycy5wdXNoKHN0bXQubmFtZSk7XG4gICAgfVxuICAgIHJldHVybiBzdXBlci52aXNpdERlY2xhcmVDbGFzc1N0bXQoc3RtdCwgY3R4KTtcbiAgfVxuXG4gIHByaXZhdGUgX2VtaXRSZWZlcmVuY2VUb0V4dGVybmFsKGFzdDogby5FeHByZXNzaW9uLCB2YWx1ZTogYW55LCBjdHg6IEVtaXR0ZXJWaXNpdG9yQ29udGV4dCk6XG4gICAgICB2b2lkIHtcbiAgICBsZXQgaWQgPSB0aGlzLl9ldmFsQXJnVmFsdWVzLmluZGV4T2YodmFsdWUpO1xuICAgIGlmIChpZCA9PT0gLTEpIHtcbiAgICAgIGlkID0gdGhpcy5fZXZhbEFyZ1ZhbHVlcy5sZW5ndGg7XG4gICAgICB0aGlzLl9ldmFsQXJnVmFsdWVzLnB1c2godmFsdWUpO1xuICAgICAgY29uc3QgbmFtZSA9IGlkZW50aWZpZXJOYW1lKHtyZWZlcmVuY2U6IHZhbHVlfSkgfHwgJ3ZhbCc7XG4gICAgICB0aGlzLl9ldmFsQXJnTmFtZXMucHVzaChgaml0XyR7bmFtZX1fJHtpZH1gKTtcbiAgICB9XG4gICAgY3R4LnByaW50KGFzdCwgdGhpcy5fZXZhbEFyZ05hbWVzW2lkXSk7XG4gIH1cbn1cblxuXG5mdW5jdGlvbiBpc1VzZVN0cmljdFN0YXRlbWVudChzdGF0ZW1lbnQ6IG8uU3RhdGVtZW50KTogYm9vbGVhbiB7XG4gIHJldHVybiBzdGF0ZW1lbnQuaXNFcXVpdmFsZW50KG8ubGl0ZXJhbCgndXNlIHN0cmljdCcpLnRvU3RtdCgpKTtcbn1cbiJdfQ==