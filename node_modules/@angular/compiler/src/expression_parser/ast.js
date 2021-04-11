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
        define("@angular/compiler/src/expression_parser/ast", ["require", "exports", "tslib"], factory);
    }
})(function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.BoundElementProperty = exports.ParsedVariable = exports.ParsedEvent = exports.ParsedPropertyType = exports.ParsedProperty = exports.AstMemoryEfficientTransformer = exports.AstTransformer = exports.RecursiveAstVisitor = exports.ExpressionBinding = exports.VariableBinding = exports.ASTWithSource = exports.AbsoluteSourceSpan = exports.FunctionCall = exports.SafeMethodCall = exports.MethodCall = exports.NonNullAssert = exports.PrefixNot = exports.Unary = exports.Binary = exports.Interpolation = exports.LiteralMap = exports.LiteralArray = exports.LiteralPrimitive = exports.BindingPipe = exports.KeyedWrite = exports.KeyedRead = exports.SafePropertyRead = exports.PropertyWrite = exports.PropertyRead = exports.Conditional = exports.Chain = exports.ThisReceiver = exports.ImplicitReceiver = exports.EmptyExpr = exports.Quote = exports.ASTWithName = exports.AST = exports.ParseSpan = exports.ParserError = void 0;
    var tslib_1 = require("tslib");
    var ParserError = /** @class */ (function () {
        function ParserError(message, input, errLocation, ctxLocation) {
            this.input = input;
            this.errLocation = errLocation;
            this.ctxLocation = ctxLocation;
            this.message = "Parser Error: " + message + " " + errLocation + " [" + input + "] in " + ctxLocation;
        }
        return ParserError;
    }());
    exports.ParserError = ParserError;
    var ParseSpan = /** @class */ (function () {
        function ParseSpan(start, end) {
            this.start = start;
            this.end = end;
        }
        ParseSpan.prototype.toAbsolute = function (absoluteOffset) {
            return new AbsoluteSourceSpan(absoluteOffset + this.start, absoluteOffset + this.end);
        };
        return ParseSpan;
    }());
    exports.ParseSpan = ParseSpan;
    var AST = /** @class */ (function () {
        function AST(span, 
        /**
         * Absolute location of the expression AST in a source code file.
         */
        sourceSpan) {
            this.span = span;
            this.sourceSpan = sourceSpan;
        }
        AST.prototype.visit = function (visitor, context) {
            if (context === void 0) { context = null; }
            return null;
        };
        AST.prototype.toString = function () {
            return 'AST';
        };
        return AST;
    }());
    exports.AST = AST;
    var ASTWithName = /** @class */ (function (_super) {
        tslib_1.__extends(ASTWithName, _super);
        function ASTWithName(span, sourceSpan, nameSpan) {
            var _this = _super.call(this, span, sourceSpan) || this;
            _this.nameSpan = nameSpan;
            return _this;
        }
        return ASTWithName;
    }(AST));
    exports.ASTWithName = ASTWithName;
    /**
     * Represents a quoted expression of the form:
     *
     * quote = prefix `:` uninterpretedExpression
     * prefix = identifier
     * uninterpretedExpression = arbitrary string
     *
     * A quoted expression is meant to be pre-processed by an AST transformer that
     * converts it into another AST that no longer contains quoted expressions.
     * It is meant to allow third-party developers to extend Angular template
     * expression language. The `uninterpretedExpression` part of the quote is
     * therefore not interpreted by the Angular's own expression parser.
     */
    var Quote = /** @class */ (function (_super) {
        tslib_1.__extends(Quote, _super);
        function Quote(span, sourceSpan, prefix, uninterpretedExpression, location) {
            var _this = _super.call(this, span, sourceSpan) || this;
            _this.prefix = prefix;
            _this.uninterpretedExpression = uninterpretedExpression;
            _this.location = location;
            return _this;
        }
        Quote.prototype.visit = function (visitor, context) {
            if (context === void 0) { context = null; }
            return visitor.visitQuote(this, context);
        };
        Quote.prototype.toString = function () {
            return 'Quote';
        };
        return Quote;
    }(AST));
    exports.Quote = Quote;
    var EmptyExpr = /** @class */ (function (_super) {
        tslib_1.__extends(EmptyExpr, _super);
        function EmptyExpr() {
            return _super !== null && _super.apply(this, arguments) || this;
        }
        EmptyExpr.prototype.visit = function (visitor, context) {
            if (context === void 0) { context = null; }
            // do nothing
        };
        return EmptyExpr;
    }(AST));
    exports.EmptyExpr = EmptyExpr;
    var ImplicitReceiver = /** @class */ (function (_super) {
        tslib_1.__extends(ImplicitReceiver, _super);
        function ImplicitReceiver() {
            return _super !== null && _super.apply(this, arguments) || this;
        }
        ImplicitReceiver.prototype.visit = function (visitor, context) {
            if (context === void 0) { context = null; }
            return visitor.visitImplicitReceiver(this, context);
        };
        return ImplicitReceiver;
    }(AST));
    exports.ImplicitReceiver = ImplicitReceiver;
    /**
     * Receiver when something is accessed through `this` (e.g. `this.foo`). Note that this class
     * inherits from `ImplicitReceiver`, because accessing something through `this` is treated the
     * same as accessing it implicitly inside of an Angular template (e.g. `[attr.title]="this.title"`
     * is the same as `[attr.title]="title"`.). Inheriting allows for the `this` accesses to be treated
     * the same as implicit ones, except for a couple of exceptions like `$event` and `$any`.
     * TODO: we should find a way for this class not to extend from `ImplicitReceiver` in the future.
     */
    var ThisReceiver = /** @class */ (function (_super) {
        tslib_1.__extends(ThisReceiver, _super);
        function ThisReceiver() {
            return _super !== null && _super.apply(this, arguments) || this;
        }
        ThisReceiver.prototype.visit = function (visitor, context) {
            var _a;
            if (context === void 0) { context = null; }
            return (_a = visitor.visitThisReceiver) === null || _a === void 0 ? void 0 : _a.call(visitor, this, context);
        };
        return ThisReceiver;
    }(ImplicitReceiver));
    exports.ThisReceiver = ThisReceiver;
    /**
     * Multiple expressions separated by a semicolon.
     */
    var Chain = /** @class */ (function (_super) {
        tslib_1.__extends(Chain, _super);
        function Chain(span, sourceSpan, expressions) {
            var _this = _super.call(this, span, sourceSpan) || this;
            _this.expressions = expressions;
            return _this;
        }
        Chain.prototype.visit = function (visitor, context) {
            if (context === void 0) { context = null; }
            return visitor.visitChain(this, context);
        };
        return Chain;
    }(AST));
    exports.Chain = Chain;
    var Conditional = /** @class */ (function (_super) {
        tslib_1.__extends(Conditional, _super);
        function Conditional(span, sourceSpan, condition, trueExp, falseExp) {
            var _this = _super.call(this, span, sourceSpan) || this;
            _this.condition = condition;
            _this.trueExp = trueExp;
            _this.falseExp = falseExp;
            return _this;
        }
        Conditional.prototype.visit = function (visitor, context) {
            if (context === void 0) { context = null; }
            return visitor.visitConditional(this, context);
        };
        return Conditional;
    }(AST));
    exports.Conditional = Conditional;
    var PropertyRead = /** @class */ (function (_super) {
        tslib_1.__extends(PropertyRead, _super);
        function PropertyRead(span, sourceSpan, nameSpan, receiver, name) {
            var _this = _super.call(this, span, sourceSpan, nameSpan) || this;
            _this.receiver = receiver;
            _this.name = name;
            return _this;
        }
        PropertyRead.prototype.visit = function (visitor, context) {
            if (context === void 0) { context = null; }
            return visitor.visitPropertyRead(this, context);
        };
        return PropertyRead;
    }(ASTWithName));
    exports.PropertyRead = PropertyRead;
    var PropertyWrite = /** @class */ (function (_super) {
        tslib_1.__extends(PropertyWrite, _super);
        function PropertyWrite(span, sourceSpan, nameSpan, receiver, name, value) {
            var _this = _super.call(this, span, sourceSpan, nameSpan) || this;
            _this.receiver = receiver;
            _this.name = name;
            _this.value = value;
            return _this;
        }
        PropertyWrite.prototype.visit = function (visitor, context) {
            if (context === void 0) { context = null; }
            return visitor.visitPropertyWrite(this, context);
        };
        return PropertyWrite;
    }(ASTWithName));
    exports.PropertyWrite = PropertyWrite;
    var SafePropertyRead = /** @class */ (function (_super) {
        tslib_1.__extends(SafePropertyRead, _super);
        function SafePropertyRead(span, sourceSpan, nameSpan, receiver, name) {
            var _this = _super.call(this, span, sourceSpan, nameSpan) || this;
            _this.receiver = receiver;
            _this.name = name;
            return _this;
        }
        SafePropertyRead.prototype.visit = function (visitor, context) {
            if (context === void 0) { context = null; }
            return visitor.visitSafePropertyRead(this, context);
        };
        return SafePropertyRead;
    }(ASTWithName));
    exports.SafePropertyRead = SafePropertyRead;
    var KeyedRead = /** @class */ (function (_super) {
        tslib_1.__extends(KeyedRead, _super);
        function KeyedRead(span, sourceSpan, obj, key) {
            var _this = _super.call(this, span, sourceSpan) || this;
            _this.obj = obj;
            _this.key = key;
            return _this;
        }
        KeyedRead.prototype.visit = function (visitor, context) {
            if (context === void 0) { context = null; }
            return visitor.visitKeyedRead(this, context);
        };
        return KeyedRead;
    }(AST));
    exports.KeyedRead = KeyedRead;
    var KeyedWrite = /** @class */ (function (_super) {
        tslib_1.__extends(KeyedWrite, _super);
        function KeyedWrite(span, sourceSpan, obj, key, value) {
            var _this = _super.call(this, span, sourceSpan) || this;
            _this.obj = obj;
            _this.key = key;
            _this.value = value;
            return _this;
        }
        KeyedWrite.prototype.visit = function (visitor, context) {
            if (context === void 0) { context = null; }
            return visitor.visitKeyedWrite(this, context);
        };
        return KeyedWrite;
    }(AST));
    exports.KeyedWrite = KeyedWrite;
    var BindingPipe = /** @class */ (function (_super) {
        tslib_1.__extends(BindingPipe, _super);
        function BindingPipe(span, sourceSpan, exp, name, args, nameSpan) {
            var _this = _super.call(this, span, sourceSpan, nameSpan) || this;
            _this.exp = exp;
            _this.name = name;
            _this.args = args;
            return _this;
        }
        BindingPipe.prototype.visit = function (visitor, context) {
            if (context === void 0) { context = null; }
            return visitor.visitPipe(this, context);
        };
        return BindingPipe;
    }(ASTWithName));
    exports.BindingPipe = BindingPipe;
    var LiteralPrimitive = /** @class */ (function (_super) {
        tslib_1.__extends(LiteralPrimitive, _super);
        function LiteralPrimitive(span, sourceSpan, value) {
            var _this = _super.call(this, span, sourceSpan) || this;
            _this.value = value;
            return _this;
        }
        LiteralPrimitive.prototype.visit = function (visitor, context) {
            if (context === void 0) { context = null; }
            return visitor.visitLiteralPrimitive(this, context);
        };
        return LiteralPrimitive;
    }(AST));
    exports.LiteralPrimitive = LiteralPrimitive;
    var LiteralArray = /** @class */ (function (_super) {
        tslib_1.__extends(LiteralArray, _super);
        function LiteralArray(span, sourceSpan, expressions) {
            var _this = _super.call(this, span, sourceSpan) || this;
            _this.expressions = expressions;
            return _this;
        }
        LiteralArray.prototype.visit = function (visitor, context) {
            if (context === void 0) { context = null; }
            return visitor.visitLiteralArray(this, context);
        };
        return LiteralArray;
    }(AST));
    exports.LiteralArray = LiteralArray;
    var LiteralMap = /** @class */ (function (_super) {
        tslib_1.__extends(LiteralMap, _super);
        function LiteralMap(span, sourceSpan, keys, values) {
            var _this = _super.call(this, span, sourceSpan) || this;
            _this.keys = keys;
            _this.values = values;
            return _this;
        }
        LiteralMap.prototype.visit = function (visitor, context) {
            if (context === void 0) { context = null; }
            return visitor.visitLiteralMap(this, context);
        };
        return LiteralMap;
    }(AST));
    exports.LiteralMap = LiteralMap;
    var Interpolation = /** @class */ (function (_super) {
        tslib_1.__extends(Interpolation, _super);
        function Interpolation(span, sourceSpan, strings, expressions) {
            var _this = _super.call(this, span, sourceSpan) || this;
            _this.strings = strings;
            _this.expressions = expressions;
            return _this;
        }
        Interpolation.prototype.visit = function (visitor, context) {
            if (context === void 0) { context = null; }
            return visitor.visitInterpolation(this, context);
        };
        return Interpolation;
    }(AST));
    exports.Interpolation = Interpolation;
    var Binary = /** @class */ (function (_super) {
        tslib_1.__extends(Binary, _super);
        function Binary(span, sourceSpan, operation, left, right) {
            var _this = _super.call(this, span, sourceSpan) || this;
            _this.operation = operation;
            _this.left = left;
            _this.right = right;
            return _this;
        }
        Binary.prototype.visit = function (visitor, context) {
            if (context === void 0) { context = null; }
            return visitor.visitBinary(this, context);
        };
        return Binary;
    }(AST));
    exports.Binary = Binary;
    /**
     * For backwards compatibility reasons, `Unary` inherits from `Binary` and mimics the binary AST
     * node that was originally used. This inheritance relation can be deleted in some future major,
     * after consumers have been given a chance to fully support Unary.
     */
    var Unary = /** @class */ (function (_super) {
        tslib_1.__extends(Unary, _super);
        /**
         * During the deprecation period this constructor is private, to avoid consumers from creating
         * a `Unary` with the fallback properties for `Binary`.
         */
        function Unary(span, sourceSpan, operator, expr, binaryOp, binaryLeft, binaryRight) {
            var _this = _super.call(this, span, sourceSpan, binaryOp, binaryLeft, binaryRight) || this;
            _this.operator = operator;
            _this.expr = expr;
            return _this;
        }
        /**
         * Creates a unary minus expression "-x", represented as `Binary` using "0 - x".
         */
        Unary.createMinus = function (span, sourceSpan, expr) {
            return new Unary(span, sourceSpan, '-', expr, '-', new LiteralPrimitive(span, sourceSpan, 0), expr);
        };
        /**
         * Creates a unary plus expression "+x", represented as `Binary` using "x - 0".
         */
        Unary.createPlus = function (span, sourceSpan, expr) {
            return new Unary(span, sourceSpan, '+', expr, '-', expr, new LiteralPrimitive(span, sourceSpan, 0));
        };
        Unary.prototype.visit = function (visitor, context) {
            if (context === void 0) { context = null; }
            if (visitor.visitUnary !== undefined) {
                return visitor.visitUnary(this, context);
            }
            return visitor.visitBinary(this, context);
        };
        return Unary;
    }(Binary));
    exports.Unary = Unary;
    var PrefixNot = /** @class */ (function (_super) {
        tslib_1.__extends(PrefixNot, _super);
        function PrefixNot(span, sourceSpan, expression) {
            var _this = _super.call(this, span, sourceSpan) || this;
            _this.expression = expression;
            return _this;
        }
        PrefixNot.prototype.visit = function (visitor, context) {
            if (context === void 0) { context = null; }
            return visitor.visitPrefixNot(this, context);
        };
        return PrefixNot;
    }(AST));
    exports.PrefixNot = PrefixNot;
    var NonNullAssert = /** @class */ (function (_super) {
        tslib_1.__extends(NonNullAssert, _super);
        function NonNullAssert(span, sourceSpan, expression) {
            var _this = _super.call(this, span, sourceSpan) || this;
            _this.expression = expression;
            return _this;
        }
        NonNullAssert.prototype.visit = function (visitor, context) {
            if (context === void 0) { context = null; }
            return visitor.visitNonNullAssert(this, context);
        };
        return NonNullAssert;
    }(AST));
    exports.NonNullAssert = NonNullAssert;
    var MethodCall = /** @class */ (function (_super) {
        tslib_1.__extends(MethodCall, _super);
        function MethodCall(span, sourceSpan, nameSpan, receiver, name, args) {
            var _this = _super.call(this, span, sourceSpan, nameSpan) || this;
            _this.receiver = receiver;
            _this.name = name;
            _this.args = args;
            return _this;
        }
        MethodCall.prototype.visit = function (visitor, context) {
            if (context === void 0) { context = null; }
            return visitor.visitMethodCall(this, context);
        };
        return MethodCall;
    }(ASTWithName));
    exports.MethodCall = MethodCall;
    var SafeMethodCall = /** @class */ (function (_super) {
        tslib_1.__extends(SafeMethodCall, _super);
        function SafeMethodCall(span, sourceSpan, nameSpan, receiver, name, args) {
            var _this = _super.call(this, span, sourceSpan, nameSpan) || this;
            _this.receiver = receiver;
            _this.name = name;
            _this.args = args;
            return _this;
        }
        SafeMethodCall.prototype.visit = function (visitor, context) {
            if (context === void 0) { context = null; }
            return visitor.visitSafeMethodCall(this, context);
        };
        return SafeMethodCall;
    }(ASTWithName));
    exports.SafeMethodCall = SafeMethodCall;
    var FunctionCall = /** @class */ (function (_super) {
        tslib_1.__extends(FunctionCall, _super);
        function FunctionCall(span, sourceSpan, target, args) {
            var _this = _super.call(this, span, sourceSpan) || this;
            _this.target = target;
            _this.args = args;
            return _this;
        }
        FunctionCall.prototype.visit = function (visitor, context) {
            if (context === void 0) { context = null; }
            return visitor.visitFunctionCall(this, context);
        };
        return FunctionCall;
    }(AST));
    exports.FunctionCall = FunctionCall;
    /**
     * Records the absolute position of a text span in a source file, where `start` and `end` are the
     * starting and ending byte offsets, respectively, of the text span in a source file.
     */
    var AbsoluteSourceSpan = /** @class */ (function () {
        function AbsoluteSourceSpan(start, end) {
            this.start = start;
            this.end = end;
        }
        return AbsoluteSourceSpan;
    }());
    exports.AbsoluteSourceSpan = AbsoluteSourceSpan;
    var ASTWithSource = /** @class */ (function (_super) {
        tslib_1.__extends(ASTWithSource, _super);
        function ASTWithSource(ast, source, location, absoluteOffset, errors) {
            var _this = _super.call(this, new ParseSpan(0, source === null ? 0 : source.length), new AbsoluteSourceSpan(absoluteOffset, source === null ? absoluteOffset : absoluteOffset + source.length)) || this;
            _this.ast = ast;
            _this.source = source;
            _this.location = location;
            _this.errors = errors;
            return _this;
        }
        ASTWithSource.prototype.visit = function (visitor, context) {
            if (context === void 0) { context = null; }
            if (visitor.visitASTWithSource) {
                return visitor.visitASTWithSource(this, context);
            }
            return this.ast.visit(visitor, context);
        };
        ASTWithSource.prototype.toString = function () {
            return this.source + " in " + this.location;
        };
        return ASTWithSource;
    }(AST));
    exports.ASTWithSource = ASTWithSource;
    var VariableBinding = /** @class */ (function () {
        /**
         * @param sourceSpan entire span of the binding.
         * @param key name of the LHS along with its span.
         * @param value optional value for the RHS along with its span.
         */
        function VariableBinding(sourceSpan, key, value) {
            this.sourceSpan = sourceSpan;
            this.key = key;
            this.value = value;
        }
        return VariableBinding;
    }());
    exports.VariableBinding = VariableBinding;
    var ExpressionBinding = /** @class */ (function () {
        /**
         * @param sourceSpan entire span of the binding.
         * @param key binding name, like ngForOf, ngForTrackBy, ngIf, along with its
         * span. Note that the length of the span may not be the same as
         * `key.source.length`. For example,
         * 1. key.source = ngFor, key.span is for "ngFor"
         * 2. key.source = ngForOf, key.span is for "of"
         * 3. key.source = ngForTrackBy, key.span is for "trackBy"
         * @param value optional expression for the RHS.
         */
        function ExpressionBinding(sourceSpan, key, value) {
            this.sourceSpan = sourceSpan;
            this.key = key;
            this.value = value;
        }
        return ExpressionBinding;
    }());
    exports.ExpressionBinding = ExpressionBinding;
    var RecursiveAstVisitor = /** @class */ (function () {
        function RecursiveAstVisitor() {
        }
        RecursiveAstVisitor.prototype.visit = function (ast, context) {
            // The default implementation just visits every node.
            // Classes that extend RecursiveAstVisitor should override this function
            // to selectively visit the specified node.
            ast.visit(this, context);
        };
        RecursiveAstVisitor.prototype.visitUnary = function (ast, context) {
            this.visit(ast.expr, context);
        };
        RecursiveAstVisitor.prototype.visitBinary = function (ast, context) {
            this.visit(ast.left, context);
            this.visit(ast.right, context);
        };
        RecursiveAstVisitor.prototype.visitChain = function (ast, context) {
            this.visitAll(ast.expressions, context);
        };
        RecursiveAstVisitor.prototype.visitConditional = function (ast, context) {
            this.visit(ast.condition, context);
            this.visit(ast.trueExp, context);
            this.visit(ast.falseExp, context);
        };
        RecursiveAstVisitor.prototype.visitPipe = function (ast, context) {
            this.visit(ast.exp, context);
            this.visitAll(ast.args, context);
        };
        RecursiveAstVisitor.prototype.visitFunctionCall = function (ast, context) {
            if (ast.target) {
                this.visit(ast.target, context);
            }
            this.visitAll(ast.args, context);
        };
        RecursiveAstVisitor.prototype.visitImplicitReceiver = function (ast, context) { };
        RecursiveAstVisitor.prototype.visitThisReceiver = function (ast, context) { };
        RecursiveAstVisitor.prototype.visitInterpolation = function (ast, context) {
            this.visitAll(ast.expressions, context);
        };
        RecursiveAstVisitor.prototype.visitKeyedRead = function (ast, context) {
            this.visit(ast.obj, context);
            this.visit(ast.key, context);
        };
        RecursiveAstVisitor.prototype.visitKeyedWrite = function (ast, context) {
            this.visit(ast.obj, context);
            this.visit(ast.key, context);
            this.visit(ast.value, context);
        };
        RecursiveAstVisitor.prototype.visitLiteralArray = function (ast, context) {
            this.visitAll(ast.expressions, context);
        };
        RecursiveAstVisitor.prototype.visitLiteralMap = function (ast, context) {
            this.visitAll(ast.values, context);
        };
        RecursiveAstVisitor.prototype.visitLiteralPrimitive = function (ast, context) { };
        RecursiveAstVisitor.prototype.visitMethodCall = function (ast, context) {
            this.visit(ast.receiver, context);
            this.visitAll(ast.args, context);
        };
        RecursiveAstVisitor.prototype.visitPrefixNot = function (ast, context) {
            this.visit(ast.expression, context);
        };
        RecursiveAstVisitor.prototype.visitNonNullAssert = function (ast, context) {
            this.visit(ast.expression, context);
        };
        RecursiveAstVisitor.prototype.visitPropertyRead = function (ast, context) {
            this.visit(ast.receiver, context);
        };
        RecursiveAstVisitor.prototype.visitPropertyWrite = function (ast, context) {
            this.visit(ast.receiver, context);
            this.visit(ast.value, context);
        };
        RecursiveAstVisitor.prototype.visitSafePropertyRead = function (ast, context) {
            this.visit(ast.receiver, context);
        };
        RecursiveAstVisitor.prototype.visitSafeMethodCall = function (ast, context) {
            this.visit(ast.receiver, context);
            this.visitAll(ast.args, context);
        };
        RecursiveAstVisitor.prototype.visitQuote = function (ast, context) { };
        // This is not part of the AstVisitor interface, just a helper method
        RecursiveAstVisitor.prototype.visitAll = function (asts, context) {
            var e_1, _a;
            try {
                for (var asts_1 = tslib_1.__values(asts), asts_1_1 = asts_1.next(); !asts_1_1.done; asts_1_1 = asts_1.next()) {
                    var ast = asts_1_1.value;
                    this.visit(ast, context);
                }
            }
            catch (e_1_1) { e_1 = { error: e_1_1 }; }
            finally {
                try {
                    if (asts_1_1 && !asts_1_1.done && (_a = asts_1.return)) _a.call(asts_1);
                }
                finally { if (e_1) throw e_1.error; }
            }
        };
        return RecursiveAstVisitor;
    }());
    exports.RecursiveAstVisitor = RecursiveAstVisitor;
    var AstTransformer = /** @class */ (function () {
        function AstTransformer() {
        }
        AstTransformer.prototype.visitImplicitReceiver = function (ast, context) {
            return ast;
        };
        AstTransformer.prototype.visitThisReceiver = function (ast, context) {
            return ast;
        };
        AstTransformer.prototype.visitInterpolation = function (ast, context) {
            return new Interpolation(ast.span, ast.sourceSpan, ast.strings, this.visitAll(ast.expressions));
        };
        AstTransformer.prototype.visitLiteralPrimitive = function (ast, context) {
            return new LiteralPrimitive(ast.span, ast.sourceSpan, ast.value);
        };
        AstTransformer.prototype.visitPropertyRead = function (ast, context) {
            return new PropertyRead(ast.span, ast.sourceSpan, ast.nameSpan, ast.receiver.visit(this), ast.name);
        };
        AstTransformer.prototype.visitPropertyWrite = function (ast, context) {
            return new PropertyWrite(ast.span, ast.sourceSpan, ast.nameSpan, ast.receiver.visit(this), ast.name, ast.value.visit(this));
        };
        AstTransformer.prototype.visitSafePropertyRead = function (ast, context) {
            return new SafePropertyRead(ast.span, ast.sourceSpan, ast.nameSpan, ast.receiver.visit(this), ast.name);
        };
        AstTransformer.prototype.visitMethodCall = function (ast, context) {
            return new MethodCall(ast.span, ast.sourceSpan, ast.nameSpan, ast.receiver.visit(this), ast.name, this.visitAll(ast.args));
        };
        AstTransformer.prototype.visitSafeMethodCall = function (ast, context) {
            return new SafeMethodCall(ast.span, ast.sourceSpan, ast.nameSpan, ast.receiver.visit(this), ast.name, this.visitAll(ast.args));
        };
        AstTransformer.prototype.visitFunctionCall = function (ast, context) {
            return new FunctionCall(ast.span, ast.sourceSpan, ast.target.visit(this), this.visitAll(ast.args));
        };
        AstTransformer.prototype.visitLiteralArray = function (ast, context) {
            return new LiteralArray(ast.span, ast.sourceSpan, this.visitAll(ast.expressions));
        };
        AstTransformer.prototype.visitLiteralMap = function (ast, context) {
            return new LiteralMap(ast.span, ast.sourceSpan, ast.keys, this.visitAll(ast.values));
        };
        AstTransformer.prototype.visitUnary = function (ast, context) {
            switch (ast.operator) {
                case '+':
                    return Unary.createPlus(ast.span, ast.sourceSpan, ast.expr.visit(this));
                case '-':
                    return Unary.createMinus(ast.span, ast.sourceSpan, ast.expr.visit(this));
                default:
                    throw new Error("Unknown unary operator " + ast.operator);
            }
        };
        AstTransformer.prototype.visitBinary = function (ast, context) {
            return new Binary(ast.span, ast.sourceSpan, ast.operation, ast.left.visit(this), ast.right.visit(this));
        };
        AstTransformer.prototype.visitPrefixNot = function (ast, context) {
            return new PrefixNot(ast.span, ast.sourceSpan, ast.expression.visit(this));
        };
        AstTransformer.prototype.visitNonNullAssert = function (ast, context) {
            return new NonNullAssert(ast.span, ast.sourceSpan, ast.expression.visit(this));
        };
        AstTransformer.prototype.visitConditional = function (ast, context) {
            return new Conditional(ast.span, ast.sourceSpan, ast.condition.visit(this), ast.trueExp.visit(this), ast.falseExp.visit(this));
        };
        AstTransformer.prototype.visitPipe = function (ast, context) {
            return new BindingPipe(ast.span, ast.sourceSpan, ast.exp.visit(this), ast.name, this.visitAll(ast.args), ast.nameSpan);
        };
        AstTransformer.prototype.visitKeyedRead = function (ast, context) {
            return new KeyedRead(ast.span, ast.sourceSpan, ast.obj.visit(this), ast.key.visit(this));
        };
        AstTransformer.prototype.visitKeyedWrite = function (ast, context) {
            return new KeyedWrite(ast.span, ast.sourceSpan, ast.obj.visit(this), ast.key.visit(this), ast.value.visit(this));
        };
        AstTransformer.prototype.visitAll = function (asts) {
            var res = [];
            for (var i = 0; i < asts.length; ++i) {
                res[i] = asts[i].visit(this);
            }
            return res;
        };
        AstTransformer.prototype.visitChain = function (ast, context) {
            return new Chain(ast.span, ast.sourceSpan, this.visitAll(ast.expressions));
        };
        AstTransformer.prototype.visitQuote = function (ast, context) {
            return new Quote(ast.span, ast.sourceSpan, ast.prefix, ast.uninterpretedExpression, ast.location);
        };
        return AstTransformer;
    }());
    exports.AstTransformer = AstTransformer;
    // A transformer that only creates new nodes if the transformer makes a change or
    // a change is made a child node.
    var AstMemoryEfficientTransformer = /** @class */ (function () {
        function AstMemoryEfficientTransformer() {
        }
        AstMemoryEfficientTransformer.prototype.visitImplicitReceiver = function (ast, context) {
            return ast;
        };
        AstMemoryEfficientTransformer.prototype.visitThisReceiver = function (ast, context) {
            return ast;
        };
        AstMemoryEfficientTransformer.prototype.visitInterpolation = function (ast, context) {
            var expressions = this.visitAll(ast.expressions);
            if (expressions !== ast.expressions)
                return new Interpolation(ast.span, ast.sourceSpan, ast.strings, expressions);
            return ast;
        };
        AstMemoryEfficientTransformer.prototype.visitLiteralPrimitive = function (ast, context) {
            return ast;
        };
        AstMemoryEfficientTransformer.prototype.visitPropertyRead = function (ast, context) {
            var receiver = ast.receiver.visit(this);
            if (receiver !== ast.receiver) {
                return new PropertyRead(ast.span, ast.sourceSpan, ast.nameSpan, receiver, ast.name);
            }
            return ast;
        };
        AstMemoryEfficientTransformer.prototype.visitPropertyWrite = function (ast, context) {
            var receiver = ast.receiver.visit(this);
            var value = ast.value.visit(this);
            if (receiver !== ast.receiver || value !== ast.value) {
                return new PropertyWrite(ast.span, ast.sourceSpan, ast.nameSpan, receiver, ast.name, value);
            }
            return ast;
        };
        AstMemoryEfficientTransformer.prototype.visitSafePropertyRead = function (ast, context) {
            var receiver = ast.receiver.visit(this);
            if (receiver !== ast.receiver) {
                return new SafePropertyRead(ast.span, ast.sourceSpan, ast.nameSpan, receiver, ast.name);
            }
            return ast;
        };
        AstMemoryEfficientTransformer.prototype.visitMethodCall = function (ast, context) {
            var receiver = ast.receiver.visit(this);
            var args = this.visitAll(ast.args);
            if (receiver !== ast.receiver || args !== ast.args) {
                return new MethodCall(ast.span, ast.sourceSpan, ast.nameSpan, receiver, ast.name, args);
            }
            return ast;
        };
        AstMemoryEfficientTransformer.prototype.visitSafeMethodCall = function (ast, context) {
            var receiver = ast.receiver.visit(this);
            var args = this.visitAll(ast.args);
            if (receiver !== ast.receiver || args !== ast.args) {
                return new SafeMethodCall(ast.span, ast.sourceSpan, ast.nameSpan, receiver, ast.name, args);
            }
            return ast;
        };
        AstMemoryEfficientTransformer.prototype.visitFunctionCall = function (ast, context) {
            var target = ast.target && ast.target.visit(this);
            var args = this.visitAll(ast.args);
            if (target !== ast.target || args !== ast.args) {
                return new FunctionCall(ast.span, ast.sourceSpan, target, args);
            }
            return ast;
        };
        AstMemoryEfficientTransformer.prototype.visitLiteralArray = function (ast, context) {
            var expressions = this.visitAll(ast.expressions);
            if (expressions !== ast.expressions) {
                return new LiteralArray(ast.span, ast.sourceSpan, expressions);
            }
            return ast;
        };
        AstMemoryEfficientTransformer.prototype.visitLiteralMap = function (ast, context) {
            var values = this.visitAll(ast.values);
            if (values !== ast.values) {
                return new LiteralMap(ast.span, ast.sourceSpan, ast.keys, values);
            }
            return ast;
        };
        AstMemoryEfficientTransformer.prototype.visitUnary = function (ast, context) {
            var expr = ast.expr.visit(this);
            if (expr !== ast.expr) {
                switch (ast.operator) {
                    case '+':
                        return Unary.createPlus(ast.span, ast.sourceSpan, expr);
                    case '-':
                        return Unary.createMinus(ast.span, ast.sourceSpan, expr);
                    default:
                        throw new Error("Unknown unary operator " + ast.operator);
                }
            }
            return ast;
        };
        AstMemoryEfficientTransformer.prototype.visitBinary = function (ast, context) {
            var left = ast.left.visit(this);
            var right = ast.right.visit(this);
            if (left !== ast.left || right !== ast.right) {
                return new Binary(ast.span, ast.sourceSpan, ast.operation, left, right);
            }
            return ast;
        };
        AstMemoryEfficientTransformer.prototype.visitPrefixNot = function (ast, context) {
            var expression = ast.expression.visit(this);
            if (expression !== ast.expression) {
                return new PrefixNot(ast.span, ast.sourceSpan, expression);
            }
            return ast;
        };
        AstMemoryEfficientTransformer.prototype.visitNonNullAssert = function (ast, context) {
            var expression = ast.expression.visit(this);
            if (expression !== ast.expression) {
                return new NonNullAssert(ast.span, ast.sourceSpan, expression);
            }
            return ast;
        };
        AstMemoryEfficientTransformer.prototype.visitConditional = function (ast, context) {
            var condition = ast.condition.visit(this);
            var trueExp = ast.trueExp.visit(this);
            var falseExp = ast.falseExp.visit(this);
            if (condition !== ast.condition || trueExp !== ast.trueExp || falseExp !== ast.falseExp) {
                return new Conditional(ast.span, ast.sourceSpan, condition, trueExp, falseExp);
            }
            return ast;
        };
        AstMemoryEfficientTransformer.prototype.visitPipe = function (ast, context) {
            var exp = ast.exp.visit(this);
            var args = this.visitAll(ast.args);
            if (exp !== ast.exp || args !== ast.args) {
                return new BindingPipe(ast.span, ast.sourceSpan, exp, ast.name, args, ast.nameSpan);
            }
            return ast;
        };
        AstMemoryEfficientTransformer.prototype.visitKeyedRead = function (ast, context) {
            var obj = ast.obj.visit(this);
            var key = ast.key.visit(this);
            if (obj !== ast.obj || key !== ast.key) {
                return new KeyedRead(ast.span, ast.sourceSpan, obj, key);
            }
            return ast;
        };
        AstMemoryEfficientTransformer.prototype.visitKeyedWrite = function (ast, context) {
            var obj = ast.obj.visit(this);
            var key = ast.key.visit(this);
            var value = ast.value.visit(this);
            if (obj !== ast.obj || key !== ast.key || value !== ast.value) {
                return new KeyedWrite(ast.span, ast.sourceSpan, obj, key, value);
            }
            return ast;
        };
        AstMemoryEfficientTransformer.prototype.visitAll = function (asts) {
            var res = [];
            var modified = false;
            for (var i = 0; i < asts.length; ++i) {
                var original = asts[i];
                var value = original.visit(this);
                res[i] = value;
                modified = modified || value !== original;
            }
            return modified ? res : asts;
        };
        AstMemoryEfficientTransformer.prototype.visitChain = function (ast, context) {
            var expressions = this.visitAll(ast.expressions);
            if (expressions !== ast.expressions) {
                return new Chain(ast.span, ast.sourceSpan, expressions);
            }
            return ast;
        };
        AstMemoryEfficientTransformer.prototype.visitQuote = function (ast, context) {
            return ast;
        };
        return AstMemoryEfficientTransformer;
    }());
    exports.AstMemoryEfficientTransformer = AstMemoryEfficientTransformer;
    // Bindings
    var ParsedProperty = /** @class */ (function () {
        function ParsedProperty(name, expression, type, 
        // TODO(FW-2095): `keySpan` should really be required but allows `undefined` so VE does
        // not need to be updated. Make `keySpan` required when VE is removed.
        sourceSpan, keySpan, valueSpan) {
            this.name = name;
            this.expression = expression;
            this.type = type;
            this.sourceSpan = sourceSpan;
            this.keySpan = keySpan;
            this.valueSpan = valueSpan;
            this.isLiteral = this.type === ParsedPropertyType.LITERAL_ATTR;
            this.isAnimation = this.type === ParsedPropertyType.ANIMATION;
        }
        return ParsedProperty;
    }());
    exports.ParsedProperty = ParsedProperty;
    var ParsedPropertyType;
    (function (ParsedPropertyType) {
        ParsedPropertyType[ParsedPropertyType["DEFAULT"] = 0] = "DEFAULT";
        ParsedPropertyType[ParsedPropertyType["LITERAL_ATTR"] = 1] = "LITERAL_ATTR";
        ParsedPropertyType[ParsedPropertyType["ANIMATION"] = 2] = "ANIMATION";
    })(ParsedPropertyType = exports.ParsedPropertyType || (exports.ParsedPropertyType = {}));
    var ParsedEvent = /** @class */ (function () {
        // Regular events have a target
        // Animation events have a phase
        function ParsedEvent(name, targetOrPhase, type, handler, sourceSpan, 
        // TODO(FW-2095): keySpan should be required but was made optional to avoid changing VE
        handlerSpan, keySpan) {
            this.name = name;
            this.targetOrPhase = targetOrPhase;
            this.type = type;
            this.handler = handler;
            this.sourceSpan = sourceSpan;
            this.handlerSpan = handlerSpan;
            this.keySpan = keySpan;
        }
        return ParsedEvent;
    }());
    exports.ParsedEvent = ParsedEvent;
    /**
     * ParsedVariable represents a variable declaration in a microsyntax expression.
     */
    var ParsedVariable = /** @class */ (function () {
        function ParsedVariable(name, value, sourceSpan, keySpan, valueSpan) {
            this.name = name;
            this.value = value;
            this.sourceSpan = sourceSpan;
            this.keySpan = keySpan;
            this.valueSpan = valueSpan;
        }
        return ParsedVariable;
    }());
    exports.ParsedVariable = ParsedVariable;
    var BoundElementProperty = /** @class */ (function () {
        function BoundElementProperty(name, type, securityContext, value, unit, sourceSpan, keySpan, valueSpan) {
            this.name = name;
            this.type = type;
            this.securityContext = securityContext;
            this.value = value;
            this.unit = unit;
            this.sourceSpan = sourceSpan;
            this.keySpan = keySpan;
            this.valueSpan = valueSpan;
        }
        return BoundElementProperty;
    }());
    exports.BoundElementProperty = BoundElementProperty;
});
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiYXN0LmpzIiwic291cmNlUm9vdCI6IiIsInNvdXJjZXMiOlsiLi4vLi4vLi4vLi4vLi4vLi4vLi4vcGFja2FnZXMvY29tcGlsZXIvc3JjL2V4cHJlc3Npb25fcGFyc2VyL2FzdC50cyJdLCJuYW1lcyI6W10sIm1hcHBpbmdzIjoiQUFBQTs7Ozs7O0dBTUc7Ozs7Ozs7Ozs7Ozs7O0lBS0g7UUFFRSxxQkFDSSxPQUFlLEVBQVMsS0FBYSxFQUFTLFdBQW1CLEVBQVMsV0FBaUI7WUFBbkUsVUFBSyxHQUFMLEtBQUssQ0FBUTtZQUFTLGdCQUFXLEdBQVgsV0FBVyxDQUFRO1lBQVMsZ0JBQVcsR0FBWCxXQUFXLENBQU07WUFDN0YsSUFBSSxDQUFDLE9BQU8sR0FBRyxtQkFBaUIsT0FBTyxTQUFJLFdBQVcsVUFBSyxLQUFLLGFBQVEsV0FBYSxDQUFDO1FBQ3hGLENBQUM7UUFDSCxrQkFBQztJQUFELENBQUMsQUFORCxJQU1DO0lBTlksa0NBQVc7SUFReEI7UUFDRSxtQkFBbUIsS0FBYSxFQUFTLEdBQVc7WUFBakMsVUFBSyxHQUFMLEtBQUssQ0FBUTtZQUFTLFFBQUcsR0FBSCxHQUFHLENBQVE7UUFBRyxDQUFDO1FBQ3hELDhCQUFVLEdBQVYsVUFBVyxjQUFzQjtZQUMvQixPQUFPLElBQUksa0JBQWtCLENBQUMsY0FBYyxHQUFHLElBQUksQ0FBQyxLQUFLLEVBQUUsY0FBYyxHQUFHLElBQUksQ0FBQyxHQUFHLENBQUMsQ0FBQztRQUN4RixDQUFDO1FBQ0gsZ0JBQUM7SUFBRCxDQUFDLEFBTEQsSUFLQztJQUxZLDhCQUFTO0lBT3RCO1FBQ0UsYUFDVyxJQUFlO1FBQ3RCOztXQUVHO1FBQ0ksVUFBOEI7WUFKOUIsU0FBSSxHQUFKLElBQUksQ0FBVztZQUlmLGVBQVUsR0FBVixVQUFVLENBQW9CO1FBQUcsQ0FBQztRQUM3QyxtQkFBSyxHQUFMLFVBQU0sT0FBbUIsRUFBRSxPQUFtQjtZQUFuQix3QkFBQSxFQUFBLGNBQW1CO1lBQzVDLE9BQU8sSUFBSSxDQUFDO1FBQ2QsQ0FB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QyxFQUMvRSxLQUFvQixFQUFTLElBQWlCLEVBQVMsVUFBMkIsRUFDaEYsT0FBa0MsRUFBUyxTQUFvQztZQUZqRixTQUFJLEdBQUosSUFBSSxDQUFRO1lBQVMsU0FBSSxHQUFKLElBQUksQ0FBYTtZQUFTLG9CQUFlLEdBQWYsZUFBZSxDQUFpQjtZQUMvRSxVQUFLLEdBQUwsS0FBSyxDQUFlO1lBQVMsU0FBSSxHQUFKLElBQUksQ0FBYTtZQUFTLGVBQVUsR0FBVixVQUFVLENBQWlCO1lBQ2hGLFlBQU8sR0FBUCxPQUFPLENBQTJCO1lBQVMsY0FBUyxHQUFULFNBQVMsQ0FBMkI7UUFBRyxDQUFDO1FBQ2xHLDJCQUFDO0lBQUQsQ0FBQyxBQUxELElBS0M7SUFMWSxvREFBb0IiLCJzb3VyY2VzQ29udGVudCI6WyIvKipcbiAqIEBsaWNlbnNlXG4gKiBDb3B5cmlnaHQgR29vZ2xlIExMQyBBbGwgUmlnaHRzIFJlc2VydmVkLlxuICpcbiAqIFVzZSBvZiB0aGlzIHNvdXJjZSBjb2RlIGlzIGdvdmVybmVkIGJ5IGFuIE1JVC1zdHlsZSBsaWNlbnNlIHRoYXQgY2FuIGJlXG4gKiBmb3VuZCBpbiB0aGUgTElDRU5TRSBmaWxlIGF0IGh0dHBzOi8vYW5ndWxhci5pby9saWNlbnNlXG4gKi9cblxuaW1wb3J0IHtTZWN1cml0eUNvbnRleHR9IGZyb20gJy4uL2NvcmUnO1xuaW1wb3J0IHtQYXJzZVNvdXJjZVNwYW59IGZyb20gJy4uL3BhcnNlX3V0aWwnO1xuXG5leHBvcnQgY2xhc3MgUGFyc2VyRXJyb3Ige1xuICBwdWJsaWMgbWVzc2FnZTogc3RyaW5nO1xuICBjb25zdHJ1Y3RvcihcbiAgICAgIG1lc3NhZ2U6IHN0cmluZywgcHVibGljIGlucHV0OiBzdHJpbmcsIHB1YmxpYyBlcnJMb2NhdGlvbjogc3RyaW5nLCBwdWJsaWMgY3R4TG9jYXRpb24/OiBhbnkpIHtcbiAgICB0aGlzLm1lc3NhZ2UgPSBgUGFyc2VyIEVycm9yOiAke21lc3NhZ2V9ICR7ZXJyTG9jYXRpb259IFske2lucHV0fV0gaW4gJHtjdHhMb2NhdGlvbn1gO1xuICB9XG59XG5cbmV4cG9ydCBjbGFzcyBQYXJzZVNwYW4ge1xuICBjb25zdHJ1Y3RvcihwdWJsaWMgc3RhcnQ6IG51bWJlciwgcHVibGljIGVuZDogbnVtYmVyKSB7fVxuICB0b0Fic29sdXRlKGFic29sdXRlT2Zmc2V0OiBudW1iZXIpOiBBYnNvbHV0ZVNvdXJjZVNwYW4ge1xuICAgIHJldHVybiBuZXcgQWJzb2x1dGVTb3VyY2VTcGFuKGFic29sdXRlT2Zmc2V0ICsgdGhpcy5zdGFydCwgYWJzb2x1dGVPZmZzZXQgKyB0aGlzLmVuZCk7XG4gIH1cbn1cblxuZXhwb3J0IGNsYXNzIEFTVCB7XG4gIGNvbnN0cnVjdG9yKFxuICAgICAgcHVibGljIHNwYW46IFBhcnNlU3BhbixcbiAgICAgIC8qKlxuICAgICAgICogQWJzb2x1dGUgbG9jYXRpb24gb2YgdGhlIGV4cHJlc3Npb24gQVNUIGluIGEgc291cmNlIGNvZGUgZmlsZS5cbiAgICAgICAqL1xuICAgICAgcHVibGljIHNvdXJjZVNwYW46IEFic29sdXRlU291cmNlU3Bhbikge31cbiAgdmlzaXQodmlzaXRvcjogQXN0VmlzaXRvciwgY29udGV4dDogYW55ID0gbnVsbCk6IGFueSB7XG4gICAgcmV0dXJuIG51bGw7XG4gIH1cbiAgdG9TdHJpbmcoKTogc3RyaW5nIHtcbiAgICByZXR1cm4gJ0FTVCc7XG4gIH1cbn1cblxuZXhwb3J0IGFic3RyYWN0IGNsYXNzIEFTVFdpdGhOYW1lIGV4dGVuZHMgQVNUIHtcbiAgY29uc3RydWN0b3IoXG4gICAgICBzcGFuOiBQYXJzZVNwYW4sIHNvdXJjZVNwYW46IEFic29sdXRlU291cmNlU3BhbiwgcHVibGljIG5hbWVTcGFuOiBBYnNvbHV0ZVNvdXJjZVNwYW4pIHtcbiAgICBzdXBlcihzcGFuLCBzb3VyY2VTcGFuKTtcbiAgfVxufVxuXG4vKipcbiAqIFJlcHJlc2VudHMgYSBxdW90ZWQgZXhwcmVzc2lvbiBvZiB0aGUgZm9ybTpcbiAqXG4gKiBxdW90ZSA9IHByZWZpeCBgOmAgdW5pbnRlcnByZXRlZEV4cHJlc3Npb25cbiAqIHByZWZpeCA9IGlkZW50aWZpZXJcbiAqIHVuaW50ZXJwcmV0ZWRFeHByZXNzaW9uID0gYXJiaXRyYXJ5IHN0cmluZ1xuICpcbiAqIEEgcXVvdGVkIGV4cHJlc3Npb24gaXMgbWVhbnQgdG8gYmUgcHJlLXByb2Nlc3NlZCBieSBhbiBBU1QgdHJhbnNmb3JtZXIgdGhhdFxuICogY29udmVydHMgaXQgaW50byBhbm90aGVyIEFTVCB0aGF0IG5vIGxvbmdlciBjb250YWlucyBxdW90ZWQgZXhwcmVzc2lvbnMuXG4gKiBJdCBpcyBtZWFudCB0byBhbGxvdyB0aGlyZC1wYXJ0eSBkZXZlbG9wZXJzIHRvIGV4dGVuZCBBbmd1bGFyIHRlbXBsYXRlXG4gKiBleHByZXNzaW9uIGxhbmd1YWdlLiBUaGUgYHVuaW50ZXJwcmV0ZWRFeHByZXNzaW9uYCBwYXJ0IG9mIHRoZSBxdW90ZSBpc1xuICogdGhlcmVmb3JlIG5vdCBpbnRlcnByZXRlZCBieSB0aGUgQW5ndWxhcidzIG93biBleHByZXNzaW9uIHBhcnNlci5cbiAqL1xuZXhwb3J0IGNsYXNzIFF1b3RlIGV4dGVuZHMgQVNUIHtcbiAgY29uc3RydWN0b3IoXG4gICAgICBzcGFuOiBQYXJzZVNwYW4sIHNvdXJjZVNwYW46IEFic29sdXRlU291cmNlU3BhbiwgcHVibGljIHByZWZpeDogc3RyaW5nLFxuICAgICAgcHVibGljIHVuaW50ZXJwcmV0ZWRFeHByZXNzaW9uOiBzdHJpbmcsIHB1YmxpYyBsb2NhdGlvbjogYW55KSB7XG4gICAgc3VwZXIoc3Bhbiwgc291cmNlU3Bhbik7XG4gIH1cbiAgdmlzaXQodmlzaXRvcjogQXN0VmlzaXRvciwgY29udGV4dDogYW55ID0gbnVsbCk6IGFueSB7XG4gICAgcmV0dXJuIHZpc2l0b3IudmlzaXRRdW90ZSh0aGlzLCBjb250ZXh0KTtcbiAgfVxuICB0b1N0cmluZygpOiBzdHJpbmcge1xuICAgIHJldHVybiAnUXVvdGUnO1xuICB9XG59XG5cbmV4cG9ydCBjbGFzcyBFbXB0eUV4cHIgZXh0ZW5kcyBBU1Qge1xuICB2aXNpdCh2aXNpdG9yOiBBc3RWaXNpdG9yLCBjb250ZXh0OiBhbnkgPSBudWxsKSB7XG4gICAgLy8gZG8gbm90aGluZ1xuICB9XG59XG5cbmV4cG9ydCBjbGFzcyBJbXBsaWNpdFJlY2VpdmVyIGV4dGVuZHMgQVNUIHtcbiAgdmlzaXQodmlzaXRvcjogQXN0VmlzaXRvciwgY29udGV4dDogYW55ID0gbnVsbCk6IGFueSB7XG4gICAgcmV0dXJuIHZpc2l0b3IudmlzaXRJbXBsaWNpdFJlY2VpdmVyKHRoaXMsIGNvbnRleHQpO1xuICB9XG59XG5cbi8qKlxuICogUmVjZWl2ZXIgd2hlbiBzb21ldGhpbmcgaXMgYWNjZXNzZWQgdGhyb3VnaCBgdGhpc2AgKGUuZy4gYHRoaXMuZm9vYCkuIE5vdGUgdGhhdCB0aGlzIGNsYXNzXG4gKiBpbmhlcml0cyBmcm9tIGBJbXBsaWNpdFJlY2VpdmVyYCwgYmVjYXVzZSBhY2Nlc3Npbmcgc29tZXRoaW5nIHRocm91Z2ggYHRoaXNgIGlzIHRyZWF0ZWQgdGhlXG4gKiBzYW1lIGFzIGFjY2Vzc2luZyBpdCBpbXBsaWNpdGx5IGluc2lkZSBvZiBhbiBBbmd1bGFyIHRlbXBsYXRlIChlLmcuIGBbYXR0ci50aXRsZV09XCJ0aGlzLnRpdGxlXCJgXG4gKiBpcyB0aGUgc2FtZSBhcyBgW2F0dHIudGl0bGVdPVwidGl0bGVcImAuKS4gSW5oZXJpdGluZyBhbGxvd3MgZm9yIHRoZSBgdGhpc2AgYWNjZXNzZXMgdG8gYmUgdHJlYXRlZFxuICogdGhlIHNhbWUgYXMgaW1wbGljaXQgb25lcywgZXhjZXB0IGZvciBhIGNvdXBsZSBvZiBleGNlcHRpb25zIGxpa2UgYCRldmVudGAgYW5kIGAkYW55YC5cbiAqIFRPRE86IHdlIHNob3VsZCBmaW5kIGEgd2F5IGZvciB0aGlzIGNsYXNzIG5vdCB0byBleHRlbmQgZnJvbSBgSW1wbGljaXRSZWNlaXZlcmAgaW4gdGhlIGZ1dHVyZS5cbiAqL1xuZXhwb3J0IGNsYXNzIFRoaXNSZWNlaXZlciBleHRlbmRzIEltcGxpY2l0UmVjZWl2ZXIge1xuICB2aXNpdCh2aXNpdG9yOiBBc3RWaXNpdG9yLCBjb250ZXh0OiBhbnkgPSBudWxsKTogYW55IHtcbiAgICByZXR1cm4gdmlzaXRvci52aXNpdFRoaXNSZWNlaXZlcj8uKHRoaXMsIGNvbnRleHQpO1xuICB9XG59XG5cbi8qKlxuICogTXVsdGlwbGUgZXhwcmVzc2lvbnMgc2VwYXJhdGVkIGJ5IGEgc2VtaWNvbG9uLlxuICovXG5leHBvcnQgY2xhc3MgQ2hhaW4gZXh0ZW5kcyBBU1Qge1xuICBjb25zdHJ1Y3RvcihzcGFuOiBQYXJzZVNwYW4sIHNvdXJjZVNwYW46IEFic29sdXRlU291cmNlU3BhbiwgcHVibGljIGV4cHJlc3Npb25zOiBhbnlbXSkge1xuICAgIHN1cGVyKHNwYW4sIHNvdXJjZVNwYW4pO1xuICB9XG4gIHZpc2l0KHZpc2l0b3I6IEFzdFZpc2l0b3IsIGNvbnRleHQ6IGFueSA9IG51bGwpOiBhbnkge1xuICAgIHJldHVybiB2aXNpdG9yLnZpc2l0Q2hhaW4odGhpcywgY29udGV4dCk7XG4gIH1cbn1cblxuZXhwb3J0IGNsYXNzIENvbmRpdGlvbmFsIGV4dGVuZHMgQVNUIHtcbiAgY29uc3RydWN0b3IoXG4gICAgICBzcGFuOiBQYXJzZVNwYW4sIHNvdXJjZVNwYW46IEFic29sdXRlU291cmNlU3BhbiwgcHVibGljIGNvbmRpdGlvbjogQVNULCBwdWJsaWMgdHJ1ZUV4cDogQVNULFxuICAgICAgcHVibGljIGZhbHNlRXhwOiBBU1QpIHtcbiAgICBzdXBlcihzcGFuLCBzb3VyY2VTcGFuKTtcbiAgfVxuICB2aXNpdCh2aXNpdG9yOiBBc3RWaXNpdG9yLCBjb250ZXh0OiBhbnkgPSBudWxsKTogYW55IHtcbiAgICByZXR1cm4gdmlzaXRvci52aXNpdENvbmRpdGlvbmFsKHRoaXMsIGNvbnRleHQpO1xuICB9XG59XG5cbmV4cG9ydCBjbGFzcyBQcm9wZXJ0eVJlYWQgZXh0ZW5kcyBBU1RXaXRoTmFtZSB7XG4gIGNvbnN0cnVjdG9yKFxuICAgICAgc3BhbjogUGFyc2VTcGFuLCBzb3VyY2VTcGFuOiBBYnNvbHV0ZVNvdXJjZVNwYW4sIG5hbWVTcGFuOiBBYnNvbHV0ZVNvdXJjZVNwYW4sXG4gICAgICBwdWJsaWMgcmVjZWl2ZXI6IEFTVCwgcHVibGljIG5hbWU6IHN0cmluZykge1xuICAgIHN1cGVyKHNwYW4sIHNvdXJjZVNwYW4sIG5hbWVTcGFuKTtcbiAgfVxuICB2aXNpdCh2aXNpdG9yOiBBc3RWaXNpdG9yLCBjb250ZXh0OiBhbnkgPSBudWxsKTogYW55IHtcbiAgICByZXR1cm4gdmlzaXRvci52aXNpdFByb3BlcnR5UmVhZCh0aGlzLCBjb250ZXh0KTtcbiAgfVxufVxuXG5leHBvcnQgY2xhc3MgUHJvcGVydHlXcml0ZSBleHRlbmRzIEFTVFdpdGhOYW1lIHtcbiAgY29uc3RydWN0b3IoXG4gICAgICBzcGFuOiBQYXJzZVNwYW4sIHNvdXJjZVNwYW46IEFic29sdXRlU291cmNlU3BhbiwgbmFtZVNwYW46IEFic29sdXRlU291cmNlU3BhbixcbiAgICAgIHB1YmxpYyByZWNlaXZlcjogQVNULCBwdWJsaWMgbmFtZTogc3RyaW5nLCBwdWJsaWMgdmFsdWU6IEFTVCkge1xuICAgIHN1cGVyKHNwYW4sIHNvdXJjZVNwYW4sIG5hbWVTcGFuKTtcbiAgfVxuICB2aXNpdCh2aXNpdG9yOiBBc3RWaXNpdG9yLCBjb250ZXh0OiBhbnkgPSBudWxsKTogYW55IHtcbiAgICByZXR1cm4gdmlzaXRvci52aXNpdFByb3BlcnR5V3JpdGUodGhpcywgY29udGV4dCk7XG4gIH1cbn1cblxuZXhwb3J0IGNsYXNzIFNhZmVQcm9wZXJ0eVJlYWQgZXh0ZW5kcyBBU1RXaXRoTmFtZSB7XG4gIGNvbnN0cnVjdG9yKFxuICAgICAgc3BhbjogUGFyc2VTcGFuLCBzb3VyY2VTcGFuOiBBYnNvbHV0ZVNvdXJjZVNwYW4sIG5hbWVTcGFuOiBBYnNvbHV0ZVNvdXJjZVNwYW4sXG4gICAgICBwdWJsaWMgcmVjZWl2ZXI6IEFTVCwgcHVibGljIG5hbWU6IHN0cmluZykge1xuICAgIHN1cGVyKHNwYW4sIHNvdXJjZVNwYW4sIG5hbWVTcGFuKTtcbiAgfVxuICB2aXNpdCh2aXNpdG9yOiBBc3RWaXNpdG9yLCBjb250ZXh0OiBhbnkgPSBudWxsKTogYW55IHtcbiAgICByZXR1cm4gdmlzaXRvci52aXNpdFNhZmVQcm9wZXJ0eVJlYWQodGhpcywgY29udGV4dCk7XG4gIH1cbn1cblxuZXhwb3J0IGNsYXNzIEtleWVkUmVhZCBleHRlbmRzIEFTVCB7XG4gIGNvbnN0cnVjdG9yKHNwYW46IFBhcnNlU3Bhbiwgc291cmNlU3BhbjogQWJzb2x1dGVTb3VyY2VTcGFuLCBwdWJsaWMgb2JqOiBBU1QsIHB1YmxpYyBrZXk6IEFTVCkge1xuICAgIHN1cGVyKHNwYW4sIHNvdXJjZVNwYW4pO1xuICB9XG4gIHZpc2l0KHZpc2l0b3I6IEFzdFZpc2l0b3IsIGNvbnRleHQ6IGFueSA9IG51bGwpOiBhbnkge1xuICAgIHJldHVybiB2aXNpdG9yLnZpc2l0S2V5ZWRSZWFkKHRoaXMsIGNvbnRleHQpO1xuICB9XG59XG5cbmV4cG9ydCBjbGFzcyBLZXllZFdyaXRlIGV4dGVuZHMgQVNUIHtcbiAgY29uc3RydWN0b3IoXG4gICAgICBzcGFuOiBQYXJzZVNwYW4sIHNvdXJjZVNwYW46IEFic29sdXRlU291cmNlU3BhbiwgcHVibGljIG9iajogQVNULCBwdWJsaWMga2V5OiBBU1QsXG4gICAgICBwdWJsaWMgdmFsdWU6IEFTVCkge1xuICAgIHN1cGVyKHNwYW4sIHNvdXJjZVNwYW4pO1xuICB9XG4gIHZpc2l0KHZpc2l0b3I6IEFzdFZpc2l0b3IsIGNvbnRleHQ6IGFueSA9IG51bGwpOiBhbnkge1xuICAgIHJldHVybiB2aXNpdG9yLnZpc2l0S2V5ZWRXcml0ZSh0aGlzLCBjb250ZXh0KTtcbiAgfVxufVxuXG5leHBvcnQgY2xhc3MgQmluZGluZ1BpcGUgZXh0ZW5kcyBBU1RXaXRoTmFtZSB7XG4gIGNvbnN0cnVjdG9yKFxuICAgICAgc3BhbjogUGFyc2VTcGFuLCBzb3VyY2VTcGFuOiBBYnNvbHV0ZVNvdXJjZVNwYW4sIHB1YmxpYyBleHA6IEFTVCwgcHVibGljIG5hbWU6IHN0cmluZyxcbiAgICAgIHB1YmxpYyBhcmdzOiBhbnlbXSwgbmFtZVNwYW46IEFic29sdXRlU291cmNlU3Bhbikge1xuICAgIHN1cGVyKHNwYW4sIHNvdXJjZVNwYW4sIG5hbWVTcGFuKTtcbiAgfVxuICB2aXNpdCh2aXNpdG9yOiBBc3RWaXNpdG9yLCBjb250ZXh0OiBhbnkgPSBudWxsKTogYW55IHtcbiAgICByZXR1cm4gdmlzaXRvci52aXNpdFBpcGUodGhpcywgY29udGV4dCk7XG4gIH1cbn1cblxuZXhwb3J0IGNsYXNzIExpdGVyYWxQcmltaXRpdmUgZXh0ZW5kcyBBU1Qge1xuICBjb25zdHJ1Y3RvcihzcGFuOiBQYXJzZVNwYW4sIHNvdXJjZVNwYW46IEFic29sdXRlU291cmNlU3BhbiwgcHVibGljIHZhbHVlOiBhbnkpIHtcbiAgICBzdXBlcihzcGFuLCBzb3VyY2VTcGFuKTtcbiAgfVxuICB2aXNpdCh2aXNpdG9yOiBBc3RWaXNpdG9yLCBjb250ZXh0OiBhbnkgPSBudWxsKTogYW55IHtcbiAgICByZXR1cm4gdmlzaXRvci52aXNpdExpdGVyYWxQcmltaXRpdmUodGhpcywgY29udGV4dCk7XG4gIH1cbn1cblxuZXhwb3J0IGNsYXNzIExpdGVyYWxBcnJheSBleHRlbmRzIEFTVCB7XG4gIGNvbnN0cnVjdG9yKHNwYW46IFBhcnNlU3Bhbiwgc291cmNlU3BhbjogQWJzb2x1dGVTb3VyY2VTcGFuLCBwdWJsaWMgZXhwcmVzc2lvbnM6IGFueVtdKSB7XG4gICAgc3VwZXIoc3Bhbiwgc291cmNlU3Bhbik7XG4gIH1cbiAgdmlzaXQodmlzaXRvcjogQXN0VmlzaXRvciwgY29udGV4dDogYW55ID0gbnVsbCk6IGFueSB7XG4gICAgcmV0dXJuIHZpc2l0b3IudmlzaXRMaXRlcmFsQXJyYXkodGhpcywgY29udGV4dCk7XG4gIH1cbn1cblxuZXhwb3J0IHR5cGUgTGl0ZXJhbE1hcEtleSA9IHtcbiAga2V5OiBzdHJpbmc7IHF1b3RlZDogYm9vbGVhbjtcbn07XG5cbmV4cG9ydCBjbGFzcyBMaXRlcmFsTWFwIGV4dGVuZHMgQVNUIHtcbiAgY29uc3RydWN0b3IoXG4gICAgICBzcGFuOiBQYXJzZVNwYW4sIHNvdXJjZVNwYW46IEFic29sdXRlU291cmNlU3BhbiwgcHVibGljIGtleXM6IExpdGVyYWxNYXBLZXlbXSxcbiAgICAgIHB1YmxpYyB2YWx1ZXM6IGFueVtdKSB7XG4gICAgc3VwZXIoc3Bhbiwgc291cmNlU3Bhbik7XG4gIH1cbiAgdmlzaXQodmlzaXRvcjogQXN0VmlzaXRvciwgY29udGV4dDogYW55ID0gbnVsbCk6IGFueSB7XG4gICAgcmV0dXJuIHZpc2l0b3IudmlzaXRMaXRlcmFsTWFwKHRoaXMsIGNvbnRleHQpO1xuICB9XG59XG5cbmV4cG9ydCBjbGFzcyBJbnRlcnBvbGF0aW9uIGV4dGVuZHMgQVNUIHtcbiAgY29uc3RydWN0b3IoXG4gICAgICBzcGFuOiBQYXJzZVNwYW4sIHNvdXJjZVNwYW46IEFic29sdXRlU291cmNlU3BhbiwgcHVibGljIHN0cmluZ3M6IGFueVtdLFxuICAgICAgcHVibGljIGV4cHJlc3Npb25zOiBhbnlbXSkge1xuICAgIHN1cGVyKHNwYW4sIHNvdXJjZVNwYW4pO1xuICB9XG4gIHZpc2l0KHZpc2l0b3I6IEFzdFZpc2l0b3IsIGNvbnRleHQ6IGFueSA9IG51bGwpOiBhbnkge1xuICAgIHJldHVybiB2aXNpdG9yLnZpc2l0SW50ZXJwb2xhdGlvbih0aGlzLCBjb250ZXh0KTtcbiAgfVxufVxuXG5leHBvcnQgY2xhc3MgQmluYXJ5IGV4dGVuZHMgQVNUIHtcbiAgY29uc3RydWN0b3IoXG4gICAgICBzcGFuOiBQYXJzZVNwYW4sIHNvdXJjZVNwYW46IEFic29sdXRlU291cmNlU3BhbiwgcHVibGljIG9wZXJhdGlvbjogc3RyaW5nLCBwdWJsaWMgbGVmdDogQVNULFxuICAgICAgcHVibGljIHJpZ2h0OiBBU1QpIHtcbiAgICBzdXBlcihzcGFuLCBzb3VyY2VTcGFuKTtcbiAgfVxuICB2aXNpdCh2aXNpdG9yOiBBc3RWaXNpdG9yLCBjb250ZXh0OiBhbnkgPSBudWxsKTogYW55IHtcbiAgICByZXR1cm4gdmlzaXRvci52aXNpdEJpbmFyeSh0aGlzLCBjb250ZXh0KTtcbiAgfVxufVxuXG4vKipcbiAqIEZvciBiYWNrd2FyZHMgY29tcGF0aWJpbGl0eSByZWFzb25zLCBgVW5hcnlgIGluaGVyaXRzIGZyb20gYEJpbmFyeWAgYW5kIG1pbWljcyB0aGUgYmluYXJ5IEFTVFxuICogbm9kZSB0aGF0IHdhcyBvcmlnaW5hbGx5IHVzZWQuIFRoaXMgaW5oZXJpdGFuY2UgcmVsYXRpb24gY2FuIGJlIGRlbGV0ZWQgaW4gc29tZSBmdXR1cmUgbWFqb3IsXG4gKiBhZnRlciBjb25zdW1lcnMgaGF2ZSBiZWVuIGdpdmVuIGEgY2hhbmNlIHRvIGZ1bGx5IHN1cHBvcnQgVW5hcnkuXG4gKi9cbmV4cG9ydCBjbGFzcyBVbmFyeSBleHRlbmRzIEJpbmFyeSB7XG4gIC8vIFJlZGVjbGFyZSB0aGUgcHJvcGVydGllcyB0aGF0IGFyZSBpbmhlcml0ZWQgZnJvbSBgQmluYXJ5YCBhcyBgbmV2ZXJgLCBhcyBjb25zdW1lcnMgc2hvdWxkIG5vdFxuICAvLyBkZXBlbmQgb24gdGhlc2UgZmllbGRzIHdoZW4gb3BlcmF0aW5nIG9uIGBVbmFyeWAuXG4gIGxlZnQ6IG5ldmVyO1xuICByaWdodDogbmV2ZXI7XG4gIG9wZXJhdGlvbjogbmV2ZXI7XG5cbiAgLyoqXG4gICAqIENyZWF0ZXMgYSB1bmFyeSBtaW51cyBleHByZXNzaW9uIFwiLXhcIiwgcmVwcmVzZW50ZWQgYXMgYEJpbmFyeWAgdXNpbmcgXCIwIC0geFwiLlxuICAgKi9cbiAgc3RhdGljIGNyZWF0ZU1pbnVzKHNwYW46IFBhcnNlU3Bhbiwgc291cmNlU3BhbjogQWJzb2x1dGVTb3VyY2VTcGFuLCBleHByOiBBU1QpOiBVbmFyeSB7XG4gICAgcmV0dXJuIG5ldyBVbmFyeShcbiAgICAgICAgc3Bhbiwgc291cmNlU3BhbiwgJy0nLCBleHByLCAnLScsIG5ldyBMaXRlcmFsUHJpbWl0aXZlKHNwYW4sIHNvdXJjZVNwYW4sIDApLCBleHByKTtcbiAgfVxuXG4gIC8qKlxuICAgKiBDcmVhdGVzIGEgdW5hcnkgcGx1cyBleHByZXNzaW9uIFwiK3hcIiwgcmVwcmVzZW50ZWQgYXMgYEJpbmFyeWAgdXNpbmcgXCJ4IC0gMFwiLlxuICAgKi9cbiAgc3RhdGljIGNyZWF0ZVBsdXMoc3BhbjogUGFyc2VTcGFuLCBzb3VyY2VTcGFuOiBBYnNvbHV0ZVNvdXJjZVNwYW4sIGV4cHI6IEFTVCk6IFVuYXJ5IHtcbiAgICByZXR1cm4gbmV3IFVuYXJ5KFxuICAgICAgICBzcGFuLCBzb3VyY2VTcGFuLCAnKycsIGV4cHIsICctJywgZXhwciwgbmV3IExpdGVyYWxQcmltaXRpdmUoc3Bhbiwgc291cmNlU3BhbiwgMCkpO1xuICB9XG5cbiAgLyoqXG4gICAqIER1cmluZyB0aGUgZGVwcmVjYXRpb24gcGVyaW9kIHRoaXMgY29uc3RydWN0b3IgaXMgcHJpdmF0ZSwgdG8gYXZvaWQgY29uc3VtZXJzIGZyb20gY3JlYXRpbmdcbiAgICogYSBgVW5hcnlgIHdpdGggdGhlIGZhbGxiYWNrIHByb3BlcnRpZXMgZm9yIGBCaW5hcnlgLlxuICAgKi9cbiAgcHJpdmF0ZSBjb25zdHJ1Y3RvcihcbiAgICAgIHNwYW46IFBhcnNlU3Bhbiwgc291cmNlU3BhbjogQWJzb2x1dGVTb3VyY2VTcGFuLCBwdWJsaWMgb3BlcmF0b3I6IHN0cmluZywgcHVibGljIGV4cHI6IEFTVCxcbiAgICAgIGJpbmFyeU9wOiBzdHJpbmcsIGJpbmFyeUxlZnQ6IEFTVCwgYmluYXJ5UmlnaHQ6IEFTVCkge1xuICAgIHN1cGVyKHNwYW4sIHNvdXJjZVNwYW4sIGJpbmFyeU9wLCBiaW5hcnlMZWZ0LCBiaW5hcnlSaWdodCk7XG4gIH1cblxuICB2aXNpdCh2aXNpdG9yOiBBc3RWaXNpdG9yLCBjb250ZXh0OiBhbnkgPSBudWxsKTogYW55IHtcbiAgICBpZiAodmlzaXRvci52aXNpdFVuYXJ5ICE9PSB1bmRlZmluZWQpIHtcbiAgICAgIHJldHVybiB2aXNpdG9yLnZpc2l0VW5hcnkodGhpcywgY29udGV4dCk7XG4gICAgfVxuICAgIHJldHVybiB2aXNpdG9yLnZpc2l0QmluYXJ5KHRoaXMsIGNvbnRleHQpO1xuICB9XG59XG5cbmV4cG9ydCBjbGFzcyBQcmVmaXhOb3QgZXh0ZW5kcyBBU1Qge1xuICBjb25zdHJ1Y3RvcihzcGFuOiBQYXJzZVNwYW4sIHNvdXJjZVNwYW46IEFic29sdXRlU291cmNlU3BhbiwgcHVibGljIGV4cHJlc3Npb246IEFTVCkge1xuICAgIHN1cGVyKHNwYW4sIHNvdXJjZVNwYW4pO1xuICB9XG4gIHZpc2l0KHZpc2l0b3I6IEFzdFZpc2l0b3IsIGNvbnRleHQ6IGFueSA9IG51bGwpOiBhbnkge1xuICAgIHJldHVybiB2aXNpdG9yLnZpc2l0UHJlZml4Tm90KHRoaXMsIGNvbnRleHQpO1xuICB9XG59XG5cbmV4cG9ydCBjbGFzcyBOb25OdWxsQXNzZXJ0IGV4dGVuZHMgQVNUIHtcbiAgY29uc3RydWN0b3Ioc3BhbjogUGFyc2VTcGFuLCBzb3VyY2VTcGFuOiBBYnNvbHV0ZVNvdXJjZVNwYW4sIHB1YmxpYyBleHByZXNzaW9uOiBBU1QpIHtcbiAgICBzdXBlcihzcGFuLCBzb3VyY2VTcGFuKTtcbiAgfVxuICB2aXNpdCh2aXNpdG9yOiBBc3RWaXNpdG9yLCBjb250ZXh0OiBhbnkgPSBudWxsKTogYW55IHtcbiAgICByZXR1cm4gdmlzaXRvci52aXNpdE5vbk51bGxBc3NlcnQodGhpcywgY29udGV4dCk7XG4gIH1cbn1cblxuZXhwb3J0IGNsYXNzIE1ldGhvZENhbGwgZXh0ZW5kcyBBU1RXaXRoTmFtZSB7XG4gIGNvbnN0cnVjdG9yKFxuICAgICAgc3BhbjogUGFyc2VTcGFuLCBzb3VyY2VTcGFuOiBBYnNvbHV0ZVNvdXJjZVNwYW4sIG5hbWVTcGFuOiBBYnNvbHV0ZVNvdXJjZVNwYW4sXG4gICAgICBwdWJsaWMgcmVjZWl2ZXI6IEFTVCwgcHVibGljIG5hbWU6IHN0cmluZywgcHVibGljIGFyZ3M6IGFueVtdKSB7XG4gICAgc3VwZXIoc3Bhbiwgc291cmNlU3BhbiwgbmFtZVNwYW4pO1xuICB9XG4gIHZpc2l0KHZpc2l0b3I6IEFzdFZpc2l0b3IsIGNvbnRleHQ6IGFueSA9IG51bGwpOiBhbnkge1xuICAgIHJldHVybiB2aXNpdG9yLnZpc2l0TWV0aG9kQ2FsbCh0aGlzLCBjb250ZXh0KTtcbiAgfVxufVxuXG5leHBvcnQgY2xhc3MgU2FmZU1ldGhvZENhbGwgZXh0ZW5kcyBBU1RXaXRoTmFtZSB7XG4gIGNvbnN0cnVjdG9yKFxuICAgICAgc3BhbjogUGFyc2VTcGFuLCBzb3VyY2VTcGFuOiBBYnNvbHV0ZVNvdXJjZVNwYW4sIG5hbWVTcGFuOiBBYnNvbHV0ZVNvdXJjZVNwYW4sXG4gICAgICBwdWJsaWMgcmVjZWl2ZXI6IEFTVCwgcHVibGljIG5hbWU6IHN0cmluZywgcHVibGljIGFyZ3M6IGFueVtdKSB7XG4gICAgc3VwZXIoc3Bhbiwgc291cmNlU3BhbiwgbmFtZVNwYW4pO1xuICB9XG4gIHZpc2l0KHZpc2l0b3I6IEFzdFZpc2l0b3IsIGNvbnRleHQ6IGFueSA9IG51bGwpOiBhbnkge1xuICAgIHJldHVybiB2aXNpdG9yLnZpc2l0U2FmZU1ldGhvZENhbGwodGhpcywgY29udGV4dCk7XG4gIH1cbn1cblxuZXhwb3J0IGNsYXNzIEZ1bmN0aW9uQ2FsbCBleHRlbmRzIEFTVCB7XG4gIGNvbnN0cnVjdG9yKFxuICAgICAgc3BhbjogUGFyc2VTcGFuLCBzb3VyY2VTcGFuOiBBYnNvbHV0ZVNvdXJjZVNwYW4sIHB1YmxpYyB0YXJnZXQ6IEFTVHxudWxsLFxuICAgICAgcHVibGljIGFyZ3M6IGFueVtdKSB7XG4gICAgc3VwZXIoc3Bhbiwgc291cmNlU3Bhbik7XG4gIH1cbiAgdmlzaXQodmlzaXRvcjogQXN0VmlzaXRvciwgY29udGV4dDogYW55ID0gbnVsbCk6IGFueSB7XG4gICAgcmV0dXJuIHZpc2l0b3IudmlzaXRGdW5jdGlvbkNhbGwodGhpcywgY29udGV4dCk7XG4gIH1cbn1cblxuLyoqXG4gKiBSZWNvcmRzIHRoZSBhYnNvbHV0ZSBwb3NpdGlvbiBvZiBhIHRleHQgc3BhbiBpbiBhIHNvdXJjZSBmaWxlLCB3aGVyZSBgc3RhcnRgIGFuZCBgZW5kYCBhcmUgdGhlXG4gKiBzdGFydGluZyBhbmQgZW5kaW5nIGJ5dGUgb2Zmc2V0cywgcmVzcGVjdGl2ZWx5LCBvZiB0aGUgdGV4dCBzcGFuIGluIGEgc291cmNlIGZpbGUuXG4gKi9cbmV4cG9ydCBjbGFzcyBBYnNvbHV0ZVNvdXJjZVNwYW4ge1xuICBjb25zdHJ1Y3RvcihwdWJsaWMgcmVhZG9ubHkgc3RhcnQ6IG51bWJlciwgcHVibGljIHJlYWRvbmx5IGVuZDogbnVtYmVyKSB7fVxufVxuXG5leHBvcnQgY2xhc3MgQVNUV2l0aFNvdXJjZSBleHRlbmRzIEFTVCB7XG4gIGNvbnN0cnVjdG9yKFxuICAgICAgcHVibGljIGFzdDogQVNULCBwdWJsaWMgc291cmNlOiBzdHJpbmd8bnVsbCwgcHVibGljIGxvY2F0aW9uOiBzdHJpbmcsIGFic29sdXRlT2Zmc2V0OiBudW1iZXIsXG4gICAgICBwdWJsaWMgZXJyb3JzOiBQYXJzZXJFcnJvcltdKSB7XG4gICAgc3VwZXIoXG4gICAgICAgIG5ldyBQYXJzZVNwYW4oMCwgc291cmNlID09PSBudWxsID8gMCA6IHNvdXJjZS5sZW5ndGgpLFxuICAgICAgICBuZXcgQWJzb2x1dGVTb3VyY2VTcGFuKFxuICAgICAgICAgICAgYWJzb2x1dGVPZmZzZXQsIHNvdXJjZSA9PT0gbnVsbCA/IGFic29sdXRlT2Zmc2V0IDogYWJzb2x1dGVPZmZzZXQgKyBzb3VyY2UubGVuZ3RoKSk7XG4gIH1cbiAgdmlzaXQodmlzaXRvcjogQXN0VmlzaXRvciwgY29udGV4dDogYW55ID0gbnVsbCk6IGFueSB7XG4gICAgaWYgKHZpc2l0b3IudmlzaXRBU1RXaXRoU291cmNlKSB7XG4gICAgICByZXR1cm4gdmlzaXRvci52aXNpdEFTVFdpdGhTb3VyY2UodGhpcywgY29udGV4dCk7XG4gICAgfVxuICAgIHJldHVybiB0aGlzLmFzdC52aXNpdCh2aXNpdG9yLCBjb250ZXh0KTtcbiAgfVxuICB0b1N0cmluZygpOiBzdHJpbmcge1xuICAgIHJldHVybiBgJHt0aGlzLnNvdXJjZX0gaW4gJHt0aGlzLmxvY2F0aW9ufWA7XG4gIH1cbn1cblxuLyoqXG4gKiBUZW1wbGF0ZUJpbmRpbmcgcmVmZXJzIHRvIGEgcGFydGljdWxhciBrZXktdmFsdWUgcGFpciBpbiBhIG1pY3Jvc3ludGF4XG4gKiBleHByZXNzaW9uLiBBIGZldyBleGFtcGxlcyBhcmU6XG4gKlxuICogICB8LS0tLS0tLS0tLS0tLS0tLS0tLS0tfC0tLS0tLS0tLS0tLS0tfC0tLS0tLS0tLXwtLS0tLS0tLS0tLS0tLXxcbiAqICAgfCAgICAgZXhwcmVzc2lvbiAgICAgIHwgICAgIGtleSAgICAgIHwgIHZhbHVlICB8IGJpbmRpbmcgdHlwZSB8XG4gKiAgIHwtLS0tLS0tLS0tLS0tLS0tLS0tLS18LS0tLS0tLS0tLS0tLS18LS0tLS0tLS0tfC0tLS0tLS0tLS0tLS0tfFxuICogICB8IDEuIGxldCBpdGVtICAgICAgICAgfCAgICBpdGVtICAgICAgfCAgbnVsbCAgIHwgICB2YXJpYWJsZSAgIHxcbiAqICAgfCAyLiBvZiBpdGVtcyAgICAgICAgIHwgICBuZ0Zvck9mICAgIHwgIGl0ZW1zICB8ICBleHByZXNzaW9uICB8XG4gKiAgIHwgMy4gbGV0IHggPSB5ICAgICAgICB8ICAgICAgeCAgICAgICB8ICAgIHkgICAgfCAgIHZhcmlhYmxlICAgfFxuICogICB8IDQuIGluZGV4IGFzIGkgICAgICAgfCAgICAgIGkgICAgICAgfCAgaW5kZXggIHwgICB2YXJpYWJsZSAgIHxcbiAqICAgfCA1LiB0cmFja0J5OiBmdW5jICAgIHwgbmdGb3JUcmFja0J5IHwgICBmdW5jICB8ICBleHByZXNzaW9uICB8XG4gKiAgIHwgNi4gKm5nSWY9XCJjb25kXCIgICAgIHwgICAgIG5nSWYgICAgIHwgICBjb25kICB8ICBleHByZXNzaW9uICB8XG4gKiAgIHwtLS0tLS0tLS0tLS0tLS0tLS0tLS18LS0tLS0tLS0tLS0tLS18LS0tLS0tLS0tfC0tLS0tLS0tLS0tLS0tfFxuICpcbiAqICg2KSBpcyBhIG5vdGFibGUgZXhjZXB0aW9uIGJlY2F1c2UgaXQgaXMgYSBiaW5kaW5nIGZyb20gdGhlIHRlbXBsYXRlIGtleSBpblxuICogdGhlIExIUyBvZiBhIEhUTUwgYXR0cmlidXRlIHRvIHRoZSBleHByZXNzaW9uIGluIHRoZSBSSFMuIEFsbCBvdGhlciBiaW5kaW5nc1xuICogaW4gdGhlIGV4YW1wbGUgYWJvdmUgYXJlIGRlcml2ZWQgc29sZWx5IGZyb20gdGhlIFJIUy5cbiAqL1xuZXhwb3J0IHR5cGUgVGVtcGxhdGVCaW5kaW5nID0gVmFyaWFibGVCaW5kaW5nfEV4cHJlc3Npb25CaW5kaW5nO1xuXG5leHBvcnQgY2xhc3MgVmFyaWFibGVCaW5kaW5nIHtcbiAgLyoqXG4gICAqIEBwYXJhbSBzb3VyY2VTcGFuIGVudGlyZSBzcGFuIG9mIHRoZSBiaW5kaW5nLlxuICAgKiBAcGFyYW0ga2V5IG5hbWUgb2YgdGhlIExIUyBhbG9uZyB3aXRoIGl0cyBzcGFuLlxuICAgKiBAcGFyYW0gdmFsdWUgb3B0aW9uYWwgdmFsdWUgZm9yIHRoZSBSSFMgYWxvbmcgd2l0aCBpdHMgc3Bhbi5cbiAgICovXG4gIGNvbnN0cnVjdG9yKFxuICAgICAgcHVibGljIHJlYWRvbmx5IHNvdXJjZVNwYW46IEFic29sdXRlU291cmNlU3BhbixcbiAgICAgIHB1YmxpYyByZWFkb25seSBrZXk6IFRlbXBsYXRlQmluZGluZ0lkZW50aWZpZXIsXG4gICAgICBwdWJsaWMgcmVhZG9ubHkgdmFsdWU6IFRlbXBsYXRlQmluZGluZ0lkZW50aWZpZXJ8bnVsbCkge31cbn1cblxuZXhwb3J0IGNsYXNzIEV4cHJlc3Npb25CaW5kaW5nIHtcbiAgLyoqXG4gICAqIEBwYXJhbSBzb3VyY2VTcGFuIGVudGlyZSBzcGFuIG9mIHRoZSBiaW5kaW5nLlxuICAgKiBAcGFyYW0ga2V5IGJpbmRpbmcgbmFtZSwgbGlrZSBuZ0Zvck9mLCBuZ0ZvclRyYWNrQnksIG5nSWYsIGFsb25nIHdpdGggaXRzXG4gICAqIHNwYW4uIE5vdGUgdGhhdCB0aGUgbGVuZ3RoIG9mIHRoZSBzcGFuIG1heSBub3QgYmUgdGhlIHNhbWUgYXNcbiAgICogYGtleS5zb3VyY2UubGVuZ3RoYC4gRm9yIGV4YW1wbGUsXG4gICAqIDEuIGtleS5zb3VyY2UgPSBuZ0Zvciwga2V5LnNwYW4gaXMgZm9yIFwibmdGb3JcIlxuICAgKiAyLiBrZXkuc291cmNlID0gbmdGb3JPZiwga2V5LnNwYW4gaXMgZm9yIFwib2ZcIlxuICAgKiAzLiBrZXkuc291cmNlID0gbmdGb3JUcmFja0J5LCBrZXkuc3BhbiBpcyBmb3IgXCJ0cmFja0J5XCJcbiAgICogQHBhcmFtIHZhbHVlIG9wdGlvbmFsIGV4cHJlc3Npb24gZm9yIHRoZSBSSFMuXG4gICAqL1xuICBjb25zdHJ1Y3RvcihcbiAgICAgIHB1YmxpYyByZWFkb25seSBzb3VyY2VTcGFuOiBBYnNvbHV0ZVNvdXJjZVNwYW4sXG4gICAgICBwdWJsaWMgcmVhZG9ubHkga2V5OiBUZW1wbGF0ZUJpbmRpbmdJZGVudGlmaWVyLCBwdWJsaWMgcmVhZG9ubHkgdmFsdWU6IEFTVFdpdGhTb3VyY2V8bnVsbCkge31cbn1cblxuZXhwb3J0IGludGVyZmFjZSBUZW1wbGF0ZUJpbmRpbmdJZGVudGlmaWVyIHtcbiAgc291cmNlOiBzdHJpbmc7XG4gIHNwYW46IEFic29sdXRlU291cmNlU3Bhbjtcbn1cblxuZXhwb3J0IGludGVyZmFjZSBBc3RWaXNpdG9yIHtcbiAgLyoqXG4gICAqIFRoZSBgdmlzaXRVbmFyeWAgbWV0aG9kIGlzIGRlY2xhcmVkIGFzIG9wdGlvbmFsIGZvciBiYWNrd2FyZHMgY29tcGF0aWJpbGl0eS4gSW4gYW4gdXBjb21pbmdcbiAgICogbWFqb3IgcmVsZWFzZSwgdGhpcyBtZXRob2Qgd2lsbCBiZSBtYWRlIHJlcXVpcmVkLlxuICAgKi9cbiAgdmlzaXRVbmFyeT8oYXN0OiBVbmFyeSwgY29udGV4dDogYW55KTogYW55O1xuICB2aXNpdEJpbmFyeShhc3Q6IEJpbmFyeSwgY29udGV4dDogYW55KTogYW55O1xuICB2aXNpdENoYWluKGFzdDogQ2hhaW4sIGNvbnRleHQ6IGFueSk6IGFueTtcbiAgdmlzaXRDb25kaXRpb25hbChhc3Q6IENvbmRpdGlvbmFsLCBjb250ZXh0OiBhbnkpOiBhbnk7XG4gIHZpc2l0RnVuY3Rpb25DYWxsKGFzdDogRnVuY3Rpb25DYWxsLCBjb250ZXh0OiBhbnkpOiBhbnk7XG4gIC8qKlxuICAgKiBUaGUgYHZpc2l0VGhpc1JlY2VpdmVyYCBtZXRob2QgaXMgZGVjbGFyZWQgYXMgb3B0aW9uYWwgZm9yIGJhY2t3YXJkcyBjb21wYXRpYmlsaXR5LlxuICAgKiBJbiBhbiB1cGNvbWluZyBtYWpvciByZWxlYXNlLCB0aGlzIG1ldGhvZCB3aWxsIGJlIG1hZGUgcmVxdWlyZWQuXG4gICAqL1xuICB2aXNpdFRoaXNSZWNlaXZlcj8oYXN0OiBUaGlzUmVjZWl2ZXIsIGNvbnRleHQ6IGFueSk6IGFueTtcbiAgdmlzaXRJbXBsaWNpdFJlY2VpdmVyKGFzdDogSW1wbGljaXRSZWNlaXZlciwgY29udGV4dDogYW55KTogYW55O1xuICB2aXNpdEludGVycG9sYXRpb24oYXN0OiBJbnRlcnBvbGF0aW9uLCBjb250ZXh0OiBhbnkpOiBhbnk7XG4gIHZpc2l0S2V5ZWRSZWFkKGFzdDogS2V5ZWRSZWFkLCBjb250ZXh0OiBhbnkpOiBhbnk7XG4gIHZpc2l0S2V5ZWRXcml0ZShhc3Q6IEtleWVkV3JpdGUsIGNvbnRleHQ6IGFueSk6IGFueTtcbiAgdmlzaXRMaXRlcmFsQXJyYXkoYXN0OiBMaXRlcmFsQXJyYXksIGNvbnRleHQ6IGFueSk6IGFueTtcbiAgdmlzaXRMaXRlcmFsTWFwKGFzdDogTGl0ZXJhbE1hcCwgY29udGV4dDogYW55KTogYW55O1xuICB2aXNpdExpdGVyYWxQcmltaXRpdmUoYXN0OiBMaXRlcmFsUHJpbWl0aXZlLCBjb250ZXh0OiBhbnkpOiBhbnk7XG4gIHZpc2l0TWV0aG9kQ2FsbChhc3Q6IE1ldGhvZENhbGwsIGNvbnRleHQ6IGFueSk6IGFueTtcbiAgdmlzaXRQaXBlKGFzdDogQmluZGluZ1BpcGUsIGNvbnRleHQ6IGFueSk6IGFueTtcbiAgdmlzaXRQcmVmaXhOb3QoYXN0OiBQcmVmaXhOb3QsIGNvbnRleHQ6IGFueSk6IGFueTtcbiAgdmlzaXROb25OdWxsQXNzZXJ0KGFzdDogTm9uTnVsbEFzc2VydCwgY29udGV4dDogYW55KTogYW55O1xuICB2aXNpdFByb3BlcnR5UmVhZChhc3Q6IFByb3BlcnR5UmVhZCwgY29udGV4dDogYW55KTogYW55O1xuICB2aXNpdFByb3BlcnR5V3JpdGUoYXN0OiBQcm9wZXJ0eVdyaXRlLCBjb250ZXh0OiBhbnkpOiBhbnk7XG4gIHZpc2l0UXVvdGUoYXN0OiBRdW90ZSwgY29udGV4dDogYW55KTogYW55O1xuICB2aXNpdFNhZmVNZXRob2RDYWxsKGFzdDogU2FmZU1ldGhvZENhbGwsIGNvbnRleHQ6IGFueSk6IGFueTtcbiAgdmlzaXRTYWZlUHJvcGVydHlSZWFkKGFzdDogU2FmZVByb3BlcnR5UmVhZCwgY29udGV4dDogYW55KTogYW55O1xuICB2aXNpdEFTVFdpdGhTb3VyY2U/KGFzdDogQVNUV2l0aFNvdXJjZSwgY29udGV4dDogYW55KTogYW55O1xuICAvKipcbiAgICogVGhpcyBmdW5jdGlvbiBpcyBvcHRpb25hbGx5IGRlZmluZWQgdG8gYWxsb3cgY2xhc3NlcyB0aGF0IGltcGxlbWVudCB0aGlzXG4gICAqIGludGVyZmFjZSB0byBzZWxlY3RpdmVseSBkZWNpZGUgaWYgdGhlIHNwZWNpZmllZCBgYXN0YCBzaG91bGQgYmUgdmlzaXRlZC5cbiAgICogQHBhcmFtIGFzdCBub2RlIHRvIHZpc2l0XG4gICAqIEBwYXJhbSBjb250ZXh0IGNvbnRleHQgdGhhdCBnZXRzIHBhc3NlZCB0byB0aGUgbm9kZSBhbmQgYWxsIGl0cyBjaGlsZHJlblxuICAgKi9cbiAgdmlzaXQ/KGFzdDogQVNULCBjb250ZXh0PzogYW55KTogYW55O1xufVxuXG5leHBvcnQgY2xhc3MgUmVjdXJzaXZlQXN0VmlzaXRvciBpbXBsZW1lbnRzIEFzdFZpc2l0b3Ige1xuICB2aXNpdChhc3Q6IEFTVCwgY29udGV4dD86IGFueSk6IGFueSB7XG4gICAgLy8gVGhlIGRlZmF1bHQgaW1wbGVtZW50YXRpb24ganVzdCB2aXNpdHMgZXZlcnkgbm9kZS5cbiAgICAvLyBDbGFzc2VzIHRoYXQgZXh0ZW5kIFJlY3Vyc2l2ZUFzdFZpc2l0b3Igc2hvdWxkIG92ZXJyaWRlIHRoaXMgZnVuY3Rpb25cbiAgICAvLyB0byBzZWxlY3RpdmVseSB2aXNpdCB0aGUgc3BlY2lmaWVkIG5vZGUuXG4gICAgYXN0LnZpc2l0KHRoaXMsIGNvbnRleHQpO1xuICB9XG4gIHZpc2l0VW5hcnkoYXN0OiBVbmFyeSwgY29udGV4dDogYW55KTogYW55IHtcbiAgICB0aGlzLnZpc2l0KGFzdC5leHByLCBjb250ZXh0KTtcbiAgfVxuICB2aXNpdEJpbmFyeShhc3Q6IEJpbmFyeSwgY29udGV4dDogYW55KTogYW55IHtcbiAgICB0aGlzLnZpc2l0KGFzdC5sZWZ0LCBjb250ZXh0KTtcbiAgICB0aGlzLnZpc2l0KGFzdC5yaWdodCwgY29udGV4dCk7XG4gIH1cbiAgdmlzaXRDaGFpbihhc3Q6IENoYWluLCBjb250ZXh0OiBhbnkpOiBhbnkge1xuICAgIHRoaXMudmlzaXRBbGwoYXN0LmV4cHJlc3Npb25zLCBjb250ZXh0KTtcbiAgfVxuICB2aXNpdENvbmRpdGlvbmFsKGFzdDogQ29uZGl0aW9uYWwsIGNvbnRleHQ6IGFueSk6IGFueSB7XG4gICAgdGhpcy52aXNpdChhc3QuY29uZGl0aW9uLCBjb250ZXh0KTtcbiAgICB0aGlzLnZpc2l0KGFzdC50cnVlRXhwLCBjb250ZXh0KTtcbiAgICB0aGlzLnZpc2l0KGFzdC5mYWxzZUV4cCwgY29udGV4dCk7XG4gIH1cbiAgdmlzaXRQaXBlKGFzdDogQmluZGluZ1BpcGUsIGNvbnRleHQ6IGFueSk6IGFueSB7XG4gICAgdGhpcy52aXNpdChhc3QuZXhwLCBjb250ZXh0KTtcbiAgICB0aGlzLnZpc2l0QWxsKGFzdC5hcmdzLCBjb250ZXh0KTtcbiAgfVxuICB2aXNpdEZ1bmN0aW9uQ2FsbChhc3Q6IEZ1bmN0aW9uQ2FsbCwgY29udGV4dDogYW55KTogYW55IHtcbiAgICBpZiAoYXN0LnRhcmdldCkge1xuICAgICAgdGhpcy52aXNpdChhc3QudGFyZ2V0LCBjb250ZXh0KTtcbiAgICB9XG4gICAgdGhpcy52aXNpdEFsbChhc3QuYXJncywgY29udGV4dCk7XG4gIH1cbiAgdmlzaXRJbXBsaWNpdFJlY2VpdmVyKGFzdDogVGhpc1JlY2VpdmVyLCBjb250ZXh0OiBhbnkpOiBhbnkge31cbiAgdmlzaXRUaGlzUmVjZWl2ZXIoYXN0OiBUaGlzUmVjZWl2ZXIsIGNvbnRleHQ6IGFueSk6IGFueSB7fVxuICB2aXNpdEludGVycG9sYXRpb24oYXN0OiBJbnRlcnBvbGF0aW9uLCBjb250ZXh0OiBhbnkpOiBhbnkge1xuICAgIHRoaXMudmlzaXRBbGwoYXN0LmV4cHJlc3Npb25zLCBjb250ZXh0KTtcbiAgfVxuICB2aXNpdEtleWVkUmVhZChhc3Q6IEtleWVkUmVhZCwgY29udGV4dDogYW55KTogYW55IHtcbiAgICB0aGlzLnZpc2l0KGFzdC5vYmosIGNvbnRleHQpO1xuICAgIHRoaXMudmlzaXQoYXN0LmtleSwgY29udGV4dCk7XG4gIH1cbiAgdmlzaXRLZXllZFdyaXRlKGFzdDogS2V5ZWRXcml0ZSwgY29udGV4dDogYW55KTogYW55IHtcbiAgICB0aGlzLnZpc2l0KGFzdC5vYmosIGNvbnRleHQpO1xuICAgIHRoaXMudmlzaXQoYXN0LmtleSwgY29udGV4dCk7XG4gICAgdGhpcy52aXNpdChhc3QudmFsdWUsIGNvbnRleHQpO1xuICB9XG4gIHZpc2l0TGl0ZXJhbEFycmF5KGFzdDogTGl0ZXJhbEFycmF5LCBjb250ZXh0OiBhbnkpOiBhbnkge1xuICAgIHRoaXMudmlzaXRBbGwoYXN0LmV4cHJlc3Npb25zLCBjb250ZXh0KTtcbiAgfVxuICB2aXNpdExpdGVyYWxNYXAoYXN0OiBMaXRlcmFsTWFwLCBjb250ZXh0OiBhbnkpOiBhbnkge1xuICAgIHRoaXMudmlzaXRBbGwoYXN0LnZhbHVlcywgY29udGV4dCk7XG4gIH1cbiAgdmlzaXRMaXRlcmFsUHJpbWl0aXZlKGFzdDogTGl0ZXJhbFByaW1pdGl2ZSwgY29udGV4dDogYW55KTogYW55IHt9XG4gIHZpc2l0TWV0aG9kQ2FsbChhc3Q6IE1ldGhvZENhbGwsIGNvbnRleHQ6IGFueSk6IGFueSB7XG4gICAgdGhpcy52aXNpdChhc3QucmVjZWl2ZXIsIGNvbnRleHQpO1xuICAgIHRoaXMudmlzaXRBbGwoYXN0LmFyZ3MsIGNvbnRleHQpO1xuICB9XG4gIHZpc2l0UHJlZml4Tm90KGFzdDogUHJlZml4Tm90LCBjb250ZXh0OiBhbnkpOiBhbnkge1xuICAgIHRoaXMudmlzaXQoYXN0LmV4cHJlc3Npb24sIGNvbnRleHQpO1xuICB9XG4gIHZpc2l0Tm9uTnVsbEFzc2VydChhc3Q6IE5vbk51bGxBc3NlcnQsIGNvbnRleHQ6IGFueSk6IGFueSB7XG4gICAgdGhpcy52aXNpdChhc3QuZXhwcmVzc2lvbiwgY29udGV4dCk7XG4gIH1cbiAgdmlzaXRQcm9wZXJ0eVJlYWQoYXN0OiBQcm9wZXJ0eVJlYWQsIGNvbnRleHQ6IGFueSk6IGFueSB7XG4gICAgdGhpcy52aXNpdChhc3QucmVjZWl2ZXIsIGNvbnRleHQpO1xuICB9XG4gIHZpc2l0UHJvcGVydHlXcml0ZShhc3Q6IFByb3BlcnR5V3JpdGUsIGNvbnRleHQ6IGFueSk6IGFueSB7XG4gICAgdGhpcy52aXNpdChhc3QucmVjZWl2ZXIsIGNvbnRleHQpO1xuICAgIHRoaXMudmlzaXQoYXN0LnZhbHVlLCBjb250ZXh0KTtcbiAgfVxuICB2aXNpdFNhZmVQcm9wZXJ0eVJlYWQoYXN0OiBTYWZlUHJvcGVydHlSZWFkLCBjb250ZXh0OiBhbnkpOiBhbnkge1xuICAgIHRoaXMudmlzaXQoYXN0LnJlY2VpdmVyLCBjb250ZXh0KTtcbiAgfVxuICB2aXNpdFNhZmVNZXRob2RDYWxsKGFzdDogU2FmZU1ldGhvZENhbGwsIGNvbnRleHQ6IGFueSk6IGFueSB7XG4gICAgdGhpcy52aXNpdChhc3QucmVjZWl2ZXIsIGNvbnRleHQpO1xuICAgIHRoaXMudmlzaXRBbGwoYXN0LmFyZ3MsIGNvbnRleHQpO1xuICB9XG4gIHZpc2l0UXVvdGUoYXN0OiBRdW90ZSwgY29udGV4dDogYW55KTogYW55IHt9XG4gIC8vIFRoaXMgaXMgbm90IHBhcnQgb2YgdGhlIEFzdFZpc2l0b3IgaW50ZXJmYWNlLCBqdXN0IGEgaGVscGVyIG1ldGhvZFxuICB2aXNpdEFsbChhc3RzOiBBU1RbXSwgY29udGV4dDogYW55KTogYW55IHtcbiAgICBmb3IgKGNvbnN0IGFzdCBvZiBhc3RzKSB7XG4gICAgICB0aGlzLnZpc2l0KGFzdCwgY29udGV4dCk7XG4gICAgfVxuICB9XG59XG5cbmV4cG9ydCBjbGFzcyBBc3RUcmFuc2Zvcm1lciBpbXBsZW1lbnRzIEFzdFZpc2l0b3Ige1xuICB2aXNpdEltcGxpY2l0UmVjZWl2ZXIoYXN0OiBJbXBsaWNpdFJlY2VpdmVyLCBjb250ZXh0OiBhbnkpOiBBU1Qge1xuICAgIHJldHVybiBhc3Q7XG4gIH1cblxuICB2aXNpdFRoaXNSZWNlaXZlcihhc3Q6IFRoaXNSZWNlaXZlciwgY29udGV4dDogYW55KTogQVNUIHtcbiAgICByZXR1cm4gYXN0O1xuICB9XG5cbiAgdmlzaXRJbnRlcnBvbGF0aW9uKGFzdDogSW50ZXJwb2xhdGlvbiwgY29udGV4dDogYW55KTogQVNUIHtcbiAgICByZXR1cm4gbmV3IEludGVycG9sYXRpb24oYXN0LnNwYW4sIGFzdC5zb3VyY2VTcGFuLCBhc3Quc3RyaW5ncywgdGhpcy52aXNpdEFsbChhc3QuZXhwcmVzc2lvbnMpKTtcbiAgfVxuXG4gIHZpc2l0TGl0ZXJhbFByaW1pdGl2ZShhc3Q6IExpdGVyYWxQcmltaXRpdmUsIGNvbnRleHQ6IGFueSk6IEFTVCB7XG4gICAgcmV0dXJuIG5ldyBMaXRlcmFsUHJpbWl0aXZlKGFzdC5zcGFuLCBhc3Quc291cmNlU3BhbiwgYXN0LnZhbHVlKTtcbiAgfVxuXG4gIHZpc2l0UHJvcGVydHlSZWFkKGFzdDogUHJvcGVydHlSZWFkLCBjb250ZXh0OiBhbnkpOiBBU1Qge1xuICAgIHJldHVybiBuZXcgUHJvcGVydHlSZWFkKFxuICAgICAgICBhc3Quc3BhbiwgYXN0LnNvdXJjZVNwYW4sIGFzdC5uYW1lU3BhbiwgYXN0LnJlY2VpdmVyLnZpc2l0KHRoaXMpLCBhc3QubmFtZSk7XG4gIH1cblxuICB2aXNpdFByb3BlcnR5V3JpdGUoYXN0OiBQcm9wZXJ0eVdyaXRlLCBjb250ZXh0OiBhbnkpOiBBU1Qge1xuICAgIHJldHVybiBuZXcgUHJvcGVydHlXcml0ZShcbiAgICAgICAgYXN0LnNwYW4sIGFzdC5zb3VyY2VTcGFuLCBhc3QubmFtZVNwYW4sIGFzdC5yZWNlaXZlci52aXNpdCh0aGlzKSwgYXN0Lm5hbWUsXG4gICAgICAgIGFzdC52YWx1ZS52aXNpdCh0aGlzKSk7XG4gIH1cblxuICB2aXNpdFNhZmVQcm9wZXJ0eVJlYWQoYXN0OiBTYWZlUHJvcGVydHlSZWFkLCBjb250ZXh0OiBhbnkpOiBBU1Qge1xuICAgIHJldHVybiBuZXcgU2FmZVByb3BlcnR5UmVhZChcbiAgICAgICAgYXN0LnNwYW4sIGFzdC5zb3VyY2VTcGFuLCBhc3QubmFtZVNwYW4sIGFzdC5yZWNlaXZlci52aXNpdCh0aGlzKSwgYXN0Lm5hbWUpO1xuICB9XG5cbiAgdmlzaXRNZXRob2RDYWxsKGFzdDogTWV0aG9kQ2FsbCwgY29udGV4dDogYW55KTogQVNUIHtcbiAgICByZXR1cm4gbmV3IE1ldGhvZENhbGwoXG4gICAgICAgIGFzdC5zcGFuLCBhc3Quc291cmNlU3BhbiwgYXN0Lm5hbWVTcGFuLCBhc3QucmVjZWl2ZXIudmlzaXQodGhpcyksIGFzdC5uYW1lLFxuICAgICAgICB0aGlzLnZpc2l0QWxsKGFzdC5hcmdzKSk7XG4gIH1cblxuICB2aXNpdFNhZmVNZXRob2RDYWxsKGFzdDogU2FmZU1ldGhvZENhbGwsIGNvbnRleHQ6IGFueSk6IEFTVCB7XG4gICAgcmV0dXJuIG5ldyBTYWZlTWV0aG9kQ2FsbChcbiAgICAgICAgYXN0LnNwYW4sIGFzdC5zb3VyY2VTcGFuLCBhc3QubmFtZVNwYW4sIGFzdC5yZWNlaXZlci52aXNpdCh0aGlzKSwgYXN0Lm5hbWUsXG4gICAgICAgIHRoaXMudmlzaXRBbGwoYXN0LmFyZ3MpKTtcbiAgfVxuXG4gIHZpc2l0RnVuY3Rpb25DYWxsKGFzdDogRnVuY3Rpb25DYWxsLCBjb250ZXh0OiBhbnkpOiBBU1Qge1xuICAgIHJldHVybiBuZXcgRnVuY3Rpb25DYWxsKFxuICAgICAgICBhc3Quc3BhbiwgYXN0LnNvdXJjZVNwYW4sIGFzdC50YXJnZXQhLnZpc2l0KHRoaXMpLCB0aGlzLnZpc2l0QWxsKGFzdC5hcmdzKSk7XG4gIH1cblxuICB2aXNpdExpdGVyYWxBcnJheShhc3Q6IExpdGVyYWxBcnJheSwgY29udGV4dDogYW55KTogQVNUIHtcbiAgICByZXR1cm4gbmV3IExpdGVyYWxBcnJheShhc3Quc3BhbiwgYXN0LnNvdXJjZVNwYW4sIHRoaXMudmlzaXRBbGwoYXN0LmV4cHJlc3Npb25zKSk7XG4gIH1cblxuICB2aXNpdExpdGVyYWxNYXAoYXN0OiBMaXRlcmFsTWFwLCBjb250ZXh0OiBhbnkpOiBBU1Qge1xuICAgIHJldHVybiBuZXcgTGl0ZXJhbE1hcChhc3Quc3BhbiwgYXN0LnNvdXJjZVNwYW4sIGFzdC5rZXlzLCB0aGlzLnZpc2l0QWxsKGFzdC52YWx1ZXMpKTtcbiAgfVxuXG4gIHZpc2l0VW5hcnkoYXN0OiBVbmFyeSwgY29udGV4dDogYW55KTogQVNUIHtcbiAgICBzd2l0Y2ggKGFzdC5vcGVyYXRvcikge1xuICAgICAgY2FzZSAnKyc6XG4gICAgICAgIHJldHVybiBVbmFyeS5jcmVhdGVQbHVzKGFzdC5zcGFuLCBhc3Quc291cmNlU3BhbiwgYXN0LmV4cHIudmlzaXQodGhpcykpO1xuICAgICAgY2FzZSAnLSc6XG4gICAgICAgIHJldHVybiBVbmFyeS5jcmVhdGVNaW51cyhhc3Quc3BhbiwgYXN0LnNvdXJjZVNwYW4sIGFzdC5leHByLnZpc2l0KHRoaXMpKTtcbiAgICAgIGRlZmF1bHQ6XG4gICAgICAgIHRocm93IG5ldyBFcnJvcihgVW5rbm93biB1bmFyeSBvcGVyYXRvciAke2FzdC5vcGVyYXRvcn1gKTtcbiAgICB9XG4gIH1cblxuICB2aXNpdEJpbmFyeShhc3Q6IEJpbmFyeSwgY29udGV4dDogYW55KTogQVNUIHtcbiAgICByZXR1cm4gbmV3IEJpbmFyeShcbiAgICAgICAgYXN0LnNwYW4sIGFzdC5zb3VyY2VTcGFuLCBhc3Qub3BlcmF0aW9uLCBhc3QubGVmdC52aXNpdCh0aGlzKSwgYXN0LnJpZ2h0LnZpc2l0KHRoaXMpKTtcbiAgfVxuXG4gIHZpc2l0UHJlZml4Tm90KGFzdDogUHJlZml4Tm90LCBjb250ZXh0OiBhbnkpOiBBU1Qge1xuICAgIHJldHVybiBuZXcgUHJlZml4Tm90KGFzdC5zcGFuLCBhc3Quc291cmNlU3BhbiwgYXN0LmV4cHJlc3Npb24udmlzaXQodGhpcykpO1xuICB9XG5cbiAgdmlzaXROb25OdWxsQXNzZXJ0KGFzdDogTm9uTnVsbEFzc2VydCwgY29udGV4dDogYW55KTogQVNUIHtcbiAgICByZXR1cm4gbmV3IE5vbk51bGxBc3NlcnQoYXN0LnNwYW4sIGFzdC5zb3VyY2VTcGFuLCBhc3QuZXhwcmVzc2lvbi52aXNpdCh0aGlzKSk7XG4gIH1cblxuICB2aXNpdENvbmRpdGlvbmFsKGFzdDogQ29uZGl0aW9uYWwsIGNvbnRleHQ6IGFueSk6IEFTVCB7XG4gICAgcmV0dXJuIG5ldyBDb25kaXRpb25hbChcbiAgICAgICAgYXN0LnNwYW4sIGFzdC5zb3VyY2VTcGFuLCBhc3QuY29uZGl0aW9uLnZpc2l0KHRoaXMpLCBhc3QudHJ1ZUV4cC52aXNpdCh0aGlzKSxcbiAgICAgICAgYXN0LmZhbHNlRXhwLnZpc2l0KHRoaXMpKTtcbiAgfVxuXG4gIHZpc2l0UGlwZShhc3Q6IEJpbmRpbmdQaXBlLCBjb250ZXh0OiBhbnkpOiBBU1Qge1xuICAgIHJldHVybiBuZXcgQmluZGluZ1BpcGUoXG4gICAgICAgIGFzdC5zcGFuLCBhc3Quc291cmNlU3BhbiwgYXN0LmV4cC52aXNpdCh0aGlzKSwgYXN0Lm5hbWUsIHRoaXMudmlzaXRBbGwoYXN0LmFyZ3MpLFxuICAgICAgICBhc3QubmFtZVNwYW4pO1xuICB9XG5cbiAgdmlzaXRLZXllZFJlYWQoYXN0OiBLZXllZFJlYWQsIGNvbnRleHQ6IGFueSk6IEFTVCB7XG4gICAgcmV0dXJuIG5ldyBLZXllZFJlYWQoYXN0LnNwYW4sIGFzdC5zb3VyY2VTcGFuLCBhc3Qub2JqLnZpc2l0KHRoaXMpLCBhc3Qua2V5LnZpc2l0KHRoaXMpKTtcbiAgfVxuXG4gIHZpc2l0S2V5ZWRXcml0ZShhc3Q6IEtleWVkV3JpdGUsIGNvbnRleHQ6IGFueSk6IEFTVCB7XG4gICAgcmV0dXJuIG5ldyBLZXllZFdyaXRlKFxuICAgICAgICBhc3Quc3BhbiwgYXN0LnNvdXJjZVNwYW4sIGFzdC5vYmoudmlzaXQodGhpcyksIGFzdC5rZXkudmlzaXQodGhpcyksIGFzdC52YWx1ZS52aXNpdCh0aGlzKSk7XG4gIH1cblxuICB2aXNpdEFsbChhc3RzOiBhbnlbXSk6IGFueVtdIHtcbiAgICBjb25zdCByZXMgPSBbXTtcbiAgICBmb3IgKGxldCBpID0gMDsgaSA8IGFzdHMubGVuZ3RoOyArK2kpIHtcbiAgICAgIHJlc1tpXSA9IGFzdHNbaV0udmlzaXQodGhpcyk7XG4gICAgfVxuICAgIHJldHVybiByZXM7XG4gIH1cblxuICB2aXNpdENoYWluKGFzdDogQ2hhaW4sIGNvbnRleHQ6IGFueSk6IEFTVCB7XG4gICAgcmV0dXJuIG5ldyBDaGFpbihhc3Quc3BhbiwgYXN0LnNvdXJjZVNwYW4sIHRoaXMudmlzaXRBbGwoYXN0LmV4cHJlc3Npb25zKSk7XG4gIH1cblxuICB2aXNpdFF1b3RlKGFzdDogUXVvdGUsIGNvbnRleHQ6IGFueSk6IEFTVCB7XG4gICAgcmV0dXJuIG5ldyBRdW90ZShcbiAgICAgICAgYXN0LnNwYW4sIGFzdC5zb3VyY2VTcGFuLCBhc3QucHJlZml4LCBhc3QudW5pbnRlcnByZXRlZEV4cHJlc3Npb24sIGFzdC5sb2NhdGlvbik7XG4gIH1cbn1cblxuLy8gQSB0cmFuc2Zvcm1lciB0aGF0IG9ubHkgY3JlYXRlcyBuZXcgbm9kZXMgaWYgdGhlIHRyYW5zZm9ybWVyIG1ha2VzIGEgY2hhbmdlIG9yXG4vLyBhIGNoYW5nZSBpcyBtYWRlIGEgY2hpbGQgbm9kZS5cbmV4cG9ydCBjbGFzcyBBc3RNZW1vcnlFZmZpY2llbnRUcmFuc2Zvcm1lciBpbXBsZW1lbnRzIEFzdFZpc2l0b3Ige1xuICB2aXNpdEltcGxpY2l0UmVjZWl2ZXIoYXN0OiBJbXBsaWNpdFJlY2VpdmVyLCBjb250ZXh0OiBhbnkpOiBBU1Qge1xuICAgIHJldHVybiBhc3Q7XG4gIH1cblxuICB2aXNpdFRoaXNSZWNlaXZlcihhc3Q6IFRoaXNSZWNlaXZlciwgY29udGV4dDogYW55KTogQVNUIHtcbiAgICByZXR1cm4gYXN0O1xuICB9XG5cbiAgdmlzaXRJbnRlcnBvbGF0aW9uKGFzdDogSW50ZXJwb2xhdGlvbiwgY29udGV4dDogYW55KTogSW50ZXJwb2xhdGlvbiB7XG4gICAgY29uc3QgZXhwcmVzc2lvbnMgPSB0aGlzLnZpc2l0QWxsKGFzdC5leHByZXNzaW9ucyk7XG4gICAgaWYgKGV4cHJlc3Npb25zICE9PSBhc3QuZXhwcmVzc2lvbnMpXG4gICAgICByZXR1cm4gbmV3IEludGVycG9sYXRpb24oYXN0LnNwYW4sIGFzdC5zb3VyY2VTcGFuLCBhc3Quc3RyaW5ncywgZXhwcmVzc2lvbnMpO1xuICAgIHJldHVybiBhc3Q7XG4gIH1cblxuICB2aXNpdExpdGVyYWxQcmltaXRpdmUoYXN0OiBMaXRlcmFsUHJpbWl0aXZlLCBjb250ZXh0OiBhbnkpOiBBU1Qge1xuICAgIHJldHVybiBhc3Q7XG4gIH1cblxuICB2aXNpdFByb3BlcnR5UmVhZChhc3Q6IFByb3BlcnR5UmVhZCwgY29udGV4dDogYW55KTogQVNUIHtcbiAgICBjb25zdCByZWNlaXZlciA9IGFzdC5yZWNlaXZlci52aXNpdCh0aGlzKTtcbiAgICBpZiAocmVjZWl2ZXIgIT09IGFzdC5yZWNlaXZlcikge1xuICAgICAgcmV0dXJuIG5ldyBQcm9wZXJ0eVJlYWQoYXN0LnNwYW4sIGFzdC5zb3VyY2VTcGFuLCBhc3QubmFtZVNwYW4sIHJlY2VpdmVyLCBhc3QubmFtZSk7XG4gICAgfVxuICAgIHJldHVybiBhc3Q7XG4gIH1cblxuICB2aXNpdFByb3BlcnR5V3JpdGUoYXN0OiBQcm9wZXJ0eVdyaXRlLCBjb250ZXh0OiBhbnkpOiBBU1Qge1xuICAgIGNvbnN0IHJlY2VpdmVyID0gYXN0LnJlY2VpdmVyLnZpc2l0KHRoaXMpO1xuICAgIGNvbnN0IHZhbHVlID0gYXN0LnZhbHVlLnZpc2l0KHRoaXMpO1xuICAgIGlmIChyZWNlaXZlciAhPT0gYXN0LnJlY2VpdmVyIHx8IHZhbHVlICE9PSBhc3QudmFsdWUpIHtcbiAgICAgIHJldHVybiBuZXcgUHJvcGVydHlXcml0ZShhc3Quc3BhbiwgYXN0LnNvdXJjZVNwYW4sIGFzdC5uYW1lU3BhbiwgcmVjZWl2ZXIsIGFzdC5uYW1lLCB2YWx1ZSk7XG4gICAgfVxuICAgIHJldHVybiBhc3Q7XG4gIH1cblxuICB2aXNpdFNhZmVQcm9wZXJ0eVJlYWQoYXN0OiBTYWZlUHJvcGVydHlSZWFkLCBjb250ZXh0OiBhbnkpOiBBU1Qge1xuICAgIGNvbnN0IHJlY2VpdmVyID0gYXN0LnJlY2VpdmVyLnZpc2l0KHRoaXMpO1xuICAgIGlmIChyZWNlaXZlciAhPT0gYXN0LnJlY2VpdmVyKSB7XG4gICAgICByZXR1cm4gbmV3IFNhZmVQcm9wZXJ0eVJlYWQoYXN0LnNwYW4sIGFzdC5zb3VyY2VTcGFuLCBhc3QubmFtZVNwYW4sIHJlY2VpdmVyLCBhc3QubmFtZSk7XG4gICAgfVxuICAgIHJldHVybiBhc3Q7XG4gIH1cblxuICB2aXNpdE1ldGhvZENhbGwoYXN0OiBNZXRob2RDYWxsLCBjb250ZXh0OiBhbnkpOiBBU1Qge1xuICAgIGNvbnN0IHJlY2VpdmVyID0gYXN0LnJlY2VpdmVyLnZpc2l0KHRoaXMpO1xuICAgIGNvbnN0IGFyZ3MgPSB0aGlzLnZpc2l0QWxsKGFzdC5hcmdzKTtcbiAgICBpZiAocmVjZWl2ZXIgIT09IGFzdC5yZWNlaXZlciB8fCBhcmdzICE9PSBhc3QuYXJncykge1xuICAgICAgcmV0dXJuIG5ldyBNZXRob2RDYWxsKGFzdC5zcGFuLCBhc3Quc291cmNlU3BhbiwgYXN0Lm5hbWVTcGFuLCByZWNlaXZlciwgYXN0Lm5hbWUsIGFyZ3MpO1xuICAgIH1cbiAgICByZXR1cm4gYXN0O1xuICB9XG5cbiAgdmlzaXRTYWZlTWV0aG9kQ2FsbChhc3Q6IFNhZmVNZXRob2RDYWxsLCBjb250ZXh0OiBhbnkpOiBBU1Qge1xuICAgIGNvbnN0IHJlY2VpdmVyID0gYXN0LnJlY2VpdmVyLnZpc2l0KHRoaXMpO1xuICAgIGNvbnN0IGFyZ3MgPSB0aGlzLnZpc2l0QWxsKGFzdC5hcmdzKTtcbiAgICBpZiAocmVjZWl2ZXIgIT09IGFzdC5yZWNlaXZlciB8fCBhcmdzICE9PSBhc3QuYXJncykge1xuICAgICAgcmV0dXJuIG5ldyBTYWZlTWV0aG9kQ2FsbChhc3Quc3BhbiwgYXN0LnNvdXJjZVNwYW4sIGFzdC5uYW1lU3BhbiwgcmVjZWl2ZXIsIGFzdC5uYW1lLCBhcmdzKTtcbiAgICB9XG4gICAgcmV0dXJuIGFzdDtcbiAgfVxuXG4gIHZpc2l0RnVuY3Rpb25DYWxsKGFzdDogRnVuY3Rpb25DYWxsLCBjb250ZXh0OiBhbnkpOiBBU1Qge1xuICAgIGNvbnN0IHRhcmdldCA9IGFzdC50YXJnZXQgJiYgYXN0LnRhcmdldC52aXNpdCh0aGlzKTtcbiAgICBjb25zdCBhcmdzID0gdGhpcy52aXNpdEFsbChhc3QuYXJncyk7XG4gICAgaWYgKHRhcmdldCAhPT0gYXN0LnRhcmdldCB8fCBhcmdzICE9PSBhc3QuYXJncykge1xuICAgICAgcmV0dXJuIG5ldyBGdW5jdGlvbkNhbGwoYXN0LnNwYW4sIGFzdC5zb3VyY2VTcGFuLCB0YXJnZXQsIGFyZ3MpO1xuICAgIH1cbiAgICByZXR1cm4gYXN0O1xuICB9XG5cbiAgdmlzaXRMaXRlcmFsQXJyYXkoYXN0OiBMaXRlcmFsQXJyYXksIGNvbnRleHQ6IGFueSk6IEFTVCB7XG4gICAgY29uc3QgZXhwcmVzc2lvbnMgPSB0aGlzLnZpc2l0QWxsKGFzdC5leHByZXNzaW9ucyk7XG4gICAgaWYgKGV4cHJlc3Npb25zICE9PSBhc3QuZXhwcmVzc2lvbnMpIHtcbiAgICAgIHJldHVybiBuZXcgTGl0ZXJhbEFycmF5KGFzdC5zcGFuLCBhc3Quc291cmNlU3BhbiwgZXhwcmVzc2lvbnMpO1xuICAgIH1cbiAgICByZXR1cm4gYXN0O1xuICB9XG5cbiAgdmlzaXRMaXRlcmFsTWFwKGFzdDogTGl0ZXJhbE1hcCwgY29udGV4dDogYW55KTogQVNUIHtcbiAgICBjb25zdCB2YWx1ZXMgPSB0aGlzLnZpc2l0QWxsKGFzdC52YWx1ZXMpO1xuICAgIGlmICh2YWx1ZXMgIT09IGFzdC52YWx1ZXMpIHtcbiAgICAgIHJldHVybiBuZXcgTGl0ZXJhbE1hcChhc3Quc3BhbiwgYXN0LnNvdXJjZVNwYW4sIGFzdC5rZXlzLCB2YWx1ZXMpO1xuICAgIH1cbiAgICByZXR1cm4gYXN0O1xuICB9XG5cbiAgdmlzaXRVbmFyeShhc3Q6IFVuYXJ5LCBjb250ZXh0OiBhbnkpOiBBU1Qge1xuICAgIGNvbnN0IGV4cHIgPSBhc3QuZXhwci52aXNpdCh0aGlzKTtcbiAgICBpZiAoZXhwciAhPT0gYXN0LmV4cHIpIHtcbiAgICAgIHN3aXRjaCAoYXN0Lm9wZXJhdG9yKSB7XG4gICAgICAgIGNhc2UgJysnOlxuICAgICAgICAgIHJldHVybiBVbmFyeS5jcmVhdGVQbHVzKGFzdC5zcGFuLCBhc3Quc291cmNlU3BhbiwgZXhwcik7XG4gICAgICAgIGNhc2UgJy0nOlxuICAgICAgICAgIHJldHVybiBVbmFyeS5jcmVhdGVNaW51cyhhc3Quc3BhbiwgYXN0LnNvdXJjZVNwYW4sIGV4cHIpO1xuICAgICAgICBkZWZhdWx0OlxuICAgICAgICAgIHRocm93IG5ldyBFcnJvcihgVW5rbm93biB1bmFyeSBvcGVyYXRvciAke2FzdC5vcGVyYXRvcn1gKTtcbiAgICAgIH1cbiAgICB9XG4gICAgcmV0dXJuIGFzdDtcbiAgfVxuXG4gIHZpc2l0QmluYXJ5KGFzdDogQmluYXJ5LCBjb250ZXh0OiBhbnkpOiBBU1Qge1xuICAgIGNvbnN0IGxlZnQgPSBhc3QubGVmdC52aXNpdCh0aGlzKTtcbiAgICBjb25zdCByaWdodCA9IGFzdC5yaWdodC52aXNpdCh0aGlzKTtcbiAgICBpZiAobGVmdCAhPT0gYXN0LmxlZnQgfHwgcmlnaHQgIT09IGFzdC5yaWdodCkge1xuICAgICAgcmV0dXJuIG5ldyBCaW5hcnkoYXN0LnNwYW4sIGFzdC5zb3VyY2VTcGFuLCBhc3Qub3BlcmF0aW9uLCBsZWZ0LCByaWdodCk7XG4gICAgfVxuICAgIHJldHVybiBhc3Q7XG4gIH1cblxuICB2aXNpdFByZWZpeE5vdChhc3Q6IFByZWZpeE5vdCwgY29udGV4dDogYW55KTogQVNUIHtcbiAgICBjb25zdCBleHByZXNzaW9uID0gYXN0LmV4cHJlc3Npb24udmlzaXQodGhpcyk7XG4gICAgaWYgKGV4cHJlc3Npb24gIT09IGFzdC5leHByZXNzaW9uKSB7XG4gICAgICByZXR1cm4gbmV3IFByZWZpeE5vdChhc3Quc3BhbiwgYXN0LnNvdXJjZVNwYW4sIGV4cHJlc3Npb24pO1xuICAgIH1cbiAgICByZXR1cm4gYXN0O1xuICB9XG5cbiAgdmlzaXROb25OdWxsQXNzZXJ0KGFzdDogTm9uTnVsbEFzc2VydCwgY29udGV4dDogYW55KTogQVNUIHtcbiAgICBjb25zdCBleHByZXNzaW9uID0gYXN0LmV4cHJlc3Npb24udmlzaXQodGhpcyk7XG4gICAgaWYgKGV4cHJlc3Npb24gIT09IGFzdC5leHByZXNzaW9uKSB7XG4gICAgICByZXR1cm4gbmV3IE5vbk51bGxBc3NlcnQoYXN0LnNwYW4sIGFzdC5zb3VyY2VTcGFuLCBleHByZXNzaW9uKTtcbiAgICB9XG4gICAgcmV0dXJuIGFzdDtcbiAgfVxuXG4gIHZpc2l0Q29uZGl0aW9uYWwoYXN0OiBDb25kaXRpb25hbCwgY29udGV4dDogYW55KTogQVNUIHtcbiAgICBjb25zdCBjb25kaXRpb24gPSBhc3QuY29uZGl0aW9uLnZpc2l0KHRoaXMpO1xuICAgIGNvbnN0IHRydWVFeHAgPSBhc3QudHJ1ZUV4cC52aXNpdCh0aGlzKTtcbiAgICBjb25zdCBmYWxzZUV4cCA9IGFzdC5mYWxzZUV4cC52aXNpdCh0aGlzKTtcbiAgICBpZiAoY29uZGl0aW9uICE9PSBhc3QuY29uZGl0aW9uIHx8IHRydWVFeHAgIT09IGFzdC50cnVlRXhwIHx8IGZhbHNlRXhwICE9PSBhc3QuZmFsc2VFeHApIHtcbiAgICAgIHJldHVybiBuZXcgQ29uZGl0aW9uYWwoYXN0LnNwYW4sIGFzdC5zb3VyY2VTcGFuLCBjb25kaXRpb24sIHRydWVFeHAsIGZhbHNlRXhwKTtcbiAgICB9XG4gICAgcmV0dXJuIGFzdDtcbiAgfVxuXG4gIHZpc2l0UGlwZShhc3Q6IEJpbmRpbmdQaXBlLCBjb250ZXh0OiBhbnkpOiBBU1Qge1xuICAgIGNvbnN0IGV4cCA9IGFzdC5leHAudmlzaXQodGhpcyk7XG4gICAgY29uc3QgYXJncyA9IHRoaXMudmlzaXRBbGwoYXN0LmFyZ3MpO1xuICAgIGlmIChleHAgIT09IGFzdC5leHAgfHwgYXJncyAhPT0gYXN0LmFyZ3MpIHtcbiAgICAgIHJldHVybiBuZXcgQmluZGluZ1BpcGUoYXN0LnNwYW4sIGFzdC5zb3VyY2VTcGFuLCBleHAsIGFzdC5uYW1lLCBhcmdzLCBhc3QubmFtZVNwYW4pO1xuICAgIH1cbiAgICByZXR1cm4gYXN0O1xuICB9XG5cbiAgdmlzaXRLZXllZFJlYWQoYXN0OiBLZXllZFJlYWQsIGNvbnRleHQ6IGFueSk6IEFTVCB7XG4gICAgY29uc3Qgb2JqID0gYXN0Lm9iai52aXNpdCh0aGlzKTtcbiAgICBjb25zdCBrZXkgPSBhc3Qua2V5LnZpc2l0KHRoaXMpO1xuICAgIGlmIChvYmogIT09IGFzdC5vYmogfHwga2V5ICE9PSBhc3Qua2V5KSB7XG4gICAgICByZXR1cm4gbmV3IEtleWVkUmVhZChhc3Quc3BhbiwgYXN0LnNvdXJjZVNwYW4sIG9iaiwga2V5KTtcbiAgICB9XG4gICAgcmV0dXJuIGFzdDtcbiAgfVxuXG4gIHZpc2l0S2V5ZWRXcml0ZShhc3Q6IEtleWVkV3JpdGUsIGNvbnRleHQ6IGFueSk6IEFTVCB7XG4gICAgY29uc3Qgb2JqID0gYXN0Lm9iai52aXNpdCh0aGlzKTtcbiAgICBjb25zdCBrZXkgPSBhc3Qua2V5LnZpc2l0KHRoaXMpO1xuICAgIGNvbnN0IHZhbHVlID0gYXN0LnZhbHVlLnZpc2l0KHRoaXMpO1xuICAgIGlmIChvYmogIT09IGFzdC5vYmogfHwga2V5ICE9PSBhc3Qua2V5IHx8IHZhbHVlICE9PSBhc3QudmFsdWUpIHtcbiAgICAgIHJldHVybiBuZXcgS2V5ZWRXcml0ZShhc3Quc3BhbiwgYXN0LnNvdXJjZVNwYW4sIG9iaiwga2V5LCB2YWx1ZSk7XG4gICAgfVxuICAgIHJldHVybiBhc3Q7XG4gIH1cblxuICB2aXNpdEFsbChhc3RzOiBhbnlbXSk6IGFueVtdIHtcbiAgICBjb25zdCByZXMgPSBbXTtcbiAgICBsZXQgbW9kaWZpZWQgPSBmYWxzZTtcbiAgICBmb3IgKGxldCBpID0gMDsgaSA8IGFzdHMubGVuZ3RoOyArK2kpIHtcbiAgICAgIGNvbnN0IG9yaWdpbmFsID0gYXN0c1tpXTtcbiAgICAgIGNvbnN0IHZhbHVlID0gb3JpZ2luYWwudmlzaXQodGhpcyk7XG4gICAgICByZXNbaV0gPSB2YWx1ZTtcbiAgICAgIG1vZGlmaWVkID0gbW9kaWZpZWQgfHwgdmFsdWUgIT09IG9yaWdpbmFsO1xuICAgIH1cbiAgICByZXR1cm4gbW9kaWZpZWQgPyByZXMgOiBhc3RzO1xuICB9XG5cbiAgdmlzaXRDaGFpbihhc3Q6IENoYWluLCBjb250ZXh0OiBhbnkpOiBBU1Qge1xuICAgIGNvbnN0IGV4cHJlc3Npb25zID0gdGhpcy52aXNpdEFsbChhc3QuZXhwcmVzc2lvbnMpO1xuICAgIGlmIChleHByZXNzaW9ucyAhPT0gYXN0LmV4cHJlc3Npb25zKSB7XG4gICAgICByZXR1cm4gbmV3IENoYWluKGFzdC5zcGFuLCBhc3Quc291cmNlU3BhbiwgZXhwcmVzc2lvbnMpO1xuICAgIH1cbiAgICByZXR1cm4gYXN0O1xuICB9XG5cbiAgdmlzaXRRdW90ZShhc3Q6IFF1b3RlLCBjb250ZXh0OiBhbnkpOiBBU1Qge1xuICAgIHJldHVybiBhc3Q7XG4gIH1cbn1cblxuLy8gQmluZGluZ3NcblxuZXhwb3J0IGNsYXNzIFBhcnNlZFByb3BlcnR5IHtcbiAgcHVibGljIHJlYWRvbmx5IGlzTGl0ZXJhbDogYm9vbGVhbjtcbiAgcHVibGljIHJlYWRvbmx5IGlzQW5pbWF0aW9uOiBib29sZWFuO1xuXG4gIGNvbnN0cnVjdG9yKFxuICAgICAgcHVibGljIG5hbWU6IHN0cmluZywgcHVibGljIGV4cHJlc3Npb246IEFTVFdpdGhTb3VyY2UsIHB1YmxpYyB0eXBlOiBQYXJzZWRQcm9wZXJ0eVR5cGUsXG4gICAgICAvLyBUT0RPKEZXLTIwOTUpOiBga2V5U3BhbmAgc2hvdWxkIHJlYWxseSBiZSByZXF1aXJlZCBidXQgYWxsb3dzIGB1bmRlZmluZWRgIHNvIFZFIGRvZXNcbiAgICAgIC8vIG5vdCBuZWVkIHRvIGJlIHVwZGF0ZWQuIE1ha2UgYGtleVNwYW5gIHJlcXVpcmVkIHdoZW4gVkUgaXMgcmVtb3ZlZC5cbiAgICAgIHB1YmxpYyBzb3VyY2VTcGFuOiBQYXJzZVNvdXJjZVNwYW4sIHJlYWRvbmx5IGtleVNwYW46IFBhcnNlU291cmNlU3Bhbnx1bmRlZmluZWQsXG4gICAgICBwdWJsaWMgdmFsdWVTcGFuOiBQYXJzZVNvdXJjZVNwYW58dW5kZWZpbmVkKSB7XG4gICAgdGhpcy5pc0xpdGVyYWwgPSB0aGlzLnR5cGUgPT09IFBhcnNlZFByb3BlcnR5VHlwZS5MSVRFUkFMX0FUVFI7XG4gICAgdGhpcy5pc0FuaW1hdGlvbiA9IHRoaXMudHlwZSA9PT0gUGFyc2VkUHJvcGVydHlUeXBlLkFOSU1BVElPTjtcbiAgfVxufVxuXG5leHBvcnQgZW51bSBQYXJzZWRQcm9wZXJ0eVR5cGUge1xuICBERUZBVUxULFxuICBMSVRFUkFMX0FUVFIsXG4gIEFOSU1BVElPTlxufVxuXG5leHBvcnQgY29uc3QgZW51bSBQYXJzZWRFdmVudFR5cGUge1xuICAvLyBET00gb3IgRGlyZWN0aXZlIGV2ZW50XG4gIFJlZ3VsYXIsXG4gIC8vIEFuaW1hdGlvbiBzcGVjaWZpYyBldmVudFxuICBBbmltYXRpb24sXG59XG5cbmV4cG9ydCBjbGFzcyBQYXJzZWRFdmVudCB7XG4gIC8vIFJlZ3VsYXIgZXZlbnRzIGhhdmUgYSB0YXJnZXRcbiAgLy8gQW5pbWF0aW9uIGV2ZW50cyBoYXZlIGEgcGhhc2VcbiAgY29uc3RydWN0b3IoXG4gICAgICBwdWJsaWMgbmFtZTogc3RyaW5nLCBwdWJsaWMgdGFyZ2V0T3JQaGFzZTogc3RyaW5nLCBwdWJsaWMgdHlwZTogUGFyc2VkRXZlbnRUeXBlLFxuICAgICAgcHVibGljIGhhbmRsZXI6IEFTVFdpdGhTb3VyY2UsIHB1YmxpYyBzb3VyY2VTcGFuOiBQYXJzZVNvdXJjZVNwYW4sXG4gICAgICAvLyBUT0RPKEZXLTIwOTUpOiBrZXlTcGFuIHNob3VsZCBiZSByZXF1aXJlZCBidXQgd2FzIG1hZGUgb3B0aW9uYWwgdG8gYXZvaWQgY2hhbmdpbmcgVkVcbiAgICAgIHB1YmxpYyBoYW5kbGVyU3BhbjogUGFyc2VTb3VyY2VTcGFuLCByZWFkb25seSBrZXlTcGFuOiBQYXJzZVNvdXJjZVNwYW58dW5kZWZpbmVkKSB7fVxufVxuXG4vKipcbiAqIFBhcnNlZFZhcmlhYmxlIHJlcHJlc2VudHMgYSB2YXJpYWJsZSBkZWNsYXJhdGlvbiBpbiBhIG1pY3Jvc3ludGF4IGV4cHJlc3Npb24uXG4gKi9cbmV4cG9ydCBjbGFzcyBQYXJzZWRWYXJpYWJsZSB7XG4gIGNvbnN0cnVjdG9yKFxuICAgICAgcHVibGljIHJlYWRvbmx5IG5hbWU6IHN0cmluZywgcHVibGljIHJlYWRvbmx5IHZhbHVlOiBzdHJpbmcsXG4gICAgICBwdWJsaWMgcmVhZG9ubHkgc291cmNlU3BhbjogUGFyc2VTb3VyY2VTcGFuLCBwdWJsaWMgcmVhZG9ubHkga2V5U3BhbjogUGFyc2VTb3VyY2VTcGFuLFxuICAgICAgcHVibGljIHJlYWRvbmx5IHZhbHVlU3Bhbj86IFBhcnNlU291cmNlU3Bhbikge31cbn1cblxuZXhwb3J0IGNvbnN0IGVudW0gQmluZGluZ1R5cGUge1xuICAvLyBBIHJlZ3VsYXIgYmluZGluZyB0byBhIHByb3BlcnR5IChlLmcuIGBbcHJvcGVydHldPVwiZXhwcmVzc2lvblwiYCkuXG4gIFByb3BlcnR5LFxuICAvLyBBIGJpbmRpbmcgdG8gYW4gZWxlbWVudCBhdHRyaWJ1dGUgKGUuZy4gYFthdHRyLm5hbWVdPVwiZXhwcmVzc2lvblwiYCkuXG4gIEF0dHJpYnV0ZSxcbiAgLy8gQSBiaW5kaW5nIHRvIGEgQ1NTIGNsYXNzIChlLmcuIGBbY2xhc3MubmFtZV09XCJjb25kaXRpb25cImApLlxuICBDbGFzcyxcbiAgLy8gQSBiaW5kaW5nIHRvIGEgc3R5bGUgcnVsZSAoZS5nLiBgW3N0eWxlLnJ1bGVdPVwiZXhwcmVzc2lvblwiYCkuXG4gIFN0eWxlLFxuICAvLyBBIGJpbmRpbmcgdG8gYW4gYW5pbWF0aW9uIHJlZmVyZW5jZSAoZS5nLiBgW2FuaW1hdGUua2V5XT1cImV4cHJlc3Npb25cImApLlxuICBBbmltYXRpb24sXG59XG5cbmV4cG9ydCBjbGFzcyBCb3VuZEVsZW1lbnRQcm9wZXJ0eSB7XG4gIGNvbnN0cnVjdG9yKFxuICAgICAgcHVibGljIG5hbWU6IHN0cmluZywgcHVibGljIHR5cGU6IEJpbmRpbmdUeXBlLCBwdWJsaWMgc2VjdXJpdHlDb250ZXh0OiBTZWN1cml0eUNvbnRleHQsXG4gICAgICBwdWJsaWMgdmFsdWU6IEFTVFdpdGhTb3VyY2UsIHB1YmxpYyB1bml0OiBzdHJpbmd8bnVsbCwgcHVibGljIHNvdXJjZVNwYW46IFBhcnNlU291cmNlU3BhbixcbiAgICAgIHJlYWRvbmx5IGtleVNwYW46IFBhcnNlU291cmNlU3Bhbnx1bmRlZmluZWQsIHB1YmxpYyB2YWx1ZVNwYW46IFBhcnNlU291cmNlU3Bhbnx1bmRlZmluZWQpIHt9XG59XG4iXX0=