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
        define("@angular/compiler/src/view_compiler/type_check_compiler", ["require", "exports", "tslib", "@angular/compiler/src/aot/static_symbol", "@angular/compiler/src/compiler_util/expression_converter", "@angular/compiler/src/output/output_ast", "@angular/compiler/src/template_parser/template_ast"], factory);
    }
})(function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.TypeCheckCompiler = void 0;
    var tslib_1 = require("tslib");
    var static_symbol_1 = require("@angular/compiler/src/aot/static_symbol");
    var expression_converter_1 = require("@angular/compiler/src/compiler_util/expression_converter");
    var o = require("@angular/compiler/src/output/output_ast");
    var template_ast_1 = require("@angular/compiler/src/template_parser/template_ast");
    /**
     * Generates code that is used to type check templates.
     */
    var TypeCheckCompiler = /** @class */ (function () {
        function TypeCheckCompiler(options, reflector) {
            this.options = options;
            this.reflector = reflector;
        }
        /**
         * Important notes:
         * - This must not produce new `import` statements, but only refer to types outside
         *   of the file via the variables provided via externalReferenceVars.
         *   This allows Typescript to reuse the old program's structure as no imports have changed.
         * - This must not produce any exports, as this would pollute the .d.ts file
         *   and also violate the point above.
         */
        TypeCheckCompiler.prototype.compileComponent = function (componentId, component, template, usedPipes, externalReferenceVars, ctx) {
            var _this = this;
            var pipes = new Map();
            usedPipes.forEach(function (p) { return pipes.set(p.name, p.type.reference); });
            var embeddedViewCount = 0;
            var viewBuilderFactory = function (parent, guards) {
                var embeddedViewIndex = embeddedViewCount++;
                return new ViewBuilder(_this.options, _this.reflector, externalReferenceVars, parent, component.type.reference, component.isHost, embeddedViewIndex, pipes, guards, ctx, viewBuilderFactory);
            };
            var visitor = viewBuilderFactory(null, []);
            visitor.visitAll([], template);
            return visitor.build(componentId);
        };
        return TypeCheckCompiler;
    }());
    exports.TypeCheckCompiler = TypeCheckCompiler;
    var DYNAMIC_VAR_NAME = '_any';
    var TypeCheckLocalResolver = /** @class */ (function () {
        function TypeCheckLocalResolver() {
        }
        TypeCheckLocalResolver.prototype.notifyImplicitReceiverUse = function () { };
        TypeCheckLocalResolver.prototype.getLocal = function (name) {
            if (name === expression_converter_1.EventHandlerVars.event.name) {
                // References to the event should not be type-checked.
                // TODO(chuckj): determine a better type for the event.
                return o.variable(DYNAMIC_VAR_NAME);
            }
            return null;
        };
        return TypeCheckLocalResolver;
    }());
    var defaultResolver = new TypeCheckLocalResolver();
    var ViewBuilder = /** @class */ (function () {
        function ViewBuilder(options, reflector, externalReferenceVars, parent, component, isHostComponent, embeddedViewIndex, pipes, guards, ctx, viewBuilderFactory) {
            this.options = options;
            this.reflector = reflector;
            this.externalReferenceVars = externalReferenceVars;
            this.parent = parent;
            this.component = component;
            this.isHostComponent = isHostComponent;
            this.embeddedViewIndex = embeddedViewIndex;
            this.pipes = pipes;
            this.guards = guards;
            this.ctx = ctx;
            this.viewBuilderFactory = viewBuilderFactory;
            this.refOutputVars = new Map();
            this.variables = [];
            this.children = [];
            this.updates = [];
            this.actions = [];
        }
        ViewBuilder.prototype.getOutputVar = function (type) {
            var varName;
            if (type === this.component && this.isHostComponent) {
                varName = DYNAMIC_VAR_NAME;
            }
            else if (type instanceof static_symbol_1.StaticSymbol) {
                varName = this.externalReferenceVars.get(type);
            }
            else {
                varName = DYNAMIC_VAR_NAME;
            }
            if (!varName) {
                throw new Error("Illegal State: referring to a type without a variable " + JSON.stringify(type));
            }
            return varName;
        };
        ViewBuilder.prototype.getTypeGuardExpressions = function (ast) {
            var e_1, _a, e_2, _b;
            var result = tslib_1.__spread(this.guards);
            try {
                for (var _c = tslib_1.__values(ast.directives), _d = _c.next(); !_d.done; _d = _c.next()) {
                    var directive = _d.value;
                    try {
                        for (var _e = (e_2 = void 0, tslib_1.__values(directive.inputs)), _f = _e.next(); !_f.done; _f = _e.next()) {
                            var input = _f.value;
                            var guard = directive.directive.guards[input.directiveName];
                            if (guard) {
                                var useIf = guard === 'UseIf';
                                result.push({
                                    guard: guard,
                                    useIf: useIf,
                                    expression: {
                                        context: this.component,
                                        value: input.value,
                                        sourceSpan: input.sourceSpan,
                                    },
                                });
                            }
                        }
                    }
                    catch (e_2_1) { e_2 = { error: e_2_1 }; }
                    finally {
                        try {
                            if (_f && !_f.done && (_b = _e.return)) _b.call(_e);
                        }
                        finally { if (e_2) throw e_2.error; }
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
            return result;
        };
        ViewBuilder.prototype.visitAll = function (variables, astNodes) {
            this.variables = variables;
            template_ast_1.templateVisitAll(this, astNodes);
        };
        ViewBuilder.prototype.build = function (componentId, targetStatements) {
            var e_3, _a;
            var _this = this;
            if (targetStatements === void 0) { targetStatements = []; }
            this.children.forEach(function (child) { return child.build(componentId, targetStatements); });
            var viewStmts = [o.variable(DYNAMIC_VAR_NAME).set(o.NULL_EXPR).toDeclStmt(o.DYNAMIC_TYPE)];
            var bindingCount = 0;
            this.updates.forEach(function (expression) {
                var _a = _this.preprocessUpdateExpression(expression), sourceSpan = _a.sourceSpan, context = _a.context, value = _a.value;
                var bindingId = "" + bindingCount++;
                var nameResolver = context === _this.component ? _this : defaultResolver;
                var _b = expression_converter_1.convertPropertyBinding(nameResolver, o.variable(_this.getOutputVar(context)), value, bindingId, expression_converter_1.BindingForm.General), stmts = _b.stmts, currValExpr = _b.currValExpr;
                stmts.push(new o.ExpressionStatement(currValExpr));
                viewStmts.push.apply(viewStmts, tslib_1.__spread(stmts.map(function (stmt) { return o.applySourceSpanToStatementIfNeeded(stmt, sourceSpan); })));
            });
            this.actions.forEach(function (_a) {
                var sourceSpan = _a.sourceSpan, context = _a.context, value = _a.value;
                var bindingId = "" + bindingCount++;
                var nameResolver = context === _this.component ? _this : defaultResolver;
                var stmts = expression_converter_1.convertActionBinding(nameResolver, o.variable(_this.getOutputVar(context)), value, bindingId).stmts;
                viewStmts.push.apply(viewStmts, tslib_1.__spread(stmts.map(function (stmt) { return o.applySourceSpanToStatementIfNeeded(stmt, sourceSpan); })));
            });
            if (this.guards.length) {
                var guardExpression = undefined;
                try {
                    for (var _b = tslib_1.__values(this.guards), _c = _b.next(); !_c.done; _c = _b.next()) {
                        var guard = _c.value;
                        var _d = this.preprocessUpdateExpression(guard.expression), context = _d.context, value = _d.value;
                        var bindingId = "" + bindingCount++;
                        var nameResolver = context === this.component ? this : defaultResolver;
                        // We only support support simple expressions and ignore others as they
                        // are unlikely to affect type narrowing.
                        var _e = expression_converter_1.convertPropertyBinding(nameResolver, o.variable(this.getOutputVar(context)), value, bindingId, expression_converter_1.BindingForm.TrySimple), stmts = _e.stmts, currValExpr = _e.currValExpr;
                        if (stmts.length == 0) {
                            var guardClause = guard.useIf ? currValExpr : this.ctx.importExpr(guard.guard).callFn([currValExpr]);
                            guardExpression = guardExpression ? guardExpression.and(guardClause) : guardClause;
                        }
                    }
                }
                catch (e_3_1) { e_3 = { error: e_3_1 }; }
                finally {
                    try {
                        if (_c && !_c.done && (_a = _b.return)) _a.call(_b);
                    }
                    finally { if (e_3) throw e_3.error; }
                }
                if (guardExpression) {
                    viewStmts = [new o.IfStmt(guardExpression, viewStmts)];
                }
            }
            var viewName = "_View_" + componentId + "_" + this.embeddedViewIndex;
            var viewFactory = new o.DeclareFunctionStmt(viewName, [], viewStmts);
            targetStatements.push(viewFactory);
            return targetStatements;
        };
        ViewBuilder.prototype.visitBoundText = function (ast, context) {
            var _this = this;
            var astWithSource = ast.value;
            var inter = astWithSource.ast;
            inter.expressions.forEach(function (expr) {
                return _this.updates.push({ context: _this.component, value: expr, sourceSpan: ast.sourceSpan });
            });
        };
        ViewBuilder.prototype.visitEmbeddedTemplate = function (ast, context) {
            this.visitElementOrTemplate(ast);
            // Note: The old view compiler used to use an `any` type
            // for the context in any embedded view.
            // We keep this behaivor behind a flag for now.
            if (this.options.fullTemplateTypeCheck) {
                // Find any applicable type guards. For example, NgIf has a type guard on ngIf
                // (see NgIf.ngIfTypeGuard) that can be used to indicate that a template is only
                // stamped out if ngIf is truthy so any bindings in the template can assume that,
                // if a nullable type is used for ngIf, that expression is not null or undefined.
                var guards = this.getTypeGuardExpressions(ast);
                var childVisitor = this.viewBuilderFactory(this, guards);
                this.children.push(childVisitor);
                childVisitor.visitAll(ast.variables, ast.children);
            }
        };
        ViewBuilder.prototype.visitElement = function (ast, context) {
            var _this = this;
            this.visitElementOrTemplate(ast);
            var inputDefs = [];
            var updateRendererExpressions = [];
            var outputDefs = [];
            ast.inputs.forEach(function (inputAst) {
                _this.updates.push({ context: _this.component, value: inputAst.value, sourceSpan: inputAst.sourceSpan });
            });
            template_ast_1.templateVisitAll(this, ast.children);
        };
        ViewBuilder.prototype.visitElementOrTemplate = function (ast) {
            var _this = this;
            ast.directives.forEach(function (dirAst) {
                _this.visitDirective(dirAst);
            });
            ast.references.forEach(function (ref) {
                var outputVarType = null;
                // Note: The old view compiler used to use an `any` type
                // for directives exposed via `exportAs`.
                // We keep this behaivor behind a flag for now.
                if (ref.value && ref.value.identifier && _this.options.fullTemplateTypeCheck) {
                    outputVarType = ref.value.identifier.reference;
                }
                else {
                    outputVarType = o.BuiltinTypeName.Dynamic;
                }
                _this.refOutputVars.set(ref.name, outputVarType);
            });
            ast.outputs.forEach(function (outputAst) {
                _this.actions.push({ context: _this.component, value: outputAst.handler, sourceSpan: outputAst.sourceSpan });
            });
        };
        ViewBuilder.prototype.visitDirective = function (dirAst) {
            var _this = this;
            var dirType = dirAst.directive.type.reference;
            dirAst.inputs.forEach(function (input) { return _this.updates.push({ context: _this.component, value: input.value, sourceSpan: input.sourceSpan }); });
            // Note: The old view compiler used to use an `any` type
            // for expressions in host properties / events.
            // We keep this behaivor behind a flag for now.
            if (this.options.fullTemplateTypeCheck) {
                dirAst.hostProperties.forEach(function (inputAst) { return _this.updates.push({ context: dirType, value: inputAst.value, sourceSpan: inputAst.sourceSpan }); });
                dirAst.hostEvents.forEach(function (hostEventAst) { return _this.actions.push({
                    context: dirType,
                    value: hostEventAst.handler,
                    sourceSpan: hostEventAst.sourceSpan
                }); });
            }
        };
        ViewBuilder.prototype.notifyImplicitReceiverUse = function () { };
        ViewBuilder.prototype.getLocal = function (name) {
            if (name == expression_converter_1.EventHandlerVars.event.name) {
                return o.variable(this.getOutputVar(o.BuiltinTypeName.Dynamic));
            }
            for (var currBuilder = this; currBuilder; currBuilder = currBuilder.parent) {
                var outputVarType = void 0;
                // check references
                outputVarType = currBuilder.refOutputVars.get(name);
                if (outputVarType == null) {
                    // check variables
                    var varAst = currBuilder.variables.find(function (varAst) { return varAst.name === name; });
                    if (varAst) {
                        outputVarType = o.BuiltinTypeName.Dynamic;
                    }
                }
                if (outputVarType != null) {
                    return o.variable(this.getOutputVar(outputVarType));
                }
            }
            return null;
        };
        ViewBuilder.prototype.pipeOutputVar = function (name) {
            var pipe = this.pipes.get(name);
            if (!pipe) {
                throw new Error("Illegal State: Could not find pipe " + name + " in template of " + this.component);
            }
            return this.getOutputVar(pipe);
        };
        ViewBuilder.prototype.preprocessUpdateExpression = function (expression) {
            var _this = this;
            return {
                sourceSpan: expression.sourceSpan,
                context: expression.context,
                value: expression_converter_1.convertPropertyBindingBuiltins({
                    createLiteralArrayConverter: function (argCount) { return function (args) {
                        var arr = o.literalArr(args);
                        // Note: The old view compiler used to use an `any` type
                        // for arrays.
                        return _this.options.fullTemplateTypeCheck ? arr : arr.cast(o.DYNAMIC_TYPE);
                    }; },
                    createLiteralMapConverter: function (keys) {
                        return function (values) {
                            var entries = keys.map(function (k, i) { return ({
                                key: k.key,
                                value: values[i],
                                quoted: k.quoted,
                            }); });
                            var map = o.literalMap(entries);
                            // Note: The old view compiler used to use an `any` type
                            // for maps.
                            return _this.options.fullTemplateTypeCheck ? map : map.cast(o.DYNAMIC_TYPE);
                        };
                    },
                    createPipeConverter: function (name, argCount) { return function (args) {
                        // Note: The old view compiler used to use an `any` type
                        // for pipes.
                        var pipeExpr = _this.options.fullTemplateTypeCheck ?
                            o.variable(_this.pipeOutputVar(name)) :
                            o.variable(_this.getOutputVar(o.BuiltinTypeName.Dynamic));
                        return pipeExpr.callMethod('transform', args);
                    }; },
                }, expression.value)
            };
        };
        ViewBuilder.prototype.visitNgContent = function (ast, context) { };
        ViewBuilder.prototype.visitText = function (ast, context) { };
        ViewBuilder.prototype.visitDirectiveProperty = function (ast, context) { };
        ViewBuilder.prototype.visitReference = function (ast, context) { };
        ViewBuilder.prototype.visitVariable = function (ast, context) { };
        ViewBuilder.prototype.visitEvent = function (ast, context) { };
        ViewBuilder.prototype.visitElementProperty = function (ast, context) { };
        ViewBuilder.prototype.visitAttr = function (ast, context) { };
        return ViewBuilder;
    }());
});
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoidHlwZV9jaGVja19jb21waWxlci5qcyIsInNvdXJjZVJvb3QiOiIiLCJzb3VyY2VzIjpbIi4uLy4uLy4uLy4uLy4uLy4uLy4uL3BhY2thZ2VzL2NvbXBpbGVyL3NyYy92aWV3X2NvbXBpbGVyL3R5cGVfY2hlY2tfY29tcGlsZXIudHMiXSwibmFtZXMiOltdLCJtYXBwaW5ncyI6IkFBQUE7Ozs7OztHQU1HOzs7Ozs7Ozs7Ozs7OztJQUlILHlFQUFrRDtJQUVsRCxpR0FBaUw7SUFFakwsMkRBQTBDO0lBRTFDLG1GQUE2UjtJQUk3Ujs7T0FFRztJQUNIO1FBQ0UsMkJBQW9CLE9BQTJCLEVBQVUsU0FBMEI7WUFBL0QsWUFBTyxHQUFQLE9BQU8sQ0FBb0I7WUFBVSxjQUFTLEdBQVQsU0FBUyxDQUFpQjtRQUFHLENBQUM7UUFFdkY7Ozs7Ozs7V0FPRztRQUNILDRDQUFnQixHQUFoQixVQUNJLFdBQW1CLEVBQUUsU0FBbUMsRUFBRSxRQUF1QixFQUNqRixTQUErQixFQUFFLHFCQUFnRCxFQUNqRixHQUFrQjtZQUh0QixpQkFtQkM7WUFmQyxJQUFNLEtBQUssR0FBRyxJQUFJLEdBQUcsRUFBd0IsQ0FBQztZQUM5QyxTQUFTLENBQUMsT0FBTyxDQUFDLFVBQUEsQ0FBQyxJQUFJLE9BQUEsS0FBSyxDQUFDLEdBQUcsQ0FBQyxDQUFDLENBQUMsSUFBSSxFQUFFLENBQUMsQ0FBQyxJQUFJLENBQUMsU0FBUyxDQUFDLEVBQW5DLENBQW1DLENBQUMsQ0FBQztZQUM1RCxJQUFJLGlCQUFpQixHQUFHLENBQUMsQ0FBQztZQUMxQixJQUFNLGtCQUFrQixHQUNwQixVQUFDLE1BQXdCLEVBQUUsTUFBeUI7Z0JBQ2xELElBQU0saUJBQWlCLEdBQUcsaUJBQWlCLEVBQUUsQ0FBQztnQkFDOUMsT0FBTyxJQUFJLFdBQVcsQ0FDbEIsS0FBSSxDQUFDLE9BQU8sRUFBRSxLQUFJLENBQUMsU0FBUyxFQUFFLHFCQUFxQixFQUFFLE1BQU0sRUFBRSxTQUFTLENBQUMsSUFBSSxDQUFDLFNBQVMsRUFD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RUFBRSxPQUFZLElBQVEsQ0FBQztRQUNyRCxnQ0FBVSxHQUFWLFVBQVcsR0FBa0IsRUFBRSxPQUFZLElBQVEsQ0FBQztRQUNwRCwwQ0FBb0IsR0FBcEIsVUFBcUIsR0FBNEIsRUFBRSxPQUFZLElBQVEsQ0FBQztRQUN4RSwrQkFBUyxHQUFULFVBQVUsR0FBWSxFQUFFLE9BQVksSUFBUSxDQUFDO1FBQy9DLGtCQUFDO0lBQUQsQ0FBQyxBQXBSRCxJQW9SQyIsInNvdXJjZXNDb250ZW50IjpbIi8qKlxuICogQGxpY2Vuc2VcbiAqIENvcHlyaWdodCBHb29nbGUgTExDIEFsbCBSaWdodHMgUmVzZXJ2ZWQuXG4gKlxuICogVXNlIG9mIHRoaXMgc291cmNlIGNvZGUgaXMgZ292ZXJuZWQgYnkgYW4gTUlULXN0eWxlIGxpY2Vuc2UgdGhhdCBjYW4gYmVcbiAqIGZvdW5kIGluIHRoZSBMSUNFTlNFIGZpbGUgYXQgaHR0cHM6Ly9hbmd1bGFyLmlvL2xpY2Vuc2VcbiAqL1xuXG5pbXBvcnQge0FvdENvbXBpbGVyT3B0aW9uc30gZnJvbSAnLi4vYW90L2NvbXBpbGVyX29wdGlvbnMnO1xuaW1wb3J0IHtTdGF0aWNSZWZsZWN0b3J9IGZyb20gJy4uL2FvdC9zdGF0aWNfcmVmbGVjdG9yJztcbmltcG9ydCB7U3RhdGljU3ltYm9sfSBmcm9tICcuLi9hb3Qvc3RhdGljX3N5bWJvbCc7XG5pbXBvcnQge0NvbXBpbGVEaXJlY3RpdmVNZXRhZGF0YSwgQ29tcGlsZVBpcGVTdW1tYXJ5fSBmcm9tICcuLi9jb21waWxlX21ldGFkYXRhJztcbmltcG9ydCB7QmluZGluZ0Zvcm0sIGNvbnZlcnRBY3Rpb25CaW5kaW5nLCBjb252ZXJ0UHJvcGVydHlCaW5kaW5nLCBjb252ZXJ0UHJvcGVydHlCaW5kaW5nQnVpbHRpbnMsIEV2ZW50SGFuZGxlclZhcnMsIExvY2FsUmVzb2x2ZXJ9IGZyb20gJy4uL2NvbXBpbGVyX3V0aWwvZXhwcmVzc2lvbl9jb252ZXJ0ZXInO1xuaW1wb3J0IHtBU1QsIEFTVFdpdGhTb3VyY2UsIEludGVycG9sYXRpb259IGZyb20gJy4uL2V4cHJlc3Npb25fcGFyc2VyL2FzdCc7XG5pbXBvcnQgKiBhcyBvIGZyb20gJy4uL291dHB1dC9vdXRwdXRfYXN0JztcbmltcG9ydCB7UGFyc2VTb3VyY2VTcGFufSBmcm9tICcuLi9wYXJzZV91dGlsJztcbmltcG9ydCB7QXR0ckFzdCwgQm91bmREaXJlY3RpdmVQcm9wZXJ0eUFzdCwgQm91bmRFbGVtZW50UHJvcGVydHlBc3QsIEJvdW5kRXZlbnRBc3QsIEJvdW5kVGV4dEFzdCwgRGlyZWN0aXZlQXN0LCBFbGVtZW50QXN0LCBFbWJlZGRlZFRlbXBsYXRlQXN0LCBOZ0NvbnRlbnRBc3QsIFJlZmVyZW5jZUFzdCwgVGVtcGxhdGVBc3QsIFRlbXBsYXRlQXN0VmlzaXRvciwgdGVtcGxhdGVWaXNpdEFsbCwgVGV4dEFzdCwgVmFyaWFibGVBc3R9IGZyb20gJy4uL3RlbXBsYXRlX3BhcnNlci90ZW1wbGF0ZV9hc3QnO1xuaW1wb3J0IHtPdXRwdXRDb250ZXh0fSBmcm9tICcuLi91dGlsJztcblxuXG4vKipcbiAqIEdlbmVyYXRlcyBjb2RlIHRoYXQgaXMgdXNlZCB0byB0eXBlIGNoZWNrIHRlbXBsYXRlcy5cbiAqL1xuZXhwb3J0IGNsYXNzIFR5cGVDaGVja0NvbXBpbGVyIHtcbiAgY29uc3RydWN0b3IocHJpdmF0ZSBvcHRpb25zOiBBb3RDb21waWxlck9wdGlvbnMsIHByaXZhdGUgcmVmbGVjdG9yOiBTdGF0aWNSZWZsZWN0b3IpIHt9XG5cbiAgLyoqXG4gICAqIEltcG9ydGFudCBub3RlczpcbiAgICogLSBUaGlzIG11c3Qgbm90IHByb2R1Y2UgbmV3IGBpbXBvcnRgIHN0YXRlbWVudHMsIGJ1dCBvbmx5IHJlZmVyIHRvIHR5cGVzIG91dHNpZGVcbiAgICogICBvZiB0aGUgZmlsZSB2aWEgdGhlIHZhcmlhYmxlcyBwcm92aWRlZCB2aWEgZXh0ZXJuYWxSZWZlcmVuY2VWYXJzLlxuICAgKiAgIFRoaXMgYWxsb3dzIFR5cGVzY3JpcHQgdG8gcmV1c2UgdGhlIG9sZCBwcm9ncmFtJ3Mgc3RydWN0dXJlIGFzIG5vIGltcG9ydHMgaGF2ZSBjaGFuZ2VkLlxuICAgKiAtIFRoaXMgbXVzdCBub3QgcHJvZHVjZSBhbnkgZXhwb3J0cywgYXMgdGhpcyB3b3VsZCBwb2xsdXRlIHRoZSAuZC50cyBmaWxlXG4gICAqICAgYW5kIGFsc28gdmlvbGF0ZSB0aGUgcG9pbnQgYWJvdmUuXG4gICAqL1xuICBjb21waWxlQ29tcG9uZW50KFxuICAgICAgY29tcG9uZW50SWQ6IHN0cmluZywgY29tcG9uZW50OiBDb21waWxlRGlyZWN0aXZlTWV0YWRhdGEsIHRlbXBsYXRlOiBUZW1wbGF0ZUFzdFtdLFxuICAgICAgdXNlZFBpcGVzOiBDb21waWxlUGlwZVN1bW1hcnlbXSwgZXh0ZXJuYWxSZWZlcmVuY2VWYXJzOiBNYXA8U3RhdGljU3ltYm9sLCBzdHJpbmc+LFxuICAgICAgY3R4OiBPdXRwdXRDb250ZXh0KTogby5TdGF0ZW1lbnRbXSB7XG4gICAgY29uc3QgcGlwZXMgPSBuZXcgTWFwPHN0cmluZywgU3RhdGljU3ltYm9sPigpO1xuICAgIHVzZWRQaXBlcy5mb3JFYWNoKHAgPT4gcGlwZXMuc2V0KHAubmFtZSwgcC50eXBlLnJlZmVyZW5jZSkpO1xuICAgIGxldCBlbWJlZGRlZFZpZXdDb3VudCA9IDA7XG4gICAgY29uc3Qgdmlld0J1aWxkZXJGYWN0b3J5ID1cbiAgICAgICAgKHBhcmVudDogVmlld0J1aWxkZXJ8bnVsbCwgZ3VhcmRzOiBHdWFyZEV4cHJlc3Npb25bXSk6IFZpZXdCdWlsZGVyID0+IHtcbiAgICAgICAgICBjb25zdCBlbWJlZGRlZFZpZXdJbmRleCA9IGVtYmVkZGVkVmlld0NvdW50Kys7XG4gICAgICAgICAgcmV0dXJuIG5ldyBWaWV3QnVpbGRlcihcbiAgICAgICAgICAgICAgdGhpcy5vcHRpb25zLCB0aGlzLnJlZmxlY3RvciwgZXh0ZXJuYWxSZWZlcmVuY2VWYXJzLCBwYXJlbnQsIGNvbXBvbmVudC50eXBlLnJlZmVyZW5jZSxcbiAgICAgICAgICAgICAgY29tcG9uZW50LmlzSG9zdCwgZW1iZWRkZWRWaWV3SW5kZXgsIHBpcGVzLCBndWFyZHMsIGN0eCwgdmlld0J1aWxkZXJGYWN0b3J5KTtcbiAgICAgICAgfTtcblxuICAgIGNvbnN0IHZpc2l0b3IgPSB2aWV3QnVpbGRlckZhY3RvcnkobnVsbCwgW10pO1xuICAgIHZpc2l0b3IudmlzaXRBbGwoW10sIHRlbXBsYXRlKTtcblxuICAgIHJldHVybiB2aXNpdG9yLmJ1aWxkKGNvbXBvbmVudElkKTtcbiAgfVxufVxuXG5pbnRlcmZhY2UgR3VhcmRFeHByZXNzaW9uIHtcbiAgZ3VhcmQ6IFN0YXRpY1N5bWJvbDtcbiAgdXNlSWY6IGJvb2xlYW47XG4gIGV4cHJlc3Npb246IEV4cHJlc3Npb247XG59XG5cbmludGVyZmFjZSBWaWV3QnVpbGRlckZhY3Rvcnkge1xuICAocGFyZW50OiBWaWV3QnVpbGRlciwgZ3VhcmRzOiBHdWFyZEV4cHJlc3Npb25bXSk6IFZpZXdCdWlsZGVyO1xufVxuXG4vLyBOb3RlOiBUaGlzIGlzIHVzZWQgYXMga2V5IGluIE1hcCBhbmQgc2hvdWxkIHRoZXJlZm9yZSBiZVxuLy8gdW5pcXVlIHBlciB2YWx1ZS5cbnR5cGUgT3V0cHV0VmFyVHlwZSA9IG8uQnVpbHRpblR5cGVOYW1lfFN0YXRpY1N5bWJvbDtcblxuaW50ZXJmYWNlIEV4cHJlc3Npb24ge1xuICBjb250ZXh0OiBPdXRwdXRWYXJUeXBlO1xuICBzb3VyY2VTcGFuOiBQYXJzZVNvdXJjZVNwYW47XG4gIHZhbHVlOiBBU1Q7XG59XG5cbmNvbnN0IERZTkFNSUNfVkFSX05BTUUgPSAnX2FueSc7XG5cbmNsYXNzIFR5cGVDaGVja0xvY2FsUmVzb2x2ZXIgaW1wbGVtZW50cyBMb2NhbFJlc29sdmVyIHtcbiAgbm90aWZ5SW1wbGljaXRSZWNlaXZlclVzZSgpOiB2b2lkIHt9XG4gIGdldExvY2FsKG5hbWU6IHN0cmluZyk6IG8uRXhwcmVzc2lvbnxudWxsIHtcbiAgICBpZiAobmFtZSA9PT0gRXZlbnRIYW5kbGVyVmFycy5ldmVudC5uYW1lKSB7XG4gICAgICAvLyBSZWZlcmVuY2VzIHRvIHRoZSBldmVudCBzaG91bGQgbm90IGJlIHR5cGUtY2hlY2tlZC5cbiAgICAgIC8vIFRPRE8oY2h1Y2tqKTogZGV0ZXJtaW5lIGEgYmV0dGVyIHR5cGUgZm9yIHRoZSBldmVudC5cbiAgICAgIHJldHVybiBvLnZhcmlhYmxlKERZTkFNSUNfVkFSX05BTUUpO1xuICAgIH1cbiAgICByZXR1cm4gbnVsbDtcbiAgfVxufVxuXG5jb25zdCBkZWZhdWx0UmVzb2x2ZXIgPSBuZXcgVHlwZUNoZWNrTG9jYWxSZXNvbHZlcigpO1xuXG5jbGFzcyBWaWV3QnVpbGRlciBpbXBsZW1lbnRzIFRlbXBsYXRlQXN0VmlzaXRvciwgTG9jYWxSZXNvbHZlciB7XG4gIHByaXZhdGUgcmVmT3V0cHV0VmFycyA9IG5ldyBNYXA8c3RyaW5nLCBPdXRwdXRWYXJUeXBlPigpO1xuICBwcml2YXRlIHZhcmlhYmxlczogVmFyaWFibGVBc3RbXSA9IFtdO1xuICBwcml2YXRlIGNoaWxkcmVuOiBWaWV3QnVpbGRlcltdID0gW107XG4gIHByaXZhdGUgdXBkYXRlczogRXhwcmVzc2lvbltdID0gW107XG4gIHByaXZhdGUgYWN0aW9uczogRXhwcmVzc2lvbltdID0gW107XG5cbiAgY29uc3RydWN0b3IoXG4gICAgICBwcml2YXRlIG9wdGlvbnM6IEFvdENvbXBpbGVyT3B0aW9ucywgcHJpdmF0ZSByZWZsZWN0b3I6IFN0YXRpY1JlZmxlY3RvcixcbiAgICAgIHByaXZhdGUgZXh0ZXJuYWxSZWZlcmVuY2VWYXJzOiBNYXA8U3RhdGljU3ltYm9sLCBzdHJpbmc+LCBwcml2YXRlIHBhcmVudDogVmlld0J1aWxkZXJ8bnVsbCxcbiAgICAgIHByaXZhdGUgY29tcG9uZW50OiBTdGF0aWNTeW1ib2wsIHByaXZhdGUgaXNIb3N0Q29tcG9uZW50OiBib29sZWFuLFxuICAgICAgcHJpdmF0ZSBlbWJlZGRlZFZpZXdJbmRleDogbnVtYmVyLCBwcml2YXRlIHBpcGVzOiBNYXA8c3RyaW5nLCBTdGF0aWNTeW1ib2w+LFxuICAgICAgcHJpdmF0ZSBndWFyZHM6IEd1YXJkRXhwcmVzc2lvbltdLCBwcml2YXRlIGN0eDogT3V0cHV0Q29udGV4dCxcbiAgICAgIHByaXZhdGUgdmlld0J1aWxkZXJGYWN0b3J5OiBWaWV3QnVpbGRlckZhY3RvcnkpIHt9XG5cbiAgcHJpdmF0ZSBnZXRPdXRwdXRWYXIodHlwZTogby5CdWlsdGluVHlwZU5hbWV8U3RhdGljU3ltYm9sKTogc3RyaW5nIHtcbiAgICBsZXQgdmFyTmFtZTogc3RyaW5nfHVuZGVmaW5lZDtcbiAgICBpZiAodHlwZSA9PT0gdGhpcy5jb21wb25lbnQgJiYgdGhpcy5pc0hvc3RDb21wb25lbnQpIHtcbiAgICAgIHZhck5hbWUgPSBEWU5BTUlDX1ZBUl9OQU1FO1xuICAgIH0gZWxzZSBpZiAodHlwZSBpbnN0YW5jZW9mIFN0YXRpY1N5bWJvbCkge1xuICAgICAgdmFyTmFtZSA9IHRoaXMuZXh0ZXJuYWxSZWZlcmVuY2VWYXJzLmdldCh0eXBlKTtcbiAgICB9IGVsc2Uge1xuICAgICAgdmFyTmFtZSA9IERZTkFNSUNfVkFSX05BTUU7XG4gICAgfVxuICAgIGlmICghdmFyTmFtZSkge1xuICAgICAgdGhyb3cgbmV3IEVycm9yKFxuICAgICAgICAgIGBJbGxlZ2FsIFN0YXRlOiByZWZlcnJpbmcgdG8gYSB0eXBlIHdpdGhvdXQgYSB2YXJpYWJsZSAke0pTT04uc3RyaW5naWZ5KHR5cGUpfWApO1xuICAgIH1cbiAgICByZXR1cm4gdmFyTmFtZTtcbiAgfVxuXG4gIHByaXZhdGUgZ2V0VHlwZUd1YXJkRXhwcmVzc2lvbnMoYXN0OiBFbWJlZGRlZFRlbXBsYXRlQXN0KTogR3VhcmRFeHByZXNzaW9uW10ge1xuICAgIGNvbnN0IHJlc3VsdCA9IFsuLi50aGlzLmd1YXJkc107XG4gICAgZm9yIChsZXQgZGlyZWN0aXZlIG9mIGFzdC5kaXJlY3RpdmVzKSB7XG4gICAgICBmb3IgKGxldCBpbnB1dCBvZiBkaXJlY3RpdmUuaW5wdXRzKSB7XG4gICAgICAgIGNvbnN0IGd1YXJkID0gZGlyZWN0aXZlLmRpcmVjdGl2ZS5ndWFyZHNbaW5wdXQuZGlyZWN0aXZlTmFtZV07XG4gICAgICAgIGlmIChndWFyZCkge1xuICAgICAgICAgIGNvbnN0IHVzZUlmID0gZ3VhcmQgPT09ICdVc2VJZic7XG4gICAgICAgICAgcmVzdWx0LnB1c2goe1xuICAgICAgICAgICAgZ3VhcmQsXG4gICAgICAgICAgICB1c2VJZixcbiAgICAgICAgICAgIGV4cHJlc3Npb246IHtcbiAgICAgICAgICAgICAgY29udGV4dDogdGhpcy5jb21wb25lbnQsXG4gICAgICAgICAgICAgIHZhbHVlOiBpbnB1dC52YWx1ZSxcbiAgICAgICAgICAgICAgc291cmNlU3BhbjogaW5wdXQuc291cmNlU3BhbixcbiAgICAgICAgICAgIH0sXG4gICAgICAgICAgfSk7XG4gICAgICAgIH1cbiAgICAgIH1cbiAgICB9XG4gICAgcmV0dXJuIHJlc3VsdDtcbiAgfVxuXG4gIHZpc2l0QWxsKHZhcmlhYmxlczogVmFyaWFibGVBc3RbXSwgYXN0Tm9kZXM6IFRlbXBsYXRlQXN0W10pIHtcbiAgICB0aGlzLnZhcmlhYmxlcyA9IHZhcmlhYmxlcztcbiAgICB0ZW1wbGF0ZVZpc2l0QWxsKHRoaXMsIGFzdE5vZGVzKTtcbiAgfVxuXG4gIGJ1aWxkKGNvbXBvbmVudElkOiBzdHJpbmcsIHRhcmdldFN0YXRlbWVudHM6IG8uU3RhdGVtZW50W10gPSBbXSk6IG8uU3RhdGVtZW50W10ge1xuICAgIHRoaXMuY2hpbGRyZW4uZm9yRWFjaCgoY2hpbGQpID0+IGNoaWxkLmJ1aWxkKGNvbXBvbmVudElkLCB0YXJnZXRTdGF0ZW1lbnRzKSk7XG4gICAgbGV0IHZpZXdTdG10czogby5TdGF0ZW1lbnRbXSA9XG4gICAgICAgIFtvLnZhcmlhYmxlKERZTkFNSUNfVkFSX05BTUUpLnNldChvLk5VTExfRVhQUikudG9EZWNsU3RtdChvLkRZTkFNSUNfVFlQRSldO1xuICAgIGxldCBiaW5kaW5nQ291bnQgPSAwO1xuICAgIHRoaXMudXBkYXRlcy5mb3JFYWNoKChleHByZXNzaW9uKSA9PiB7XG4gICAgICBjb25zdCB7c291cmNlU3BhbiwgY29udGV4dCwgdmFsdWV9ID0gdGhpcy5wcmVwcm9jZXNzVXBkYXRlRXhwcmVzc2lvbihleHByZXNzaW9uKTtcbiAgICAgIGNvbnN0IGJpbmRpbmdJZCA9IGAke2JpbmRpbmdDb3VudCsrfWA7XG4gICAgICBjb25zdCBuYW1lUmVzb2x2ZXIgPSBjb250ZXh0ID09PSB0aGlzLmNvbXBvbmVudCA/IHRoaXMgOiBkZWZhdWx0UmVzb2x2ZXI7XG4gICAgICBjb25zdCB7c3RtdHMsIGN1cnJWYWxFeHByfSA9IGNvbnZlcnRQcm9wZXJ0eUJpbmRpbmcoXG4gICAgICAgICAgbmFtZVJlc29sdmVyLCBvLnZhcmlhYmxlKHRoaXMuZ2V0T3V0cHV0VmFyKGNvbnRleHQpKSwgdmFsdWUsIGJpbmRpbmdJZCxcbiAgICAgICAgICBCaW5kaW5nRm9ybS5HZW5lcmFsKTtcbiAgICAgIHN0bXRzLnB1c2gobmV3IG8uRXhwcmVzc2lvblN0YXRlbWVudChjdXJyVmFsRXhwcikpO1xuICAgICAgdmlld1N0bXRzLnB1c2goLi4uc3RtdHMubWFwKFxuICAgICAgICAgIChzdG10OiBvLlN0YXRlbWVudCkgPT4gby5hcHBseVNvdXJjZVNwYW5Ub1N0YXRlbWVudElmTmVlZGVkKHN0bXQsIHNvdXJjZVNwYW4pKSk7XG4gICAgfSk7XG5cbiAgICB0aGlzLmFjdGlvbnMuZm9yRWFjaCgoe3NvdXJjZVNwYW4sIGNvbnRleHQsIHZhbHVlfSkgPT4ge1xuICAgICAgY29uc3QgYmluZGluZ0lkID0gYCR7YmluZGluZ0NvdW50Kyt9YDtcbiAgICAgIGNvbnN0IG5hbWVSZXNvbHZlciA9IGNvbnRleHQgPT09IHRoaXMuY29tcG9uZW50ID8gdGhpcyA6IGRlZmF1bHRSZXNvbHZlcjtcbiAgICAgIGNvbnN0IHtzdG10c30gPSBjb252ZXJ0QWN0aW9uQmluZGluZyhcbiAgICAgICAgICBuYW1lUmVzb2x2ZXIsIG8udmFyaWFibGUodGhpcy5nZXRPdXRwdXRWYXIoY29udGV4dCkpLCB2YWx1ZSwgYmluZGluZ0lkKTtcbiAgICAgIHZpZXdTdG10cy5wdXNoKC4uLnN0bXRzLm1hcChcbiAgICAgICAgICAoc3RtdDogby5TdGF0ZW1lbnQpID0+IG8uYXBwbHlTb3VyY2VTcGFuVG9TdGF0ZW1lbnRJZk5lZWRlZChzdG10LCBzb3VyY2VTcGFuKSkpO1xuICAgIH0pO1xuXG4gICAgaWYgKHRoaXMuZ3VhcmRzLmxlbmd0aCkge1xuICAgICAgbGV0IGd1YXJkRXhwcmVzc2lvbjogby5FeHByZXNzaW9ufHVuZGVmaW5lZCA9IHVuZGVmaW5lZDtcbiAgICAgIGZvciAoY29uc3QgZ3VhcmQgb2YgdGhpcy5ndWFyZHMpIHtcbiAgICAgICAgY29uc3Qge2NvbnRleHQsIHZhbHVlfSA9IHRoaXMucHJlcHJvY2Vzc1VwZGF0ZUV4cHJlc3Npb24oZ3VhcmQuZXhwcmVzc2lvbik7XG4gICAgICAgIGNvbnN0IGJpbmRpbmdJZCA9IGAke2JpbmRpbmdDb3VudCsrfWA7XG4gICAgICAgIGNvbnN0IG5hbWVSZXNvbHZlciA9IGNvbnRleHQgPT09IHRoaXMuY29tcG9uZW50ID8gdGhpcyA6IGRlZmF1bHRSZXNvbHZlcjtcbiAgICAgICAgLy8gV2Ugb25seSBzdXBwb3J0IHN1cHBvcnQgc2ltcGxlIGV4cHJlc3Npb25zIGFuZCBpZ25vcmUgb3RoZXJzIGFzIHRoZXlcbiAgICAgICAgLy8gYXJlIHVubGlrZWx5IHRvIGFmZmVjdCB0eXBlIG5hcnJvd2luZy5cbiAgICAgICAgY29uc3Qge3N0bXRzLCBjdXJyVmFsRXhwcn0gPSBjb252ZXJ0UHJvcGVydHlCaW5kaW5nKFxuICAgICAgICAgICAgbmFtZVJlc29sdmVyLCBvLnZhcmlhYmxlKHRoaXMuZ2V0T3V0cHV0VmFyKGNvbnRleHQpKSwgdmFsdWUsIGJpbmRpbmdJZCxcbiAgICAgICAgICAgIEJpbmRpbmdGb3JtLlRyeVNpbXBsZSk7XG4gICAgICAgIGlmIChzdG10cy5sZW5ndGggPT0gMCkge1xuICAgICAgICAgIGNvbnN0IGd1YXJkQ2xhdXNlID1cbiAgICAgICAgICAgICAgZ3VhcmQudXNlSWYgPyBjdXJyVmFsRXhwciA6IHRoaXMuY3R4LmltcG9ydEV4cHIoZ3VhcmQuZ3VhcmQpLmNhbGxGbihbY3VyclZhbEV4cHJdKTtcbiAgICAgICAgICBndWFyZEV4cHJlc3Npb24gPSBndWFyZEV4cHJlc3Npb24gPyBndWFyZEV4cHJlc3Npb24uYW5kKGd1YXJkQ2xhdXNlKSA6IGd1YXJkQ2xhdXNlO1xuICAgICAgICB9XG4gICAgICB9XG4gICAgICBpZiAoZ3VhcmRFeHByZXNzaW9uKSB7XG4gICAgICAgIHZpZXdTdG10cyA9IFtuZXcgby5JZlN0bXQoZ3VhcmRFeHByZXNzaW9uLCB2aWV3U3RtdHMpXTtcbiAgICAgIH1cbiAgICB9XG5cbiAgICBjb25zdCB2aWV3TmFtZSA9IGBfVmlld18ke2NvbXBvbmVudElkfV8ke3RoaXMuZW1iZWRkZWRWaWV3SW5kZXh9YDtcbiAgICBjb25zdCB2aWV3RmFjdG9yeSA9IG5ldyBvLkRlY2xhcmVGdW5jdGlvblN0bXQodmlld05hbWUsIFtdLCB2aWV3U3RtdHMpO1xuICAgIHRhcmdldFN0YXRlbWVudHMucHVzaCh2aWV3RmFjdG9yeSk7XG4gICAgcmV0dXJuIHRhcmdldFN0YXRlbWVudHM7XG4gIH1cblxuICB2aXNpdEJvdW5kVGV4dChhc3Q6IEJvdW5kVGV4dEFzdCwgY29udGV4dDogYW55KTogYW55IHtcbiAgICBjb25zdCBhc3RXaXRoU291cmNlID0gPEFTVFdpdGhTb3VyY2U+YXN0LnZhbHVlO1xuICAgIGNvbnN0IGludGVyID0gPEludGVycG9sYXRpb24+YXN0V2l0aFNvdXJjZS5hc3Q7XG5cbiAgICBpbnRlci5leHByZXNzaW9ucy5mb3JFYWNoKFxuICAgICAgICAoZXhwcikgPT5cbiAgICAgICAgICAgIHRoaXMudXBkYXRlcy5wdXNoKHtjb250ZXh0OiB0aGlzLmNvbXBvbmVudCwgdmFsdWU6IGV4cHIsIHNvdXJjZVNwYW46IGFzdC5zb3VyY2VTcGFufSkpO1xuICB9XG5cbiAgdmlzaXRFbWJlZGRlZFRlbXBsYXRlKGFzdDogRW1iZWRkZWRUZW1wbGF0ZUFzdCwgY29udGV4dDogYW55KTogYW55IHtcbiAgICB0aGlzLnZpc2l0RWxlbWVudE9yVGVtcGxhdGUoYXN0KTtcbiAgICAvLyBOb3RlOiBUaGUgb2xkIHZpZXcgY29tcGlsZXIgdXNlZCB0byB1c2UgYW4gYGFueWAgdHlwZVxuICAgIC8vIGZvciB0aGUgY29udGV4dCBpbiBhbnkgZW1iZWRkZWQgdmlldy5cbiAgICAvLyBXZSBrZWVwIHRoaXMgYmVoYWl2b3IgYmVoaW5kIGEgZmxhZyBmb3Igbm93LlxuICAgIGlmICh0aGlzLm9wdGlvbnMuZnVsbFRlbXBsYXRlVHlwZUNoZWNrKSB7XG4gICAgICAvLyBGaW5kIGFueSBhcHBsaWNhYmxlIHR5cGUgZ3VhcmRzLiBGb3IgZXhhbXBsZSwgTmdJZiBoYXMgYSB0eXBlIGd1YXJkIG9uIG5nSWZcbiAgICAgIC8vIChzZWUgTmdJZi5uZ0lmVHlwZUd1YXJkKSB0aGF0IGNhbiBiZSB1c2VkIHRvIGluZGljYXRlIHRoYXQgYSB0ZW1wbGF0ZSBpcyBvbmx5XG4gICAgICAvLyBzdGFtcGVkIG91dCBpZiBuZ0lmIGlzIHRydXRoeSBzbyBhbnkgYmluZGluZ3MgaW4gdGhlIHRlbXBsYXRlIGNhbiBhc3N1bWUgdGhhdCxcbiAgICAgIC8vIGlmIGEgbnVsbGFibGUgdHlwZSBpcyB1c2VkIGZvciBuZ0lmLCB0aGF0IGV4cHJlc3Npb24gaXMgbm90IG51bGwgb3IgdW5kZWZpbmVkLlxuICAgICAgY29uc3QgZ3VhcmRzID0gdGhpcy5nZXRUeXBlR3VhcmRFeHByZXNzaW9ucyhhc3QpO1xuICAgICAgY29uc3QgY2hpbGRWaXNpdG9yID0gdGhpcy52aWV3QnVpbGRlckZhY3RvcnkodGhpcywgZ3VhcmRzKTtcbiAgICAgIHRoaXMuY2hpbGRyZW4ucHVzaChjaGlsZFZpc2l0b3IpO1xuICAgICAgY2hpbGRWaXNpdG9yLnZpc2l0QWxsKGFzdC52YXJpYWJsZXMsIGFzdC5jaGlsZHJlbik7XG4gICAgfVxuICB9XG5cbiAgdmlzaXRFbGVtZW50KGFzdDogRWxlbWVudEFzdCwgY29udGV4dDogYW55KTogYW55IHtcbiAgICB0aGlzLnZpc2l0RWxlbWVudE9yVGVtcGxhdGUoYXN0KTtcblxuICAgIGxldCBpbnB1dERlZnM6IG8uRXhwcmVzc2lvbltdID0gW107XG4gICAgbGV0IHVwZGF0ZVJlbmRlcmVyRXhwcmVzc2lvbnM6IEV4cHJlc3Npb25bXSA9IFtdO1xuICAgIGxldCBvdXRwdXREZWZzOiBvLkV4cHJlc3Npb25bXSA9IFtdO1xuICAgIGFzdC5pbnB1dHMuZm9yRWFjaCgoaW5wdXRBc3QpID0+IHtcbiAgICAgIHRoaXMudXBkYXRlcy5wdXNoKFxuICAgICAgICAgIHtjb250ZXh0OiB0aGlzLmNvbXBvbmVudCwgdmFsdWU6IGlucHV0QXN0LnZhbHVlLCBzb3VyY2VTcGFuOiBpbnB1dEFzdC5zb3VyY2VTcGFufSk7XG4gICAgfSk7XG5cbiAgICB0ZW1wbGF0ZVZpc2l0QWxsKHRoaXMsIGFzdC5jaGlsZHJlbik7XG4gIH1cblxuICBwcml2YXRlIHZpc2l0RWxlbWVudE9yVGVtcGxhdGUoYXN0OiB7XG4gICAgb3V0cHV0czogQm91bmRFdmVudEFzdFtdLFxuICAgIGRpcmVjdGl2ZXM6IERpcmVjdGl2ZUFzdFtdLFxuICAgIHJlZmVyZW5jZXM6IFJlZmVyZW5jZUFzdFtdLFxuICB9KSB7XG4gICAgYXN0LmRpcmVjdGl2ZXMuZm9yRWFjaCgoZGlyQXN0KSA9PiB7XG4gICAgICB0aGlzLnZpc2l0RGlyZWN0aXZlKGRpckFzdCk7XG4gICAgfSk7XG5cbiAgICBhc3QucmVmZXJlbmNlcy5mb3JFYWNoKChyZWYpID0+IHtcbiAgICAgIGxldCBvdXRwdXRWYXJUeXBlOiBPdXRwdXRWYXJUeXBlID0gbnVsbCE7XG4gICAgICAvLyBOb3RlOiBUaGUgb2xkIHZpZXcgY29tcGlsZXIgdXNlZCB0byB1c2UgYW4gYGFueWAgdHlwZVxuICAgICAgLy8gZm9yIGRpcmVjdGl2ZXMgZXhwb3NlZCB2aWEgYGV4cG9ydEFzYC5cbiAgICAgIC8vIFdlIGtlZXAgdGhpcyBiZWhhaXZvciBiZWhpbmQgYSBmbGFnIGZvciBub3cuXG4gICAgICBpZiAocmVmLnZhbHVlICYmIHJlZi52YWx1ZS5pZGVudGlmaWVyICYmIHRoaXMub3B0aW9ucy5mdWxsVGVtcGxhdGVUeXBlQ2hlY2spIHtcbiAgICAgICAgb3V0cHV0VmFyVHlwZSA9IHJlZi52YWx1ZS5pZGVudGlmaWVyLnJlZmVyZW5jZTtcbiAgICAgIH0gZWxzZSB7XG4gICAgICAgIG91dHB1dFZhclR5cGUgPSBvLkJ1aWx0aW5UeXBlTmFtZS5EeW5hbWljO1xuICAgICAgfVxuICAgICAgdGhpcy5yZWZPdXRwdXRWYXJzLnNldChyZWYubmFtZSwgb3V0cHV0VmFyVHlwZSk7XG4gICAgfSk7XG4gICAgYXN0Lm91dHB1dHMuZm9yRWFjaCgob3V0cHV0QXN0KSA9PiB7XG4gICAgICB0aGlzLmFjdGlvbnMucHVzaChcbiAgICAgICAgICB7Y29udGV4dDogdGhpcy5jb21wb25lbnQsIHZhbHVlOiBvdXRwdXRBc3QuaGFuZGxlciwgc291cmNlU3Bhbjogb3V0cHV0QXN0LnNvdXJjZVNwYW59KTtcbiAgICB9KTtcbiAgfVxuXG4gIHZpc2l0RGlyZWN0aXZlKGRpckFzdDogRGlyZWN0aXZlQXN0KSB7XG4gICAgY29uc3QgZGlyVHlwZSA9IGRpckFzdC5kaXJlY3RpdmUudHlwZS5yZWZlcmVuY2U7XG4gICAgZGlyQXN0LmlucHV0cy5mb3JFYWNoKFxuICAgICAgICAoaW5wdXQpID0+IHRoaXMudXBkYXRlcy5wdXNoKFxuICAgICAgICAgICAge2NvbnRleHQ6IHRoaXMuY29tcG9uZW50LCB2YWx1ZTogaW5wdXQudmFsdWUsIHNvdXJjZVNwYW46IGlucHV0LnNvdXJjZVNwYW59KSk7XG4gICAgLy8gTm90ZTogVGhlIG9sZCB2aWV3IGNvbXBpbGVyIHVzZWQgdG8gdXNlIGFuIGBhbnlgIHR5cGVcbiAgICAvLyBmb3IgZXhwcmVzc2lvbnMgaW4gaG9zdCBwcm9wZXJ0aWVzIC8gZXZlbnRzLlxuICAgIC8vIFdlIGtlZXAgdGhpcyBiZWhhaXZvciBiZWhpbmQgYSBmbGFnIGZvciBub3cuXG4gICAgaWYgKHRoaXMub3B0aW9ucy5mdWxsVGVtcGxhdGVUeXBlQ2hlY2spIHtcbiAgICAgIGRpckFzdC5ob3N0UHJvcGVydGllcy5mb3JFYWNoKFxuICAgICAgICAgIChpbnB1dEFzdCkgPT4gdGhpcy51cGRhdGVzLnB1c2goXG4gICAgICAgICAgICAgIHtjb250ZXh0OiBkaXJUeXBlLCB2YWx1ZTogaW5wdXRBc3QudmFsdWUsIHNvdXJjZVNwYW46IGlucHV0QXN0LnNvdXJjZVNwYW59KSk7XG4gICAgICBkaXJBc3QuaG9zdEV2ZW50cy5mb3JFYWNoKChob3N0RXZlbnRBc3QpID0+IHRoaXMuYWN0aW9ucy5wdXNoKHtcbiAgICAgICAgY29udGV4dDogZGlyVHlwZSxcbiAgICAgICAgdmFsdWU6IGhvc3RFdmVudEFzdC5oYW5kbGVyLFxuICAgICAgICBzb3VyY2VTcGFuOiBob3N0RXZlbnRBc3Quc291cmNlU3BhblxuICAgICAgfSkpO1xuICAgIH1cbiAgfVxuXG4gIG5vdGlmeUltcGxpY2l0UmVjZWl2ZXJVc2UoKTogdm9pZCB7fVxuICBnZXRMb2NhbChuYW1lOiBzdHJpbmcpOiBvLkV4cHJlc3Npb258bnVsbCB7XG4gICAgaWYgKG5hbWUgPT0gRXZlbnRIYW5kbGVyVmFycy5ldmVudC5uYW1lKSB7XG4gICAgICByZXR1cm4gby52YXJpYWJsZSh0aGlzLmdldE91dHB1dFZhcihvLkJ1aWx0aW5UeXBlTmFtZS5EeW5hbWljKSk7XG4gICAgfVxuICAgIGZvciAobGV0IGN1cnJCdWlsZGVyOiBWaWV3QnVpbGRlcnxudWxsID0gdGhpczsgY3VyckJ1aWxkZXI7IGN1cnJCdWlsZGVyID0gY3VyckJ1aWxkZXIucGFyZW50KSB7XG4gICAgICBsZXQgb3V0cHV0VmFyVHlwZTogT3V0cHV0VmFyVHlwZXx1bmRlZmluZWQ7XG4gICAgICAvLyBjaGVjayByZWZlcmVuY2VzXG4gICAgICBvdXRwdXRWYXJUeXBlID0gY3VyckJ1aWxkZXIucmVmT3V0cHV0VmFycy5nZXQobmFtZSk7XG4gICAgICBpZiAob3V0cHV0VmFyVHlwZSA9PSBudWxsKSB7XG4gICAgICAgIC8vIGNoZWNrIHZhcmlhYmxlc1xuICAgICAgICBjb25zdCB2YXJBc3QgPSBjdXJyQnVpbGRlci52YXJpYWJsZXMuZmluZCgodmFyQXN0KSA9PiB2YXJBc3QubmFtZSA9PT0gbmFtZSk7XG4gICAgICAgIGlmICh2YXJBc3QpIHtcbiAgICAgICAgICBvdXRwdXRWYXJUeXBlID0gby5CdWlsdGluVHlwZU5hbWUuRHluYW1pYztcbiAgICAgICAgfVxuICAgICAgfVxuICAgICAgaWYgKG91dHB1dFZhclR5cGUgIT0gbnVsbCkge1xuICAgICAgICByZXR1cm4gby52YXJpYWJsZSh0aGlzLmdldE91dHB1dFZhcihvdXRwdXRWYXJUeXBlKSk7XG4gICAgICB9XG4gICAgfVxuICAgIHJldHVybiBudWxsO1xuICB9XG5cbiAgcHJpdmF0ZSBwaXBlT3V0cHV0VmFyKG5hbWU6IHN0cmluZyk6IHN0cmluZyB7XG4gICAgY29uc3QgcGlwZSA9IHRoaXMucGlwZXMuZ2V0KG5hbWUpO1xuICAgIGlmICghcGlwZSkge1xuICAgICAgdGhyb3cgbmV3IEVycm9yKFxuICAgICAgICAgIGBJbGxlZ2FsIFN0YXRlOiBDb3VsZCBub3QgZmluZCBwaXBlICR7bmFtZX0gaW4gdGVtcGxhdGUgb2YgJHt0aGlzLmNvbXBvbmVudH1gKTtcbiAgICB9XG4gICAgcmV0dXJuIHRoaXMuZ2V0T3V0cHV0VmFyKHBpcGUpO1xuICB9XG5cbiAgcHJpdmF0ZSBwcmVwcm9jZXNzVXBkYXRlRXhwcmVzc2lvbihleHByZXNzaW9uOiBFeHByZXNzaW9uKTogRXhwcmVzc2lvbiB7XG4gICAgcmV0dXJuIHtcbiAgICAgIHNvdXJjZVNwYW46IGV4cHJlc3Npb24uc291cmNlU3BhbixcbiAgICAgIGNvbnRleHQ6IGV4cHJlc3Npb24uY29udGV4dCxcbiAgICAgIHZhbHVlOiBjb252ZXJ0UHJvcGVydHlCaW5kaW5nQnVpbHRpbnMoXG4gICAgICAgICAge1xuICAgICAgICAgICAgY3JlYXRlTGl0ZXJhbEFycmF5Q29udmVydGVyOiAoYXJnQ291bnQ6IG51bWJlcikgPT4gKGFyZ3M6IG8uRXhwcmVzc2lvbltdKSA9PiB7XG4gICAgICAgICAgICAgIGNvbnN0IGFyciA9IG8ubGl0ZXJhbEFycihhcmdzKTtcbiAgICAgICAgICAgICAgLy8gTm90ZTogVGhlIG9sZCB2aWV3IGNvbXBpbGVyIHVzZWQgdG8gdXNlIGFuIGBhbnlgIHR5cGVcbiAgICAgICAgICAgICAgLy8gZm9yIGFycmF5cy5cbiAgICAgICAgICAgICAgcmV0dXJuIHRoaXMub3B0aW9ucy5mdWxsVGVtcGxhdGVUeXBlQ2hlY2sgPyBhcnIgOiBhcnIuY2FzdChvLkRZTkFNSUNfVFlQRSk7XG4gICAgICAgICAgICB9LFxuICAgICAgICAgICAgY3JlYXRlTGl0ZXJhbE1hcENvbnZlcnRlcjogKGtleXM6IHtrZXk6IHN0cmluZywgcXVvdGVkOiBib29sZWFufVtdKSA9PlxuICAgICAgICAgICAgICAgICh2YWx1ZXM6IG8uRXhwcmVzc2lvbltdKSA9PiB7XG4gICAgICAgICAgICAgICAgICBjb25zdCBlbnRyaWVzID0ga2V5cy5tYXAoKGssIGkpID0+ICh7XG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBrZXk6IGsua2V5LFxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgdmFsdWU6IHZhbHVlc1tpXSxcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIHF1b3RlZDogay5xdW90ZWQsXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgfSkpO1xuICAgICAgICAgICAgICAgICAgY29uc3QgbWFwID0gby5saXRlcmFsTWFwKGVudHJpZXMpO1xuICAgICAgICAgICAgICAgICAgLy8gTm90ZTogVGhlIG9sZCB2aWV3IGNvbXBpbGVyIHVzZWQgdG8gdXNlIGFuIGBhbnlgIHR5cGVcbiAgICAgICAgICAgICAgICAgIC8vIGZvciBtYXBzLlxuICAgICAgICAgICAgICAgICAgcmV0dXJuIHRoaXMub3B0aW9ucy5mdWxsVGVtcGxhdGVUeXBlQ2hlY2sgPyBtYXAgOiBtYXAuY2FzdChvLkRZTkFNSUNfVFlQRSk7XG4gICAgICAgICAgICAgICAgfSxcbiAgICAgICAgICAgIGNyZWF0ZVBpcGVDb252ZXJ0ZXI6IChuYW1lOiBzdHJpbmcsIGFyZ0NvdW50OiBudW1iZXIpID0+IChhcmdzOiBvLkV4cHJlc3Npb25bXSkgPT4ge1xuICAgICAgICAgICAgICAvLyBOb3RlOiBUaGUgb2xkIHZpZXcgY29tcGlsZXIgdXNlZCB0byB1c2UgYW4gYGFueWAgdHlwZVxuICAgICAgICAgICAgICAvLyBmb3IgcGlwZXMuXG4gICAgICAgICAgICAgIGNvbnN0IHBpcGVFeHByID0gdGhpcy5vcHRpb25zLmZ1bGxUZW1wbGF0ZVR5cGVDaGVjayA/XG4gICAgICAgICAgICAgICAgICBvLnZhcmlhYmxlKHRoaXMucGlwZU91dHB1dFZhcihuYW1lKSkgOlxuICAgICAgICAgICAgICAgICAgby52YXJpYWJsZSh0aGlzLmdldE91dHB1dFZhcihvLkJ1aWx0aW5UeXBlTmFtZS5EeW5hbWljKSk7XG4gICAgICAgICAgICAgIHJldHVybiBwaXBlRXhwci5jYWxsTWV0aG9kKCd0cmFuc2Zvcm0nLCBhcmdzKTtcbiAgICAgICAgICAgIH0sXG4gICAgICAgICAgfSxcbiAgICAgICAgICBleHByZXNzaW9uLnZhbHVlKVxuICAgIH07XG4gIH1cblxuICB2aXNpdE5nQ29udGVudChhc3Q6IE5nQ29udGVudEFzdCwgY29udGV4dDogYW55KTogYW55IHt9XG4gIHZpc2l0VGV4dChhc3Q6IFRleHRBc3QsIGNvbnRleHQ6IGFueSk6IGFueSB7fVxuICB2aXNpdERpcmVjdGl2ZVByb3BlcnR5KGFzdDogQm91bmREaXJlY3RpdmVQcm9wZXJ0eUFzdCwgY29udGV4dDogYW55KTogYW55IHt9XG4gIHZpc2l0UmVmZXJlbmNlKGFzdDogUmVmZXJlbmNlQXN0LCBjb250ZXh0OiBhbnkpOiBhbnkge31cbiAgdmlzaXRWYXJpYWJsZShhc3Q6IFZhcmlhYmxlQXN0LCBjb250ZXh0OiBhbnkpOiBhbnkge31cbiAgdmlzaXRFdmVudChhc3Q6IEJvdW5kRXZlbnRBc3QsIGNvbnRleHQ6IGFueSk6IGFueSB7fVxuICB2aXNpdEVsZW1lbnRQcm9wZXJ0eShhc3Q6IEJvdW5kRWxlbWVudFByb3BlcnR5QXN0LCBjb250ZXh0OiBhbnkpOiBhbnkge31cbiAgdmlzaXRBdHRyKGFzdDogQXR0ckFzdCwgY29udGV4dDogYW55KTogYW55IHt9XG59XG4iXX0=