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
        define("@angular/compiler/src/i18n/i18n_parser", ["require", "exports", "@angular/compiler/src/expression_parser/lexer", "@angular/compiler/src/expression_parser/parser", "@angular/compiler/src/ml_parser/ast", "@angular/compiler/src/ml_parser/html_tags", "@angular/compiler/src/parse_util", "@angular/compiler/src/i18n/i18n_ast", "@angular/compiler/src/i18n/serializers/placeholder"], factory);
    }
})(function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.createI18nMessageFactory = void 0;
    var lexer_1 = require("@angular/compiler/src/expression_parser/lexer");
    var parser_1 = require("@angular/compiler/src/expression_parser/parser");
    var html = require("@angular/compiler/src/ml_parser/ast");
    var html_tags_1 = require("@angular/compiler/src/ml_parser/html_tags");
    var parse_util_1 = require("@angular/compiler/src/parse_util");
    var i18n = require("@angular/compiler/src/i18n/i18n_ast");
    var placeholder_1 = require("@angular/compiler/src/i18n/serializers/placeholder");
    var _expParser = new parser_1.Parser(new lexer_1.Lexer());
    /**
     * Returns a function converting html nodes to an i18n Message given an interpolationConfig
     */
    function createI18nMessageFactory(interpolationConfig) {
        var visitor = new _I18nVisitor(_expParser, interpolationConfig);
        return function (nodes, meaning, description, customId, visitNodeFn) {
            return visitor.toI18nMessage(nodes, meaning, description, customId, visitNodeFn);
        };
    }
    exports.createI18nMessageFactory = createI18nMessageFactory;
    function noopVisitNodeFn(_html, i18n) {
        return i18n;
    }
    var _I18nVisitor = /** @class */ (function () {
        function _I18nVisitor(_expressionParser, _interpolationConfig) {
            this._expressionParser = _expressionParser;
            this._interpolationConfig = _interpolationConfig;
        }
        _I18nVisitor.prototype.toI18nMessage = function (nodes, meaning, description, customId, visitNodeFn) {
            if (meaning === void 0) { meaning = ''; }
            if (description === void 0) { description = ''; }
            if (customId === void 0) { customId = ''; }
            var context = {
                isIcu: nodes.length == 1 && nodes[0] instanceof html.Expansion,
                icuDepth: 0,
                placeholderRegistry: new placeholder_1.PlaceholderRegistry(),
                placeholderToContent: {},
                placeholderToMessage: {},
                visitNodeFn: visitNodeFn || noopVisitNodeFn,
            };
            var i18nodes = html.visitAll(this, nodes, context);
            return new i18n.Message(i18nodes, context.placeholderToContent, context.placeholderToMessage, meaning, description, customId);
        };
        _I18nVisitor.prototype.visitElement = function (el, context) {
            var _a;
            var children = html.visitAll(this, el.children, context);
            var attrs = {};
            el.attrs.forEach(function (attr) {
                // Do not visit the attributes, translatable ones are top-level ASTs
                attrs[attr.name] = attr.value;
            });
            var isVoid = html_tags_1.getHtmlTagDefinition(el.name).isVoid;
            var startPhName = context.placeholderRegistry.getStartTagPlaceholderName(el.name, attrs, isVoid);
            context.placeholderToContent[startPhName] = {
                text: el.startSourceSpan.toString(),
                sourceSpan: el.startSourceSpan,
            };
            var closePhName = '';
            if (!isVoid) {
                closePhName = context.placeholderRegistry.getCloseTagPlaceholderName(el.name);
                context.placeholderToContent[closePhName] = {
                    text: "</" + el.name + ">",
                    sourceSpan: (_a = el.endSourceSpan) !== null && _a !== void 0 ? _a : el.sourceSpan,
                };
            }
            var node = new i18n.TagPlaceholder(el.name, attrs, startPhName, closePhName, children, isVoid, el.sourceSpan, el.startSourceSpan, el.endSourceSpan);
            return context.visitNodeFn(el, node);
        };
        _I18nVisitor.prototype.visitAttribute = function (attribute, context) {
            var node = this._visitTextWithInterpolation(attribute.value, attribute.valueSpan || attribute.sourceSpan, context, attribute.i18n);
            return context.visitNodeFn(attribute, node);
        };
        _I18nVisitor.prototype.visitText = function (text, context) {
            var node = this._visitTextWithInterpolation(text.value, text.sourceSpan, context, text.i18n);
            return context.visitNodeFn(text, node);
        };
        _I18nVisitor.prototype.visitComment = function (comment, context) {
            return null;
        };
        _I18nVisitor.prototype.visitExpansion = function (icu, context) {
            var _this = this;
            context.icuDepth++;
            var i18nIcuCases = {};
            var i18nIcu = new i18n.Icu(icu.switchValue, icu.type, i18nIcuCases, icu.sourceSpan);
            icu.cases.forEach(function (caze) {
                i18nIcuCases[caze.value] = new i18n.Container(caze.expression.map(function (node) { return node.visit(_this, context); }), caze.expSourceSpan);
            });
            context.icuDepth--;
            if (context.isIcu || context.icuDepth > 0) {
                // Returns an ICU node when:
                // - the message (vs a part of the message) is an ICU message, or
                // - the ICU message is nested.
                var expPh = context.placeholderRegistry.getUniquePlaceholder("VAR_" + icu.type);
                i18nIcu.expressionPlaceholder = expPh;
                context.placeholderToContent[expPh] = {
                    text: icu.switchValue,
                    sourceSpan: icu.switchValueSourceSpan,
                };
                return context.visitNodeFn(icu, i18nIcu);
            }
            // Else returns a placeholder
            // ICU placeholders should not be replaced with their original content but with the their
            // translations.
            // TODO(vicb): add a html.Node -> i18n.Message cache to avoid having to re-create the msg
            var phName = context.placeholderRegistry.getPlaceholderName('ICU', icu.sourceSpan.toString());
            context.placeholderToMessage[phName] = this.toI18nMessage([icu], '', '', '', undefined);
            var node = new i18n.IcuPlaceholder(i18nIcu, phName, icu.sourceSpan);
            return context.visitNodeFn(icu, node);
        };
        _I18nVisitor.prototype.visitExpansionCase = function (_icuCase, _context) {
            throw new Error('Unreachable code');
        };
        /**
         * Split the, potentially interpolated, text up into text and placeholder pieces.
         *
         * @param text The potentially interpolated string to be split.
         * @param sourceSpan The span of the whole of the `text` string.
         * @param context The current context of the visitor, used to compute and store placeholders.
         * @param previousI18n Any i18n metadata associated with this `text` from a previous pass.
         */
        _I18nVisitor.prototype._visitTextWithInterpolation = function (text, sourceSpan, context, previousI18n) {
            var _a = this._expressionParser.splitInterpolation(text, sourceSpan.start.toString(), this._interpolationConfig), strings = _a.strings, expressions = _a.expressions;
            // No expressions, return a single text.
            if (expressions.length === 0) {
                return new i18n.Text(text, sourceSpan);
            }
            // Return a sequence of `Text` and `Placeholder` nodes grouped in a `Container`.
            var nodes = [];
            for (var i = 0; i < strings.length - 1; i++) {
                this._addText(nodes, strings[i], sourceSpan);
                this._addPlaceholder(nodes, context, expressions[i], sourceSpan);
            }
            // The last index contains no expression
            this._addText(nodes, strings[strings.length - 1], sourceSpan);
            // Whitespace removal may have invalidated the interpolation source-spans.
            reusePreviousSourceSpans(nodes, previousI18n);
            return new i18n.Container(nodes, sourceSpan);
        };
        /**
         * Create a new `Text` node from the `textPiece` and add it to the `nodes` collection.
         *
         * @param nodes The nodes to which the created `Text` node should be added.
         * @param textPiece The text and relative span information for this `Text` node.
         * @param interpolationSpan The span of the whole interpolated text.
         */
        _I18nVisitor.prototype._addText = function (nodes, textPiece, interpolationSpan) {
            if (textPiece.text.length > 0) {
                // No need to add empty strings
                var stringSpan = getOffsetSourceSpan(interpolationSpan, textPiece);
                nodes.push(new i18n.Text(textPiece.text, stringSpan));
            }
        };
        /**
         * Create a new `Placeholder` node from the `expression` and add it to the `nodes` collection.
         *
         * @param nodes The nodes to which the created `Text` node should be added.
         * @param context The current context of the visitor, used to compute and store placeholders.
         * @param expression The expression text and relative span information for this `Placeholder`
         *     node.
         * @param interpolationSpan The span of the whole interpolated text.
         */
        _I18nVisitor.prototype._addPlaceholder = function (nodes, context, expression, interpolationSpan) {
            var sourceSpan = getOffsetSourceSpan(interpolationSpan, expression);
            var baseName = extractPlaceholderName(expression.text) || 'INTERPOLATION';
            var phName = context.placeholderRegistry.getPlaceholderName(baseName, expression.text);
            var text = this._interpolationConfig.start + expression.text + this._interpolationConfig.end;
            context.placeholderToContent[phName] = { text: text, sourceSpan: sourceSpan };
            nodes.push(new i18n.Placeholder(expression.text, phName, sourceSpan));
        };
        return _I18nVisitor;
    }());
    /**
     * Re-use the source-spans from `previousI18n` metadata for the `nodes`.
     *
     * Whitespace removal can invalidate the source-spans of interpolation nodes, so we
     * reuse the source-span stored from a previous pass before the whitespace was removed.
     *
     * @param nodes The `Text` and `Placeholder` nodes to be processed.
     * @param previousI18n Any i18n metadata for these `nodes` stored from a previous pass.
     */
    function reusePreviousSourceSpans(nodes, previousI18n) {
        if (previousI18n instanceof i18n.Message) {
            // The `previousI18n` is an i18n `Message`, so we are processing an `Attribute` with i18n
            // metadata. The `Message` should consist only of a single `Container` that contains the
            // parts (`Text` and `Placeholder`) to process.
            assertSingleContainerMessage(previousI18n);
            previousI18n = previousI18n.nodes[0];
        }
        if (previousI18n instanceof i18n.Container) {
            // The `previousI18n` is a `Container`, which means that this is a second i18n extraction pass
            // after whitespace has been removed from the AST ndoes.
            assertEquivalentNodes(previousI18n.children, nodes);
            // Reuse the source-spans from the first pass.
            for (var i = 0; i < nodes.length; i++) {
                nodes[i].sourceSpan = previousI18n.children[i].sourceSpan;
            }
        }
    }
    /**
     * Asserts that the `message` contains exactly one `Container` node.
     */
    function assertSingleContainerMessage(message) {
        var nodes = message.nodes;
        if (nodes.length !== 1 || !(nodes[0] instanceof i18n.Container)) {
            throw new Error('Unexpected previous i18n message - expected it to consist of only a single `Container` node.');
        }
    }
    /**
     * Asserts that the `previousNodes` and `node` collections have the same number of elements and
     * corresponding elements have the same node type.
     */
    function assertEquivalentNodes(previousNodes, nodes) {
        if (previousNodes.length !== nodes.length) {
            throw new Error('The number of i18n message children changed between first and second pass.');
        }
        if (previousNodes.some(function (node, i) { return nodes[i].constructor !== node.constructor; })) {
            throw new Error('The types of the i18n message children changed between first and second pass.');
        }
    }
    /**
     * Create a new `ParseSourceSpan` from the `sourceSpan`, offset by the `start` and `end` values.
     */
    function getOffsetSourceSpan(sourceSpan, _a) {
        var start = _a.start, end = _a.end;
        return new parse_util_1.ParseSourceSpan(sourceSpan.fullStart.moveBy(start), sourceSpan.fullStart.moveBy(end));
    }
    var _CUSTOM_PH_EXP = /\/\/[\s\S]*i18n[\s\S]*\([\s\S]*ph[\s\S]*=[\s\S]*("|')([\s\S]*?)\1[\s\S]*\)/g;
    function extractPlaceholderName(input) {
        return input.split(_CUSTOM_PH_EXP)[2];
    }
});
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiaTE4bl9wYXJzZXIuanMiLCJzb3VyY2VSb290IjoiIiwic291cmNlcyI6WyIuLi8uLi8uLi8uLi8uLi8uLi8uLi9wYWNrYWdlcy9jb21waWxlci9zcmMvaTE4bi9pMThuX3BhcnNlci50cyJdLCJuYW1lcyI6W10sIm1hcHBpbmdzIjoiQUFBQTs7Ozs7O0dBTUc7Ozs7Ozs7Ozs7Ozs7SUFFSCx1RUFBb0U7SUFDcEUseUVBQTJGO0lBQzNGLDBEQUF5QztJQUN6Qyx1RUFBNEQ7SUFFNUQsK0RBQThDO0lBRTlDLDBEQUFtQztJQUNuQyxrRkFBOEQ7SUFFOUQsSUFBTSxVQUFVLEdBQUcsSUFBSSxlQUFnQixDQUFDLElBQUksYUFBZSxFQUFFLENBQUMsQ0FBQztJQVMvRDs7T0FFRztJQUNILFNBQWdCLHdCQUF3QixDQUFDLG1CQUF3QztRQUUvRSxJQUFNLE9BQU8sR0FBRyxJQUFJLFlBQVksQ0FBQyxVQUFVLEVBQUUsbUJBQW1CLENBQUMsQ0FBQztRQUNsRSxPQUFPLFVBQUMsS0FBSyxFQUFFLE9BQU8sRUFBRSxXQUFXLEVBQUUsUUFBUSxFQUFFLFdBQVc7WUFDL0MsT0FBQSxPQUFPLENBQUMsYUFBYSxDQUFDLEtBQUssRUFBRSxPQUFPLEVBQUUsV0FBVyxFQUFFLFFBQVEsRUFBRSxXQUFXLENBQUM7UUFBekUsQ0FBeUUsQ0FBQztJQUN2RixDQUFDO0lBTEQsNERBS0M7SUFXRCxTQUFTLGVBQWUsQ0FBQyxLQUFnQixFQUFFLElBQWU7UUFDeEQsT0FBTyxJQUFJLENBQUM7SUFDZCxDQUFDO0lBRUQ7UUFDRSxzQkFDWSxpQkFBbUMsRUFDbkMsb0JBQXlDO1lBRHpDLHNCQUFpQixHQUFqQixpQkFBaUIsQ0FBa0I7WUFDbkMseUJBQW9CLEdBQXBCLG9CQUFvQixDQUFxQjtRQUFHLENBQUM7UUFFbEQsb0NBQWEsR0FBcEIsVUFDSSxLQUFrQixFQUFFLE9BQVksRUFBRSxXQUFnQixFQUFFLFFBQWEsRUFDakUsV0FBa0M7WUFEZCx3QkFBQSxFQUFBLFlBQVk7WUFBRSw0QkFBQSxF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QUFxQixDQUFDLGFBQTBCLEVBQUUsS0FBa0I7UUFDM0UsSUFBSSxhQUFhLENBQUMsTUFBTSxLQUFLLEtBQUssQ0FBQyxNQUFNLEVBQUU7WUFDekMsTUFBTSxJQUFJLEtBQUssQ0FBQyw0RUFBNEUsQ0FBQyxDQUFDO1NBQy9GO1FBQ0QsSUFBSSxhQUFhLENBQUMsSUFBSSxDQUFDLFVBQUMsSUFBSSxFQUFFLENBQUMsSUFBSyxPQUFBLEtBQUssQ0FBQyxDQUFDLENBQUMsQ0FBQyxXQUFXLEtBQUssSUFBSSxDQUFDLFdBQVcsRUFBekMsQ0FBeUMsQ0FBQyxFQUFFO1lBQzlFLE1BQU0sSUFBSSxLQUFLLENBQ1gsK0VBQStFLENBQUMsQ0FBQztTQUN0RjtJQUNILENBQUM7SUFFRDs7T0FFRztJQUNILFNBQVMsbUJBQW1CLENBQ3hCLFVBQTJCLEVBQUUsRUFBZ0M7WUFBL0IsS0FBSyxXQUFBLEVBQUUsR0FBRyxTQUFBO1FBQzFDLE9BQU8sSUFBSSw0QkFBZSxDQUFDLFVBQVUsQ0FBQyxTQUFTLENBQUMsTUFBTSxDQUFDLEtBQUssQ0FBQyxFQUFFLFVBQVUsQ0FBQyxTQUFTLENBQUMsTUFBTSxDQUFDLEdBQUcsQ0FBQyxDQUFDLENBQUM7SUFDbkcsQ0FBQztJQUVELElBQU0sY0FBYyxHQUNoQiw2RUFBNkUsQ0FBQztJQUVsRixTQUFTLHNCQUFzQixDQUFDLEtBQWE7UUFDM0MsT0FBTyxLQUFLLENBQUMsS0FBSyxDQUFDLGNBQWMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDO0lBQ3hDLENBQUMiLCJzb3VyY2VzQ29udGVudCI6WyIvKipcbiAqIEBsaWNlbnNlXG4gKiBDb3B5cmlnaHQgR29vZ2xlIExMQyBBbGwgUmlnaHRzIFJlc2VydmVkLlxuICpcbiAqIFVzZSBvZiB0aGlzIHNvdXJjZSBjb2RlIGlzIGdvdmVybmVkIGJ5IGFuIE1JVC1zdHlsZSBsaWNlbnNlIHRoYXQgY2FuIGJlXG4gKiBmb3VuZCBpbiB0aGUgTElDRU5TRSBmaWxlIGF0IGh0dHBzOi8vYW5ndWxhci5pby9saWNlbnNlXG4gKi9cblxuaW1wb3J0IHtMZXhlciBhcyBFeHByZXNzaW9uTGV4ZXJ9IGZyb20gJy4uL2V4cHJlc3Npb25fcGFyc2VyL2xleGVyJztcbmltcG9ydCB7SW50ZXJwb2xhdGlvblBpZWNlLCBQYXJzZXIgYXMgRXhwcmVzc2lvblBhcnNlcn0gZnJvbSAnLi4vZXhwcmVzc2lvbl9wYXJzZXIvcGFyc2VyJztcbmltcG9ydCAqIGFzIGh0bWwgZnJvbSAnLi4vbWxfcGFyc2VyL2FzdCc7XG5pbXBvcnQge2dldEh0bWxUYWdEZWZpbml0aW9ufSBmcm9tICcuLi9tbF9wYXJzZXIvaHRtbF90YWdzJztcbmltcG9ydCB7SW50ZXJwb2xhdGlvbkNvbmZpZ30gZnJvbSAnLi4vbWxfcGFyc2VyL2ludGVycG9sYXRpb25fY29uZmlnJztcbmltcG9ydCB7UGFyc2VTb3VyY2VTcGFufSBmcm9tICcuLi9wYXJzZV91dGlsJztcblxuaW1wb3J0ICogYXMgaTE4biBmcm9tICcuL2kxOG5fYXN0JztcbmltcG9ydCB7UGxhY2Vob2xkZXJSZWdpc3RyeX0gZnJvbSAnLi9zZXJpYWxpemVycy9wbGFjZWhvbGRlcic7XG5cbmNvbnN0IF9leHBQYXJzZXIgPSBuZXcgRXhwcmVzc2lvblBhcnNlcihuZXcgRXhwcmVzc2lvbkxleGVyKCkpO1xuXG5leHBvcnQgdHlwZSBWaXNpdE5vZGVGbiA9IChodG1sOiBodG1sLk5vZGUsIGkxOG46IGkxOG4uTm9kZSkgPT4gaTE4bi5Ob2RlO1xuXG5leHBvcnQgaW50ZXJmYWNlIEkxOG5NZXNzYWdlRmFjdG9yeSB7XG4gIChub2RlczogaHRtbC5Ob2RlW10sIG1lYW5pbmc6IHN0cmluZ3x1bmRlZmluZWQsIGRlc2NyaXB0aW9uOiBzdHJpbmd8dW5kZWZpbmVkLFxuICAgY3VzdG9tSWQ6IHN0cmluZ3x1bmRlZmluZWQsIHZpc2l0Tm9kZUZuPzogVmlzaXROb2RlRm4pOiBpMThuLk1lc3NhZ2U7XG59XG5cbi8qKlxuICogUmV0dXJucyBhIGZ1bmN0aW9uIGNvbnZlcnRpbmcgaHRtbCBub2RlcyB0byBhbiBpMThuIE1lc3NhZ2UgZ2l2ZW4gYW4gaW50ZXJwb2xhdGlvbkNvbmZpZ1xuICovXG5leHBvcnQgZnVuY3Rpb24gY3JlYXRlSTE4bk1lc3NhZ2VGYWN0b3J5KGludGVycG9sYXRpb25Db25maWc6IEludGVycG9sYXRpb25Db25maWcpOlxuICAgIEkxOG5NZXNzYWdlRmFjdG9yeSB7XG4gIGNvbnN0IHZpc2l0b3IgPSBuZXcgX0kxOG5WaXNpdG9yKF9leHBQYXJzZXIsIGludGVycG9sYXRpb25Db25maWcpO1xuICByZXR1cm4gKG5vZGVzLCBtZWFuaW5nLCBkZXNjcmlwdGlvbiwgY3VzdG9tSWQsIHZpc2l0Tm9kZUZuKSA9PlxuICAgICAgICAgICAgIHZpc2l0b3IudG9JMThuTWVzc2FnZShub2RlcywgbWVhbmluZywgZGVzY3JpcHRpb24sIGN1c3RvbUlkLCB2aXNpdE5vZGVGbik7XG59XG5cbmludGVyZmFjZSBJMThuTWVzc2FnZVZpc2l0b3JDb250ZXh0IHtcbiAgaXNJY3U6IGJvb2xlYW47XG4gIGljdURlcHRoOiBudW1iZXI7XG4gIHBsYWNlaG9sZGVyUmVnaXN0cnk6IFBsYWNlaG9sZGVyUmVnaXN0cnk7XG4gIHBsYWNlaG9sZGVyVG9Db250ZW50OiB7W3BoTmFtZTogc3RyaW5nXTogaTE4bi5NZXNzYWdlUGxhY2Vob2xkZXJ9O1xuICBwbGFjZWhvbGRlclRvTWVzc2FnZToge1twaE5hbWU6IHN0cmluZ106IGkxOG4uTWVzc2FnZX07XG4gIHZpc2l0Tm9kZUZuOiBWaXNpdE5vZGVGbjtcbn1cblxuZnVuY3Rpb24gbm9vcFZpc2l0Tm9kZUZuKF9odG1sOiBodG1sLk5vZGUsIGkxOG46IGkxOG4uTm9kZSk6IGkxOG4uTm9kZSB7XG4gIHJldHVybiBpMThuO1xufVxuXG5jbGFzcyBfSTE4blZpc2l0b3IgaW1wbGVtZW50cyBodG1sLlZpc2l0b3Ige1xuICBjb25zdHJ1Y3RvcihcbiAgICAgIHByaXZhdGUgX2V4cHJlc3Npb25QYXJzZXI6IEV4cHJlc3Npb25QYXJzZXIsXG4gICAgICBwcml2YXRlIF9pbnRlcnBvbGF0aW9uQ29uZmlnOiBJbnRlcnBvbGF0aW9uQ29uZmlnKSB7fVxuXG4gIHB1YmxpYyB0b0kxOG5NZXNzYWdlKFxuICAgICAgbm9kZXM6IGh0bWwuTm9kZVtdLCBtZWFuaW5nID0gJycsIGRlc2NyaXB0aW9uID0gJycsIGN1c3RvbUlkID0gJycsXG4gICAgICB2aXNpdE5vZGVGbjogVmlzaXROb2RlRm58dW5kZWZpbmVkKTogaTE4bi5NZXNzYWdlIHtcbiAgICBjb25zdCBjb250ZXh0OiBJMThuTWVzc2FnZVZpc2l0b3JDb250ZXh0ID0ge1xuICAgICAgaXNJY3U6IG5vZGVzLmxlbmd0aCA9PSAxICYmIG5vZGVzWzBdIGluc3RhbmNlb2YgaHRtbC5FeHBhbnNpb24sXG4gICAgICBpY3VEZXB0aDogMCxcbiAgICAgIHBsYWNlaG9sZGVyUmVnaXN0cnk6IG5ldyBQbGFjZWhvbGRlclJlZ2lzdHJ5KCksXG4gICAgICBwbGFjZWhvbGRlclRvQ29udGVudDoge30sXG4gICAgICBwbGFjZWhvbGRlclRvTWVzc2FnZToge30sXG4gICAgICB2aXNpdE5vZGVGbjogdmlzaXROb2RlRm4gfHwgbm9vcFZpc2l0Tm9kZUZuLFxuICAgIH07XG5cbiAgICBjb25zdCBpMThub2RlczogaTE4bi5Ob2RlW10gPSBodG1sLnZpc2l0QWxsKHRoaXMsIG5vZGVzLCBjb250ZXh0KTtcblxuICAgIHJldHVybiBuZXcgaTE4bi5NZXNzYWdlKFxuICAgICAgICBpMThub2RlcywgY29udGV4dC5wbGFjZWhvbGRlclRvQ29udGVudCwgY29udGV4dC5wbGFjZWhvbGRlclRvTWVzc2FnZSwgbWVhbmluZywgZGVzY3JpcHRpb24sXG4gICAgICAgIGN1c3RvbUlkKTtcbiAgfVxuXG4gIHZpc2l0RWxlbWVudChlbDogaHRtbC5FbGVtZW50LCBjb250ZXh0OiBJMThuTWVzc2FnZVZpc2l0b3JDb250ZXh0KTogaTE4bi5Ob2RlIHtcbiAgICBjb25zdCBjaGlsZHJlbiA9IGh0bWwudmlzaXRBbGwodGhpcywgZWwuY2hpbGRyZW4sIGNvbnRleHQpO1xuICAgIGNvbnN0IGF0dHJzOiB7W2s6IHN0cmluZ106IHN0cmluZ30gPSB7fTtcbiAgICBlbC5hdHRycy5mb3JFYWNoKGF0dHIgPT4ge1xuICAgICAgLy8gRG8gbm90IHZpc2l0IHRoZSBhdHRyaWJ1dGVzLCB0cmFuc2xhdGFibGUgb25lcyBhcmUgdG9wLWxldmVsIEFTVHNcbiAgICAgIGF0dHJzW2F0dHIubmFtZV0gPSBhdHRyLnZhbHVlO1xuICAgIH0pO1xuXG4gICAgY29uc3QgaXNWb2lkOiBib29sZWFuID0gZ2V0SHRtbFRhZ0RlZmluaXRpb24oZWwubmFtZSkuaXNWb2lkO1xuICAgIGNvbnN0IHN0YXJ0UGhOYW1lID1cbiAgICAgICAgY29udGV4dC5wbGFjZWhvbGRlclJlZ2lzdHJ5LmdldFN0YXJ0VGFnUGxhY2Vob2xkZXJOYW1lKGVsLm5hbWUsIGF0dHJzLCBpc1ZvaWQpO1xuICAgIGNvbnRleHQucGxhY2Vob2xkZXJUb0NvbnRlbnRbc3RhcnRQaE5hbWVdID0ge1xuICAgICAgdGV4dDogZWwuc3RhcnRTb3VyY2VTcGFuLnRvU3RyaW5nKCksXG4gICAgICBzb3VyY2VTcGFuOiBlbC5zdGFydFNvdXJjZVNwYW4sXG4gICAgfTtcblxuICAgIGxldCBjbG9zZVBoTmFtZSA9ICcnO1xuXG4gICAgaWYgKCFpc1ZvaWQpIHtcbiAgICAgIGNsb3NlUGhOYW1lID0gY29udGV4dC5wbGFjZWhvbGRlclJlZ2lzdHJ5LmdldENsb3NlVGFnUGxhY2Vob2xkZXJOYW1lKGVsLm5hbWUpO1xuICAgICAgY29udGV4dC5wbGFjZWhvbGRlclRvQ29udGVudFtjbG9zZVBoTmFtZV0gPSB7XG4gICAgICAgIHRleHQ6IGA8LyR7ZWwubmFtZX0+YCxcbiAgICAgICAgc291cmNlU3BhbjogZWwuZW5kU291cmNlU3BhbiA/PyBlbC5zb3VyY2VTcGFuLFxuICAgICAgfTtcbiAgICB9XG5cbiAgICBjb25zdCBub2RlID0gbmV3IGkxOG4uVGFnUGxhY2Vob2xkZXIoXG4gICAgICAgIGVsLm5hbWUsIGF0dHJzLCBzdGFydFBoTmFtZSwgY2xvc2VQaE5hbWUsIGNoaWxkcmVuLCBpc1ZvaWQsIGVsLnNvdXJjZVNwYW4sXG4gICAgICAgIGVsLnN0YXJ0U291cmNlU3BhbiwgZWwuZW5kU291cmNlU3Bhbik7XG4gICAgcmV0dXJuIGNvbnRleHQudmlzaXROb2RlRm4oZWwsIG5vZGUpO1xuICB9XG5cbiAgdmlzaXRBdHRyaWJ1dGUoYXR0cmlidXRlOiBodG1sLkF0dHJpYnV0ZSwgY29udGV4dDogSTE4bk1lc3NhZ2VWaXNpdG9yQ29udGV4dCk6IGkxOG4uTm9kZSB7XG4gICAgY29uc3Qgbm9kZSA9IHRoaXMuX3Zpc2l0VGV4dFdpdGhJbnRlcnBvbGF0aW9uKFxuICAgICAgICBhdHRyaWJ1dGUudmFsdWUsIGF0dHJpYnV0ZS52YWx1ZVNwYW4gfHwgYXR0cmlidXRlLnNvdXJjZVNwYW4sIGNvbnRleHQsIGF0dHJpYnV0ZS5pMThuKTtcbiAgICByZXR1cm4gY29udGV4dC52aXNpdE5vZGVGbihhdHRyaWJ1dGUsIG5vZGUpO1xuICB9XG5cbiAgdmlzaXRUZXh0KHRleHQ6IGh0bWwuVGV4dCwgY29udGV4dDogSTE4bk1lc3NhZ2VWaXNpdG9yQ29udGV4dCk6IGkxOG4uTm9kZSB7XG4gICAgY29uc3Qgbm9kZSA9IHRoaXMuX3Zpc2l0VGV4dFdpdGhJbnRlcnBvbGF0aW9uKHRleHQudmFsdWUsIHRleHQuc291cmNlU3BhbiwgY29udGV4dCwgdGV4dC5pMThuKTtcbiAgICByZXR1cm4gY29udGV4dC52aXNpdE5vZGVGbih0ZXh0LCBub2RlKTtcbiAgfVxuXG4gIHZpc2l0Q29tbWVudChjb21tZW50OiBodG1sLkNvbW1lbnQsIGNvbnRleHQ6IEkxOG5NZXNzYWdlVmlzaXRvckNvbnRleHQpOiBpMThuLk5vZGV8bnVsbCB7XG4gICAgcmV0dXJuIG51bGw7XG4gIH1cblxuICB2aXNpdEV4cGFuc2lvbihpY3U6IGh0bWwuRXhwYW5zaW9uLCBjb250ZXh0OiBJMThuTWVzc2FnZVZpc2l0b3JDb250ZXh0KTogaTE4bi5Ob2RlIHtcbiAgICBjb250ZXh0LmljdURlcHRoKys7XG4gICAgY29uc3QgaTE4bkljdUNhc2VzOiB7W2s6IHN0cmluZ106IGkxOG4uTm9kZX0gPSB7fTtcbiAgICBjb25zdCBpMThuSWN1ID0gbmV3IGkxOG4uSWN1KGljdS5zd2l0Y2hWYWx1ZSwgaWN1LnR5cGUsIGkxOG5JY3VDYXNlcywgaWN1LnNvdXJjZVNwYW4pO1xuICAgIGljdS5jYXNlcy5mb3JFYWNoKChjYXplKTogdm9pZCA9PiB7XG4gICAgICBpMThuSWN1Q2FzZXNbY2F6ZS52YWx1ZV0gPSBuZXcgaTE4bi5Db250YWluZXIoXG4gICAgICAgICAgY2F6ZS5leHByZXNzaW9uLm1hcCgobm9kZSkgPT4gbm9kZS52aXNpdCh0aGlzLCBjb250ZXh0KSksIGNhemUuZXhwU291cmNlU3Bhbik7XG4gICAgfSk7XG4gICAgY29udGV4dC5pY3VEZXB0aC0tO1xuXG4gICAgaWYgKGNvbnRleHQuaXNJY3UgfHwgY29udGV4dC5pY3VEZXB0aCA+IDApIHtcbiAgICAgIC8vIFJldHVybnMgYW4gSUNVIG5vZGUgd2hlbjpcbiAgICAgIC8vIC0gdGhlIG1lc3NhZ2UgKHZzIGEgcGFydCBvZiB0aGUgbWVzc2FnZSkgaXMgYW4gSUNVIG1lc3NhZ2UsIG9yXG4gICAgICAvLyAtIHRoZSBJQ1UgbWVzc2FnZSBpcyBuZXN0ZWQuXG4gICAgICBjb25zdCBleHBQaCA9IGNvbnRleHQucGxhY2Vob2xkZXJSZWdpc3RyeS5nZXRVbmlxdWVQbGFjZWhvbGRlcihgVkFSXyR7aWN1LnR5cGV9YCk7XG4gICAgICBpMThuSWN1LmV4cHJlc3Npb25QbGFjZWhvbGRlciA9IGV4cFBoO1xuICAgICAgY29udGV4dC5wbGFjZWhvbGRlclRvQ29udGVudFtleHBQaF0gPSB7XG4gICAgICAgIHRleHQ6IGljdS5zd2l0Y2hWYWx1ZSxcbiAgICAgICAgc291cmNlU3BhbjogaWN1LnN3aXRjaFZhbHVlU291cmNlU3BhbixcbiAgICAgIH07XG4gICAgICByZXR1cm4gY29udGV4dC52aXNpdE5vZGVGbihpY3UsIGkxOG5JY3UpO1xuICAgIH1cblxuICAgIC8vIEVsc2UgcmV0dXJucyBhIHBsYWNlaG9sZGVyXG4gICAgLy8gSUNVIHBsYWNlaG9sZGVycyBzaG91bGQgbm90IGJlIHJlcGxhY2VkIHdpdGggdGhlaXIgb3JpZ2luYWwgY29udGVudCBidXQgd2l0aCB0aGUgdGhlaXJcbiAgICAvLyB0cmFuc2xhdGlvbnMuXG4gICAgLy8gVE9ETyh2aWNiKTogYWRkIGEgaHRtbC5Ob2RlIC0+IGkxOG4uTWVzc2FnZSBjYWNoZSB0byBhdm9pZCBoYXZpbmcgdG8gcmUtY3JlYXRlIHRoZSBtc2dcbiAgICBjb25zdCBwaE5hbWUgPSBjb250ZXh0LnBsYWNlaG9sZGVyUmVnaXN0cnkuZ2V0UGxhY2Vob2xkZXJOYW1lKCdJQ1UnLCBpY3Uuc291cmNlU3Bhbi50b1N0cmluZygpKTtcbiAgICBjb250ZXh0LnBsYWNlaG9sZGVyVG9NZXNzYWdlW3BoTmFtZV0gPSB0aGlzLnRvSTE4bk1lc3NhZ2UoW2ljdV0sICcnLCAnJywgJycsIHVuZGVmaW5lZCk7XG4gICAgY29uc3Qgbm9kZSA9IG5ldyBpMThuLkljdVBsYWNlaG9sZGVyKGkxOG5JY3UsIHBoTmFtZSwgaWN1LnNvdXJjZVNwYW4pO1xuICAgIHJldHVybiBjb250ZXh0LnZpc2l0Tm9kZUZuKGljdSwgbm9kZSk7XG4gIH1cblxuICB2aXNpdEV4cGFuc2lvbkNhc2UoX2ljdUNhc2U6IGh0bWwuRXhwYW5zaW9uQ2FzZSwgX2NvbnRleHQ6IEkxOG5NZXNzYWdlVmlzaXRvckNvbnRleHQpOiBpMThuLk5vZGUge1xuICAgIHRocm93IG5ldyBFcnJvcignVW5yZWFjaGFibGUgY29kZScpO1xuICB9XG5cbiAgLyoqXG4gICAqIFNwbGl0IHRoZSwgcG90ZW50aWFsbHkgaW50ZXJwb2xhdGVkLCB0ZXh0IHVwIGludG8gdGV4dCBhbmQgcGxhY2Vob2xkZXIgcGllY2VzLlxuICAgKlxuICAgKiBAcGFyYW0gdGV4dCBUaGUgcG90ZW50aWFsbHkgaW50ZXJwb2xhdGVkIHN0cmluZyB0byBiZSBzcGxpdC5cbiAgICogQHBhcmFtIHNvdXJjZVNwYW4gVGhlIHNwYW4gb2YgdGhlIHdob2xlIG9mIHRoZSBgdGV4dGAgc3RyaW5nLlxuICAgKiBAcGFyYW0gY29udGV4dCBUaGUgY3VycmVudCBjb250ZXh0IG9mIHRoZSB2aXNpdG9yLCB1c2VkIHRvIGNvbXB1dGUgYW5kIHN0b3JlIHBsYWNlaG9sZGVycy5cbiAgICogQHBhcmFtIHByZXZpb3VzSTE4biBBbnkgaTE4biBtZXRhZGF0YSBhc3NvY2lhdGVkIHdpdGggdGhpcyBgdGV4dGAgZnJvbSBhIHByZXZpb3VzIHBhc3MuXG4gICAqL1xuICBwcml2YXRlIF92aXNpdFRleHRXaXRoSW50ZXJwb2xhdGlvbihcbiAgICAgIHRleHQ6IHN0cmluZywgc291cmNlU3BhbjogUGFyc2VTb3VyY2VTcGFuLCBjb250ZXh0OiBJMThuTWVzc2FnZVZpc2l0b3JDb250ZXh0LFxuICAgICAgcHJldmlvdXNJMThuOiBpMThuLkkxOG5NZXRhfHVuZGVmaW5lZCk6IGkxOG4uTm9kZSB7XG4gICAgY29uc3Qge3N0cmluZ3MsIGV4cHJlc3Npb25zfSA9IHRoaXMuX2V4cHJlc3Npb25QYXJzZXIuc3BsaXRJbnRlcnBvbGF0aW9uKFxuICAgICAgICB0ZXh0LCBzb3VyY2VTcGFuLnN0YXJ0LnRvU3RyaW5nKCksIHRoaXMuX2ludGVycG9sYXRpb25Db25maWcpO1xuXG4gICAgLy8gTm8gZXhwcmVzc2lvbnMsIHJldHVybiBhIHNpbmdsZSB0ZXh0LlxuICAgIGlmIChleHByZXNzaW9ucy5sZW5ndGggPT09IDApIHtcbiAgICAgIHJldHVybiBuZXcgaTE4bi5UZXh0KHRleHQsIHNvdXJjZVNwYW4pO1xuICAgIH1cblxuICAgIC8vIFJldHVybiBhIHNlcXVlbmNlIG9mIGBUZXh0YCBhbmQgYFBsYWNlaG9sZGVyYCBub2RlcyBncm91cGVkIGluIGEgYENvbnRhaW5lcmAuXG4gICAgY29uc3Qgbm9kZXM6IGkxOG4uTm9kZVtdID0gW107XG4gICAgZm9yIChsZXQgaSA9IDA7IGkgPCBzdHJpbmdzLmxlbmd0aCAtIDE7IGkrKykge1xuICAgICAgdGhpcy5fYWRkVGV4dChub2Rlcywgc3RyaW5nc1tpXSwgc291cmNlU3Bhbik7XG4gICAgICB0aGlzLl9hZGRQbGFjZWhvbGRlcihub2RlcywgY29udGV4dCwgZXhwcmVzc2lvbnNbaV0sIHNvdXJjZVNwYW4pO1xuICAgIH1cbiAgICAvLyBUaGUgbGFzdCBpbmRleCBjb250YWlucyBubyBleHByZXNzaW9uXG4gICAgdGhpcy5fYWRkVGV4dChub2Rlcywgc3RyaW5nc1tzdHJpbmdzLmxlbmd0aCAtIDFdLCBzb3VyY2VTcGFuKTtcblxuICAgIC8vIFdoaXRlc3BhY2UgcmVtb3ZhbCBtYXkgaGF2ZSBpbnZhbGlkYXRlZCB0aGUgaW50ZXJwb2xhdGlvbiBzb3VyY2Utc3BhbnMuXG4gICAgcmV1c2VQcmV2aW91c1NvdXJjZVNwYW5zKG5vZGVzLCBwcmV2aW91c0kxOG4pO1xuXG4gICAgcmV0dXJuIG5ldyBpMThuLkNvbnRhaW5lcihub2Rlcywgc291cmNlU3Bhbik7XG4gIH1cblxuICAvKipcbiAgICogQ3JlYXRlIGEgbmV3IGBUZXh0YCBub2RlIGZyb20gdGhlIGB0ZXh0UGllY2VgIGFuZCBhZGQgaXQgdG8gdGhlIGBub2Rlc2AgY29sbGVjdGlvbi5cbiAgICpcbiAgICogQHBhcmFtIG5vZGVzIFRoZSBub2RlcyB0byB3aGljaCB0aGUgY3JlYXRlZCBgVGV4dGAgbm9kZSBzaG91bGQgYmUgYWRkZWQuXG4gICAqIEBwYXJhbSB0ZXh0UGllY2UgVGhlIHRleHQgYW5kIHJlbGF0aXZlIHNwYW4gaW5mb3JtYXRpb24gZm9yIHRoaXMgYFRleHRgIG5vZGUuXG4gICAqIEBwYXJhbSBpbnRlcnBvbGF0aW9uU3BhbiBUaGUgc3BhbiBvZiB0aGUgd2hvbGUgaW50ZXJwb2xhdGVkIHRleHQuXG4gICAqL1xuICBwcml2YXRlIF9hZGRUZXh0KFxuICAgICAgbm9kZXM6IGkxOG4uTm9kZVtdLCB0ZXh0UGllY2U6IEludGVycG9sYXRpb25QaWVjZSwgaW50ZXJwb2xhdGlvblNwYW46IFBhcnNlU291cmNlU3Bhbik6IHZvaWQge1xuICAgIGlmICh0ZXh0UGllY2UudGV4dC5sZW5ndGggPiAwKSB7XG4gICAgICAvLyBObyBuZWVkIHRvIGFkZCBlbXB0eSBzdHJpbmdzXG4gICAgICBjb25zdCBzdHJpbmdTcGFuID0gZ2V0T2Zmc2V0U291cmNlU3BhbihpbnRlcnBvbGF0aW9uU3BhbiwgdGV4dFBpZWNlKTtcbiAgICAgIG5vZGVzLnB1c2gobmV3IGkxOG4uVGV4dCh0ZXh0UGllY2UudGV4dCwgc3RyaW5nU3BhbikpO1xuICAgIH1cbiAgfVxuXG4gIC8qKlxuICAgKiBDcmVhdGUgYSBuZXcgYFBsYWNlaG9sZGVyYCBub2RlIGZyb20gdGhlIGBleHByZXNzaW9uYCBhbmQgYWRkIGl0IHRvIHRoZSBgbm9kZXNgIGNvbGxlY3Rpb24uXG4gICAqXG4gICAqIEBwYXJhbSBub2RlcyBUaGUgbm9kZXMgdG8gd2hpY2ggdGhlIGNyZWF0ZWQgYFRleHRgIG5vZGUgc2hvdWxkIGJlIGFkZGVkLlxuICAgKiBAcGFyYW0gY29udGV4dCBUaGUgY3VycmVudCBjb250ZXh0IG9mIHRoZSB2aXNpdG9yLCB1c2VkIHRvIGNvbXB1dGUgYW5kIHN0b3JlIHBsYWNlaG9sZGVycy5cbiAgICogQHBhcmFtIGV4cHJlc3Npb24gVGhlIGV4cHJlc3Npb24gdGV4dCBhbmQgcmVsYXRpdmUgc3BhbiBpbmZvcm1hdGlvbiBmb3IgdGhpcyBgUGxhY2Vob2xkZXJgXG4gICAqICAgICBub2RlLlxuICAgKiBAcGFyYW0gaW50ZXJwb2xhdGlvblNwYW4gVGhlIHNwYW4gb2YgdGhlIHdob2xlIGludGVycG9sYXRlZCB0ZXh0LlxuICAgKi9cbiAgcHJpdmF0ZSBfYWRkUGxhY2Vob2xkZXIoXG4gICAgICBub2RlczogaTE4bi5Ob2RlW10sIGNvbnRleHQ6IEkxOG5NZXNzYWdlVmlzaXRvckNvbnRleHQsIGV4cHJlc3Npb246IEludGVycG9sYXRpb25QaWVjZSxcbiAgICAgIGludGVycG9sYXRpb25TcGFuOiBQYXJzZVNvdXJjZVNwYW4pOiB2b2lkIHtcbiAgICBjb25zdCBzb3VyY2VTcGFuID0gZ2V0T2Zmc2V0U291cmNlU3BhbihpbnRlcnBvbGF0aW9uU3BhbiwgZXhwcmVzc2lvbik7XG4gICAgY29uc3QgYmFzZU5hbWUgPSBleHRyYWN0UGxhY2Vob2xkZXJOYW1lKGV4cHJlc3Npb24udGV4dCkgfHwgJ0lOVEVSUE9MQVRJT04nO1xuICAgIGNvbnN0IHBoTmFtZSA9IGNvbnRleHQucGxhY2Vob2xkZXJSZWdpc3RyeS5nZXRQbGFjZWhvbGRlck5hbWUoYmFzZU5hbWUsIGV4cHJlc3Npb24udGV4dCk7XG4gICAgY29uc3QgdGV4dCA9IHRoaXMuX2ludGVycG9sYXRpb25Db25maWcuc3RhcnQgKyBleHByZXNzaW9uLnRleHQgKyB0aGlzLl9pbnRlcnBvbGF0aW9uQ29uZmlnLmVuZDtcbiAgICBjb250ZXh0LnBsYWNlaG9sZGVyVG9Db250ZW50W3BoTmFtZV0gPSB7dGV4dCwgc291cmNlU3Bhbn07XG4gICAgbm9kZXMucHVzaChuZXcgaTE4bi5QbGFjZWhvbGRlcihleHByZXNzaW9uLnRleHQsIHBoTmFtZSwgc291cmNlU3BhbikpO1xuICB9XG59XG5cbi8qKlxuICogUmUtdXNlIHRoZSBzb3VyY2Utc3BhbnMgZnJvbSBgcHJldmlvdXNJMThuYCBtZXRhZGF0YSBmb3IgdGhlIGBub2Rlc2AuXG4gKlxuICogV2hpdGVzcGFjZSByZW1vdmFsIGNhbiBpbnZhbGlkYXRlIHRoZSBzb3VyY2Utc3BhbnMgb2YgaW50ZXJwb2xhdGlvbiBub2Rlcywgc28gd2VcbiAqIHJldXNlIHRoZSBzb3VyY2Utc3BhbiBzdG9yZWQgZnJvbSBhIHByZXZpb3VzIHBhc3MgYmVmb3JlIHRoZSB3aGl0ZXNwYWNlIHdhcyByZW1vdmVkLlxuICpcbiAqIEBwYXJhbSBub2RlcyBUaGUgYFRleHRgIGFuZCBgUGxhY2Vob2xkZXJgIG5vZGVzIHRvIGJlIHByb2Nlc3NlZC5cbiAqIEBwYXJhbSBwcmV2aW91c0kxOG4gQW55IGkxOG4gbWV0YWRhdGEgZm9yIHRoZXNlIGBub2Rlc2Agc3RvcmVkIGZyb20gYSBwcmV2aW91cyBwYXNzLlxuICovXG5mdW5jdGlvbiByZXVzZVByZXZpb3VzU291cmNlU3BhbnMobm9kZXM6IGkxOG4uTm9kZVtdLCBwcmV2aW91c0kxOG46IGkxOG4uSTE4bk1ldGF8dW5kZWZpbmVkKTogdm9pZCB7XG4gIGlmIChwcmV2aW91c0kxOG4gaW5zdGFuY2VvZiBpMThuLk1lc3NhZ2UpIHtcbiAgICAvLyBUaGUgYHByZXZpb3VzSTE4bmAgaXMgYW4gaTE4biBgTWVzc2FnZWAsIHNvIHdlIGFyZSBwcm9jZXNzaW5nIGFuIGBBdHRyaWJ1dGVgIHdpdGggaTE4blxuICAgIC8vIG1ldGFkYXRhLiBUaGUgYE1lc3NhZ2VgIHNob3VsZCBjb25zaXN0IG9ubHkgb2YgYSBzaW5nbGUgYENvbnRhaW5lcmAgdGhhdCBjb250YWlucyB0aGVcbiAgICAvLyBwYXJ0cyAoYFRleHRgIGFuZCBgUGxhY2Vob2xkZXJgKSB0byBwcm9jZXNzLlxuICAgIGFzc2VydFNpbmdsZUNvbnRhaW5lck1lc3NhZ2UocHJldmlvdXNJMThuKTtcbiAgICBwcmV2aW91c0kxOG4gPSBwcmV2aW91c0kxOG4ubm9kZXNbMF07XG4gIH1cblxuICBpZiAocHJldmlvdXNJMThuIGluc3RhbmNlb2YgaTE4bi5Db250YWluZXIpIHtcbiAgICAvLyBUaGUgYHByZXZpb3VzSTE4bmAgaXMgYSBgQ29udGFpbmVyYCwgd2hpY2ggbWVhbnMgdGhhdCB0aGlzIGlzIGEgc2Vjb25kIGkxOG4gZXh0cmFjdGlvbiBwYXNzXG4gICAgLy8gYWZ0ZXIgd2hpdGVzcGFjZSBoYXMgYmVlbiByZW1vdmVkIGZyb20gdGhlIEFTVCBuZG9lcy5cbiAgICBhc3NlcnRFcXVpdmFsZW50Tm9kZXMocHJldmlvdXNJMThuLmNoaWxkcmVuLCBub2Rlcyk7XG5cbiAgICAvLyBSZXVzZSB0aGUgc291cmNlLXNwYW5zIGZyb20gdGhlIGZpcnN0IHBhc3MuXG4gICAgZm9yIChsZXQgaSA9IDA7IGkgPCBub2Rlcy5sZW5ndGg7IGkrKykge1xuICAgICAgbm9kZXNbaV0uc291cmNlU3BhbiA9IHByZXZpb3VzSTE4bi5jaGlsZHJlbltpXS5zb3VyY2VTcGFuO1xuICAgIH1cbiAgfVxufVxuXG4vKipcbiAqIEFzc2VydHMgdGhhdCB0aGUgYG1lc3NhZ2VgIGNvbnRhaW5zIGV4YWN0bHkgb25lIGBDb250YWluZXJgIG5vZGUuXG4gKi9cbmZ1bmN0aW9uIGFzc2VydFNpbmdsZUNvbnRhaW5lck1lc3NhZ2UobWVzc2FnZTogaTE4bi5NZXNzYWdlKTogdm9pZCB7XG4gIGNvbnN0IG5vZGVzID0gbWVzc2FnZS5ub2RlcztcbiAgaWYgKG5vZGVzLmxlbmd0aCAhPT0gMSB8fCAhKG5vZGVzWzBdIGluc3RhbmNlb2YgaTE4bi5Db250YWluZXIpKSB7XG4gICAgdGhyb3cgbmV3IEVycm9yKFxuICAgICAgICAnVW5leHBlY3RlZCBwcmV2aW91cyBpMThuIG1lc3NhZ2UgLSBleHBlY3RlZCBpdCB0byBjb25zaXN0IG9mIG9ubHkgYSBzaW5nbGUgYENvbnRhaW5lcmAgbm9kZS4nKTtcbiAgfVxufVxuXG4vKipcbiAqIEFzc2VydHMgdGhhdCB0aGUgYHByZXZpb3VzTm9kZXNgIGFuZCBgbm9kZWAgY29sbGVjdGlvbnMgaGF2ZSB0aGUgc2FtZSBudW1iZXIgb2YgZWxlbWVudHMgYW5kXG4gKiBjb3JyZXNwb25kaW5nIGVsZW1lbnRzIGhhdmUgdGhlIHNhbWUgbm9kZSB0eXBlLlxuICovXG5mdW5jdGlvbiBhc3NlcnRFcXVpdmFsZW50Tm9kZXMocHJldmlvdXNOb2RlczogaTE4bi5Ob2RlW10sIG5vZGVzOiBpMThuLk5vZGVbXSk6IHZvaWQge1xuICBpZiAocHJldmlvdXNOb2Rlcy5sZW5ndGggIT09IG5vZGVzLmxlbmd0aCkge1xuICAgIHRocm93IG5ldyBFcnJvcignVGhlIG51bWJlciBvZiBpMThuIG1lc3NhZ2UgY2hpbGRyZW4gY2hhbmdlZCBiZXR3ZWVuIGZpcnN0IGFuZCBzZWNvbmQgcGFzcy4nKTtcbiAgfVxuICBpZiAocHJldmlvdXNOb2Rlcy5zb21lKChub2RlLCBpKSA9PiBub2Rlc1tpXS5jb25zdHJ1Y3RvciAhPT0gbm9kZS5jb25zdHJ1Y3RvcikpIHtcbiAgICB0aHJvdyBuZXcgRXJyb3IoXG4gICAgICAgICdUaGUgdHlwZXMgb2YgdGhlIGkxOG4gbWVzc2FnZSBjaGlsZHJlbiBjaGFuZ2VkIGJldHdlZW4gZmlyc3QgYW5kIHNlY29uZCBwYXNzLicpO1xuICB9XG59XG5cbi8qKlxuICogQ3JlYXRlIGEgbmV3IGBQYXJzZVNvdXJjZVNwYW5gIGZyb20gdGhlIGBzb3VyY2VTcGFuYCwgb2Zmc2V0IGJ5IHRoZSBgc3RhcnRgIGFuZCBgZW5kYCB2YWx1ZXMuXG4gKi9cbmZ1bmN0aW9uIGdldE9mZnNldFNvdXJjZVNwYW4oXG4gICAgc291cmNlU3BhbjogUGFyc2VTb3VyY2VTcGFuLCB7c3RhcnQsIGVuZH06IEludGVycG9sYXRpb25QaWVjZSk6IFBhcnNlU291cmNlU3BhbiB7XG4gIHJldHVybiBuZXcgUGFyc2VTb3VyY2VTcGFuKHNvdXJjZVNwYW4uZnVsbFN0YXJ0Lm1vdmVCeShzdGFydCksIHNvdXJjZVNwYW4uZnVsbFN0YXJ0Lm1vdmVCeShlbmQpKTtcbn1cblxuY29uc3QgX0NVU1RPTV9QSF9FWFAgPVxuICAgIC9cXC9cXC9bXFxzXFxTXSppMThuW1xcc1xcU10qXFwoW1xcc1xcU10qcGhbXFxzXFxTXSo9W1xcc1xcU10qKFwifCcpKFtcXHNcXFNdKj8pXFwxW1xcc1xcU10qXFwpL2c7XG5cbmZ1bmN0aW9uIGV4dHJhY3RQbGFjZWhvbGRlck5hbWUoaW5wdXQ6IHN0cmluZyk6IHN0cmluZyB7XG4gIHJldHVybiBpbnB1dC5zcGxpdChfQ1VTVE9NX1BIX0VYUClbMl07XG59XG4iXX0=