(function (factory) {
    if (typeof module === "object" && typeof module.exports === "object") {
        var v = factory(require, exports);
        if (v !== undefined) module.exports = v;
    }
    else if (typeof define === "function" && define.amd) {
        define("@angular/compiler/src/render3/view/i18n/get_msg_utils", ["require", "exports", "@angular/compiler/src/output/map_util", "@angular/compiler/src/output/output_ast", "@angular/compiler/src/render3/view/i18n/icu_serializer", "@angular/compiler/src/render3/view/i18n/meta", "@angular/compiler/src/render3/view/i18n/util"], factory);
    }
})(function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.serializeI18nMessageForGetMsg = exports.createGoogleGetMsgStatements = void 0;
    var map_util_1 = require("@angular/compiler/src/output/map_util");
    var o = require("@angular/compiler/src/output/output_ast");
    var icu_serializer_1 = require("@angular/compiler/src/render3/view/i18n/icu_serializer");
    var meta_1 = require("@angular/compiler/src/render3/view/i18n/meta");
    var util_1 = require("@angular/compiler/src/render3/view/i18n/util");
    /** Closure uses `goog.getMsg(message)` to lookup translations */
    var GOOG_GET_MSG = 'goog.getMsg';
    function createGoogleGetMsgStatements(variable, message, closureVar, params) {
        var messageString = serializeI18nMessageForGetMsg(message);
        var args = [o.literal(messageString)];
        if (Object.keys(params).length) {
            args.push(map_util_1.mapLiteral(params, true));
        }
        // /**
        //  * @desc description of message
        //  * @meaning meaning of message
        //  */
        // const MSG_... = goog.getMsg(..);
        // I18N_X = MSG_...;
        var googGetMsgStmt = closureVar.set(o.variable(GOOG_GET_MSG).callFn(args)).toConstDecl();
        var metaComment = meta_1.i18nMetaToJSDoc(message);
        if (metaComment !== null) {
            googGetMsgStmt.addLeadingComment(metaComment);
        }
        var i18nAssignmentStmt = new o.ExpressionStatement(variable.set(closureVar));
        return [googGetMsgStmt, i18nAssignmentStmt];
    }
    exports.createGoogleGetMsgStatements = createGoogleGetMsgStatements;
    /**
     * This visitor walks over i18n tree and generates its string representation, including ICUs and
     * placeholders in `{$placeholder}` (for plain messages) or `{PLACEHOLDER}` (inside ICUs) format.
     */
    var GetMsgSerializerVisitor = /** @class */ (function () {
        function GetMsgSerializerVisitor() {
        }
        GetMsgSerializerVisitor.prototype.formatPh = function (value) {
            return "{$" + util_1.formatI18nPlaceholderName(value) + "}";
        };
        GetMsgSerializerVisitor.prototype.visitText = function (text) {
            return text.value;
        };
        GetMsgSerializerVisitor.prototype.visitContainer = function (container) {
            var _this = this;
            return container.children.map(function (child) { return child.visit(_this); }).join('');
        };
        GetMsgSerializerVisitor.prototype.visitIcu = function (icu) {
            return icu_serializer_1.serializeIcuNode(icu);
        };
        GetMsgSerializerVisitor.prototype.visitTagPlaceholder = function (ph) {
            var _this = this;
            return ph.isVoid ?
                this.formatPh(ph.startName) :
                "" + this.formatPh(ph.startName) + ph.children.map(function (child) { return child.visit(_this); }).join('') + this.formatPh(ph.closeName);
        };
        GetMsgSerializerVisitor.prototype.visitPlaceholder = function (ph) {
            return this.formatPh(ph.name);
        };
        GetMsgSerializerVisitor.prototype.visitIcuPlaceholder = function (ph, context) {
            return this.formatPh(ph.name);
        };
        return GetMsgSerializerVisitor;
    }());
    var serializerVisitor = new GetMsgSerializerVisitor();
    function serializeI18nMessageForGetMsg(message) {
        return message.nodes.map(function (node) { return node.visit(serializerVisitor, null); }).join('');
    }
    exports.serializeI18nMessageForGetMsg = serializeI18nMessageForGetMsg;
});
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiZ2V0X21zZ191dGlscy5qcyIsInNvdXJjZVJvb3QiOiIiLCJzb3VyY2VzIjpbIi4uLy4uLy4uLy4uLy4uLy4uLy4uLy4uLy4uL3BhY2thZ2VzL2NvbXBpbGVyL3NyYy9yZW5kZXIzL3ZpZXcvaTE4bi9nZXRfbXNnX3V0aWxzLnRzIl0sIm5hbWVzIjpbXSwibWFwcGluZ3MiOiI7Ozs7Ozs7Ozs7OztJQVFBLGtFQUFvRDtJQUNwRCwyREFBZ0Q7SUFFaEQseUZBQWtEO0lBQ2xELHFFQUF1QztJQUN2QyxxRUFBaUQ7SUFFakQsaUVBQWlFO0lBQ2pFLElBQU0sWUFBWSxHQUFHLGFBQWEsQ0FBQztJQUVuQyxTQUFnQiw0QkFBNEIsQ0FDeEMsUUFBdUIsRUFBRSxPQUFxQixFQUFFLFVBQXlCLEVBQ3pFLE1BQXNDO1FBQ3hDLElBQU0sYUFBYSxHQUFHLDZCQUE2QixDQUFDLE9BQU8sQ0FBQyxDQUFDO1FBQzdELElBQU0sSUFBSSxHQUFHLENBQUMsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxhQUFhLENBQWlCLENBQUMsQ0FBQztRQUN4RCxJQUFJLE1BQU0sQ0FBQyxJQUFJLENBQUMsTUFBTSxDQUFDLENBQUMsTUFBTSxFQUFFO1lBQzlCLElBQUksQ0FBQyxJQUFJLENBQUMscUJBQVUsQ0FBQyxNQUFNLEVBQUUsSUFBSSxDQUFDLENBQUMsQ0FBQztTQUNyQztRQUVELE1BQU07UUFDTixrQ0FBa0M7UUFDbEMsaUNBQWlDO1FBQ2pDLE1BQU07UUFDTixtQ0FBbUM7UUFDbkMsb0JBQW9CO1FBQ3BCLElBQU0sY0FBYyxHQUFHLFVBQVUsQ0FBQyxHQUFHLENBQUMsQ0FBQyxDQUFDLFFBQVEsQ0FBQyxZQUFZLENBQUMsQ0FBQyxNQUFNLENBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxXQUFXLEVBQUUsQ0FBQztRQUMzRixJQUFNLFdBQVcsR0FBRyxzQkFBZSxDQUFDLE9BQU8sQ0FBQyxDQUFDO1FBQzdDLElBQUksV0FBVyxLQUFLLElBQUksRUFBRTtZQUN4QixjQUFjLENBQUMsaUJBQWlCLENBQUMsV0FBVyxDQUFDLENBQUM7U0FDL0M7UUFDRCxJQUFNLGtCQUFrQixHQUFHLElBQUksQ0FBQyxDQUFDLG1CQUFtQixDQUFDLFFBQVEsQ0FBQyxHQUFHLENBQUMsVUFBVSxDQUFDLENBQUMsQ0FBQztRQUMvRSxPQUFPLENBQUMsY0FBYyxFQUFFLGtCQUFrQixDQUFDLENBQUM7SUFDOUMsQ0FBQztJQXRCRCxvRUFzQkM7SUFFRDs7O09BR0c7SUFDSDtRQUFBO1FBK0JBLENBQUM7UUE5QlMsMENBQVEsR0FBaEIsVUFBaUIsS0FBYTtZQUM1QixPQUFPLE9BQUssZ0NBQXlCLENBQUMsS0FBSyxDQUFDLE1BQUcsQ0FBQztRQUNsRCxDQUFDO1FBRUQsMkNBQVMsR0FBVCxVQUFVLElBQWU7WUFDdkIsT0FBTyxJQUFJLENBQUMsS0FBSyxDQUFDO1FBQ3BCLENBQUM7UUFFRCxnREFBYyxHQUFkLFVBQWUsU0FBeUI7WUFBeEMsaUJBRUM7WUFEQyxPQUFPLFNBQVMsQ0FBQyxRQUFRLENBQUMsR0FBRyxDQUFDLFVBQUEsS0FBSyxJQUFJLE9BQUEsS0FBSyxDQUFDLEtBQUssQ0FBQyxLQUFJLENBQUMsRUFBakIsQ0FBaUIsQ0FBQyxDQUFDLElBQUksQ0FBQyxFQUFFLENBQUMsQ0FBQztRQUNyRSxDQUFDO1FBRUQsMENBQVEsR0FBUixVQUFTLEdBQWE7WUFDcEIsT0FBTyxpQ0FBZ0IsQ0FBQyxHQUFHLENBQUMsQ0FBQztRQUMvQixDQUFDO1FBRUQscURBQW1CLEdBQW5CLFVBQW9CLEVBQXVCO1lBQTNDLGlCQUtDO1lBSkMsT0FBTyxFQUFFLENBQUMsTUFBTSxDQUFDLENBQUM7Z0JBQ2QsSUFBSSxDQUFDLFFBQVEsQ0FBQyxFQUFFLENBQUMsU0FBUyxDQUFDLENBQUMsQ0FBQztnQkFDN0IsS0FBRyxJQUFJLENBQUMsUUFBUSxDQUFDLEVBQUUsQ0FBQyxTQUFTLENBQUMsR0FBRyxFQUFFLENBQUMsUUFBUSxDQUFDLEdBQUcsQ0FBQyxVQUFBLEtBQUssSUFBSSxPQUFBLEtBQUssQ0FBQyxLQUFLLENBQUMsS0FBSSxDQUFDLEVBQWpCLENBQWlCLENBQUMsQ0FBQyxJQUFJLENBQUMsRUFBRSxDQUFDLEdBQ2pGLElBQUksQ0FBQyxRQUFRLENBQUMsRUFBRSxDQUFDLFNBQVMsQ0FBRyxDQUFDO1FBQ3hDLENBQUM7UUFFRCxrREFBZ0IsR0FBaEIsVUFBaUIsRUFBb0I7WUFDbkMsT0FBTyxJQUFJLENBQUMsUUFBUSxDQUFDLEVBQUUsQ0FBQyxJQUFJLENBQUMsQ0FBQztRQUNoQyxDQUFDO1FBRUQscURBQW1CLEdBQW5CLFVBQW9CLEVBQXVCLEVBQUUsT0FBYTtZQUN4RCxPQUFPLElBQUksQ0FBQyxRQUFRLENBQUMsRUFBRSxDQUFDLElBQUksQ0FBQyxDQUFDO1FBQ2hDLENBQUM7UUFDSCw4QkFBQztJQUFELENBQUMsQUEvQkQsSUErQkM7SUFFRCxJQUFNLGlCQUFpQixHQUFHLElBQUksdUJBQXVCLEVBQUUsQ0FBQztJQUV4RCxTQUFnQiw2QkFBNkIsQ0FBQyxPQUFxQjtRQUNqRSxPQUFPLE9BQU8sQ0FBQyxLQUFLLENBQUMsR0FBRyxDQUFDLFVBQUEsSUFBSSxJQUFJLE9BQUEsSUFBSSxDQUFDLEtBQUssQ0FBQyxpQkFBaUIsRUFBRSxJQUFJLENBQUMsRUFBbkMsQ0FBbUMsQ0FBQyxDQUFDLElBQUksQ0FBQyxFQUFFLENBQUMsQ0FBQztJQUNqRixDQUFDO0lBRkQsc0VBRUMiLCJzb3VyY2VzQ29udGVudCI6WyIvKipcbiAqIEBsaWNlbnNlXG4gKiBDb3B5cmlnaHQgR29vZ2xlIExMQyBBbGwgUmlnaHRzIFJlc2VydmVkLlxuICpcbiAqIFVzZSBvZiB0aGlzIHNvdXJjZSBjb2RlIGlzIGdvdmVybmVkIGJ5IGFuIE1JVC1zdHlsZSBsaWNlbnNlIHRoYXQgY2FuIGJlXG4gKiBmb3VuZCBpbiB0aGUgTElDRU5TRSBmaWxlIGF0IGh0dHBzOi8vYW5ndWxhci5pby9saWNlbnNlXG4gKi9cbmltcG9ydCAqIGFzIGkxOG4gZnJvbSAnLi4vLi4vLi4vaTE4bi9pMThuX2FzdCc7XG5pbXBvcnQge21hcExpdGVyYWx9IGZyb20gJy4uLy4uLy4uL291dHB1dC9tYXBfdXRpbCc7XG5pbXBvcnQgKiBhcyBvIGZyb20gJy4uLy4uLy4uL291dHB1dC9vdXRwdXRfYXN0JztcblxuaW1wb3J0IHtzZXJpYWxpemVJY3VOb2RlfSBmcm9tICcuL2ljdV9zZXJpYWxpemVyJztcbmltcG9ydCB7aTE4bk1ldGFUb0pTRG9jfSBmcm9tICcuL21ldGEnO1xuaW1wb3J0IHtmb3JtYXRJMThuUGxhY2Vob2xkZXJOYW1lfSBmcm9tICcuL3V0aWwnO1xuXG4vKiogQ2xvc3VyZSB1c2VzIGBnb29nLmdldE1zZyhtZXNzYWdlKWAgdG8gbG9va3VwIHRyYW5zbGF0aW9ucyAqL1xuY29uc3QgR09PR19HRVRfTVNHID0gJ2dvb2cuZ2V0TXNnJztcblxuZXhwb3J0IGZ1bmN0aW9uIGNyZWF0ZUdvb2dsZUdldE1zZ1N0YXRlbWVudHMoXG4gICAgdmFyaWFibGU6IG8uUmVhZFZhckV4cHIsIG1lc3NhZ2U6IGkxOG4uTWVzc2FnZSwgY2xvc3VyZVZhcjogby5SZWFkVmFyRXhwcixcbiAgICBwYXJhbXM6IHtbbmFtZTogc3RyaW5nXTogby5FeHByZXNzaW9ufSk6IG8uU3RhdGVtZW50W10ge1xuICBjb25zdCBtZXNzYWdlU3RyaW5nID0gc2VyaWFsaXplSTE4bk1lc3NhZ2VGb3JHZXRNc2cobWVzc2FnZSk7XG4gIGNvbnN0IGFyZ3MgPSBbby5saXRlcmFsKG1lc3NhZ2VTdHJpbmcpIGFzIG8uRXhwcmVzc2lvbl07XG4gIGlmIChPYmplY3Qua2V5cyhwYXJhbXMpLmxlbmd0aCkge1xuICAgIGFyZ3MucHVzaChtYXBMaXRlcmFsKHBhcmFtcywgdHJ1ZSkpO1xuICB9XG5cbiAgLy8gLyoqXG4gIC8vICAqIEBkZXNjIGRlc2NyaXB0aW9uIG9mIG1lc3NhZ2VcbiAgLy8gICogQG1lYW5pbmcgbWVhbmluZyBvZiBtZXNzYWdlXG4gIC8vICAqL1xuICAvLyBjb25zdCBNU0dfLi4uID0gZ29vZy5nZXRNc2coLi4pO1xuICAvLyBJMThOX1ggPSBNU0dfLi4uO1xuICBjb25zdCBnb29nR2V0TXNnU3RtdCA9IGNsb3N1cmVWYXIuc2V0KG8udmFyaWFibGUoR09PR19HRVRfTVNHKS5jYWxsRm4oYXJncykpLnRvQ29uc3REZWNsKCk7XG4gIGNvbnN0IG1ldGFDb21tZW50ID0gaTE4bk1ldGFUb0pTRG9jKG1lc3NhZ2UpO1xuICBpZiAobWV0YUNvbW1lbnQgIT09IG51bGwpIHtcbiAgICBnb29nR2V0TXNnU3RtdC5hZGRMZWFkaW5nQ29tbWVudChtZXRhQ29tbWVudCk7XG4gIH1cbiAgY29uc3QgaTE4bkFzc2lnbm1lbnRTdG10ID0gbmV3IG8uRXhwcmVzc2lvblN0YXRlbWVudCh2YXJpYWJsZS5zZXQoY2xvc3VyZVZhcikpO1xuICByZXR1cm4gW2dvb2dHZXRNc2dTdG10LCBpMThuQXNzaWdubWVudFN0bXRdO1xufVxuXG4vKipcbiAqIFRoaXMgdmlzaXRvciB3YWxrcyBvdmVyIGkxOG4gdHJlZSBhbmQgZ2VuZXJhdGVzIGl0cyBzdHJpbmcgcmVwcmVzZW50YXRpb24sIGluY2x1ZGluZyBJQ1VzIGFuZFxuICogcGxhY2Vob2xkZXJzIGluIGB7JHBsYWNlaG9sZGVyfWAgKGZvciBwbGFpbiBtZXNzYWdlcykgb3IgYHtQTEFDRUhPTERFUn1gIChpbnNpZGUgSUNVcykgZm9ybWF0LlxuICovXG5jbGFzcyBHZXRNc2dTZXJpYWxpemVyVmlzaXRvciBpbXBsZW1lbnRzIGkxOG4uVmlzaXRvciB7XG4gIHByaXZhdGUgZm9ybWF0UGgodmFsdWU6IHN0cmluZyk6IHN0cmluZyB7XG4gICAgcmV0dXJuIGB7JCR7Zm9ybWF0STE4blBsYWNlaG9sZGVyTmFtZSh2YWx1ZSl9fWA7XG4gIH1cblxuICB2aXNpdFRleHQodGV4dDogaTE4bi5UZXh0KTogYW55IHtcbiAgICByZXR1cm4gdGV4dC52YWx1ZTtcbiAgfVxuXG4gIHZpc2l0Q29udGFpbmVyKGNvbnRhaW5lcjogaTE4bi5Db250YWluZXIpOiBhbnkge1xuICAgIHJldHVybiBjb250YWluZXIuY2hpbGRyZW4ubWFwKGNoaWxkID0+IGNoaWxkLnZpc2l0KHRoaXMpKS5qb2luKCcnKTtcbiAgfVxuXG4gIHZpc2l0SWN1KGljdTogaTE4bi5JY3UpOiBhbnkge1xuICAgIHJldHVybiBzZXJpYWxpemVJY3VOb2RlKGljdSk7XG4gIH1cblxuICB2aXNpdFRhZ1BsYWNlaG9sZGVyKHBoOiBpMThuLlRhZ1BsYWNlaG9sZGVyKTogYW55IHtcbiAgICByZXR1cm4gcGguaXNWb2lkID9cbiAgICAgICAgdGhpcy5mb3JtYXRQaChwaC5zdGFydE5hbWUpIDpcbiAgICAgICAgYCR7dGhpcy5mb3JtYXRQaChwaC5zdGFydE5hbWUpfSR7cGguY2hpbGRyZW4ubWFwKGNoaWxkID0+IGNoaWxkLnZpc2l0KHRoaXMpKS5qb2luKCcnKX0ke1xuICAgICAgICAgICAgdGhpcy5mb3JtYXRQaChwaC5jbG9zZU5hbWUpfWA7XG4gIH1cblxuICB2aXNpdFBsYWNlaG9sZGVyKHBoOiBpMThuLlBsYWNlaG9sZGVyKTogYW55IHtcbiAgICByZXR1cm4gdGhpcy5mb3JtYXRQaChwaC5uYW1lKTtcbiAgfVxuXG4gIHZpc2l0SWN1UGxhY2Vob2xkZXIocGg6IGkxOG4uSWN1UGxhY2Vob2xkZXIsIGNvbnRleHQ/OiBhbnkpOiBhbnkge1xuICAgIHJldHVybiB0aGlzLmZvcm1hdFBoKHBoLm5hbWUpO1xuICB9XG59XG5cbmNvbnN0IHNlcmlhbGl6ZXJWaXNpdG9yID0gbmV3IEdldE1zZ1NlcmlhbGl6ZXJWaXNpdG9yKCk7XG5cbmV4cG9ydCBmdW5jdGlvbiBzZXJpYWxpemVJMThuTWVzc2FnZUZvckdldE1zZyhtZXNzYWdlOiBpMThuLk1lc3NhZ2UpOiBzdHJpbmcge1xuICByZXR1cm4gbWVzc2FnZS5ub2Rlcy5tYXAobm9kZSA9PiBub2RlLnZpc2l0KHNlcmlhbGl6ZXJWaXNpdG9yLCBudWxsKSkuam9pbignJyk7XG59XG4iXX0=