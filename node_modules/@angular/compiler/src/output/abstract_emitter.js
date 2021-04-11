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
        define("@angular/compiler/src/output/abstract_emitter", ["require", "exports", "tslib", "@angular/compiler/src/output/output_ast", "@angular/compiler/src/output/source_map"], factory);
    }
})(function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.escapeIdentifier = exports.AbstractEmitterVisitor = exports.EmitterVisitorContext = exports.CATCH_STACK_VAR = exports.CATCH_ERROR_VAR = void 0;
    var tslib_1 = require("tslib");
    var o = require("@angular/compiler/src/output/output_ast");
    var source_map_1 = require("@angular/compiler/src/output/source_map");
    var _SINGLE_QUOTE_ESCAPE_STRING_RE = /'|\\|\n|\r|\$/g;
    var _LEGAL_IDENTIFIER_RE = /^[$A-Z_][0-9A-Z_$]*$/i;
    var _INDENT_WITH = '  ';
    exports.CATCH_ERROR_VAR = o.variable('error', null, null);
    exports.CATCH_STACK_VAR = o.variable('stack', null, null);
    var _EmittedLine = /** @class */ (function () {
        function _EmittedLine(indent) {
            this.indent = indent;
            this.partsLength = 0;
            this.parts = [];
            this.srcSpans = [];
        }
        return _EmittedLine;
    }());
    var EmitterVisitorContext = /** @class */ (function () {
        function EmitterVisitorContext(_indent) {
            this._indent = _indent;
            this._classes = [];
            this._preambleLineCount = 0;
            this._lines = [new _EmittedLine(_indent)];
        }
        EmitterVisitorContext.createRoot = function () {
            return new EmitterVisitorContext(0);
        };
        Object.defineProperty(EmitterVisitorContext.prototype, "_currentLine", {
            /**
             * @internal strip this from published d.ts files due to
             * https://github.com/microsoft/TypeScript/issues/36216
             */
            get: function () {
                return this._lines[this._lines.length - 1];
            },
            enumerable: false,
            configurable: true
        });
        EmitterVisitorContext.prototype.println = function (from, lastPart) {
            if (lastPart === void 0) { lastPart = ''; }
            this.print(from || null, lastPart, true);
        };
        EmitterVisitorContext.prototype.lineIsEmpty = function () {
            return this._currentLine.parts.length === 0;
        };
        EmitterVisitorContext.prototype.lineLength = function () {
            return this._currentLine.indent * _INDENT_WITH.length + this._currentLine.partsLength;
        };
        EmitterVisitorContext.prototype.print = function (from, part, newLine) {
            if (newLine === void 0) { newLine = false; }
            if (part.length > 0) {
                this._currentLine.parts.push(part);
                this._currentLine.partsLength += part.length;
                this._currentLine.srcSpans.push(from && from.sourceSpan || null);
            }
            if (newLine) {
                this._lines.push(new _EmittedLine(this._indent));
            }
        };
        EmitterVisitorContext.prototype.removeEmptyLastLine = function () {
            if (this.lineIsEmpty()) {
                this._lines.pop();
            }
        };
        EmitterVisitorContext.prototype.incIndent = function () {
            this._indent++;
            if (this.lineIsEmpty()) {
                this._currentLine.indent = this._indent;
            }
        };
        EmitterVisitorContext.prototype.decIndent = function () {
            this._indent--;
            if (this.lineIsEmpty()) {
                this._currentLine.indent = this._indent;
            }
        };
        EmitterVisitorContext.prototype.pushClass = function (clazz) {
            this._classes.push(clazz);
        };
        EmitterVisitorContext.prototype.popClass = function () {
            return this._classes.pop();
        };
        Object.defineProperty(EmitterVisitorContext.prototype, "currentClass", {
            get: function () {
                return this._classes.length > 0 ? this._classes[this._classes.length - 1] : null;
            },
            enumerable: false,
            configurable: true
        });
        EmitterVisitorContext.prototype.toSource = function () {
            return this.sourceLines
                .map(function (l) { return l.parts.length > 0 ? _createIndent(l.indent) + l.parts.join('') : ''; })
                .join('\n');
        };
        EmitterVisitorContext.prototype.toSourceMapGenerator = function (genFilePath, startsAtLine) {
            if (startsAtLine === void 0) { startsAtLine = 0; }
            var map = new source_map_1.SourceMapGenerator(genFilePath);
            var firstOffsetMapped = false;
            var mapFirstOffsetIfNeeded = function () {
                if (!firstOffsetMapped) {
                    // Add a single space so that tools won't try to load the file from disk.
                    // Note: We are using virtual urls like `ng:///`, so we have to
                    // provide a content here.
                    map.addSource(genFilePath, ' ').addMapping(0, genFilePath, 0, 0);
                    firstOffsetMapped = true;
                }
            };
            for (var i = 0; i < startsAtLine; i++) {
                map.addLine();
                mapFirstOffsetIfNeeded();
            }
            this.sourceLines.forEach(function (line, lineIdx) {
                map.addLine();
                var spans = line.srcSpans;
                var parts = line.parts;
                var col0 = line.indent * _INDENT_WITH.length;
                var spanIdx = 0;
                // skip leading parts without source spans
                while (spanIdx < spans.length && !spans[spanIdx]) {
                    col0 += parts[spanIdx].length;
                    spanIdx++;
                }
                if (spanIdx < spans.length && lineIdx === 0 && col0 === 0) {
                    firstOffsetMapped = true;
                }
                else {
                    mapFirstOffsetIfNeeded();
                }
                while (spanIdx < spans.length) {
                    var span = spans[spanIdx];
                    var source = span.start.file;
                    var sourceLine = span.start.line;
                    var sourceCol = span.start.col;
                    map.addSource(source.url, source.content)
                        .addMapping(col0, source.url, sourceLine, sourceCol);
                    col0 += parts[spanIdx].length;
                    spanIdx++;
                    // assign parts without span or the same span to the previous segment
                    while (spanIdx < spans.length && (span === spans[spanIdx] || !spans[spanIdx])) {
                        col0 += parts[spanIdx].length;
                        spanIdx++;
                    }
                }
            });
            return map;
        };
        EmitterVisitorContext.prototype.setPreambleLineCount = function (count) {
            return this._preambleLineCount = count;
        };
        EmitterVisitorContext.prototype.spanOf = function (line, column) {
            var emittedLine = this._lines[line - this._preambleLineCount];
            if (emittedLine) {
                var columnsLeft = column - _createIndent(emittedLine.indent).length;
                for (var partIndex = 0; partIndex < emittedLine.parts.length; partIndex++) {
                    var part = emittedLine.parts[partIndex];
                    if (part.length > columnsLeft) {
                        return emittedLine.srcSpans[partIndex];
                    }
                    columnsLeft -= part.length;
                }
            }
            return null;
        };
        Object.defineProperty(EmitterVisitorContext.prototype, "sourceLines", {
            /**
             * @internal strip this from published d.ts files due to
             * https://github.com/microsoft/TypeScript/issues/36216
             */
            get: function () {
                if (this._lines.length && this._lines[this._lines.length - 1].parts.length === 0) {
                    return this._lines.slice(0, -1);
                }
                return this._lines;
            },
            enumerable: false,
            configurable: true
        });
        return EmitterVisitorContext;
    }());
    exports.EmitterVisitorContext = EmitterVisitorContext;
    var AbstractEmitterVisitor = /** @class */ (function () {
        function AbstractEmitterVisitor(_escapeDollarInStrings) {
            this._escapeDollarInStrings = _escapeDollarInStrings;
        }
        AbstractEmitterVisitor.prototype.printLeadingComments = function (stmt, ctx) {
            var e_1, _a;
            if (stmt.leadingComments === undefined) {
                return;
            }
            try {
                for (var _b = tslib_1.__values(stmt.leadingComments), _c = _b.next(); !_c.done; _c = _b.next()) {
                    var comment = _c.value;
                    if (comment instanceof o.JSDocComment) {
                        ctx.print(stmt, "/*" + comment.toString() + "*/", comment.trailingNewline);
                    }
                    else {
                        if (comment.multiline) {
                            ctx.print(stmt, "/* " + comment.text + " */", comment.trailingNewline);
                        }
                        else {
                            comment.text.split('\n').forEach(function (line) {
                                ctx.println(stmt, "// " + line);
                            });
                        }
                    }
                }
            }
            catch (e_1_1) { e_1 = { error: e_1_1 }; }
            finally {
                try {
                    if (_c && !_c.done && (_a = _b.return)) _a.call(_b);
                }
                finally { if (e_1) throw e_1.error; }
            }
        };
        AbstractEmitterVisitor.prototype.visitExpressionStmt = function (stmt, ctx) {
            this.printLeadingComments(stmt, ctx);
            stmt.expr.visitExpression(this, ctx);
            ctx.println(stmt, ';');
            return null;
        };
        AbstractEmitterVisitor.prototype.visitReturnStmt = function (stmt, ctx) {
            this.printLeadingComments(stmt, ctx);
            ctx.print(stmt, "return ");
            stmt.value.visitExpression(this, ctx);
            ctx.println(stmt, ';');
            return null;
        };
        AbstractEmitterVisitor.prototype.visitIfStmt = function (stmt, ctx) {
            this.printLeadingComments(stmt, ctx);
            ctx.print(stmt, "if (");
            stmt.condition.visitExpression(this, ctx);
            ctx.print(stmt, ") {");
            var hasElseCase = stmt.falseCase != null && stmt.falseCase.length > 0;
            if (stmt.trueCase.length <= 1 && !hasElseCase) {
                ctx.print(stmt, " ");
                this.visitAllStatements(stmt.trueCase, ctx);
                ctx.removeEmptyLastLine();
                ctx.print(stmt, " ");
            }
            else {
                ctx.println();
                ctx.incIndent();
                this.visitAllStatements(stmt.trueCase, ctx);
                ctx.decIndent();
                if (hasElseCase) {
                    ctx.println(stmt, "} else {");
                    ctx.incIndent();
                    this.visitAllStatements(stmt.falseCase, ctx);
                    ctx.decIndent();
                }
            }
            ctx.println(stmt, "}");
            return null;
        };
        AbstractEmitterVisitor.prototype.visitThrowStmt = function (stmt, ctx) {
            this.printLeadingComments(stmt, ctx);
            ctx.print(stmt, "throw ");
            stmt.error.visitExpression(this, ctx);
            ctx.println(stmt, ";");
            return null;
        };
        AbstractEmitterVisitor.prototype.visitWriteVarExpr = function (expr, ctx) {
            var lineWasEmpty = ctx.lineIsEmpty();
            if (!lineWasEmpty) {
                ctx.print(expr, '(');
            }
            ctx.print(expr, expr.name + " = ");
            expr.value.visitExpression(this, ctx);
            if (!lineWasEmpty) {
                ctx.print(expr, ')');
            }
            return null;
        };
        AbstractEmitterVisitor.prototype.visitWriteKeyExpr = function (expr, ctx) {
            var lineWasEmpty = ctx.lineIsEmpty();
            if (!lineWasEmpty) {
                ctx.print(expr, '(');
            }
            expr.receiver.visitExpression(this, ctx);
            ctx.print(expr, "[");
            expr.index.visitExpression(this, ctx);
            ctx.print(expr, "] = ");
            expr.value.visitExpression(this, ctx);
            if (!lineWasEmpty) {
                ctx.print(expr, ')');
            }
            return null;
        };
        AbstractEmitterVisitor.prototype.visitWritePropExpr = function (expr, ctx) {
            var lineWasEmpty = ctx.lineIsEmpty();
            if (!lineWasEmpty) {
                ctx.print(expr, '(');
            }
            expr.receiver.visitExpression(this, ctx);
            ctx.print(expr, "." + expr.name + " = ");
            expr.value.visitExpression(this, ctx);
            if (!lineWasEmpty) {
                ctx.print(expr, ')');
            }
            return null;
        };
        AbstractEmitterVisitor.prototype.visitInvokeMethodExpr = function (expr, ctx) {
            expr.receiver.visitExpression(this, ctx);
            var name = expr.name;
            if (expr.builtin != null) {
                name = this.getBuiltinMethodName(expr.builtin);
                if (name == null) {
                    // some builtins just mean to skip the call.
                    return null;
                }
            }
            ctx.print(expr, "." + name + "(");
            this.visitAllExpressions(expr.args, ctx, ",");
            ctx.print(expr, ")");
            return null;
        };
        AbstractEmitterVisitor.prototype.visitInvokeFunctionExpr = function (expr, ctx) {
            expr.fn.visitExpression(this, ctx);
            ctx.print(expr, "(");
            this.visitAllExpressions(expr.args, ctx, ',');
            ctx.print(expr, ")");
            return null;
        };
        AbstractEmitterVisitor.prototype.visitTaggedTemplateExpr = function (expr, ctx) {
            expr.tag.visitExpression(this, ctx);
            ctx.print(expr, '`' + expr.template.elements[0].rawText);
            for (var i = 1; i < expr.template.elements.length; i++) {
                ctx.print(expr, '${');
                expr.template.expressions[i - 1].visitExpression(this, ctx);
                ctx.print(expr, "}" + expr.template.elements[i].rawText);
            }
            ctx.print(expr, '`');
            return null;
        };
        AbstractEmitterVisitor.prototype.visitWrappedNodeExpr = function (ast, ctx) {
            throw new Error('Abstract emitter cannot visit WrappedNodeExpr.');
        };
        AbstractEmitterVisitor.prototype.visitTypeofExpr = function (expr, ctx) {
            ctx.print(expr, 'typeof ');
            expr.expr.visitExpression(this, ctx);
        };
        AbstractEmitterVisitor.prototype.visitReadVarExpr = function (ast, ctx) {
            var varName = ast.name;
            if (ast.builtin != null) {
                switch (ast.builtin) {
                    case o.BuiltinVar.Super:
                        varName = 'super';
                        break;
                    case o.BuiltinVar.This:
                        varName = 'this';
                        break;
                    case o.BuiltinVar.CatchError:
                        varName = exports.CATCH_ERROR_VAR.name;
                        break;
                    case o.BuiltinVar.CatchStack:
                        varName = exports.CATCH_STACK_VAR.name;
                        break;
                    default:
                        throw new Error("Unknown builtin variable " + ast.builtin);
                }
            }
            ctx.print(ast, varName);
            return null;
        };
        AbstractEmitterVisitor.prototype.visitInstantiateExpr = function (ast, ctx) {
            ctx.print(ast, "new ");
            ast.classExpr.visitExpression(this, ctx);
            ctx.print(ast, "(");
            this.visitAllExpressions(ast.args, ctx, ',');
            ctx.print(ast, ")");
            return null;
        };
        AbstractEmitterVisitor.prototype.visitLiteralExpr = function (ast, ctx) {
            var value = ast.value;
            if (typeof value === 'string') {
                ctx.print(ast, escapeIdentifier(value, this._escapeDollarInStrings));
            }
            else {
                ctx.print(ast, "" + value);
            }
            return null;
        };
        AbstractEmitterVisitor.prototype.visitLocalizedString = function (ast, ctx) {
            var head = ast.serializeI18nHead();
            ctx.print(ast, '$localize `' + head.raw);
            for (var i = 1; i < ast.messageParts.length; i++) {
                ctx.print(ast, '${');
                ast.expressions[i - 1].visitExpression(this, ctx);
                ctx.print(ast, "}" + ast.serializeI18nTemplatePart(i).raw);
            }
            ctx.print(ast, '`');
            return null;
        };
        AbstractEmitterVisitor.prototype.visitConditionalExpr = function (ast, ctx) {
            ctx.print(ast, "(");
            ast.condition.visitExpression(this, ctx);
            ctx.print(ast, '? ');
            ast.trueCase.visitExpression(this, ctx);
            ctx.print(ast, ': ');
            ast.falseCase.visitExpression(this, ctx);
            ctx.print(ast, ")");
            return null;
        };
        AbstractEmitterVisitor.prototype.visitNotExpr = function (ast, ctx) {
            ctx.print(ast, '!');
            ast.condition.visitExpression(this, ctx);
            return null;
        };
        AbstractEmitterVisitor.prototype.visitAssertNotNullExpr = function (ast, ctx) {
            ast.condition.visitExpression(this, ctx);
            return null;
        };
        AbstractEmitterVisitor.prototype.visitUnaryOperatorExpr = function (ast, ctx) {
            var opStr;
            switch (ast.operator) {
                case o.UnaryOperator.Plus:
                    opStr = '+';
                    break;
                case o.UnaryOperator.Minus:
                    opStr = '-';
                    break;
                default:
                    throw new Error("Unknown operator " + ast.operator);
            }
            if (ast.parens)
                ctx.print(ast, "(");
            ctx.print(ast, opStr);
            ast.expr.visitExpression(this, ctx);
            if (ast.parens)
                ctx.print(ast, ")");
            return null;
        };
        AbstractEmitterVisitor.prototype.visitBinaryOperatorExpr = function (ast, ctx) {
            var opStr;
            switch (ast.operator) {
                case o.BinaryOperator.Equals:
                    opStr = '==';
                    break;
                case o.BinaryOperator.Identical:
                    opStr = '===';
                    break;
                case o.BinaryOperator.NotEquals:
                    opStr = '!=';
                    break;
                case o.BinaryOperator.NotIdentical:
                    opStr = '!==';
                    break;
                case o.BinaryOperator.And:
                    opStr = '&&';
                    break;
                case o.BinaryOperator.BitwiseAnd:
                    opStr = '&';
                    break;
                case o.BinaryOperator.Or:
                    opStr = '||';
                    break;
                case o.BinaryOperator.Plus:
                    opStr = '+';
                    break;
                case o.BinaryOperator.Minus:
                    opStr = '-';
                    break;
                case o.BinaryOperator.Divide:
                    opStr = '/';
                    break;
                case o.BinaryOperator.Multiply:
                    opStr = '*';
                    break;
                case o.BinaryOperator.Modulo:
                    opStr = '%';
                    break;
                case o.BinaryOperator.Lower:
                    opStr = '<';
                    break;
                case o.BinaryOperator.LowerEquals:
                    opStr = '<=';
                    break;
                case o.BinaryOperator.Bigger:
                    opStr = '>';
                    break;
                case o.BinaryOperator.BiggerEquals:
                    opStr = '>=';
                    break;
                default:
                    throw new Error("Unknown operator " + ast.operator);
            }
            if (ast.parens)
                ctx.print(ast, "(");
            ast.lhs.visitExpression(this, ctx);
            ctx.print(ast, " " + opStr + " ");
            ast.rhs.visitExpression(this, ctx);
            if (ast.parens)
                ctx.print(ast, ")");
            return null;
        };
        AbstractEmitterVisitor.prototype.visitReadPropExpr = function (ast, ctx) {
            ast.receiver.visitExpression(this, ctx);
            ctx.print(ast, ".");
            ctx.print(ast, ast.name);
            return null;
        };
        AbstractEmitterVisitor.prototype.visitReadKeyExpr = function (ast, ctx) {
            ast.receiver.visitExpression(this, ctx);
            ctx.print(ast, "[");
            ast.index.visitExpression(this, ctx);
            ctx.print(ast, "]");
            return null;
        };
        AbstractEmitterVisitor.prototype.visitLiteralArrayExpr = function (ast, ctx) {
            ctx.print(ast, "[");
            this.visitAllExpressions(ast.entries, ctx, ',');
            ctx.print(ast, "]");
            return null;
        };
        AbstractEmitterVisitor.prototype.visitLiteralMapExpr = function (ast, ctx) {
            var _this = this;
            ctx.print(ast, "{");
            this.visitAllObjects(function (entry) {
                ctx.print(ast, escapeIdentifier(entry.key, _this._escapeDollarInStrings, entry.quoted) + ":");
                entry.value.visitExpression(_this, ctx);
            }, ast.entries, ctx, ',');
            ctx.print(ast, "}");
            return null;
        };
        AbstractEmitterVisitor.prototype.visitCommaExpr = function (ast, ctx) {
            ctx.print(ast, '(');
            this.visitAllExpressions(ast.parts, ctx, ',');
            ctx.print(ast, ')');
            return null;
        };
        AbstractEmitterVisitor.prototype.visitAllExpressions = function (expressions, ctx, separator) {
            var _this = this;
            this.visitAllObjects(function (expr) { return expr.visitExpression(_this, ctx); }, expressions, ctx, separator);
        };
        AbstractEmitterVisitor.prototype.visitAllObjects = function (handler, expressions, ctx, separator) {
            var incrementedIndent = false;
            for (var i = 0; i < expressions.length; i++) {
                if (i > 0) {
                    if (ctx.lineLength() > 80) {
                        ctx.print(null, separator, true);
                        if (!incrementedIndent) {
                            // continuation are marked with double indent.
                            ctx.incIndent();
                            ctx.incIndent();
                            incrementedIndent = true;
                        }
                    }
                    else {
                        ctx.print(null, separator, false);
                    }
                }
                handler(expressions[i]);
            }
            if (incrementedIndent) {
                // continuation are marked with double indent.
                ctx.decIndent();
                ctx.decIndent();
            }
        };
        AbstractEmitterVisitor.prototype.visitAllStatements = function (statements, ctx) {
            var _this = this;
            statements.forEach(function (stmt) { return stmt.visitStatement(_this, ctx); });
        };
        return AbstractEmitterVisitor;
    }());
    exports.AbstractEmitterVisitor = AbstractEmitterVisitor;
    function escapeIdentifier(input, escapeDollar, alwaysQuote) {
        if (alwaysQuote === void 0) { alwaysQuote = true; }
        if (input == null) {
            return null;
        }
        var body = input.replace(_SINGLE_QUOTE_ESCAPE_STRING_RE, function () {
            var match = [];
            for (var _i = 0; _i < arguments.length; _i++) {
                match[_i] = arguments[_i];
            }
            if (match[0] == '$') {
                return escapeDollar ? '\\$' : '$';
            }
            else if (match[0] == '\n') {
                return '\\n';
            }
            else if (match[0] == '\r') {
                return '\\r';
            }
            else {
                return "\\" + match[0];
            }
        });
        var requiresQuotes = alwaysQuote || !_LEGAL_IDENTIFIER_RE.test(body);
        return requiresQuotes ? "'" + body + "'" : body;
    }
    exports.escapeIdentifier = escapeIdentifier;
    function _createIndent(count) {
        var res = '';
        for (var i = 0; i < count; i++) {
            res += _INDENT_WITH;
        }
        return res;
    }
});
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiYWJzdHJhY3RfZW1pdHRlci5qcyIsInNvdXJjZVJvb3QiOiIiLCJzb3VyY2VzIjpbIi4uLy4uLy4uLy4uLy4uLy4uLy4uL3BhY2thZ2VzL2NvbXBpbGVyL3NyYy9vdXRwdXQvYWJzdHJhY3RfZW1pdHRlci50cyJdLCJuYW1lcyI6W10sIm1hcHBpbmdzIjoiQUFBQTs7Ozs7O0dBTUc7Ozs7Ozs7Ozs7Ozs7O0lBR0gsMkRBQWtDO0lBQ2xDLHNFQUFnRDtJQUVoRCxJQUFNLDhCQUE4QixHQUFHLGdCQUFnQixDQUFDO0lBQ3hELElBQU0sb0JBQW9CLEdBQUcsdUJBQXVCLENBQUM7SUFDckQsSUFBTSxZQUFZLEdBQUcsSUFBSSxDQUFDO0lBQ2IsUUFBQSxlQUFlLEdBQUcsQ0FBQyxDQUFDLFFBQVEsQ0FBQyxPQUFPLEVBQUUsSUFBSSxFQUFFLElBQUksQ0FBQyxDQUFDO0lBQ2xELFFBQUEsZUFBZSxHQUFHLENBQUMsQ0FBQyxRQUFRLENBQUMsT0FBTyxFQUFFLElBQUksRUFBRSxJQUFJLENBQUMsQ0FBQztJQU0vRDtRQUlFLHNCQUFtQixNQUFjO1lBQWQsV0FBTSxHQUFOLE1BQU0sQ0FBUTtZQUhqQyxnQkFBVyxHQUFHLENBQUMsQ0FBQztZQUNoQixVQUFLLEdBQWEsRUFBRSxDQUFDO1lBQ3JCLGFBQVEsR0FBNkIsRUFBRSxDQUFDO1FBQ0osQ0FBQztRQUN2QyxtQkFBQztJQUFELENBQUMsQUFMRCxJQUtDO0lBRUQ7UUFTRSwrQkFBb0IsT0FBZTtZQUFmLFlBQU8sR0FBUCxPQUFPLENBQVE7WUFIM0IsYUFBUSxHQUFrQixFQUFFLENBQUM7WUFDN0IsdUJBQWtCLEdBQUcsQ0FBQyxDQUFDO1lBRzdCLElBQUksQ0FBQyxNQUFNLEdBQUcsQ0FBQyxJQUFJLFlBQVksQ0FBQyxPQUFPLENBQUMsQ0FBQyxDQUFDO1FBQzVDLENBQUM7UUFWTSxnQ0FBVSxHQUFqQjtZQUNFLE9BQU8sSUFBSSxxQkFBcUIsQ0FBQyxDQUFDLENBQUMsQ0FBQztRQUN0QyxDQUFDO1FBY0Qsc0JBQVksK0NBQVk7WUFKeEI7OztlQUdHO2lCQUNIO2dCQUNFLE9BQU8sSUFBSSxDQUFDLE1BQU0sQ0FBQyxJQUFJLENBQUMsTUFBTSxDQUFDLE1BQU0sR0FBRyxDQUFDLENBQUMsQ0FBQztZQUM3QyxDQUFDOzs7V0FBQTtRQUVELHVDQUFPLEdBQVAsVUFBUSxJQUE4QyxFQUFFLFFBQXFCO1lBQXJCLHlCQUFBLEVBQUEsYUFBcUI7WUFDM0UsSUFBSSxDQUFDLEtBQUssQ0FBQyxJQUFJLElBQUksSUFBSSxFQUFFLFFBQVEsRUFBRSxJQUFJLENBQUMsQ0FBQztRQUMzQyxDQUFDO1FBRUQsMkNBQVcsR0FBWDtZQUNFLE9BQU8sSUFBSSxDQUFDLFlBQVksQ0FBQyxLQUFLLENBQUMsTUFBTSxLQUFLLENBQUMsQ0FBQztRQUM5QyxDQUFDO1FBRUQsMENBQVUsR0FBVjtZQUNFLE9BQU8sSUFBSSxDQUFDLFlBQVksQ0FBQyxNQUFNLEdBQUcsWUFBWSxDQUFDLE1BQU0sR0FBRyxJQUFJLENBQUMsWUFBWSxDQUFDLFdBQVcsQ0FBQztRQUN4RixDQUFDO1FBRUQscUNBQUssR0FBTCxVQUFNLElBQTZDLEVBQUUsSUFBWSxFQUFFLE9BQXdCO1lBQXhCLHdCQUFBLEVBQUEsZUFBd0I7WUFDekYsSUFBSSxJQUFJLENBQUMsTUFBTSxHQUFHLENBQUMsRUFBRTtnQkFDbkIsSUFBSSxDQUFDLFlBQVksQ0FBQyxLQUFLLENBQUMsSUFBSSxDQUFDLElBQUksQ0FBQyxDQUFDO2dCQUNuQyxJQUFJLENBQUMsWUFBWSxDQUFDLFdBQVcsSUFBSSxJQUFJLENBQUMsTUFBTSxDQUFDO2dCQUM3QyxJQUFJLENBQUMsWUFBWSxDQUFDLFFBQVEsQ0FBQyxJQUFJLENBQUMsSUFBSSxJQUFJLElBQUksQ0FBQyxVQUFVLElBQUksSUFBSSxDQUFDLENBQUM7YUFDbEU7WUFDRCxJQUFJLE9BQU8sRUFBRTtnQkFDWCxJQUFJLENBQUMsTUFBTSxDQUFDLElBQUksQ0FBQyxJQUFJLFlBQVksQ0FBQyxJQUFJLENBQUMsT0FBTyxDQUFDLENBQUMsQ0FBQzthQUNsRDtRQUNILENBQUM7UUFFRCxtREFBbUIsR0FBbkI7WUFDRSxJQUFJLElBQUksQ0FBQyxXQUFXLEVBQUUsRUFBRTtnQkFDdEIsSUFBSSxDQUFDLE1BQU0sQ0FBQyxHQUFHLEVBQUUsQ0FBQzthQUNuQjtRQUNILENBQUM7UUFFRCx5Q0FBUyxHQUFUO1lBQ0UsSUFBSSxDQUFDLE9BQU8sRUFBRSxDQUFDO1lBQ2YsSUFBSSxJQUFJLENBQUMsV0FBVyxFQUFFLEVBQUU7Z0JBQ3RCLElBQUksQ0FBQyxZQUFZLENBQUMsTUFBTSxHQUFHLElBQUksQ0FBQyxPQUFPLENBQUM7YUFDekM7UUFDSCxDQUFDO1FBRUQseUNBQVMsR0FBVDtZQUNFLElBQUksQ0FBQyxPQUFPLEVBQUUsQ0FBQztZQUNmLElBQUksSUFBSSxDQUFDLFdBQVcsRUFBRSxFQUFFO2dCQUN0QixJQUFJLENBQUMsWUFBWSxDQUFDLE1BQU0sR0FBRyxJQUFJLENBQUMsT0FBTyxDQUFDO2FBQ3pDO1FBQ0gsQ0FBQztRQUVELHlDQUFTLEdBQVQsVUFBVSxLQUFrQjtZQUMxQixJQUFJLENBQUMsUUFBUSxDQUFDLElBQUksQ0FBQyxLQUFLLENBQUMsQ0FBQztRQUM1QixDQUFDO1FBRUQsd0NBQVEsR0FBUjtZQUNFLE9BQU8sSUFBSSxDQUFDLFFBQVEsQ0FBQyxHQUFHLEVBQUcsQ0FBQztRQUM5QixDQUFDO1FBRUQsc0JBQUksK0NBQVk7aUJBQWhCO2dCQUNFLE9BQU8sSUFBSSxDQUFDLFFBQVEsQ0FBQyxNQUFNLEdBQUcsQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFJLENBQUMsUUFBUSxDQUFDLElBQUksQ0FBQyxRQUFRLENBQUMsTUFBTSxHQUFHLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFJLENBQUM7WUFDbkYsQ0FBQzs7O1dBQUE7UUFFRCx3Q0FBUSxHQUFSO1lBQ0UsT0FBTyxJQUFJLENBQUMsV0FBVztpQkFDbEIsR0FBRyxDQUFDLFVBQUEsQ0FBQyxJQUFJLE9BQUEsQ0FBQyxDQUFDLEtBQUssQ0FBQyxNQUFNLEdBQUcsQ0FBQyxDQUFDLENBQUMsQ0FBQyxhQUFhLENBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxHQUFHLENBQUMsQ0FBQyxLQUFLLENBQUMsSUFBSSxDQUFDLEVBQUUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFFLEVBQXBFLENBQW9FLENBQUM7aUJBQzlFLElBQUksQ0FBQyxJQUFJLENBQUMsQ0FBQztRQUNsQixDQUFDO1FBRUQsb0RBQW9CLEdBQXBCLFVBQXFCLFdBQW1CLEVBQUUsWUFBd0I7WUFBeEIsNkJBQUEsRUFBQSxnQkFBd0I7WUFDaEUsSUFBTSxHQUFHLEdBQUcsSUFBSSwrQkFBa0IsQ0FBQyxXQUFXLENBQUMsQ0FBQztZQUVoRCxJQUFJLGlCQUFpQixHQUFHLEtBQUssQ0FBQztZQUM5QixJQUFNLHNCQUFzQixHQUFHO2dCQUM3QixJQUFJLENBQUMsaUJBQWlCLEVBQUU7b0JBQ3RCLHlFQUF5RTtvQkFDekUsK0RBQStEO29CQUMvRCwwQkFBMEI7b0JBQzFCLEdBQUcsQ0FBQyxTQUFTLENBQUMsV0FBVyxFQUFFLEdBQUcsQ0FBQyxDQUFDLFVBQVUsQ0FBQyxDQUFDLEVBQUUsV0FBVyxFQUFFLENBQUMsRUFBRSxDQUFDLENBQUMsQ0FBQztvQkFDakUsaUJBQWlCLEdBQUcsSUFBSSxDQUFDO2lCQUMxQjtZQUNILENBQUMsQ0FBQztZQUVGLEtBQUssSUFBSSxDQUFDLEdBQUcsQ0FBQyxFQUFFLENBQUMsR0FBRyxZQUFZLEVBQUUsQ0FBQyxFQUFFLEVBQUU7Z0JBQ3JDLEdBQUcsQ0FBQyxPQUFPLEVBQUUsQ0FBQztnQkFDZCxzQkFBc0IsRUFBRSxDQUFDO2FBQzFCO1lBRUQsSUFBSSxDQUFDLFdBQVcsQ0FBQyxPQUFPLENBQUMsVUFBQyxJQUFJLEVBQUUsT0FBTztnQkFDckMsR0FBRyxDQUFDLE9BQU8sRUFBRSxDQUFDO2dCQUVkLElBQU0sS0FBSyxHQUFHLElBQUksQ0FBQyxRQUFRLENBQUM7Z0JBQzVCLElBQU0sS0FBSyxHQUFHLElBQUksQ0FBQyxLQUFLLENBQUM7Z0JBQ3pCLElBQUksSUFBSSxHQUFHLElBQUksQ0FBQyxNQUFNLEdBQUcsWUFBWSxDQUFDLE1BQU0sQ0FBQztnQkFDN0MsSUFBSSxPQUFPLEdBQUcsQ0FBQyxDQUFDO2dCQUNoQiwwQ0FBMEM7Z0JBQzFDLE9BQU8sT0FBTyxHQUFHLEtBQUssQ0FBQyxNQUFNLElBQUksQ0FBQyxLQUFLLENBQUMsT0FBTyxDQUFDLEVBQUU7b0JBQ2hELElBQUksSUFBSSxLQUFLLENBQUMsT0FBTyxDQUFDLENBQUMsTUFBTSxDQUFDO29CQUM5QixPQUFPLEVBQUUsQ0FBQztpQkFDWDtnQkFDRCxJQUFJLE9BQU8sR0FBRyxLQUFLLENBQUMsTUFBTSxJQUFJLE9BQU8sS0FBSyxDQUFDLElBQUksSUFBSSxLQUFLLENBQUMsRUFBRTtvQkFDekQsaUJBQWlCLEdBQUcsSUFBSSxDQUFDO2lCQUMxQjtxQkFBTTtvQkFDTCxzQkFBc0IsRUFBRSxDQUFDO2lCQUMxQjtnQkFFRCxPQUFPLE9BQU8sR0FBRyxLQUFLLENBQUMsTUFBTSxFQUFFO29CQUM3QixJQUFNLElBQUksR0FBRyxLQUFLLENBQUMsT0FBTyxDQUFFLENBQUM7b0JBQzdCLElBQU0sTUFBTSxHQUFHLElBQUksQ0FBQyxLQUFLLENBQUMsSUFBSSxDQUFDO29CQUMvQixJQUFNLFVBQVUsR0FBRyxJQUFJLENBQUMsS0FBSyxDQUFDLElBQUksQ0FBQztvQkFDbkMsSUFBTSxTQUFTLEdBQUcsSUFBSSxDQUFDLEtBQUssQ0FBQyxHQUFHLENBQUM7b0JBQ2pDLEdBQUcsQ0FBQyxTQUFTLENBQUMsTUFBTSxDQUFDLEdBQUcsRUFBRSxNQUFNLENBQUMsT0FBTyxDQUFDO3lCQUNwQyxVQUFVLENBQUMsSUFBSSxFQUFFLE1BQU0sQ0FBQyxHQUFHLEVBQUUsVUFBVSxFQUFFLFNBQVMsQ0FBQyxDQUFDO29CQUV6RCxJQUFJLElBQUksS0FBSyxDQUFDLE9BQU8sQ0FBQyxDQUFDLE1BQU0sQ0FBQztvQkFDOUIsT0FBTyxFQUFFLENBQUM7b0JBRVYscUVBQXFFO29CQUNyRSxPQUFPLE9BQU8sR0FBRyxLQUFLLENBQUMsTUFBTSxJQUFJLENBQUMsSUFBSSxLQUFLLEtBQUssQ0FBQyxPQUFPLENBQUMsSUFBSSxDQUFDLEtBQUssQ0FBQyxPQUFPLENBQUMsQ0FBQyxFQUFFO3dCQUM3RSxJQUFJLElBQUksS0FBSyxDQUFDLE9BQU8sQ0FBQyxDQUFDLE1BQU0sQ0FBQzt3QkFDOUIsT0FBTyxFQUFFLENBQUM7cUJBQ1g7aUJBQ0Y7WUFDSCxDQUFDLENBQUMsQ0FBQztZQUVILE9BQU8sR0FBRyxDQUFDO1FBQ2IsQ0FBQztRQUVELG9EQUFvQixHQUFwQixVQUFxQixLQUFhO1lBQ2hDLE9BQU8sSUFBSSxDQUFDLGtCQUFrQixHQUFHLEtBQUssQ0FBQztRQUN6QyxDQUFDO1FBRUQsc0NBQU0sR0FBTixVQUFPLElBQVksRUFBRSxNQUFjO1lBQ2pDLElBQU0sV0FBVyxHQUFHLElBQUksQ0FBQyxNQUFNLENBQUMsSUFBSSxHQUFHLElBQUksQ0FBQyxrQkFBa0IsQ0FBQyxDQUFDO1lBQ2hFLElBQUksV0FBVyxFQUFFO2dCQUNmLElBQUksV0FBVyxHQUFHLE1BQU0sR0FBRyxhQUFhLENBQUMsV0FBVyxDQUFDLE1BQU0sQ0FBQyxDQUFDLE1BQU0sQ0FBQztnQkFDcEUsS0FBSyxJQUFJLFNBQVMsR0FBRyxDQUFDLEVBQUUsU0FBUyxHQUFHLFdBQVcsQ0FBQyxLQUFLLENBQUMsTUFBTSxFQUFFLFNBQVMsRUFBRSxFQUFFO29CQUN6RSxJQUFNLElBQUksR0FBRyxXQUFXLENBQUMsS0FBSyxDQUFDLFNBQVMsQ0FBQyxDQUFDO29CQUMxQyxJQUFJLElBQUksQ0FBQyxNQUFNLEdBQUcsV0FBVyxFQUFFO3dCQUM3QixPQUFPLFdBQVcsQ0FBQyxRQUFRLENBQUMsU0FBUyxDQUFDLENBQUM7cUJBQ3hDO29CQUNELFdBQVcsSUFBSSxJQUFJLENBQUMsTUFBTSxDQUFDO2lCQUM1QjthQUNGO1lBQ0QsT0FBTyxJQUFJLENBQUM7UUFDZCxDQUFDO1FBTUQsc0JBQVksOENBQVc7WUFKdkI7OztlQUdHO2lCQUNIO2dCQUNFLElBQUksSUFBSSxDQUFDLE1BQU0sQ0FBQyxNQUFNLElBQUksSUFBSSxDQUFDLE1BQU0sQ0FBQyxJQUFJLENBQUMsTUFBTSxDQUFDLE1BQU0sR0FBRyxDQUFDLENBQUMsQ0FBQyxLQUFLLENBQUMsTUFBTSxLQUFLLENBQUMsRUFBRTtvQkFDaEYsT0FBTyxJQUFJLENBQUMsTUFBTSxDQUFDLEtBQUssQ0FBQyxDQUFDLEVBQUUsQ0FBQyxDQUFDLENBQUMsQ0FBQztpQkFDakM7Z0JBQ0QsT0FBTyxJQUFJLENBQUMsTUFBTSxDQUFDO1lBQ3JCLENBQUM7OztXQUFBO1FBQ0gsNEJBQUM7SUFBRCxDQUFDLEFBMUtELElBMEtDO0lBMUtZLHNEQUFxQjtJQTRLbEM7UUFDRSxnQ0FBb0Isc0JBQStCO1lBQS9CLDJCQUFzQixHQUF0QixzQkFBc0IsQ0FBUztRQUFHLENBQUM7UUFFN0MscURBQW9CLEdBQTlCLFVBQStCLElBQWlCLEVBQUUsR0FBMEI7O1lBQzFFLElBQUksSUFBSSxDQUFDLGVBQWUsS0FBSyxTQUFTLEVBQUU7Z0JBQ3RDLE9BQU87YUFDUjs7Z0JBQ0QsS0FBc0IsSUFBQSxLQUFBLGlCQUFBLElBQUksQ0FBQyxlQUFlLENBQUEsZ0JBQUEsNEJBQUU7b0JBQXZDLElBQU0sT0FBTyxXQUFBO29CQUNoQixJQUFJLE9BQU8sWUFBWSxDQUFDLENBQUMsWUFBWSxFQUFFO3dCQUNyQyxHQUFHLENBQUMsS0FBSyxDQUFDLElBQUksRUFBRSxPQUFLLE9BQU8sQ0FBQyxRQUFRLEVBQUUsT0FBSSxFQUFFLE9BQU8sQ0FBQyxlQUFlLENBQUMsQ0FBQztxQkFDdkU7eUJBQU07d0JBQ0wsSUFBSSxPQUFPLENBQUMsU0FBUyxFQUFFOzRCQUNyQixHQUFHLENBQUMsS0FBSyxDQUFDLElBQUksRUFBRSxRQUFNLE9BQU8sQ0FBQyxJQUFJLFFBQUssRUFBRSxPQUFPLENBQUMsZUFBZSxDQUFDLENBQUM7eUJBQ25FOzZCQUFNOzRCQUNMLE9BQU8sQ0FBQyxJQUFJLENBQUMsS0FBSyxDQUFDLElBQUksQ0FBQyxDQUFDLE9BQU8sQ0FBQyxVQUFDLElBQUk7Z0NBQ3BDLEdBQUcsQ0FBQyxPQUFPLENBQUMsSUFBSSxFQUFFLFFBQU0sSUFBTSxDQUFDLENBQUM7NEJBQ2xDLENBQUMsQ0FBQyxDQUFDO3lCQUNKO3FCQUNGO2lCQUNGOzs7Ozs7Ozs7UUFDSCxDQUFDO1FBRUQsb0RBQW1CLEdBQW5CLFVBQW9CLElBQTJCLEVBQUUsR0FBMEI7WUFDekUsSUFBSSxDQUFDLG9CQUFvQixDQUFDLElBQUksRUFBRSxHQUFHLENBQUMsQ0FBQztZQUNyQyxJQUFJLENBQUMsSUFBSSxDQUFDLGVBQWUsQ0FBQyxJQUFJLEVBQUUsR0FBRyxDQUFDLENBQUM7WUFDckMsR0FBRyxDQUFDLE9BQU8sQ0FBQyxJQUFJLEVBQUUsR0FBRyxDQUFDLENBQUM7WUFDdkIsT0FBTyxJQUFJLENBQUM7UUFDZCxDQUFDO1FBRUQsZ0RBQWUsR0FBZixVQUFnQixJQUF1QixFQUFFLEdBQTBCO1lBQ2pFLElBQUksQ0FBQyxvQkFBb0IsQ0FBQyxJQUFJLEVBQUUsR0FBRyxDQUFDLENBQUM7WUFDckMsR0FBRyxDQUFDLEtBQUssQ0FBQyxJQUFJLEVBQUUsU0FBUyxDQUFDLENBQUM7WUFDM0IsSUFBSSxDQUFDLEtBQUssQ0FBQyxlQUFlLENBQUMsSUFBSSxFQUFFLEdBQUcsQ0FBQyxDQUFDO1lBQ3RDLEdBQUcsQ0FBQyxPQUFPLENBQUMsSUFBSSxFQUFFLEdBQUcsQ0FBQyxDQUFDO1lBQ3ZCLE9BQU8sSUFBSSxDQUFDO1FBQ2QsQ0FBQztRQU1ELDRDQUFXLEdBQVgsVUFBWSxJQUFjLEVBQUUsR0FBMEI7WUFDcEQsSUFBSSxDQUFDLG9CQUFvQixDQUFDLElBQUksRUFBRSxHQUFHLENBQUMsQ0FBQztZQUNyQyxHQUFHLENBQUMsS0FBSyxDQUFDLElBQUksRUFBRSxNQUFNLENBQUMsQ0FBQztZQUN4QixJQUFJLENBQUMsU0FBUyxDQUFDLGVBQWUsQ0FBQyxJQUFJLEVBQUUsR0FBRyxDQUFDLENBQUM7WUFDMUMsR0FBRyxDQUFDLEtBQUssQ0FBQyxJQUFJLEVBQUUsS0FBSyxDQUFDLENBQUM7WUFDdkIsSUFBTSxXQUFXLEdBQUcsSUFBSSxDQUFDLFNBQVMsSUFBSSxJQUFJLElBQUksSUFBSSxDQUFDLFNBQVMsQ0FBQyxNQUFNLEdBQUcsQ0FBQyxDQUFDO1lBQ3hFLElBQUksSUFBSSxDQUFDLFFBQVEsQ0FBQyxNQUFNLElBQUksQ0FBQyxJQUFJLENBQUMsV0FBVyxFQUFFO2dCQUM3QyxHQUFHLENBQUMsS0FBSyxDQUFDLElBQUksRUFBRSxHQUFHLENBQUMsQ0FBQztnQkFDckIsSUFBSSxDQUFDLGtCQUFrQixDQUFDLElBQUksQ0FBQyxRQUFRLEVBQUUsR0FBRyxDQUFDLENBQUM7Z0JBQzVDLEdBQUcsQ0FBQyxtQkFBbUIsRUFBRSxDQUFDO2dCQUMxQixHQUFHLENBQUMsS0FBSyxDQUFDLElBQUksRUFBRSxHQUFHLENBQUMsQ0FBQzthQUN0QjtpQkFBTTtnQkFDTCxHQUFHLENBQUMsT0FBTyxFQUFFLENBQUM7Z0JBQ2QsR0FBRyxDQUFDLFNBQVMsRUFBRSxDQUFDO2dCQUNoQixJQUFJLENBQUMsa0JBQWtCLENBQUMsSUFBSSxDQUFDLFFBQVEsRUFBRSxHQUFHLENBQUMsQ0FBQztnQkFDNUMsR0FBRyxDQUFDLFNBQVMsRUFBRSxDQUFDO2dCQUNoQixJQUFJLFdBQVcsRUFBRTtvQkFDZixHQUFHLENBQUMsT0FBTyxDQUFDLElBQUksRUFBRSxVQUFVLENBQUMsQ0FBQztvQkFDOUIsR0FBRyxDQUFDLFNBQVMsRUFBRSxDQUFDO29CQUNoQixJQUFJLENBQUMsa0JBQWtCLENBQUMsSUFBSSxDQUFDLFNBQVMsRUFBRSxHQUFHLENBQUMsQ0FBQztvQkFDN0MsR0FBRyxDQUFDLFNBQVMsRUFBRSxDQUFDO2lCQUNqQjthQUNGO1lBQ0QsR0FBRyxDQUFDLE9BQU8sQ0FBQyxJQUFJLEVBQUUsR0FBRyxDQUFDLENBQUM7WUFDdkIsT0FBTyxJQUFJLENBQUM7UUFDZCxDQUFDO1FBSUQsK0NBQWMsR0FBZCxVQUFlLElBQWlCLEVBQUUsR0FBMEI7WUFDMUQsSUFBSSxDQUFDLG9CQUFvQixDQUFDLElBQUksRUFBRSxHQUFHLENBQUMsQ0FBQztZQUNyQyxHQUFHLENBQUMsS0FBSyxDQUFDLElBQUksRUFBRSxRQUFRLENBQUMsQ0FBQztZQUMxQixJQUFJLENBQUMsS0FBSyxDQUFDLGVBQWUsQ0FBQyxJQUFJLEVBQUUsR0FBRyxDQUFDLENBQUM7WUFDdEMsR0FBRyxDQUFDLE9BQU8sQ0FBQyxJQUFJLEVBQUUsR0FBRyxDQUFDLENBQUM7WUFDdkIsT0FBTyxJQUFJLENBQUM7UUFDZCxDQUFDO1FBSUQsa0RBQWlCLEdBQWpCLFVBQWtCLElBQW9CLEVBQUUsR0FBMEI7WUFDaEUsSUFBTSxZQUFZLEdBQUcsR0FBRyxDQUFDLFdBQVcsRUFBRSxDQUFDO1lBQ3ZDLElBQUksQ0FBQyxZQUFZLEVBQUU7Z0JBQ2pCLEdBQUcsQ0FBQyxLQUFLLENBQUMsSUFBSSxFQUFFLEdBQUcsQ0FBQyxDQUFDO2FBQ3RCO1lBQ0QsR0FBRyxDQUFDLEtBQUssQ0FBQyxJQUFJLEVBQUssSUFBSSxDQUFDLElBQUksUUFBSyxDQUFDLENBQUM7WUFDbkMsSUFBSSxDQUFDLEtBQUssQ0FBQyxlQUFlLENBQUMsSUFBSSxFQUFFLEdBQUcsQ0FBQyxDQUFDO1lBQ3RDLElBQUksQ0FBQyxZQUFZLEVBQUU7Z0JBQ2pCLEdBQUcsQ0FBQyxLQUFLLENBQUMsSUFBSSxFQUFFLEdBQUcsQ0FBQyxDQUFDO2FBQ3RCO1lBQ0QsT0FBTyxJQUFJLENBQUM7UUFDZCxDQUFDO1FBQ0Qsa0RBQWlCLEdBQWpCLFVBQWtCLElBQW9CLEVBQUUsR0FBMEI7WUFDaEUsSUFBTSxZQUFZLEdBQUcsR0FBRyxDQUFDLFdBQVcsRUFBRSxDQUFDO1lBQ3ZDLElBQUksQ0FBQyxZQUFZLEVBQUU7Z0JBQ2pCLEdBQUcsQ0FBQyxLQUFLLENBQUMsSUFBSSxFQUFFLEdBQUcsQ0FBQyxDQUFDO2FBQ3RCO1lBQ0QsSUFBSSxDQUFDLFFBQVEsQ0FBQyxlQUFlLENBQUMsSUFBSSxFQUFFLEdBQUcsQ0FBQyxDQUFDO1lBQ3pDLEdBQUcsQ0FBQyxLQUFLLENBQUMsSUFBSSxFQUFFLEdBQUcsQ0FBQyxDQUFDO1lBQ3JCLElBQUksQ0FBQyxLQUFLLENBQUMsZUFBZSxDQUFDLElBQUksRUFBRSxHQUFHLENBQUMsQ0FBQztZQUN0QyxHQUFHLENBQUMsS0FBSyxDQUFDLElBQUksRUFBRSxNQUFNLENBQUMsQ0FBQztZQUN4QixJQUFJLENBQUMsS0FBSyxDQUFDLGVBQWUsQ0FBQyxJQUFJLEVBQUUsR0FBRyxDQUFDLENBQUM7WUFDdEMsSUFBSSxDQUFDLFlBQVksRUFBRTtnQkFDakIsR0FBRyxDQUFDLEtBQUssQ0FBQyxJQUFJLEVBQUUsR0FBRyxDQUFDLENBQUM7YUFDdEI7WUFDRCxPQUFPLElBQUksQ0FBQztRQUNkLENBQUM7UUFDRCxtREFBa0IsR0FBbEIsVUFBbUIsSUFBcUIsRUFBRSxHQUEwQjtZQUNsRSxJQUFNLFlBQVksR0FBRyxHQUFHLENBQUMsV0FBVyxFQUFFLENBQUM7WUFDdkMsSUFBSSxDQUFDLFlBQVksRUFBRTtnQkFDakIsR0FBRyxDQUFDLEtBQUssQ0FBQyxJQUFJLEVBQUUsR0FBRyxDQUFDLENBQUM7YUFDdEI7WUFDRCxJQUFJLENBQUMsUUFBUSxDQUFDLGVBQWUsQ0FBQyxJQUFJLEVBQUUsR0FBRyxDQUFDLENBQUM7WUFDekMsR0FBRyxDQUFDLEtBQUssQ0FBQyxJQUFJLEVBQUUsTUFBSSxJQUFJLENBQUMsSUFBSSxRQUFLLENBQUMsQ0FBQztZQUNwQyxJQUFJLENBQUMsS0FBSyxDQUFDLGVBQWUsQ0FBQyxJQUFJLEVBQUUsR0FBRyxDQUFDLENBQUM7WUFDdEMsSUFBSSxDQUFDLFlBQVksRUFBRTtnQkFDakIsR0FBRyxDQUFDLEtBQUssQ0FBQyxJQUFJLEVBQUUsR0FBRyxDQUFDLENBQUM7YUFDdEI7WUFDRCxPQUFPLElBQUksQ0FBQztRQUNkLENBQUM7UUFDRCxzREFBcUIsR0FBckIsVUFBc0IsSUFBd0IsRUFBRSxHQUEwQjtZQUN4RSxJQUFJLENBQUMsUUFBUSxDQUFDLGVBQWUsQ0FBQyxJQUFJLEVBQUUsR0FBRyxDQUFDLENBQUM7WUFDekMsSUFBSSxJQUFJLEdBQUcsSUFBSSxDQUFDLElBQUksQ0FBQztZQUNyQixJQUFJLElBQUksQ0FBQyxPQUFPLElBQUksSUFBSSxFQUFFO2dCQUN4QixJQUFJLEdBQUcsSUFBSSxDQUFDLG9CQUFvQixDQUFDLElBQUksQ0FBQyxPQUFPLENBQUMsQ0FBQztnQkFDL0MsSUFBSSxJQUFJLElBQUksSUFBSSxFQUFFO29CQUNoQiw0Q0FBNEM7b0JBQzVDLE9BQU8sSUFBSSxDQUFDO2lCQUNiO2FBQ0Y7WUFDRCxHQUFHLENBQUMsS0FBSyxDQUFDLElBQUksRUFBRSxNQUFJLElBQUksTUFBRyxDQUFDLENBQUM7WUFDN0IsSUFBSSxDQUFDLG1CQUFtQixDQUFDLElBQUksQ0FBQyxJQUFJLEVBQUUsR0FBRyxFQUFFLEdBQUcsQ0FBQyxDQUFDO1lBQzlDLEdBQUcsQ0FBQyxLQUFLLENBQUMsSUFBSSxFQUFFLEdBQUcsQ0FBQyxDQUFDO1lBQ3JCLE9BQU8sSUFBSSxDQUFDO1FBQ2QsQ0FBQztRQUlELHdEQUF1QixHQUF2QixVQUF3QixJQUEwQixFQUFFLEdBQTBCO1lBQzVFLElBQUksQ0FBQyxFQUFFLENBQUMsZUFBZSxDQUFDLElBQUksRUFBRSxHQUFHLENBQUMsQ0FBQztZQUNuQyxHQUFHLENBQUMsS0FBSyxDQUFDLElBQUksRUFBRSxHQUFHLENBQUMsQ0FBQztZQUNyQixJQUFJLENBQUMsbUJBQW1CLENBQUMsSUFBSSxDQUFDLElBQUksRUFBRSxHQUFHLEVBQUUsR0FBRyxDQUFDLENBQUM7WUFDOUMsR0FBRyxDQUFDLEtBQUssQ0FBQyxJQUFJLEVBQUUsR0FBRyxDQUFDLENBQUM7WUFDckIsT0FBTyxJQUFJLENBQUM7UUFDZCxDQUFDO1FBQ0Qsd0RBQXVCLEdBQXZCLFVBQXdCLElBQTBCLEVBQUUsR0FBMEI7WUFDNUUsSUFBSSxDQUFDLEdBQUcsQ0FBQyxlQUFlLENBQUMsSUFBSSxFQUFFLEdBQUcsQ0FBQyxDQUFDO1lBQ3BDLEdBQUcsQ0FBQyxLQUFLLENBQUMsSUFBSSxFQUFFLEdBQUcsR0FBRyxJQUFJLENBQUMsUUFBUSxDQUFDLFFBQVEsQ0FBQyxDQUFDLENBQUMsQ0FBQyxPQUFPLENBQUMsQ0FBQztZQUN6RCxLQUFLLElBQUksQ0FBQyxHQUFHLENBQUMsRUFBRSxDQUFDLEdBQUcsSUFBSSxDQUFDLFFBQVEsQ0FBQyxRQUFRLENBQUMsTUFBTSxFQUFFLENBQUMsRUFBRSxFQUFFO2dCQUN0RCxHQUFHLENBQUMsS0FBSyxDQUFDLElBQUksRUFBRSxJQUFJLENBQUMsQ0FBQztnQkFDdEIsSUFBSSxDQUFDLFFBQVEsQ0FBQyxXQUFXLENBQUMsQ0FBQyxHQUFHLENBQUMsQ0FBQyxDQUFDLGVBQWUsQ0FBQyxJQUFJLEVBQUUsR0FBRyxDQUFDLENBQUM7Z0JBQzVELEdBQUcsQ0FBQyxLQUFLLENBQUMsSUFBSSxFQUFFLE1BQUksSUFBSSxDQUFDLFFBQVEsQ0FBQyxRQUFRLENBQUMsQ0FBQyxDQUFDLENBQUMsT0FBUyxDQUFDLENBQUM7YUFDMUQ7WUFDRCxHQUFHLENBQUMsS0FBSyxDQUFDLElBQUksRUFBRSxHQUFHLENBQUMsQ0FBQztZQUNyQixPQUFPLElBQUksQ0FBQztRQUNkLENBQUM7UUFDRCxxREFBb0IsR0FBcEIsVUFBcUIsR0FBMkIsRUFBRSxHQUEwQjtZQUMxRSxNQUFNLElBQUksS0FBSyxDQUFDLGdEQUFnRCxDQUFDLENBQUM7UUFDcEUsQ0FBQztRQUNELGdEQUFlLEdBQWYsVUFBZ0IsSUFBa0IsRUFBRSxHQUEwQjtZQUM1RCxHQUFHLENBQUMsS0FBSyxDQUFDLElBQUksRUFBRSxTQUFTLENBQUMsQ0FBQztZQUMzQixJQUFJLENBQUMsSUFBSSxDQUFDLGVBQWUsQ0FBQyxJQUFJLEVBQUUsR0FBRyxDQUFDLENBQUM7UUFDdkMsQ0FBQztRQUNELGlEQUFnQixHQUFoQixVQUFpQixHQUFrQixFQUFFLEdBQTBCO1lBQzdELElBQUksT0FBTyxHQUFHLEdBQUcsQ0FBQyxJQUFLLENBQUM7WUFDeEIsSUFBSSxHQUFHLENBQUMsT0FBTyxJQUFJLElBQUksRUFBRTtnQkFDdkIsUUFBUSxHQUFHLENBQUMsT0FBTyxFQUFFO29CQUNuQixLQUFLLENBQUMsQ0FBQyxVQUFVLENBQUMsS0FBSzt3QkFDckIsT0FBTyxHQUFHLE9BQU8sQ0FBQzt3QkFDbEIsTUFBTTtvQkFDUixLQUFLLENBQUMsQ0FBQyxVQUFVLENBQUMsSUFBSTt3QkFDcEIsT0FBTyxHQUFHLE1BQU0sQ0FBQzt3QkFDakIsTUFBTTtvQkFDUixLQUFLLENBQUMsQ0FBQyxVQUFVLENBQUMsVUFBVTt3QkFDMUIsT0FBTyxHQUFHLHVCQUFlLENBQUMsSUFBSyxDQUFDO3dCQUNoQyxNQUFNO29CQUNSLEtBQUssQ0FBQyxDQUFDLFVBQVUsQ0FBQyxVQUFVO3dCQUMxQixPQUFPLEdBQUcsdUJBQWUsQ0FBQyxJQUFLLENBQUM7d0JBQ2hDLE1BQU07b0JBQ1I7d0JBQ0UsTUFBTSxJQUFJLEtBQUssQ0FBQyw4QkFBNEIsR0FBRyxDQUFDLE9BQVMsQ0FBQyxDQUFDO2lCQUM5RDthQUNGO1lBQ0QsR0FBRyxDQUFDLEtBQUssQ0FBQyxHQUFHLEVBQUUsT0FBTyxDQUFDLENBQUM7WUFDeEIsT0FBTyxJQUFJLENBQUM7UUFDZCxDQUFDO1FBQ0QscURBQW9CLEdBQXBCLFVBQXFCLEdBQXNCLEVBQUUsR0FBMEI7WUFDckUsR0FBRyxDQUFDLEtBQUssQ0FBQyxHQUFHLEVBQUUsTUFBTSxDQUFDLENBQUM7WUFDdkIsR0FBRyxDQUFDLFNBQVMsQ0FBQyxlQUFlLENBQUMsSUFBSSxFQUFFLEdBQUcsQ0FBQyxDQUFDO1lBQ3pDLEdBQUcsQ0FBQyxLQUFLLENBQUMsR0FBRyxFQUFFLEdBQUcsQ0FBQyxDQUFDO1lBQ3BCLElBQUksQ0FBQyxtQkFBbUIsQ0FBQyxHQUFHLENBQUMsSUFBSSxFQUFFLEdBQUcsRUFBRSxHQUFHLENBQUMsQ0FBQztZQUM3QyxHQUFHLENBQUMsS0FBSyxDQUFDLEdBQUcsRUFBRSxHQUFHLENBQUMsQ0FBQztZQUNwQixPQUFPLElBQUksQ0FBQztRQUNkLENBQUM7UUFFRCxpREFBZ0IsR0FBaEIsVUFBaUIsR0FBa0IsRUFBRSxHQUEwQjtZQUM3RCxJQUFNLEtBQUssR0FBRyxHQUFHLENBQUMsS0FBSyxDQUFDO1lBQ3hCLElBQUksT0FBTyxLQUFLLEtBQUssUUFBUSxFQUFFO2dCQUM3QixHQUFHLENBQUMsS0FBSyxDQUFDLEdBQUcsRUFBRSxnQkFBZ0IsQ0FBQyxLQUFLLEVBQUUsSUFBSSxDQUFDLHNCQUFzQixDQUFDLENBQUMsQ0FBQzthQUN0RTtpQkFBTTtnQkFDTCxHQUFHLENBQUMsS0FBSyxDQUFDLEdBQUcsRUFBRSxLQUFHLEtBQU8sQ0FBQyxDQUFDO2FBQzVCO1lBQ0QsT0FBTyxJQUFJLENBQUM7UUFDZCxDQUFDO1FBRUQscURBQW9CLEdBQXBCLFVBQXFCLEdBQXNCLEVBQUUsR0FBMEI7WUFDckUsSUFBTSxJQUFJLEdBQUcsR0FBRyxDQUFDLGlCQUFpQixFQUFFLENBQUM7WUFDckMsR0FBRyxDQUFDLEtBQUssQ0FBQyxHQUFHLEVBQUUsYUFBYSxHQUFHLElBQUksQ0FBQyxHQUFHLENBQUMsQ0FBQztZQUN6QyxLQUFLLElBQUksQ0FBQyxHQUFHLENBQUMsRUFBRSxDQUFDLEdBQUcsR0FBRyxDQUFDLFlBQVksQ0FBQyxNQUFNLEVBQUUsQ0FBQyxFQUFFLEVBQUU7Z0JBQ2hELEdBQUcsQ0FBQyxLQUFLLENBQUMsR0FBRyxFQUFFLElBQUksQ0FBQyxDQUFDO2dCQUNyQixHQUFHLENBQUMsV0FBVyxDQUFDLENBQUMsR0FBRyxDQUFDLENBQUMsQ0FBQyxlQUFlLENBQUMsSUFBSSxFQUFFLEdBQUcsQ0FBQyxDQUFDO2dCQUNsRCxHQUFHLENBQUMsS0FBSyxDQUFDLEdBQUcsRUFBRSxNQUFJLEdBQUcsQ0FBQyx5QkFBeUIsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFLLENBQUMsQ0FBQzthQUM1RDtZQUNELEdBQUcsQ0FBQyxLQUFLLENBQUMsR0FBRyxFQUFFLEdBQUcsQ0FBQyxDQUFDO1lBQ3BCLE9BQU8sSUFBSSxDQUFDO1FBQ2QsQ0FBQztRQUlELHFEQUFvQixHQUFwQixVQUFxQixHQUFzQixFQUFFLEdBQTBCO1lBQ3JFLEdBQUcsQ0FBQyxLQUFLLENBQUMsR0FBRyxFQUFFLEdBQUcsQ0FBQyxDQUFDO1lBQ3BCLEdBQUcsQ0FBQyxTQUFTLENBQUMsZUFBZSxDQUFDLElBQUksRUFBRSxHQUFHLENBQUMsQ0FBQztZQUN6QyxHQUFHLENBQUMsS0FBSyxDQUFDLEdBQUcsRUFBRSxJQUFJLENBQUMsQ0FBQztZQUNyQixHQUFHLENBQUMsUUFBUSxDQUFDLGVBQWUsQ0FBQyxJQUFJLEVBQUUsR0FBRyxDQUFDLENBQUM7WUFDeEMsR0FBRyxDQUFDLEtBQUssQ0FBQyxHQUFHLEVBQUUsSUFBSSxDQUFDLENBQUM7WUFDckIsR0FBRyxDQUFDLFNBQVUsQ0FBQyxlQUFlLENBQUMsSUFBSSxFQUFFLEdBQUcsQ0FBQyxDQUFDO1lBQzFDLEdBQUcsQ0FBQyxLQUFLLENBQUMsR0FBRyxFQUFFLEdBQUcsQ0FBQyxDQUFDO1lBQ3BCLE9BQU8sSUFBSSxDQUFDO1FBQ2QsQ0FBQztRQUNELDZDQUFZLEdBQVosVUFBYSxHQUFjLEVBQUUsR0FBMEI7WUFDckQsR0FBRyxDQUFDLEtBQUssQ0FBQyxHQUFHLEVBQUUsR0FBRyxDQUFDLENBQUM7WUFDcEIsR0FBRyxDQUFDLFNBQVMsQ0FBQyxlQUFlLENBQUMsSUFBSSxFQUFFLEdBQUcsQ0FBQyxDQUFDO1lBQ3pDLE9BQU8sSUFBSSxDQUFDO1FBQ2QsQ0FBQztRQUNELHVEQUFzQixHQUF0QixVQUF1QixHQUFvQixFQUFFLEdBQTBCO1lBQ3JFLEdBQUcsQ0FBQyxTQUFTLENBQUMsZUFBZSxDQUFDLElBQUksRUFBRSxHQUFHLENBQUMsQ0FBQztZQUN6QyxPQUFPLElBQUksQ0FBQztRQUNkLENBQUM7UUFJRCx1REFBc0IsR0FBdEIsVUFBdUIsR0FBd0IsRUFBRSxHQUEwQjtZQUN6RSxJQUFJLEtBQWEsQ0FBQztZQUNsQixRQUFRLEdBQUcsQ0FBQyxRQUFRLEVBQUU7Z0JBQ3BCLEtBQUssQ0FBQyxDQUFDLGFBQWEsQ0FBQyxJQUFJO29CQUN2QixLQUFLLEdBQUcsR0FBRyxDQUFDO29CQUNaLE1BQU07Z0JBQ1IsS0FBSyxDQUFDLENBQUMsYUFBYSxDQUFDLEtBQUs7b0JBQ3hCLEtBQUssR0FBRyxHQUFHLENBQUM7b0JBQ1osTUFBTTtnQkFDUjtvQkFDRSxNQUFNLElBQUksS0FBSyxDQUFDLHNCQUFvQixHQUFHLENBQUMsUUFBVSxDQUFDLENBQUM7YUFDdkQ7WUFDRCxJQUFJLEdBQUcsQ0FBQyxNQUFNO2dCQUFFLEdBQUcsQ0FBQyxLQUFLLENBQUMsR0FBRyxFQUFFLEdBQUcsQ0FBQyxDQUFDO1lBQ3BDLEdBQUcsQ0FBQyxLQUFLLENBQUMsR0FBRyxFQUFFLEtBQUssQ0FBQyxDQUFDO1lBQ3RCLEdBQUcsQ0FBQyxJQUFJLENBQUMsZUFBZSxDQUFDLElBQUksRUFBRSxHQUFHLENBQUMsQ0FBQztZQUNwQyxJQUFJLEdBQUcsQ0FBQyxNQUFNO2dCQUFFLEdBQUcsQ0FBQyxLQUFLLENBQUMsR0FBRyxFQUFFLEdBQUcsQ0FBQyxDQUFDO1lBQ3BDLE9BQU8sSUFBSSxDQUFDO1FBQ2QsQ0FBQztRQUVELHdEQUF1QixHQUF2QixVQUF3QixHQUF5QixFQUFFLEdBQTBCO1lBQzNFLElBQUksS0FBYSxDQUFDO1lBQ2xCLFFBQVEsR0FBRyxDQUFDLFFBQVEsRUFBRTtnQkFDcEIsS0FBSyxDQUFDLENBQUMsY0FBYyxDQUFDLE1BQU07b0JBQzFCLEtBQUssR0FBRyxJQUFJLENBQUM7b0JBQ2IsTUFBTTtnQkFDUixLQUFLLENBQUMsQ0FBQyxjQUFjLENBQUMsU0FBUztvQkFDN0IsS0FBSyxHQUFHLEtBQUssQ0FBQztvQkFDZCxNQUFNO2dCQUNSLEtBQUssQ0FBQyxDQUFDLGNBQWMsQ0FBQyxTQUFTO29CQUM3QixLQUFLLEdBQUcsSUFBSSxDQUFDO29CQUNiLE1BQU07Z0JBQ1IsS0FBSyxDQUFDLENBQUMsY0FBYyxDQUFDLFlBQVk7b0JBQ2hDLEtBQUssR0FBRyxLQUFLLENBQUM7b0JBQ2QsTUFBTTtnQkFDUixLQUFLLENBQUMsQ0FBQyxjQUFjLENBQUMsR0FBRztvQkFDdkIsS0FBSyxHQUFHLElBQUksQ0FBQztvQkFDYixNQUFNO2dCQUNSLEtBQUssQ0FBQyxDQUFDLGNBQWMsQ0FBQyxVQUFVO29CQUM5QixLQUFLLEdBQUcsR0FBRyxDQUFDO29CQUNaLE1BQU07Z0JBQ1IsS0FBSyxDQUFDLENBQUMsY0FBYyxDQUFDLEVBQUU7b0JBQ3RCLEtBQUssR0FBRyxJQUFJLENBQUM7b0JBQ2IsTUFBTTtnQkFDUixLQUFLLENBQUMsQ0FBQyxjQUFjLENBQUMsSUFBSTtvQkFDeEIsS0FBSyxHQUFHLEdBQUcsQ0FBQztvQkFDWixNQUFNO2dCQUNSLEtBQUssQ0FBQyxDQUFDLGNBQWMsQ0FBQyxLQUFLO29CQUN6QixLQUFLLEdBQUcsR0FBRyxDQUFDO29CQUNaLE1BQU07Z0JBQ1IsS0FBSyxDQUFDLENBQUMsY0FBYyxDQUFDLE1BQU07b0JBQzFCLEtBQUssR0FBRyxHQUFHLENBQUM7b0JBQ1osTUFBTTtnQkFDUixLQUFLLENBQUMsQ0FBQyxjQUFjLENBQUMsUUFBUTtvQkFDNUIsS0FBSyxHQUFHLEdBQUcsQ0FBQztvQkFDWixNQUFNO2dCQUNSLEtBQUssQ0FBQyxDQUFDLGNBQWMsQ0FBQyxNQUFNO29CQUMxQixLQUFLLEdBQUcsR0FBRyxDQUFDO29CQUNaLE1BQU07Z0JBQ1IsS0FBSyxDQUFDLENBQUMsY0FBYyxDQUFDLEtBQUs7b0JBQ3pCLEtBQUssR0FBRyxHQUFHLENBQUM7b0JBQ1osTUFBTTtnQkFDUixLQUFLLENBQUMsQ0FBQyxjQUFjLENBQUMsV0FBVztvQkFDL0IsS0FBSyxHQUFHLElBQUksQ0FBQztvQkFDYixNQUFNO2dCQUNSLEtBQUssQ0FBQyxDQUFDLGNBQWMsQ0FBQyxNQUFNO29CQUMxQixLQUFLLEdBQUcsR0FBRyxDQUFDO29CQUNaLE1BQU07Z0JBQ1IsS0FBSyxDQUFDLENBQUMsY0FBYyxDQUFDLFlBQVk7b0JBQ2hDLEtBQUssR0FBRyxJQUFJLENBQUM7b0JBQ2IsTUFBTTtnQkFDUjtvQkFDRSxNQUFNLElBQUksS0FBSyxDQUFDLHNCQUFvQixHQUFHLENBQUMsUUFBVSxDQUFDLENBQUM7YUFDdkQ7WUFDRCxJQUFJLEdBQUcsQ0FBQyxNQUFNO2dCQUFFLEdBQUcsQ0FBQyxLQUFLLENBQUMsR0FBRyxFQUFFLEdBQUcsQ0FBQyxDQUFDO1lBQ3BDLEdBQUcsQ0FBQyxHQUFHLENBQUMsZUFBZSxDQUFDLElBQUksRUFBRSxHQUFHLENBQUMsQ0FBQztZQUNuQyxHQUFHLENBQUMsS0FBSyxDQUFDLEdBQUcsRUFBRSxNQUFJLEtBQUssTUFBRyxDQUFDLENBQUM7WUFDN0IsR0FBRyxDQUFDLEdBQUcsQ0FBQyxlQUFlLENBQUMsSUFBSSxFQUFFLEdBQUcsQ0FBQyxDQUFDO1lBQ25DLElBQUksR0FBRyxDQUFDLE1BQU07Z0JBQUUsR0FBRyxDQUFDLEtBQUssQ0FBQyxHQUFHLEVBQUUsR0FBRyxDQUFDLENBQUM7WUFDcEMsT0FBTyxJQUFJLENBQUM7UUFDZCxDQUFDO1FBRUQsa0RBQWlCLEdBQWpCLFVBQWtCLEdBQW1CLEVBQUUsR0FBMEI7WUFDL0QsR0FBRyxDQUFDLFFBQVEsQ0FBQyxlQUFlLENBQUMsSUFBSSxFQUFFLEdBQUcsQ0FBQyxDQUFDO1lBQ3hDLEdBQUcsQ0FBQyxLQUFLLENBQUMsR0FBRyxFQUFFLEdBQUcsQ0FBQyxDQUFDO1lBQ3BCLEdBQUcsQ0FBQyxLQUFLLENBQUMsR0FBRyxFQUFFLEdBQUcsQ0FBQyxJQUFJLENBQUMsQ0FBQztZQUN6QixPQUFPLElBQUksQ0FBQztRQUNkLENBQUM7UUFDRCxpREFBZ0IsR0FBaEIsVUFBaUIsR0FBa0IsRUFBRSxHQUEwQjtZQUM3RCxHQUFHLENBQUMsUUFBUSxDQUFDLGVBQWUsQ0FBQyxJQUFJLEVBQUUsR0FBRyxDQUFDLENBQUM7WUFDeEMsR0FBRyxDQUFDLEtBQUssQ0FBQyxHQUFHLEVBQUUsR0FBRyxDQUFDLENBQUM7WUFDcEIsR0FBRyxDQUFDLEtBQUssQ0FBQyxlQUFlLENBQUMsSUFBSSxFQUFFLEdBQUcsQ0FBQyxDQUFDO1lBQ3JDLEdBQUcsQ0FBQyxLQUFLLENBQUMsR0FBRyxFQUFFLEdBQUcsQ0FBQyxDQUFDO1lBQ3BCLE9BQU8sSUFBSSxDQUFDO1FBQ2QsQ0FBQztRQUNELHNEQUFxQixHQUFyQixVQUFzQixHQUF1QixFQUFFLEdBQTBCO1lBQ3ZFLEdBQUcsQ0FBQyxLQUFLLENBQUMsR0FBRyxFQUFFLEdBQUcsQ0FBQyxDQUFDO1lBQ3BCLElBQUksQ0FBQyxtQkFBbUIsQ0FBQyxHQUFHLENBQUMsT0FBTyxFQUFFLEdBQUcsRUFBRSxHQUFHLENBQUMsQ0FBQztZQUNoRCxHQUFHLENBQUMsS0FBSyxDQUFDLEdBQUcsRUFBRSxHQUFHLENBQUMsQ0FBQztZQUNwQixPQUFPLElBQUksQ0FBQztRQUNkLENBQUM7UUFDRCxvREFBbUIsR0FBbkIsVUFBb0IsR0FBcUIsRUFBRSxHQUEwQjtZQUFyRSxpQkFRQztZQVBDLEdBQUcsQ0FBQyxLQUFLLENBQUMsR0FBRyxFQUFFLEdBQUcsQ0FBQyxDQUFDO1lBQ3BCLElBQUksQ0FBQyxlQUFlLENBQUMsVUFBQSxLQUFLO2dCQUN4QixHQUFHLENBQUMsS0FBSyxDQUFDLEdBQUcsRUFBSyxnQkFBZ0IsQ0FBQyxLQUFLLENBQUMsR0FBRyxFQUFFLEtBQUksQ0FBQyxzQkFBc0IsRUFBRSxLQUFLLENBQUMsTUFBTSxDQUFDLE1BQUcsQ0FBQyxDQUFDO2dCQUM3RixLQUFLLENBQUMsS0FBSyxDQUFDLGVBQWUsQ0FBQyxLQUFJLEVBQUUsR0FBRyxDQUFDLENBQUM7WUFDekMsQ0FBQyxFQUFFLEdBQUcsQ0FBQyxPQUFPLEVBQUUsR0FBRyxFQUFFLEdBQUcsQ0FBQyxDQUFDO1lBQzFCLEdBQUcsQ0FBQyxLQUFLLENBQUMsR0FBRyxFQUFFLEdBQUcsQ0FBQyxDQUFDO1lBQ3BCLE9BQU8sSUFBSSxDQUFDO1FBQ2QsQ0FBQztRQUNELCtDQUFjLEdBQWQsVUFBZSxHQUFnQixFQUFFLEdBQTBCO1lBQ3pELEdBQUcsQ0FBQyxLQUFLLENBQUMsR0FBRyxFQUFFLEdBQUcsQ0FBQyxDQUFDO1lBQ3BCLElBQUksQ0FBQyxtQkFBbUIsQ0FBQyxHQUFHLENBQUMsS0FBSyxFQUFFLEdBQUcsRUFBRSxHQUFHLENBQUMsQ0FBQztZQUM5QyxHQUFHLENBQUMsS0FBSyxDQUFDLEdBQUcsRUFBRSxHQUFHLENBQUMsQ0FBQztZQUNwQixPQUFPLElBQUksQ0FBQztRQUNkLENBQUM7UUFDRCxvREFBbUIsR0FBbkIsVUFBb0IsV0FBMkIsRUFBRSxHQUEwQixFQUFFLFNBQWlCO1lBQTlGLGlCQUdDO1lBREMsSUFBSSxDQUFDLGVBQWUsQ0FBQyxVQUFBLElBQUksSUFBSSxPQUFBLElBQUksQ0FBQyxlQUFlLENBQUMsS0FBSSxFQUFFLEdBQUcsQ0FBQyxFQUEvQixDQUErQixFQUFFLFdBQVcsRUFBRSxHQUFHLEVBQUUsU0FBUyxDQUFDLENBQUM7UUFDN0YsQ0FBQztRQUVELGdEQUFlLEdBQWYsVUFDSSxPQUF1QixFQUFFLFdBQWdCLEVBQUUsR0FBMEIsRUFDckUsU0FBaUI7WUFDbkIsSUFBSSxpQkFBaUIsR0FBRyxLQUFLLENBQUM7WUFDOUIsS0FBSyxJQUFJLENBQUMsR0FBRyxDQUFDLEVBQUUsQ0FBQyxHQUFHLFdBQVcsQ0FBQyxNQUFNLEVBQUUsQ0FBQyxFQUFFLEVBQUU7Z0JBQzNDLElBQUksQ0FBQyxHQUFHLENBQUMsRUFBRTtvQkFDVCxJQUFJLEdBQUcsQ0FBQyxVQUFVLEVBQUUsR0FBRyxFQUFFLEVBQUU7d0JBQ3pCLEdBQUcsQ0FBQyxLQUFLLENBQUMsSUFBSSxFQUFFLFNBQVMsRUFBRSxJQUFJLENBQUMsQ0FBQzt3QkFDakMsSUFBSSxDQUFDLGlCQUFpQixFQUFFOzRCQUN0Qiw4Q0FBOEM7NEJBQzlDLEdBQUcsQ0FBQyxTQUFTLEVBQUUsQ0FBQzs0QkFDaEIsR0FBRyxDQUFDLFNBQVMsRUFBRSxDQUFDOzRCQUNoQixpQkFBaUIsR0FBRyxJQUFJLENBQUM7eUJBQzFCO3FCQUNGO3lCQUFNO3dCQUNMLEdBQUcsQ0FBQyxLQUFLLENBQUMsSUFBSSxFQUFFLFNBQVMsRUFBRSxLQUFLLENBQUMsQ0FBQztxQkFDbkM7aUJBQ0Y7Z0JBQ0QsT0FBTyxDQUFDLFdBQVcsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDO2FBQ3pCO1lBQ0QsSUFBSSxpQkFBaUIsRUFBRTtnQkFDckIsOENBQThDO2dCQUM5QyxHQUFHLENBQUMsU0FBUyxFQUFFLENBQUM7Z0JBQ2hCLEdBQUcsQ0FBQyxTQUFTLEVBQUUsQ0FBQzthQUNqQjtRQUNILENBQUM7UUFFRCxtREFBa0IsR0FBbEIsVUFBbUIsVUFBeUIsRUFBRSxHQUEwQjtZQUF4RSxpQkFFQztZQURDLFVBQVUsQ0FBQyxPQUFPLENBQUMsVUFBQyxJQUFJLElBQUssT0FBQSxJQUFJLENBQUMsY0FBYyxDQUFDLEtBQUksRUFBRSxHQUFHLENBQUMsRUFBOUIsQ0FBOEIsQ0FBQyxDQUFDO1FBQy9ELENBQUM7UUFDSCw2QkFBQztJQUFELENBQUMsQUF2WUQsSUF1WUM7SUF2WXFCLHdEQUFzQjtJQXlZNUMsU0FBZ0IsZ0JBQWdCLENBQzVCLEtBQWEsRUFBRSxZQUFxQixFQUFFLFdBQTJCO1FBQTNCLDRCQUFBLEVBQUEsa0JBQTJCO1FBQ25FLElBQUksS0FBSyxJQUFJLElBQUksRUFBRTtZQUNqQixPQUFPLElBQUksQ0FBQztTQUNiO1FBQ0QsSUFBTSxJQUFJLEdBQUcsS0FBSyxDQUFDLE9BQU8sQ0FBQyw4QkFBOEIsRUFBRTtZQUFDLGVBQWtCO2lCQUFsQixVQUFrQixFQUFsQixxQkFBa0IsRUFBbEIsSUFBa0I7Z0JBQWxCLDBCQUFrQjs7WUFDNUUsSUFBSSxLQUFLLENBQUMsQ0FBQyxDQUFDLElBQUksR0FBRyxFQUFFO2dCQUNuQixPQUFPLFlBQVksQ0FBQyxDQUFDLENBQUMsS0FBSyxDQUFDLENBQUMsQ0FBQyxHQUFHLENBQUM7YUFDbkM7aUJBQU0sSUFBSSxLQUFLLENBQUMsQ0FBQyxDQUFDLElBQUksSUFBSSxFQUFFO2dCQUMzQixPQUFPLEtBQUssQ0FBQzthQUNkO2lCQUFNLElBQUksS0FBSyxDQUFDLENBQUMsQ0FBQyxJQUFJLElBQUksRUFBRTtnQkFDM0IsT0FBTyxLQUFLLENBQUM7YUFDZDtpQkFBTTtnQkFDTCxPQUFPLE9BQUssS0FBSyxDQUFDLENBQUMsQ0FBRyxDQUFDO2FBQ3hCO1FBQ0gsQ0FBQyxDQUFDLENBQUM7UUFDSCxJQUFNLGNBQWMsR0FBRyxXQUFXLElBQUksQ0FBQyxvQkFBb0IsQ0FBQyxJQUFJLENBQUMsSUFBSSxDQUFDLENBQUM7UUFDdkUsT0FBTyxjQUFjLENBQUMsQ0FBQyxDQUFDLE1BQUksSUFBSSxNQUFHLENBQUMsQ0FBQyxDQUFDLElBQUksQ0FBQztJQUM3QyxDQUFDO0lBbEJELDRDQWtCQztJQUVELFNBQVMsYUFBYSxDQUFDLEtBQWE7UUFDbEMsSUFBSSxHQUFHLEdBQUcsRUFBRSxDQUFDO1FBQ2IsS0FBSyxJQUFJLENBQUMsR0FBRyxDQUFDLEVBQUUsQ0FBQyxHQUFHLEtBQUssRUFBRSxDQUFDLEVBQUUsRUFBRTtZQUM5QixHQUFHLElBQUksWUFBWSxDQUFDO1NBQ3JCO1FBQ0QsT0FBTyxHQUFHLENBQUM7SUFDYixDQUFDIiwic291cmNlc0NvbnRlbnQiOlsiLyoqXG4gKiBAbGljZW5zZVxuICogQ29weXJpZ2h0IEdvb2dsZSBMTEMgQWxsIFJpZ2h0cyBSZXNlcnZlZC5cbiAqXG4gKiBVc2Ugb2YgdGhpcyBzb3VyY2UgY29kZSBpcyBnb3Zlcm5lZCBieSBhbiBNSVQtc3R5bGUgbGljZW5zZSB0aGF0IGNhbiBiZVxuICogZm91bmQgaW4gdGhlIExJQ0VOU0UgZmlsZSBhdCBodHRwczovL2FuZ3VsYXIuaW8vbGljZW5zZVxuICovXG5cbmltcG9ydCB7UGFyc2VTb3VyY2VTcGFufSBmcm9tICcuLi9wYXJzZV91dGlsJztcbmltcG9ydCAqIGFzIG8gZnJvbSAnLi9vdXRwdXRfYXN0JztcbmltcG9ydCB7U291cmNlTWFwR2VuZXJhdG9yfSBmcm9tICcuL3NvdXJjZV9tYXAnO1xuXG5jb25zdCBfU0lOR0xFX1FVT1RFX0VTQ0FQRV9TVFJJTkdfUkUgPSAvJ3xcXFxcfFxcbnxcXHJ8XFwkL2c7XG5jb25zdCBfTEVHQUxfSURFTlRJRklFUl9SRSA9IC9eWyRBLVpfXVswLTlBLVpfJF0qJC9pO1xuY29uc3QgX0lOREVOVF9XSVRIID0gJyAgJztcbmV4cG9ydCBjb25zdCBDQVRDSF9FUlJPUl9WQVIgPSBvLnZhcmlhYmxlKCdlcnJvcicsIG51bGwsIG51bGwpO1xuZXhwb3J0IGNvbnN0IENBVENIX1NUQUNLX1ZBUiA9IG8udmFyaWFibGUoJ3N0YWNrJywgbnVsbCwgbnVsbCk7XG5cbmV4cG9ydCBpbnRlcmZhY2UgT3V0cHV0RW1pdHRlciB7XG4gIGVtaXRTdGF0ZW1lbnRzKGdlbkZpbGVQYXRoOiBzdHJpbmcsIHN0bXRzOiBvLlN0YXRlbWVudFtdLCBwcmVhbWJsZT86IHN0cmluZ3xudWxsKTogc3RyaW5nO1xufVxuXG5jbGFzcyBfRW1pdHRlZExpbmUge1xuICBwYXJ0c0xlbmd0aCA9IDA7XG4gIHBhcnRzOiBzdHJpbmdbXSA9IFtdO1xuICBzcmNTcGFuczogKFBhcnNlU291cmNlU3BhbnxudWxsKVtdID0gW107XG4gIGNvbnN0cnVjdG9yKHB1YmxpYyBpbmRlbnQ6IG51bWJlcikge31cbn1cblxuZXhwb3J0IGNsYXNzIEVtaXR0ZXJWaXNpdG9yQ29udGV4dCB7XG4gIHN0YXRpYyBjcmVhdGVSb290KCk6IEVtaXR0ZXJWaXNpdG9yQ29udGV4dCB7XG4gICAgcmV0dXJuIG5ldyBFbWl0dGVyVmlzaXRvckNvbnRleHQoMCk7XG4gIH1cblxuICBwcml2YXRlIF9saW5lczogX0VtaXR0ZWRMaW5lW107XG4gIHByaXZhdGUgX2NsYXNzZXM6IG8uQ2xhc3NTdG10W10gPSBbXTtcbiAgcHJpdmF0ZSBfcHJlYW1ibGVMaW5lQ291bnQgPSAwO1xuXG4gIGNvbnN0cnVjdG9yKHByaXZhdGUgX2luZGVudDogbnVtYmVyKSB7XG4gICAgdGhpcy5fbGluZXMgPSBbbmV3IF9FbWl0dGVkTGluZShfaW5kZW50KV07XG4gIH1cblxuICAvKipcbiAgICogQGludGVybmFsIHN0cmlwIHRoaXMgZnJvbSBwdWJsaXNoZWQgZC50cyBmaWxlcyBkdWUgdG9cbiAgICogaHR0cHM6Ly9naXRodWIuY29tL21pY3Jvc29mdC9UeXBlU2NyaXB0L2lzc3Vlcy8zNjIxNlxuICAgKi9cbiAgcHJpdmF0ZSBnZXQgX2N1cnJlbnRMaW5lKCk6IF9FbWl0dGVkTGluZSB7XG4gICAgcmV0dXJuIHRoaXMuX2xpbmVzW3RoaXMuX2xpbmVzLmxlbmd0aCAtIDFdO1xuICB9XG5cbiAgcHJpbnRsbihmcm9tPzoge3NvdXJjZVNwYW46IFBhcnNlU291cmNlU3BhbnxudWxsfXxudWxsLCBsYXN0UGFydDogc3RyaW5nID0gJycpOiB2b2lkIHtcbiAgICB0aGlzLnByaW50KGZyb20gfHwgbnVsbCwgbGFzdFBhcnQsIHRydWUpO1xuICB9XG5cbiAgbGluZUlzRW1wdHkoKTogYm9vbGVhbiB7XG4gICAgcmV0dXJuIHRoaXMuX2N1cnJlbnRMaW5lLnBhcnRzLmxlbmd0aCA9PT0gMDtcbiAgfVxuXG4gIGxpbmVMZW5ndGgoKTogbnVtYmVyIHtcbiAgICByZXR1cm4gdGhpcy5fY3VycmVudExpbmUuaW5kZW50ICogX0lOREVOVF9XSVRILmxlbmd0aCArIHRoaXMuX2N1cnJlbnRMaW5lLnBhcnRzTGVuZ3RoO1xuICB9XG5cbiAgcHJpbnQoZnJvbToge3NvdXJjZVNwYW46IFBhcnNlU291cmNlU3BhbnxudWxsfXxudWxsLCBwYXJ0OiBzdHJpbmcsIG5ld0xpbmU6IGJvb2xlYW4gPSBmYWxzZSkge1xuICAgIGlmIChwYXJ0Lmxlbmd0aCA+IDApIHtcbiAgICAgIHRoaXMuX2N1cnJlbnRMaW5lLnBhcnRzLnB1c2gocGFydCk7XG4gICAgICB0aGlzLl9jdXJyZW50TGluZS5wYXJ0c0xlbmd0aCArPSBwYXJ0Lmxlbmd0aDtcbiAgICAgIHRoaXMuX2N1cnJlbnRMaW5lLnNyY1NwYW5zLnB1c2goZnJvbSAmJiBmcm9tLnNvdXJjZVNwYW4gfHwgbnVsbCk7XG4gICAgfVxuICAgIGlmIChuZXdMaW5lKSB7XG4gICAgICB0aGlzLl9saW5lcy5wdXNoKG5ldyBfRW1pdHRlZExpbmUodGhpcy5faW5kZW50KSk7XG4gICAgfVxuICB9XG5cbiAgcmVtb3ZlRW1wdHlMYXN0TGluZSgpIHtcbiAgICBpZiAodGhpcy5saW5lSXNFbXB0eSgpKSB7XG4gICAgICB0aGlzLl9saW5lcy5wb3AoKTtcbiAgICB9XG4gIH1cblxuICBpbmNJbmRlbnQoKSB7XG4gICAgdGhpcy5faW5kZW50Kys7XG4gICAgaWYgKHRoaXMubGluZUlzRW1wdHkoKSkge1xuICAgICAgdGhpcy5fY3VycmVudExpbmUuaW5kZW50ID0gdGhpcy5faW5kZW50O1xuICAgIH1cbiAgfVxuXG4gIGRlY0luZGVudCgpIHtcbiAgICB0aGlzLl9pbmRlbnQtLTtcbiAgICBpZiAodGhpcy5saW5lSXNFbXB0eSgpKSB7XG4gICAgICB0aGlzLl9jdXJyZW50TGluZS5pbmRlbnQgPSB0aGlzLl9pbmRlbnQ7XG4gICAgfVxuICB9XG5cbiAgcHVzaENsYXNzKGNsYXp6OiBvLkNsYXNzU3RtdCkge1xuICAgIHRoaXMuX2NsYXNzZXMucHVzaChjbGF6eik7XG4gIH1cblxuICBwb3BDbGFzcygpOiBvLkNsYXNzU3RtdCB7XG4gICAgcmV0dXJuIHRoaXMuX2NsYXNzZXMucG9wKCkhO1xuICB9XG5cbiAgZ2V0IGN1cnJlbnRDbGFzcygpOiBvLkNsYXNzU3RtdHxudWxsIHtcbiAgICByZXR1cm4gdGhpcy5fY2xhc3Nlcy5sZW5ndGggPiAwID8gdGhpcy5fY2xhc3Nlc1t0aGlzLl9jbGFzc2VzLmxlbmd0aCAtIDFdIDogbnVsbDtcbiAgfVxuXG4gIHRvU291cmNlKCk6IHN0cmluZyB7XG4gICAgcmV0dXJuIHRoaXMuc291cmNlTGluZXNcbiAgICAgICAgLm1hcChsID0+IGwucGFydHMubGVuZ3RoID4gMCA/IF9jcmVhdGVJbmRlbnQobC5pbmRlbnQpICsgbC5wYXJ0cy5qb2luKCcnKSA6ICcnKVxuICAgICAgICAuam9pbignXFxuJyk7XG4gIH1cblxuICB0b1NvdXJjZU1hcEdlbmVyYXRvcihnZW5GaWxlUGF0aDogc3RyaW5nLCBzdGFydHNBdExpbmU6IG51bWJlciA9IDApOiBTb3VyY2VNYXBHZW5lcmF0b3Ige1xuICAgIGNvbnN0IG1hcCA9IG5ldyBTb3VyY2VNYXBHZW5lcmF0b3IoZ2VuRmlsZVBhdGgpO1xuXG4gICAgbGV0IGZpcnN0T2Zmc2V0TWFwcGVkID0gZmFsc2U7XG4gICAgY29uc3QgbWFwRmlyc3RPZmZzZXRJZk5lZWRlZCA9ICgpID0+IHtcbiAgICAgIGlmICghZmlyc3RPZmZzZXRNYXBwZWQpIHtcbiAgICAgICAgLy8gQWRkIGEgc2luZ2xlIHNwYWNlIHNvIHRoYXQgdG9vbHMgd29uJ3QgdHJ5IHRvIGxvYWQgdGhlIGZpbGUgZnJvbSBkaXNrLlxuICAgICAgICAvLyBOb3RlOiBXZSBhcmUgdXNpbmcgdmlydHVhbCB1cmxzIGxpa2UgYG5nOi8vL2AsIHNvIHdlIGhhdmUgdG9cbiAgICAgICAgLy8gcHJvdmlkZSBhIGNvbnRlbnQgaGVyZS5cbiAgICAgICAgbWFwLmFkZFNvdXJjZShnZW5GaWxlUGF0aCwgJyAnKS5hZGRNYXBwaW5nKDAsIGdlbkZpbGVQYXRoLCAwLCAwKTtcbiAgICAgICAgZmlyc3RPZmZzZXRNYXBwZWQgPSB0cnVlO1xuICAgICAgfVxuICAgIH07XG5cbiAgICBmb3IgKGxldCBpID0gMDsgaSA8IHN0YXJ0c0F0TGluZTsgaSsrKSB7XG4gICAgICBtYXAuYWRkTGluZSgpO1xuICAgICAgbWFwRmlyc3RPZmZzZXRJZk5lZWRlZCgpO1xuICAgIH1cblxuICAgIHRoaXMuc291cmNlTGluZXMuZm9yRWFjaCgobGluZSwgbGluZUlkeCkgPT4ge1xuICAgICAgbWFwLmFkZExpbmUoKTtcblxuICAgICAgY29uc3Qgc3BhbnMgPSBsaW5lLnNyY1NwYW5zO1xuICAgICAgY29uc3QgcGFydHMgPSBsaW5lLnBhcnRzO1xuICAgICAgbGV0IGNvbDAgPSBsaW5lLmluZGVudCAqIF9JTkRFTlRfV0lUSC5sZW5ndGg7XG4gICAgICBsZXQgc3BhbklkeCA9IDA7XG4gICAgICAvLyBza2lwIGxlYWRpbmcgcGFydHMgd2l0aG91dCBzb3VyY2Ugc3BhbnNcbiAgICAgIHdoaWxlIChzcGFuSWR4IDwgc3BhbnMubGVuZ3RoICYmICFzcGFuc1tzcGFuSWR4XSkge1xuICAgICAgICBjb2wwICs9IHBhcnRzW3NwYW5JZHhdLmxlbmd0aDtcbiAgICAgICAgc3BhbklkeCsrO1xuICAgICAgfVxuICAgICAgaWYgKHNwYW5JZHggPCBzcGFucy5sZW5ndGggJiYgbGluZUlkeCA9PT0gMCAmJiBjb2wwID09PSAwKSB7XG4gICAgICAgIGZpcnN0T2Zmc2V0TWFwcGVkID0gdHJ1ZTtcbiAgICAgIH0gZWxzZSB7XG4gICAgICAgIG1hcEZpcnN0T2Zmc2V0SWZOZWVkZWQoKTtcbiAgICAgIH1cblxuICAgICAgd2hpbGUgKHNwYW5JZHggPCBzcGFucy5sZW5ndGgpIHtcbiAgICAgICAgY29uc3Qgc3BhbiA9IHNwYW5zW3NwYW5JZHhdITtcbiAgICAgICAgY29uc3Qgc291cmNlID0gc3Bhbi5zdGFydC5maWxlO1xuICAgICAgICBjb25zdCBzb3VyY2VMaW5lID0gc3Bhbi5zdGFydC5saW5lO1xuICAgICAgICBjb25zdCBzb3VyY2VDb2wgPSBzcGFuLnN0YXJ0LmNvbDtcbiAgICAgICAgbWFwLmFkZFNvdXJjZShzb3VyY2UudXJsLCBzb3VyY2UuY29udGVudClcbiAgICAgICAgICAgIC5hZGRNYXBwaW5nKGNvbDAsIHNvdXJjZS51cmwsIHNvdXJjZUxpbmUsIHNvdXJjZUNvbCk7XG5cbiAgICAgICAgY29sMCArPSBwYXJ0c1tzcGFuSWR4XS5sZW5ndGg7XG4gICAgICAgIHNwYW5JZHgrKztcblxuICAgICAgICAvLyBhc3NpZ24gcGFydHMgd2l0aG91dCBzcGFuIG9yIHRoZSBzYW1lIHNwYW4gdG8gdGhlIHByZXZpb3VzIHNlZ21lbnRcbiAgICAgICAgd2hpbGUgKHNwYW5JZHggPCBzcGFucy5sZW5ndGggJiYgKHNwYW4gPT09IHNwYW5zW3NwYW5JZHhdIHx8ICFzcGFuc1tzcGFuSWR4XSkpIHtcbiAgICAgICAgICBjb2wwICs9IHBhcnRzW3NwYW5JZHhdLmxlbmd0aDtcbiAgICAgICAgICBzcGFuSWR4Kys7XG4gICAgICAgIH1cbiAgICAgIH1cbiAgICB9KTtcblxuICAgIHJldHVybiBtYXA7XG4gIH1cblxuICBzZXRQcmVhbWJsZUxpbmVDb3VudChjb3VudDogbnVtYmVyKSB7XG4gICAgcmV0dXJuIHRoaXMuX3ByZWFtYmxlTGluZUNvdW50ID0gY291bnQ7XG4gIH1cblxuICBzcGFuT2YobGluZTogbnVtYmVyLCBjb2x1bW46IG51bWJlcik6IFBhcnNlU291cmNlU3BhbnxudWxsIHtcbiAgICBjb25zdCBlbWl0dGVkTGluZSA9IHRoaXMuX2xpbmVzW2xpbmUgLSB0aGlzLl9wcmVhbWJsZUxpbmVDb3VudF07XG4gICAgaWYgKGVtaXR0ZWRMaW5lKSB7XG4gICAgICBsZXQgY29sdW1uc0xlZnQgPSBjb2x1bW4gLSBfY3JlYXRlSW5kZW50KGVtaXR0ZWRMaW5lLmluZGVudCkubGVuZ3RoO1xuICAgICAgZm9yIChsZXQgcGFydEluZGV4ID0gMDsgcGFydEluZGV4IDwgZW1pdHRlZExpbmUucGFydHMubGVuZ3RoOyBwYXJ0SW5kZXgrKykge1xuICAgICAgICBjb25zdCBwYXJ0ID0gZW1pdHRlZExpbmUucGFydHNbcGFydEluZGV4XTtcbiAgICAgICAgaWYgKHBhcnQubGVuZ3RoID4gY29sdW1uc0xlZnQpIHtcbiAgICAgICAgICByZXR1cm4gZW1pdHRlZExpbmUuc3JjU3BhbnNbcGFydEluZGV4XTtcbiAgICAgICAgfVxuICAgICAgICBjb2x1bW5zTGVmdCAtPSBwYXJ0Lmxlbmd0aDtcbiAgICAgIH1cbiAgICB9XG4gICAgcmV0dXJuIG51bGw7XG4gIH1cblxuICAvKipcbiAgICogQGludGVybmFsIHN0cmlwIHRoaXMgZnJvbSBwdWJsaXNoZWQgZC50cyBmaWxlcyBkdWUgdG9cbiAgICogaHR0cHM6Ly9naXRodWIuY29tL21pY3Jvc29mdC9UeXBlU2NyaXB0L2lzc3Vlcy8zNjIxNlxuICAgKi9cbiAgcHJpdmF0ZSBnZXQgc291cmNlTGluZXMoKTogX0VtaXR0ZWRMaW5lW10ge1xuICAgIGlmICh0aGlzLl9saW5lcy5sZW5ndGggJiYgdGhpcy5fbGluZXNbdGhpcy5fbGluZXMubGVuZ3RoIC0gMV0ucGFydHMubGVuZ3RoID09PSAwKSB7XG4gICAgICByZXR1cm4gdGhpcy5fbGluZXMuc2xpY2UoMCwgLTEpO1xuICAgIH1cbiAgICByZXR1cm4gdGhpcy5fbGluZXM7XG4gIH1cbn1cblxuZXhwb3J0IGFic3RyYWN0IGNsYXNzIEFic3RyYWN0RW1pdHRlclZpc2l0b3IgaW1wbGVtZW50cyBvLlN0YXRlbWVudFZpc2l0b3IsIG8uRXhwcmVzc2lvblZpc2l0b3Ige1xuICBjb25zdHJ1Y3Rvcihwcml2YXRlIF9lc2NhcGVEb2xsYXJJblN0cmluZ3M6IGJvb2xlYW4pIHt9XG5cbiAgcHJvdGVjdGVkIHByaW50TGVhZGluZ0NvbW1lbnRzKHN0bXQ6IG8uU3RhdGVtZW50LCBjdHg6IEVtaXR0ZXJWaXNpdG9yQ29udGV4dCk6IHZvaWQge1xuICAgIGlmIChzdG10LmxlYWRpbmdDb21tZW50cyA9PT0gdW5kZWZpbmVkKSB7XG4gICAgICByZXR1cm47XG4gICAgfVxuICAgIGZvciAoY29uc3QgY29tbWVudCBvZiBzdG10LmxlYWRpbmdDb21tZW50cykge1xuICAgICAgaWYgKGNvbW1lbnQgaW5zdGFuY2VvZiBvLkpTRG9jQ29tbWVudCkge1xuICAgICAgICBjdHgucHJpbnQoc3RtdCwgYC8qJHtjb21tZW50LnRvU3RyaW5nKCl9Ki9gLCBjb21tZW50LnRyYWlsaW5nTmV3bGluZSk7XG4gICAgICB9IGVsc2Uge1xuICAgICAgICBpZiAoY29tbWVudC5tdWx0aWxpbmUpIHtcbiAgICAgICAgICBjdHgucHJpbnQoc3RtdCwgYC8qICR7Y29tbWVudC50ZXh0fSAqL2AsIGNvbW1lbnQudHJhaWxpbmdOZXdsaW5lKTtcbiAgICAgICAgfSBlbHNlIHtcbiAgICAgICAgICBjb21tZW50LnRleHQuc3BsaXQoJ1xcbicpLmZvckVhY2goKGxpbmUpID0+IHtcbiAgICAgICAgICAgIGN0eC5wcmludGxuKHN0bXQsIGAvLyAke2xpbmV9YCk7XG4gICAgICAgICAgfSk7XG4gICAgICAgIH1cbiAgICAgIH1cbiAgICB9XG4gIH1cblxuICB2aXNpdEV4cHJlc3Npb25TdG10KHN0bXQ6IG8uRXhwcmVzc2lvblN0YXRlbWVudCwgY3R4OiBFbWl0dGVyVmlzaXRvckNvbnRleHQpOiBhbnkge1xuICAgIHRoaXMucHJpbnRMZWFkaW5nQ29tbWVudHMoc3RtdCwgY3R4KTtcbiAgICBzdG10LmV4cHIudmlzaXRFeHByZXNzaW9uKHRoaXMsIGN0eCk7XG4gICAgY3R4LnByaW50bG4oc3RtdCwgJzsnKTtcbiAgICByZXR1cm4gbnVsbDtcbiAgfVxuXG4gIHZpc2l0UmV0dXJuU3RtdChzdG10OiBvLlJldHVyblN0YXRlbWVudCwgY3R4OiBFbWl0dGVyVmlzaXRvckNvbnRleHQpOiBhbnkge1xuICAgIHRoaXMucHJpbnRMZWFkaW5nQ29tbWVudHMoc3RtdCwgY3R4KTtcbiAgICBjdHgucHJpbnQoc3RtdCwgYHJldHVybiBgKTtcbiAgICBzdG10LnZhbHVlLnZpc2l0RXhwcmVzc2lvbih0aGlzLCBjdHgpO1xuICAgIGN0eC5wcmludGxuKHN0bXQsICc7Jyk7XG4gICAgcmV0dXJuIG51bGw7XG4gIH1cblxuICBhYnN0cmFjdCB2aXNpdENhc3RFeHByKGFzdDogby5DYXN0RXhwciwgY29udGV4dDogYW55KTogYW55O1xuXG4gIGFic3RyYWN0IHZpc2l0RGVjbGFyZUNsYXNzU3RtdChzdG10OiBvLkNsYXNzU3RtdCwgY3R4OiBFbWl0dGVyVmlzaXRvckNvbnRleHQpOiBhbnk7XG5cbiAgdmlzaXRJZlN0bXQoc3RtdDogby5JZlN0bXQsIGN0eDogRW1pdHRlclZpc2l0b3JDb250ZXh0KTogYW55IHtcbiAgICB0aGlzLnByaW50TGVhZGluZ0NvbW1lbnRzKHN0bXQsIGN0eCk7XG4gICAgY3R4LnByaW50KHN0bXQsIGBpZiAoYCk7XG4gICAgc3RtdC5jb25kaXRpb24udmlzaXRFeHByZXNzaW9uKHRoaXMsIGN0eCk7XG4gICAgY3R4LnByaW50KHN0bXQsIGApIHtgKTtcbiAgICBjb25zdCBoYXNFbHNlQ2FzZSA9IHN0bXQuZmFsc2VDYXNlICE9IG51bGwgJiYgc3RtdC5mYWxzZUNhc2UubGVuZ3RoID4gMDtcbiAgICBpZiAoc3RtdC50cnVlQ2FzZS5sZW5ndGggPD0gMSAmJiAhaGFzRWxzZUNhc2UpIHtcbiAgICAgIGN0eC5wcmludChzdG10LCBgIGApO1xuICAgICAgdGhpcy52aXNpdEFsbFN0YXRlbWVudHMoc3RtdC50cnVlQ2FzZSwgY3R4KTtcbiAgICAgIGN0eC5yZW1vdmVFbXB0eUxhc3RMaW5lKCk7XG4gICAgICBjdHgucHJpbnQoc3RtdCwgYCBgKTtcbiAgICB9IGVsc2Uge1xuICAgICAgY3R4LnByaW50bG4oKTtcbiAgICAgIGN0eC5pbmNJbmRlbnQoKTtcbiAgICAgIHRoaXMudmlzaXRBbGxTdGF0ZW1lbnRzKHN0bXQudHJ1ZUNhc2UsIGN0eCk7XG4gICAgICBjdHguZGVjSW5kZW50KCk7XG4gICAgICBpZiAoaGFzRWxzZUNhc2UpIHtcbiAgICAgICAgY3R4LnByaW50bG4oc3RtdCwgYH0gZWxzZSB7YCk7XG4gICAgICAgIGN0eC5pbmNJbmRlbnQoKTtcbiAgICAgICAgdGhpcy52aXNpdEFsbFN0YXRlbWVudHMoc3RtdC5mYWxzZUNhc2UsIGN0eCk7XG4gICAgICAgIGN0eC5kZWNJbmRlbnQoKTtcbiAgICAgIH1cbiAgICB9XG4gICAgY3R4LnByaW50bG4oc3RtdCwgYH1gKTtcbiAgICByZXR1cm4gbnVsbDtcbiAgfVxuXG4gIGFic3RyYWN0IHZpc2l0VHJ5Q2F0Y2hTdG10KHN0bXQ6IG8uVHJ5Q2F0Y2hTdG10LCBjdHg6IEVtaXR0ZXJWaXNpdG9yQ29udGV4dCk6IGFueTtcblxuICB2aXNpdFRocm93U3RtdChzdG10OiBvLlRocm93U3RtdCwgY3R4OiBFbWl0dGVyVmlzaXRvckNvbnRleHQpOiBhbnkge1xuICAgIHRoaXMucHJpbnRMZWFkaW5nQ29tbWVudHMoc3RtdCwgY3R4KTtcbiAgICBjdHgucHJpbnQoc3RtdCwgYHRocm93IGApO1xuICAgIHN0bXQuZXJyb3IudmlzaXRFeHByZXNzaW9uKHRoaXMsIGN0eCk7XG4gICAgY3R4LnByaW50bG4oc3RtdCwgYDtgKTtcbiAgICByZXR1cm4gbnVsbDtcbiAgfVxuXG4gIGFic3RyYWN0IHZpc2l0RGVjbGFyZVZhclN0bXQoc3RtdDogby5EZWNsYXJlVmFyU3RtdCwgY3R4OiBFbWl0dGVyVmlzaXRvckNvbnRleHQpOiBhbnk7XG5cbiAgdmlzaXRXcml0ZVZhckV4cHIoZXhwcjogby5Xcml0ZVZhckV4cHIsIGN0eDogRW1pdHRlclZpc2l0b3JDb250ZXh0KTogYW55IHtcbiAgICBjb25zdCBsaW5lV2FzRW1wdHkgPSBjdHgubGluZUlzRW1wdHkoKTtcbiAgICBpZiAoIWxpbmVXYXNFbXB0eSkge1xuICAgICAgY3R4LnByaW50KGV4cHIsICcoJyk7XG4gICAgfVxuICAgIGN0eC5wcmludChleHByLCBgJHtleHByLm5hbWV9ID0gYCk7XG4gICAgZXhwci52YWx1ZS52aXNpdEV4cHJlc3Npb24odGhpcywgY3R4KTtcbiAgICBpZiAoIWxpbmVXYXNFbXB0eSkge1xuICAgICAgY3R4LnByaW50KGV4cHIsICcpJyk7XG4gICAgfVxuICAgIHJldHVybiBudWxsO1xuICB9XG4gIHZpc2l0V3JpdGVLZXlFeHByKGV4cHI6IG8uV3JpdGVLZXlFeHByLCBjdHg6IEVtaXR0ZXJWaXNpdG9yQ29udGV4dCk6IGFueSB7XG4gICAgY29uc3QgbGluZVdhc0VtcHR5ID0gY3R4LmxpbmVJc0VtcHR5KCk7XG4gICAgaWYgKCFsaW5lV2FzRW1wdHkpIHtcbiAgICAgIGN0eC5wcmludChleHByLCAnKCcpO1xuICAgIH1cbiAgICBleHByLnJlY2VpdmVyLnZpc2l0RXhwcmVzc2lvbih0aGlzLCBjdHgpO1xuICAgIGN0eC5wcmludChleHByLCBgW2ApO1xuICAgIGV4cHIuaW5kZXgudmlzaXRFeHByZXNzaW9uKHRoaXMsIGN0eCk7XG4gICAgY3R4LnByaW50KGV4cHIsIGBdID0gYCk7XG4gICAgZXhwci52YWx1ZS52aXNpdEV4cHJlc3Npb24odGhpcywgY3R4KTtcbiAgICBpZiAoIWxpbmVXYXNFbXB0eSkge1xuICAgICAgY3R4LnByaW50KGV4cHIsICcpJyk7XG4gICAgfVxuICAgIHJldHVybiBudWxsO1xuICB9XG4gIHZpc2l0V3JpdGVQcm9wRXhwcihleHByOiBvLldyaXRlUHJvcEV4cHIsIGN0eDogRW1pdHRlclZpc2l0b3JDb250ZXh0KTogYW55IHtcbiAgICBjb25zdCBsaW5lV2FzRW1wdHkgPSBjdHgubGluZUlzRW1wdHkoKTtcbiAgICBpZiAoIWxpbmVXYXNFbXB0eSkge1xuICAgICAgY3R4LnByaW50KGV4cHIsICcoJyk7XG4gICAgfVxuICAgIGV4cHIucmVjZWl2ZXIudmlzaXRFeHByZXNzaW9uKHRoaXMsIGN0eCk7XG4gICAgY3R4LnByaW50KGV4cHIsIGAuJHtleHByLm5hbWV9ID0gYCk7XG4gICAgZXhwci52YWx1ZS52aXNpdEV4cHJlc3Npb24odGhpcywgY3R4KTtcbiAgICBpZiAoIWxpbmVXYXNFbXB0eSkge1xuICAgICAgY3R4LnByaW50KGV4cHIsICcpJyk7XG4gICAgfVxuICAgIHJldHVybiBudWxsO1xuICB9XG4gIHZpc2l0SW52b2tlTWV0aG9kRXhwcihleHByOiBvLkludm9rZU1ldGhvZEV4cHIsIGN0eDogRW1pdHRlclZpc2l0b3JDb250ZXh0KTogYW55IHtcbiAgICBleHByLnJlY2VpdmVyLnZpc2l0RXhwcmVzc2lvbih0aGlzLCBjdHgpO1xuICAgIGxldCBuYW1lID0gZXhwci5uYW1lO1xuICAgIGlmIChleHByLmJ1aWx0aW4gIT0gbnVsbCkge1xuICAgICAgbmFtZSA9IHRoaXMuZ2V0QnVpbHRpbk1ldGhvZE5hbWUoZXhwci5idWlsdGluKTtcbiAgICAgIGlmIChuYW1lID09IG51bGwpIHtcbiAgICAgICAgLy8gc29tZSBidWlsdGlucyBqdXN0IG1lYW4gdG8gc2tpcCB0aGUgY2FsbC5cbiAgICAgICAgcmV0dXJuIG51bGw7XG4gICAgICB9XG4gICAgfVxuICAgIGN0eC5wcmludChleHByLCBgLiR7bmFtZX0oYCk7XG4gICAgdGhpcy52aXNpdEFsbEV4cHJlc3Npb25zKGV4cHIuYXJncywgY3R4LCBgLGApO1xuICAgIGN0eC5wcmludChleHByLCBgKWApO1xuICAgIHJldHVybiBudWxsO1xuICB9XG5cbiAgYWJzdHJhY3QgZ2V0QnVpbHRpbk1ldGhvZE5hbWUobWV0aG9kOiBvLkJ1aWx0aW5NZXRob2QpOiBzdHJpbmc7XG5cbiAgdmlzaXRJbnZva2VGdW5jdGlvbkV4cHIoZXhwcjogby5JbnZva2VGdW5jdGlvbkV4cHIsIGN0eDogRW1pdHRlclZpc2l0b3JDb250ZXh0KTogYW55IHtcbiAgICBleHByLmZuLnZpc2l0RXhwcmVzc2lvbih0aGlzLCBjdHgpO1xuICAgIGN0eC5wcmludChleHByLCBgKGApO1xuICAgIHRoaXMudmlzaXRBbGxFeHByZXNzaW9ucyhleHByLmFyZ3MsIGN0eCwgJywnKTtcbiAgICBjdHgucHJpbnQoZXhwciwgYClgKTtcbiAgICByZXR1cm4gbnVsbDtcbiAgfVxuICB2aXNpdFRhZ2dlZFRlbXBsYXRlRXhwcihleHByOiBvLlRhZ2dlZFRlbXBsYXRlRXhwciwgY3R4OiBFbWl0dGVyVmlzaXRvckNvbnRleHQpOiBhbnkge1xuICAgIGV4cHIudGFnLnZpc2l0RXhwcmVzc2lvbih0aGlzLCBjdHgpO1xuICAgIGN0eC5wcmludChleHByLCAnYCcgKyBleHByLnRlbXBsYXRlLmVsZW1lbnRzWzBdLnJhd1RleHQpO1xuICAgIGZvciAobGV0IGkgPSAxOyBpIDwgZXhwci50ZW1wbGF0ZS5lbGVtZW50cy5sZW5ndGg7IGkrKykge1xuICAgICAgY3R4LnByaW50KGV4cHIsICckeycpO1xuICAgICAgZXhwci50ZW1wbGF0ZS5leHByZXNzaW9uc1tpIC0gMV0udmlzaXRFeHByZXNzaW9uKHRoaXMsIGN0eCk7XG4gICAgICBjdHgucHJpbnQoZXhwciwgYH0ke2V4cHIudGVtcGxhdGUuZWxlbWVudHNbaV0ucmF3VGV4dH1gKTtcbiAgICB9XG4gICAgY3R4LnByaW50KGV4cHIsICdgJyk7XG4gICAgcmV0dXJuIG51bGw7XG4gIH1cbiAgdmlzaXRXcmFwcGVkTm9kZUV4cHIoYXN0OiBvLldyYXBwZWROb2RlRXhwcjxhbnk+LCBjdHg6IEVtaXR0ZXJWaXNpdG9yQ29udGV4dCk6IGFueSB7XG4gICAgdGhyb3cgbmV3IEVycm9yKCdBYnN0cmFjdCBlbWl0dGVyIGNhbm5vdCB2aXNpdCBXcmFwcGVkTm9kZUV4cHIuJyk7XG4gIH1cbiAgdmlzaXRUeXBlb2ZFeHByKGV4cHI6IG8uVHlwZW9mRXhwciwgY3R4OiBFbWl0dGVyVmlzaXRvckNvbnRleHQpOiBhbnkge1xuICAgIGN0eC5wcmludChleHByLCAndHlwZW9mICcpO1xuICAgIGV4cHIuZXhwci52aXNpdEV4cHJlc3Npb24odGhpcywgY3R4KTtcbiAgfVxuICB2aXNpdFJlYWRWYXJFeHByKGFzdDogby5SZWFkVmFyRXhwciwgY3R4OiBFbWl0dGVyVmlzaXRvckNvbnRleHQpOiBhbnkge1xuICAgIGxldCB2YXJOYW1lID0gYXN0Lm5hbWUhO1xuICAgIGlmIChhc3QuYnVpbHRpbiAhPSBudWxsKSB7XG4gICAgICBzd2l0Y2ggKGFzdC5idWlsdGluKSB7XG4gICAgICAgIGNhc2Ugby5CdWlsdGluVmFyLlN1cGVyOlxuICAgICAgICAgIHZhck5hbWUgPSAnc3VwZXInO1xuICAgICAgICAgIGJyZWFrO1xuICAgICAgICBjYXNlIG8uQnVpbHRpblZhci5UaGlzOlxuICAgICAgICAgIHZhck5hbWUgPSAndGhpcyc7XG4gICAgICAgICAgYnJlYWs7XG4gICAgICAgIGNhc2Ugby5CdWlsdGluVmFyLkNhdGNoRXJyb3I6XG4gICAgICAgICAgdmFyTmFtZSA9IENBVENIX0VSUk9SX1ZBUi5uYW1lITtcbiAgICAgICAgICBicmVhaztcbiAgICAgICAgY2FzZSBvLkJ1aWx0aW5WYXIuQ2F0Y2hTdGFjazpcbiAgICAgICAgICB2YXJOYW1lID0gQ0FUQ0hfU1RBQ0tfVkFSLm5hbWUhO1xuICAgICAgICAgIGJyZWFrO1xuICAgICAgICBkZWZhdWx0OlxuICAgICAgICAgIHRocm93IG5ldyBFcnJvcihgVW5rbm93biBidWlsdGluIHZhcmlhYmxlICR7YXN0LmJ1aWx0aW59YCk7XG4gICAgICB9XG4gICAgfVxuICAgIGN0eC5wcmludChhc3QsIHZhck5hbWUpO1xuICAgIHJldHVybiBudWxsO1xuICB9XG4gIHZpc2l0SW5zdGFudGlhdGVFeHByKGFzdDogby5JbnN0YW50aWF0ZUV4cHIsIGN0eDogRW1pdHRlclZpc2l0b3JDb250ZXh0KTogYW55IHtcbiAgICBjdHgucHJpbnQoYXN0LCBgbmV3IGApO1xuICAgIGFzdC5jbGFzc0V4cHIudmlzaXRFeHByZXNzaW9uKHRoaXMsIGN0eCk7XG4gICAgY3R4LnByaW50KGFzdCwgYChgKTtcbiAgICB0aGlzLnZpc2l0QWxsRXhwcmVzc2lvbnMoYXN0LmFyZ3MsIGN0eCwgJywnKTtcbiAgICBjdHgucHJpbnQoYXN0LCBgKWApO1xuICAgIHJldHVybiBudWxsO1xuICB9XG5cbiAgdmlzaXRMaXRlcmFsRXhwcihhc3Q6IG8uTGl0ZXJhbEV4cHIsIGN0eDogRW1pdHRlclZpc2l0b3JDb250ZXh0KTogYW55IHtcbiAgICBjb25zdCB2YWx1ZSA9IGFzdC52YWx1ZTtcbiAgICBpZiAodHlwZW9mIHZhbHVlID09PSAnc3RyaW5nJykge1xuICAgICAgY3R4LnByaW50KGFzdCwgZXNjYXBlSWRlbnRpZmllcih2YWx1ZSwgdGhpcy5fZXNjYXBlRG9sbGFySW5TdHJpbmdzKSk7XG4gICAgfSBlbHNlIHtcbiAgICAgIGN0eC5wcmludChhc3QsIGAke3ZhbHVlfWApO1xuICAgIH1cbiAgICByZXR1cm4gbnVsbDtcbiAgfVxuXG4gIHZpc2l0TG9jYWxpemVkU3RyaW5nKGFzdDogby5Mb2NhbGl6ZWRTdHJpbmcsIGN0eDogRW1pdHRlclZpc2l0b3JDb250ZXh0KTogYW55IHtcbiAgICBjb25zdCBoZWFkID0gYXN0LnNlcmlhbGl6ZUkxOG5IZWFkKCk7XG4gICAgY3R4LnByaW50KGFzdCwgJyRsb2NhbGl6ZSBgJyArIGhlYWQucmF3KTtcbiAgICBmb3IgKGxldCBpID0gMTsgaSA8IGFzdC5tZXNzYWdlUGFydHMubGVuZ3RoOyBpKyspIHtcbiAgICAgIGN0eC5wcmludChhc3QsICckeycpO1xuICAgICAgYXN0LmV4cHJlc3Npb25zW2kgLSAxXS52aXNpdEV4cHJlc3Npb24odGhpcywgY3R4KTtcbiAgICAgIGN0eC5wcmludChhc3QsIGB9JHthc3Quc2VyaWFsaXplSTE4blRlbXBsYXRlUGFydChpKS5yYXd9YCk7XG4gICAgfVxuICAgIGN0eC5wcmludChhc3QsICdgJyk7XG4gICAgcmV0dXJuIG51bGw7XG4gIH1cblxuICBhYnN0cmFjdCB2aXNpdEV4dGVybmFsRXhwcihhc3Q6IG8uRXh0ZXJuYWxFeHByLCBjdHg6IEVtaXR0ZXJWaXNpdG9yQ29udGV4dCk6IGFueTtcblxuICB2aXNpdENvbmRpdGlvbmFsRXhwcihhc3Q6IG8uQ29uZGl0aW9uYWxFeHByLCBjdHg6IEVtaXR0ZXJWaXNpdG9yQ29udGV4dCk6IGFueSB7XG4gICAgY3R4LnByaW50KGFzdCwgYChgKTtcbiAgICBhc3QuY29uZGl0aW9uLnZpc2l0RXhwcmVzc2lvbih0aGlzLCBjdHgpO1xuICAgIGN0eC5wcmludChhc3QsICc/ICcpO1xuICAgIGFzdC50cnVlQ2FzZS52aXNpdEV4cHJlc3Npb24odGhpcywgY3R4KTtcbiAgICBjdHgucHJpbnQoYXN0LCAnOiAnKTtcbiAgICBhc3QuZmFsc2VDYXNlIS52aXNpdEV4cHJlc3Npb24odGhpcywgY3R4KTtcbiAgICBjdHgucHJpbnQoYXN0LCBgKWApO1xuICAgIHJldHVybiBudWxsO1xuICB9XG4gIHZpc2l0Tm90RXhwcihhc3Q6IG8uTm90RXhwciwgY3R4OiBFbWl0dGVyVmlzaXRvckNvbnRleHQpOiBhbnkge1xuICAgIGN0eC5wcmludChhc3QsICchJyk7XG4gICAgYXN0LmNvbmRpdGlvbi52aXNpdEV4cHJlc3Npb24odGhpcywgY3R4KTtcbiAgICByZXR1cm4gbnVsbDtcbiAgfVxuICB2aXNpdEFzc2VydE5vdE51bGxFeHByKGFzdDogby5Bc3NlcnROb3ROdWxsLCBjdHg6IEVtaXR0ZXJWaXNpdG9yQ29udGV4dCk6IGFueSB7XG4gICAgYXN0LmNvbmRpdGlvbi52aXNpdEV4cHJlc3Npb24odGhpcywgY3R4KTtcbiAgICByZXR1cm4gbnVsbDtcbiAgfVxuICBhYnN0cmFjdCB2aXNpdEZ1bmN0aW9uRXhwcihhc3Q6IG8uRnVuY3Rpb25FeHByLCBjdHg6IEVtaXR0ZXJWaXNpdG9yQ29udGV4dCk6IGFueTtcbiAgYWJzdHJhY3QgdmlzaXREZWNsYXJlRnVuY3Rpb25TdG10KHN0bXQ6IG8uRGVjbGFyZUZ1bmN0aW9uU3RtdCwgY29udGV4dDogYW55KTogYW55O1xuXG4gIHZpc2l0VW5hcnlPcGVyYXRvckV4cHIoYXN0OiBvLlVuYXJ5T3BlcmF0b3JFeHByLCBjdHg6IEVtaXR0ZXJWaXNpdG9yQ29udGV4dCk6IGFueSB7XG4gICAgbGV0IG9wU3RyOiBzdHJpbmc7XG4gICAgc3dpdGNoIChhc3Qub3BlcmF0b3IpIHtcbiAgICAgIGNhc2Ugby5VbmFyeU9wZXJhdG9yLlBsdXM6XG4gICAgICAgIG9wU3RyID0gJysnO1xuICAgICAgICBicmVhaztcbiAgICAgIGNhc2Ugby5VbmFyeU9wZXJhdG9yLk1pbnVzOlxuICAgICAgICBvcFN0ciA9ICctJztcbiAgICAgICAgYnJlYWs7XG4gICAgICBkZWZhdWx0OlxuICAgICAgICB0aHJvdyBuZXcgRXJyb3IoYFVua25vd24gb3BlcmF0b3IgJHthc3Qub3BlcmF0b3J9YCk7XG4gICAgfVxuICAgIGlmIChhc3QucGFyZW5zKSBjdHgucHJpbnQoYXN0LCBgKGApO1xuICAgIGN0eC5wcmludChhc3QsIG9wU3RyKTtcbiAgICBhc3QuZXhwci52aXNpdEV4cHJlc3Npb24odGhpcywgY3R4KTtcbiAgICBpZiAoYXN0LnBhcmVucykgY3R4LnByaW50KGFzdCwgYClgKTtcbiAgICByZXR1cm4gbnVsbDtcbiAgfVxuXG4gIHZpc2l0QmluYXJ5T3BlcmF0b3JFeHByKGFzdDogby5CaW5hcnlPcGVyYXRvckV4cHIsIGN0eDogRW1pdHRlclZpc2l0b3JDb250ZXh0KTogYW55IHtcbiAgICBsZXQgb3BTdHI6IHN0cmluZztcbiAgICBzd2l0Y2ggKGFzdC5vcGVyYXRvcikge1xuICAgICAgY2FzZSBvLkJpbmFyeU9wZXJhdG9yLkVxdWFsczpcbiAgICAgICAgb3BTdHIgPSAnPT0nO1xuICAgICAgICBicmVhaztcbiAgICAgIGNhc2Ugby5CaW5hcnlPcGVyYXRvci5JZGVudGljYWw6XG4gICAgICAgIG9wU3RyID0gJz09PSc7XG4gICAgICAgIGJyZWFrO1xuICAgICAgY2FzZSBvLkJpbmFyeU9wZXJhdG9yLk5vdEVxdWFsczpcbiAgICAgICAgb3BTdHIgPSAnIT0nO1xuICAgICAgICBicmVhaztcbiAgICAgIGNhc2Ugby5CaW5hcnlPcGVyYXRvci5Ob3RJZGVudGljYWw6XG4gICAgICAgIG9wU3RyID0gJyE9PSc7XG4gICAgICAgIGJyZWFrO1xuICAgICAgY2FzZSBvLkJpbmFyeU9wZXJhdG9yLkFuZDpcbiAgICAgICAgb3BTdHIgPSAnJiYnO1xuICAgICAgICBicmVhaztcbiAgICAgIGNhc2Ugby5CaW5hcnlPcGVyYXRvci5CaXR3aXNlQW5kOlxuICAgICAgICBvcFN0ciA9ICcmJztcbiAgICAgICAgYnJlYWs7XG4gICAgICBjYXNlIG8uQmluYXJ5T3BlcmF0b3IuT3I6XG4gICAgICAgIG9wU3RyID0gJ3x8JztcbiAgICAgICAgYnJlYWs7XG4gICAgICBjYXNlIG8uQmluYXJ5T3BlcmF0b3IuUGx1czpcbiAgICAgICAgb3BTdHIgPSAnKyc7XG4gICAgICAgIGJyZWFrO1xuICAgICAgY2FzZSBvLkJpbmFyeU9wZXJhdG9yLk1pbnVzOlxuICAgICAgICBvcFN0ciA9ICctJztcbiAgICAgICAgYnJlYWs7XG4gICAgICBjYXNlIG8uQmluYXJ5T3BlcmF0b3IuRGl2aWRlOlxuICAgICAgICBvcFN0ciA9ICcvJztcbiAgICAgICAgYnJlYWs7XG4gICAgICBjYXNlIG8uQmluYXJ5T3BlcmF0b3IuTXVsdGlwbHk6XG4gICAgICAgIG9wU3RyID0gJyonO1xuICAgICAgICBicmVhaztcbiAgICAgIGNhc2Ugby5CaW5hcnlPcGVyYXRvci5Nb2R1bG86XG4gICAgICAgIG9wU3RyID0gJyUnO1xuICAgICAgICBicmVhaztcbiAgICAgIGNhc2Ugby5CaW5hcnlPcGVyYXRvci5Mb3dlcjpcbiAgICAgICAgb3BTdHIgPSAnPCc7XG4gICAgICAgIGJyZWFrO1xuICAgICAgY2FzZSBvLkJpbmFyeU9wZXJhdG9yLkxvd2VyRXF1YWxzOlxuICAgICAgICBvcFN0ciA9ICc8PSc7XG4gICAgICAgIGJyZWFrO1xuICAgICAgY2FzZSBvLkJpbmFyeU9wZXJhdG9yLkJpZ2dlcjpcbiAgICAgICAgb3BTdHIgPSAnPic7XG4gICAgICAgIGJyZWFrO1xuICAgICAgY2FzZSBvLkJpbmFyeU9wZXJhdG9yLkJpZ2dlckVxdWFsczpcbiAgICAgICAgb3BTdHIgPSAnPj0nO1xuICAgICAgICBicmVhaztcbiAgICAgIGRlZmF1bHQ6XG4gICAgICAgIHRocm93IG5ldyBFcnJvcihgVW5rbm93biBvcGVyYXRvciAke2FzdC5vcGVyYXRvcn1gKTtcbiAgICB9XG4gICAgaWYgKGFzdC5wYXJlbnMpIGN0eC5wcmludChhc3QsIGAoYCk7XG4gICAgYXN0Lmxocy52aXNpdEV4cHJlc3Npb24odGhpcywgY3R4KTtcbiAgICBjdHgucHJpbnQoYXN0LCBgICR7b3BTdHJ9IGApO1xuICAgIGFzdC5yaHMudmlzaXRFeHByZXNzaW9uKHRoaXMsIGN0eCk7XG4gICAgaWYgKGFzdC5wYXJlbnMpIGN0eC5wcmludChhc3QsIGApYCk7XG4gICAgcmV0dXJuIG51bGw7XG4gIH1cblxuICB2aXNpdFJlYWRQcm9wRXhwcihhc3Q6IG8uUmVhZFByb3BFeHByLCBjdHg6IEVtaXR0ZXJWaXNpdG9yQ29udGV4dCk6IGFueSB7XG4gICAgYXN0LnJlY2VpdmVyLnZpc2l0RXhwcmVzc2lvbih0aGlzLCBjdHgpO1xuICAgIGN0eC5wcmludChhc3QsIGAuYCk7XG4gICAgY3R4LnByaW50KGFzdCwgYXN0Lm5hbWUpO1xuICAgIHJldHVybiBudWxsO1xuICB9XG4gIHZpc2l0UmVhZEtleUV4cHIoYXN0OiBvLlJlYWRLZXlFeHByLCBjdHg6IEVtaXR0ZXJWaXNpdG9yQ29udGV4dCk6IGFueSB7XG4gICAgYXN0LnJlY2VpdmVyLnZpc2l0RXhwcmVzc2lvbih0aGlzLCBjdHgpO1xuICAgIGN0eC5wcmludChhc3QsIGBbYCk7XG4gICAgYXN0LmluZGV4LnZpc2l0RXhwcmVzc2lvbih0aGlzLCBjdHgpO1xuICAgIGN0eC5wcmludChhc3QsIGBdYCk7XG4gICAgcmV0dXJuIG51bGw7XG4gIH1cbiAgdmlzaXRMaXRlcmFsQXJyYXlFeHByKGFzdDogby5MaXRlcmFsQXJyYXlFeHByLCBjdHg6IEVtaXR0ZXJWaXNpdG9yQ29udGV4dCk6IGFueSB7XG4gICAgY3R4LnByaW50KGFzdCwgYFtgKTtcbiAgICB0aGlzLnZpc2l0QWxsRXhwcmVzc2lvbnMoYXN0LmVudHJpZXMsIGN0eCwgJywnKTtcbiAgICBjdHgucHJpbnQoYXN0LCBgXWApO1xuICAgIHJldHVybiBudWxsO1xuICB9XG4gIHZpc2l0TGl0ZXJhbE1hcEV4cHIoYXN0OiBvLkxpdGVyYWxNYXBFeHByLCBjdHg6IEVtaXR0ZXJWaXNpdG9yQ29udGV4dCk6IGFueSB7XG4gICAgY3R4LnByaW50KGFzdCwgYHtgKTtcbiAgICB0aGlzLnZpc2l0QWxsT2JqZWN0cyhlbnRyeSA9PiB7XG4gICAgICBjdHgucHJpbnQoYXN0LCBgJHtlc2NhcGVJZGVudGlmaWVyKGVudHJ5LmtleSwgdGhpcy5fZXNjYXBlRG9sbGFySW5TdHJpbmdzLCBlbnRyeS5xdW90ZWQpfTpgKTtcbiAgICAgIGVudHJ5LnZhbHVlLnZpc2l0RXhwcmVzc2lvbih0aGlzLCBjdHgpO1xuICAgIH0sIGFzdC5lbnRyaWVzLCBjdHgsICcsJyk7XG4gICAgY3R4LnByaW50KGFzdCwgYH1gKTtcbiAgICByZXR1cm4gbnVsbDtcbiAgfVxuICB2aXNpdENvbW1hRXhwcihhc3Q6IG8uQ29tbWFFeHByLCBjdHg6IEVtaXR0ZXJWaXNpdG9yQ29udGV4dCk6IGFueSB7XG4gICAgY3R4LnByaW50KGFzdCwgJygnKTtcbiAgICB0aGlzLnZpc2l0QWxsRXhwcmVzc2lvbnMoYXN0LnBhcnRzLCBjdHgsICcsJyk7XG4gICAgY3R4LnByaW50KGFzdCwgJyknKTtcbiAgICByZXR1cm4gbnVsbDtcbiAgfVxuICB2aXNpdEFsbEV4cHJlc3Npb25zKGV4cHJlc3Npb25zOiBvLkV4cHJlc3Npb25bXSwgY3R4OiBFbWl0dGVyVmlzaXRvckNvbnRleHQsIHNlcGFyYXRvcjogc3RyaW5nKTpcbiAgICAgIHZvaWQge1xuICAgIHRoaXMudmlzaXRBbGxPYmplY3RzKGV4cHIgPT4gZXhwci52aXNpdEV4cHJlc3Npb24odGhpcywgY3R4KSwgZXhwcmVzc2lvbnMsIGN0eCwgc2VwYXJhdG9yKTtcbiAgfVxuXG4gIHZpc2l0QWxsT2JqZWN0czxUPihcbiAgICAgIGhhbmRsZXI6ICh0OiBUKSA9PiB2b2lkLCBleHByZXNzaW9uczogVFtdLCBjdHg6IEVtaXR0ZXJWaXNpdG9yQ29udGV4dCxcbiAgICAgIHNlcGFyYXRvcjogc3RyaW5nKTogdm9pZCB7XG4gICAgbGV0IGluY3JlbWVudGVkSW5kZW50ID0gZmFsc2U7XG4gICAgZm9yIChsZXQgaSA9IDA7IGkgPCBleHByZXNzaW9ucy5sZW5ndGg7IGkrKykge1xuICAgICAgaWYgKGkgPiAwKSB7XG4gICAgICAgIGlmIChjdHgubGluZUxlbmd0aCgpID4gODApIHtcbiAgICAgICAgICBjdHgucHJpbnQobnVsbCwgc2VwYXJhdG9yLCB0cnVlKTtcbiAgICAgICAgICBpZiAoIWluY3JlbWVudGVkSW5kZW50KSB7XG4gICAgICAgICAgICAvLyBjb250aW51YXRpb24gYXJlIG1hcmtlZCB3aXRoIGRvdWJsZSBpbmRlbnQuXG4gICAgICAgICAgICBjdHguaW5jSW5kZW50KCk7XG4gICAgICAgICAgICBjdHguaW5jSW5kZW50KCk7XG4gICAgICAgICAgICBpbmNyZW1lbnRlZEluZGVudCA9IHRydWU7XG4gICAgICAgICAgfVxuICAgICAgICB9IGVsc2Uge1xuICAgICAgICAgIGN0eC5wcmludChudWxsLCBzZXBhcmF0b3IsIGZhbHNlKTtcbiAgICAgICAgfVxuICAgICAgfVxuICAgICAgaGFuZGxlcihleHByZXNzaW9uc1tpXSk7XG4gICAgfVxuICAgIGlmIChpbmNyZW1lbnRlZEluZGVudCkge1xuICAgICAgLy8gY29udGludWF0aW9uIGFyZSBtYXJrZWQgd2l0aCBkb3VibGUgaW5kZW50LlxuICAgICAgY3R4LmRlY0luZGVudCgpO1xuICAgICAgY3R4LmRlY0luZGVudCgpO1xuICAgIH1cbiAgfVxuXG4gIHZpc2l0QWxsU3RhdGVtZW50cyhzdGF0ZW1lbnRzOiBvLlN0YXRlbWVudFtdLCBjdHg6IEVtaXR0ZXJWaXNpdG9yQ29udGV4dCk6IHZvaWQge1xuICAgIHN0YXRlbWVudHMuZm9yRWFjaCgoc3RtdCkgPT4gc3RtdC52aXNpdFN0YXRlbWVudCh0aGlzLCBjdHgpKTtcbiAgfVxufVxuXG5leHBvcnQgZnVuY3Rpb24gZXNjYXBlSWRlbnRpZmllcihcbiAgICBpbnB1dDogc3RyaW5nLCBlc2NhcGVEb2xsYXI6IGJvb2xlYW4sIGFsd2F5c1F1b3RlOiBib29sZWFuID0gdHJ1ZSk6IGFueSB7XG4gIGlmIChpbnB1dCA9PSBudWxsKSB7XG4gICAgcmV0dXJuIG51bGw7XG4gIH1cbiAgY29uc3QgYm9keSA9IGlucHV0LnJlcGxhY2UoX1NJTkdMRV9RVU9URV9FU0NBUEVfU1RSSU5HX1JFLCAoLi4ubWF0Y2g6IHN0cmluZ1tdKSA9PiB7XG4gICAgaWYgKG1hdGNoWzBdID09ICckJykge1xuICAgICAgcmV0dXJuIGVzY2FwZURvbGxhciA/ICdcXFxcJCcgOiAnJCc7XG4gICAgfSBlbHNlIGlmIChtYXRjaFswXSA9PSAnXFxuJykge1xuICAgICAgcmV0dXJuICdcXFxcbic7XG4gICAgfSBlbHNlIGlmIChtYXRjaFswXSA9PSAnXFxyJykge1xuICAgICAgcmV0dXJuICdcXFxccic7XG4gICAgfSBlbHNlIHtcbiAgICAgIHJldHVybiBgXFxcXCR7bWF0Y2hbMF19YDtcbiAgICB9XG4gIH0pO1xuICBjb25zdCByZXF1aXJlc1F1b3RlcyA9IGFsd2F5c1F1b3RlIHx8ICFfTEVHQUxfSURFTlRJRklFUl9SRS50ZXN0KGJvZHkpO1xuICByZXR1cm4gcmVxdWlyZXNRdW90ZXMgPyBgJyR7Ym9keX0nYCA6IGJvZHk7XG59XG5cbmZ1bmN0aW9uIF9jcmVhdGVJbmRlbnQoY291bnQ6IG51bWJlcik6IHN0cmluZyB7XG4gIGxldCByZXMgPSAnJztcbiAgZm9yIChsZXQgaSA9IDA7IGkgPCBjb3VudDsgaSsrKSB7XG4gICAgcmVzICs9IF9JTkRFTlRfV0lUSDtcbiAgfVxuICByZXR1cm4gcmVzO1xufVxuIl19