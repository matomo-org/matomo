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
        define("@angular/compiler/src/i18n/message_bundle", ["require", "exports", "tslib", "@angular/compiler/src/i18n/extractor_merger", "@angular/compiler/src/i18n/i18n_ast"], factory);
    }
})(function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.MessageBundle = void 0;
    var tslib_1 = require("tslib");
    var extractor_merger_1 = require("@angular/compiler/src/i18n/extractor_merger");
    var i18n = require("@angular/compiler/src/i18n/i18n_ast");
    /**
     * A container for message extracted from the templates.
     */
    var MessageBundle = /** @class */ (function () {
        function MessageBundle(_htmlParser, _implicitTags, _implicitAttrs, _locale) {
            if (_locale === void 0) { _locale = null; }
            this._htmlParser = _htmlParser;
            this._implicitTags = _implicitTags;
            this._implicitAttrs = _implicitAttrs;
            this._locale = _locale;
            this._messages = [];
        }
        MessageBundle.prototype.updateFromTemplate = function (html, url, interpolationConfig) {
            var _a;
            var htmlParserResult = this._htmlParser.parse(html, url, { tokenizeExpansionForms: true, interpolationConfig: interpolationConfig });
            if (htmlParserResult.errors.length) {
                return htmlParserResult.errors;
            }
            var i18nParserResult = extractor_merger_1.extractMessages(htmlParserResult.rootNodes, interpolationConfig, this._implicitTags, this._implicitAttrs);
            if (i18nParserResult.errors.length) {
                return i18nParserResult.errors;
            }
            (_a = this._messages).push.apply(_a, tslib_1.__spread(i18nParserResult.messages));
            return [];
        };
        // Return the message in the internal format
        // The public (serialized) format might be different, see the `write` method.
        MessageBundle.prototype.getMessages = function () {
            return this._messages;
        };
        MessageBundle.prototype.write = function (serializer, filterSources) {
            var messages = {};
            var mapperVisitor = new MapPlaceholderNames();
            // Deduplicate messages based on their ID
            this._messages.forEach(function (message) {
                var _a;
                var id = serializer.digest(message);
                if (!messages.hasOwnProperty(id)) {
                    messages[id] = message;
                }
                else {
                    (_a = messages[id].sources).push.apply(_a, tslib_1.__spread(message.sources));
                }
            });
            // Transform placeholder names using the serializer mapping
            var msgList = Object.keys(messages).map(function (id) {
                var mapper = serializer.createNameMapper(messages[id]);
                var src = messages[id];
                var nodes = mapper ? mapperVisitor.convert(src.nodes, mapper) : src.nodes;
                var transformedMessage = new i18n.Message(nodes, {}, {}, src.meaning, src.description, id);
                transformedMessage.sources = src.sources;
                if (filterSources) {
                    transformedMessage.sources.forEach(function (source) { return source.filePath = filterSources(source.filePath); });
                }
                return transformedMessage;
            });
            return serializer.write(msgList, this._locale);
        };
        return MessageBundle;
    }());
    exports.MessageBundle = MessageBundle;
    // Transform an i18n AST by renaming the placeholder nodes with the given mapper
    var MapPlaceholderNames = /** @class */ (function (_super) {
        tslib_1.__extends(MapPlaceholderNames, _super);
        function MapPlaceholderNames() {
            return _super !== null && _super.apply(this, arguments) || this;
        }
        MapPlaceholderNames.prototype.convert = function (nodes, mapper) {
            var _this = this;
            return mapper ? nodes.map(function (n) { return n.visit(_this, mapper); }) : nodes;
        };
        MapPlaceholderNames.prototype.visitTagPlaceholder = function (ph, mapper) {
            var _this = this;
            var startName = mapper.toPublicName(ph.startName);
            var closeName = ph.closeName ? mapper.toPublicName(ph.closeName) : ph.closeName;
            var children = ph.children.map(function (n) { return n.visit(_this, mapper); });
            return new i18n.TagPlaceholder(ph.tag, ph.attrs, startName, closeName, children, ph.isVoid, ph.sourceSpan, ph.startSourceSpan, ph.endSourceSpan);
        };
        MapPlaceholderNames.prototype.visitPlaceholder = function (ph, mapper) {
            return new i18n.Placeholder(ph.value, mapper.toPublicName(ph.name), ph.sourceSpan);
        };
        MapPlaceholderNames.prototype.visitIcuPlaceholder = function (ph, mapper) {
            return new i18n.IcuPlaceholder(ph.value, mapper.toPublicName(ph.name), ph.sourceSpan);
        };
        return MapPlaceholderNames;
    }(i18n.CloneVisitor));
});
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoibWVzc2FnZV9idW5kbGUuanMiLCJzb3VyY2VSb290IjoiIiwic291cmNlcyI6WyIuLi8uLi8uLi8uLi8uLi8uLi8uLi9wYWNrYWdlcy9jb21waWxlci9zcmMvaTE4bi9tZXNzYWdlX2J1bmRsZS50cyJdLCJuYW1lcyI6W10sIm1hcHBpbmdzIjoiQUFBQTs7Ozs7O0dBTUc7Ozs7Ozs7Ozs7Ozs7O0lBTUgsZ0ZBQW1EO0lBQ25ELDBEQUFtQztJQUluQzs7T0FFRztJQUNIO1FBR0UsdUJBQ1ksV0FBdUIsRUFBVSxhQUF1QixFQUN4RCxjQUF1QyxFQUFVLE9BQTJCO1lBQTNCLHdCQUFBLEVBQUEsY0FBMkI7WUFENUUsZ0JBQVcsR0FBWCxXQUFXLENBQVk7WUFBVSxrQkFBYSxHQUFiLGFBQWEsQ0FBVTtZQUN4RCxtQkFBYyxHQUFkLGNBQWMsQ0FBeUI7WUFBVSxZQUFPLEdBQVAsT0FBTyxDQUFvQjtZQUpoRixjQUFTLEdBQW1CLEVBQUUsQ0FBQztRQUlvRCxDQUFDO1FBRTVGLDBDQUFrQixHQUFsQixVQUFtQixJQUFZLEVBQUUsR0FBVyxFQUFFLG1CQUF3Qzs7WUFFcEYsSUFBTSxnQkFBZ0IsR0FDbEIsSUFBSSxDQUFDLFdBQVcsQ0FBQyxLQUFLLENBQUMsSUFBSSxFQUFFLEdBQUcsRUFBRSxFQUFDLHNCQUFzQixFQUFFLElBQUksRUFBRSxtQkFBbUIscUJBQUEsRUFBQyxDQUFDLENBQUM7WUFFM0YsSUFBSSxnQkFBZ0IsQ0FBQyxNQUFNLENBQUMsTUFBTSxFQUFFO2dCQUNsQyxPQUFPLGdCQUFnQixDQUFDLE1BQU0sQ0FBQzthQUNoQztZQUVELElBQU0sZ0JBQWdCLEdBQUcsa0NBQWUsQ0FDcEMsZ0JBQWdCLENBQUMsU0FBUyxFQUFFLG1CQUFtQixFQUFFLElBQUksQ0FBQyxhQUFhLEVBQUUsSUFBSSxDQUFDLGNBQWMsQ0FBQyxDQUFDO1lBRTlGLElBQUksZ0JBQWdCLENBQUMsTUFBTSxDQUFDLE1BQU0sRUFBRTtnQkFDbEMsT0FBTyxnQkFBZ0IsQ0FBQyxNQUFNLENB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MEJBQUM7SUFBRCxDQUFDLEFBckJELENBQWtDLElBQUksQ0FBQyxZQUFZLEdBcUJsRCIsInNvdXJjZXNDb250ZW50IjpbIi8qKlxuICogQGxpY2Vuc2VcbiAqIENvcHlyaWdodCBHb29nbGUgTExDIEFsbCBSaWdodHMgUmVzZXJ2ZWQuXG4gKlxuICogVXNlIG9mIHRoaXMgc291cmNlIGNvZGUgaXMgZ292ZXJuZWQgYnkgYW4gTUlULXN0eWxlIGxpY2Vuc2UgdGhhdCBjYW4gYmVcbiAqIGZvdW5kIGluIHRoZSBMSUNFTlNFIGZpbGUgYXQgaHR0cHM6Ly9hbmd1bGFyLmlvL2xpY2Vuc2VcbiAqL1xuXG5pbXBvcnQge0h0bWxQYXJzZXJ9IGZyb20gJy4uL21sX3BhcnNlci9odG1sX3BhcnNlcic7XG5pbXBvcnQge0ludGVycG9sYXRpb25Db25maWd9IGZyb20gJy4uL21sX3BhcnNlci9pbnRlcnBvbGF0aW9uX2NvbmZpZyc7XG5pbXBvcnQge1BhcnNlRXJyb3J9IGZyb20gJy4uL3BhcnNlX3V0aWwnO1xuXG5pbXBvcnQge2V4dHJhY3RNZXNzYWdlc30gZnJvbSAnLi9leHRyYWN0b3JfbWVyZ2VyJztcbmltcG9ydCAqIGFzIGkxOG4gZnJvbSAnLi9pMThuX2FzdCc7XG5pbXBvcnQge1BsYWNlaG9sZGVyTWFwcGVyLCBTZXJpYWxpemVyfSBmcm9tICcuL3NlcmlhbGl6ZXJzL3NlcmlhbGl6ZXInO1xuXG5cbi8qKlxuICogQSBjb250YWluZXIgZm9yIG1lc3NhZ2UgZXh0cmFjdGVkIGZyb20gdGhlIHRlbXBsYXRlcy5cbiAqL1xuZXhwb3J0IGNsYXNzIE1lc3NhZ2VCdW5kbGUge1xuICBwcml2YXRlIF9tZXNzYWdlczogaTE4bi5NZXNzYWdlW10gPSBbXTtcblxuICBjb25zdHJ1Y3RvcihcbiAgICAgIHByaXZhdGUgX2h0bWxQYXJzZXI6IEh0bWxQYXJzZXIsIHByaXZhdGUgX2ltcGxpY2l0VGFnczogc3RyaW5nW10sXG4gICAgICBwcml2YXRlIF9pbXBsaWNpdEF0dHJzOiB7W2s6IHN0cmluZ106IHN0cmluZ1tdfSwgcHJpdmF0ZSBfbG9jYWxlOiBzdHJpbmd8bnVsbCA9IG51bGwpIHt9XG5cbiAgdXBkYXRlRnJvbVRlbXBsYXRlKGh0bWw6IHN0cmluZywgdXJsOiBzdHJpbmcsIGludGVycG9sYXRpb25Db25maWc6IEludGVycG9sYXRpb25Db25maWcpOlxuICAgICAgUGFyc2VFcnJvcltdIHtcbiAgICBjb25zdCBodG1sUGFyc2VyUmVzdWx0ID1cbiAgICAgICAgdGhpcy5faHRtbFBhcnNlci5wYXJzZShodG1sLCB1cmwsIHt0b2tlbml6ZUV4cGFuc2lvbkZvcm1zOiB0cnVlLCBpbnRlcnBvbGF0aW9uQ29uZmlnfSk7XG5cbiAgICBpZiAoaHRtbFBhcnNlclJlc3VsdC5lcnJvcnMubGVuZ3RoKSB7XG4gICAgICByZXR1cm4gaHRtbFBhcnNlclJlc3VsdC5lcnJvcnM7XG4gICAgfVxuXG4gICAgY29uc3QgaTE4blBhcnNlclJlc3VsdCA9IGV4dHJhY3RNZXNzYWdlcyhcbiAgICAgICAgaHRtbFBhcnNlclJlc3VsdC5yb290Tm9kZXMsIGludGVycG9sYXRpb25Db25maWcsIHRoaXMuX2ltcGxpY2l0VGFncywgdGhpcy5faW1wbGljaXRBdHRycyk7XG5cbiAgICBpZiAoaTE4blBhcnNlclJlc3VsdC5lcnJvcnMubGVuZ3RoKSB7XG4gICAgICByZXR1cm4gaTE4blBhcnNlclJlc3VsdC5lcnJvcnM7XG4gICAgfVxuXG4gICAgdGhpcy5fbWVzc2FnZXMucHVzaCguLi5pMThuUGFyc2VyUmVzdWx0Lm1lc3NhZ2VzKTtcbiAgICByZXR1cm4gW107XG4gIH1cblxuICAvLyBSZXR1cm4gdGhlIG1lc3NhZ2UgaW4gdGhlIGludGVybmFsIGZvcm1hdFxuICAvLyBUaGUgcHVibGljIChzZXJpYWxpemVkKSBmb3JtYXQgbWlnaHQgYmUgZGlmZmVyZW50LCBzZWUgdGhlIGB3cml0ZWAgbWV0aG9kLlxuICBnZXRNZXNzYWdlcygpOiBpMThuLk1lc3NhZ2VbXSB7XG4gICAgcmV0dXJuIHRoaXMuX21lc3NhZ2VzO1xuICB9XG5cbiAgd3JpdGUoc2VyaWFsaXplcjogU2VyaWFsaXplciwgZmlsdGVyU291cmNlcz86IChwYXRoOiBzdHJpbmcpID0+IHN0cmluZyk6IHN0cmluZyB7XG4gICAgY29uc3QgbWVzc2FnZXM6IHtbaWQ6IHN0cmluZ106IGkxOG4uTWVzc2FnZX0gPSB7fTtcbiAgICBjb25zdCBtYXBwZXJWaXNpdG9yID0gbmV3IE1hcFBsYWNlaG9sZGVyTmFtZXMoKTtcblxuICAgIC8vIERlZHVwbGljYXRlIG1lc3NhZ2VzIGJhc2VkIG9uIHRoZWlyIElEXG4gICAgdGhpcy5fbWVzc2FnZXMuZm9yRWFjaChtZXNzYWdlID0+IHtcbiAgICAgIGNvbnN0IGlkID0gc2VyaWFsaXplci5kaWdlc3QobWVzc2FnZSk7XG4gICAgICBpZiAoIW1lc3NhZ2VzLmhhc093blByb3BlcnR5KGlkKSkge1xuICAgICAgICBtZXNzYWdlc1tpZF0gPSBtZXNzYWdlO1xuICAgICAgfSBlbHNlIHtcbiAgICAgICAgbWVzc2FnZXNbaWRdLnNvdXJjZXMucHVzaCguLi5tZXNzYWdlLnNvdXJjZXMpO1xuICAgICAgfVxuICAgIH0pO1xuXG4gICAgLy8gVHJhbnNmb3JtIHBsYWNlaG9sZGVyIG5hbWVzIHVzaW5nIHRoZSBzZXJpYWxpemVyIG1hcHBpbmdcbiAgICBjb25zdCBtc2dMaXN0ID0gT2JqZWN0LmtleXMobWVzc2FnZXMpLm1hcChpZCA9PiB7XG4gICAgICBjb25zdCBtYXBwZXIgPSBzZXJpYWxpemVyLmNyZWF0ZU5hbWVNYXBwZXIobWVzc2FnZXNbaWRdKTtcbiAgICAgIGNvbnN0IHNyYyA9IG1lc3NhZ2VzW2lkXTtcbiAgICAgIGNvbnN0IG5vZGVzID0gbWFwcGVyID8gbWFwcGVyVmlzaXRvci5jb252ZXJ0KHNyYy5ub2RlcywgbWFwcGVyKSA6IHNyYy5ub2RlcztcbiAgICAgIGxldCB0cmFuc2Zvcm1lZE1lc3NhZ2UgPSBuZXcgaTE4bi5NZXNzYWdlKG5vZGVzLCB7fSwge30sIHNyYy5tZWFuaW5nLCBzcmMuZGVzY3JpcHRpb24sIGlkKTtcbiAgICAgIHRyYW5zZm9ybWVkTWVzc2FnZS5zb3VyY2VzID0gc3JjLnNvdXJjZXM7XG4gICAgICBpZiAoZmlsdGVyU291cmNlcykge1xuICAgICAgICB0cmFuc2Zvcm1lZE1lc3NhZ2Uuc291cmNlcy5mb3JFYWNoKFxuICAgICAgICAgICAgKHNvdXJjZTogaTE4bi5NZXNzYWdlU3BhbikgPT4gc291cmNlLmZpbGVQYXRoID0gZmlsdGVyU291cmNlcyhzb3VyY2UuZmlsZVBhdGgpKTtcbiAgICAgIH1cbiAgICAgIHJldHVybiB0cmFuc2Zvcm1lZE1lc3NhZ2U7XG4gICAgfSk7XG5cbiAgICByZXR1cm4gc2VyaWFsaXplci53cml0ZShtc2dMaXN0LCB0aGlzLl9sb2NhbGUpO1xuICB9XG59XG5cbi8vIFRyYW5zZm9ybSBhbiBpMThuIEFTVCBieSByZW5hbWluZyB0aGUgcGxhY2Vob2xkZXIgbm9kZXMgd2l0aCB0aGUgZ2l2ZW4gbWFwcGVyXG5jbGFzcyBNYXBQbGFjZWhvbGRlck5hbWVzIGV4dGVuZHMgaTE4bi5DbG9uZVZpc2l0b3Ige1xuICBjb252ZXJ0KG5vZGVzOiBpMThuLk5vZGVbXSwgbWFwcGVyOiBQbGFjZWhvbGRlck1hcHBlcik6IGkxOG4uTm9kZVtdIHtcbiAgICByZXR1cm4gbWFwcGVyID8gbm9kZXMubWFwKG4gPT4gbi52aXNpdCh0aGlzLCBtYXBwZXIpKSA6IG5vZGVzO1xuICB9XG5cbiAgdmlzaXRUYWdQbGFjZWhvbGRlcihwaDogaTE4bi5UYWdQbGFjZWhvbGRlciwgbWFwcGVyOiBQbGFjZWhvbGRlck1hcHBlcik6IGkxOG4uVGFnUGxhY2Vob2xkZXIge1xuICAgIGNvbnN0IHN0YXJ0TmFtZSA9IG1hcHBlci50b1B1YmxpY05hbWUocGguc3RhcnROYW1lKSE7XG4gICAgY29uc3QgY2xvc2VOYW1lID0gcGguY2xvc2VOYW1lID8gbWFwcGVyLnRvUHVibGljTmFtZShwaC5jbG9zZU5hbWUpISA6IHBoLmNsb3NlTmFtZTtcbiAgICBjb25zdCBjaGlsZHJlbiA9IHBoLmNoaWxkcmVuLm1hcChuID0+IG4udmlzaXQodGhpcywgbWFwcGVyKSk7XG4gICAgcmV0dXJuIG5ldyBpMThuLlRhZ1BsYWNlaG9sZGVyKFxuICAgICAgICBwaC50YWcsIHBoLmF0dHJzLCBzdGFydE5hbWUsIGNsb3NlTmFtZSwgY2hpbGRyZW4sIHBoLmlzVm9pZCwgcGguc291cmNlU3BhbixcbiAgICAgICAgcGguc3RhcnRTb3VyY2VTcGFuLCBwaC5lbmRTb3VyY2VTcGFuKTtcbiAgfVxuXG4gIHZpc2l0UGxhY2Vob2xkZXIocGg6IGkxOG4uUGxhY2Vob2xkZXIsIG1hcHBlcjogUGxhY2Vob2xkZXJNYXBwZXIpOiBpMThuLlBsYWNlaG9sZGVyIHtcbiAgICByZXR1cm4gbmV3IGkxOG4uUGxhY2Vob2xkZXIocGgudmFsdWUsIG1hcHBlci50b1B1YmxpY05hbWUocGgubmFtZSkhLCBwaC5zb3VyY2VTcGFuKTtcbiAgfVxuXG4gIHZpc2l0SWN1UGxhY2Vob2xkZXIocGg6IGkxOG4uSWN1UGxhY2Vob2xkZXIsIG1hcHBlcjogUGxhY2Vob2xkZXJNYXBwZXIpOiBpMThuLkljdVBsYWNlaG9sZGVyIHtcbiAgICByZXR1cm4gbmV3IGkxOG4uSWN1UGxhY2Vob2xkZXIocGgudmFsdWUsIG1hcHBlci50b1B1YmxpY05hbWUocGgubmFtZSkhLCBwaC5zb3VyY2VTcGFuKTtcbiAgfVxufVxuIl19