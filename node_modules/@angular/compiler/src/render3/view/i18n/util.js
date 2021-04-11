(function (factory) {
    if (typeof module === "object" && typeof module.exports === "object") {
        var v = factory(require, exports);
        if (v !== undefined) module.exports = v;
    }
    else if (typeof define === "function" && define.amd) {
        define("@angular/compiler/src/render3/view/i18n/util", ["require", "exports", "tslib", "@angular/compiler/src/i18n/i18n_ast", "@angular/compiler/src/i18n/serializers/xmb", "@angular/compiler/src/output/output_ast"], factory);
    }
})(function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.declareI18nVariable = exports.getTranslationConstPrefix = exports.formatI18nPlaceholderName = exports.i18nFormatPlaceholderNames = exports.assembleBoundTextPlaceholders = exports.updatePlaceholderMap = exports.placeholdersToParams = exports.getSeqNumberGenerator = exports.assembleI18nBoundString = exports.wrapI18nPlaceholder = exports.icuFromI18nMessage = exports.hasI18nAttrs = exports.hasI18nMeta = exports.isSingleI18nIcu = exports.isI18nRootNode = exports.isI18nAttribute = exports.I18N_PLACEHOLDER_SYMBOL = exports.I18N_ICU_MAPPING_PREFIX = exports.I18N_ICU_VAR_PREFIX = exports.I18N_ATTR_PREFIX = exports.I18N_ATTR = exports.TRANSLATION_VAR_PREFIX = void 0;
    var tslib_1 = require("tslib");
    /**
     * @license
     * Copyright Google LLC All Rights Reserved.
     *
     * Use of this source code is governed by an MIT-style license that can be
     * found in the LICENSE file at https://angular.io/license
     */
    var i18n = require("@angular/compiler/src/i18n/i18n_ast");
    var xmb_1 = require("@angular/compiler/src/i18n/serializers/xmb");
    var o = require("@angular/compiler/src/output/output_ast");
    /* Closure variables holding messages must be named `MSG_[A-Z0-9]+` */
    var CLOSURE_TRANSLATION_VAR_PREFIX = 'MSG_';
    /**
     * Prefix for non-`goog.getMsg` i18n-related vars.
     * Note: the prefix uses lowercase characters intentionally due to a Closure behavior that
     * considers variables like `I18N_0` as constants and throws an error when their value changes.
     */
    exports.TRANSLATION_VAR_PREFIX = 'i18n_';
    /** Name of the i18n attributes **/
    exports.I18N_ATTR = 'i18n';
    exports.I18N_ATTR_PREFIX = 'i18n-';
    /** Prefix of var expressions used in ICUs */
    exports.I18N_ICU_VAR_PREFIX = 'VAR_';
    /** Prefix of ICU expressions for post processing */
    exports.I18N_ICU_MAPPING_PREFIX = 'I18N_EXP_';
    /** Placeholder wrapper for i18n expressions **/
    exports.I18N_PLACEHOLDER_SYMBOL = '�';
    function isI18nAttribute(name) {
        return name === exports.I18N_ATTR || name.startsWith(exports.I18N_ATTR_PREFIX);
    }
    exports.isI18nAttribute = isI18nAttribute;
    function isI18nRootNode(meta) {
        return meta instanceof i18n.Message;
    }
    exports.isI18nRootNode = isI18nRootNode;
    function isSingleI18nIcu(meta) {
        return isI18nRootNode(meta) && meta.nodes.length === 1 && meta.nodes[0] instanceof i18n.Icu;
    }
    exports.isSingleI18nIcu = isSingleI18nIcu;
    function hasI18nMeta(node) {
        return !!node.i18n;
    }
    exports.hasI18nMeta = hasI18nMeta;
    function hasI18nAttrs(element) {
        return element.attrs.some(function (attr) { return isI18nAttribute(attr.name); });
    }
    exports.hasI18nAttrs = hasI18nAttrs;
    function icuFromI18nMessage(message) {
        return message.nodes[0];
    }
    exports.icuFromI18nMessage = icuFromI18nMessage;
    function wrapI18nPlaceholder(content, contextId) {
        if (contextId === void 0) { contextId = 0; }
        var blockId = contextId > 0 ? ":" + contextId : '';
        return "" + exports.I18N_PLACEHOLDER_SYMBOL + content + blockId + exports.I18N_PLACEHOLDER_SYMBOL;
    }
    exports.wrapI18nPlaceholder = wrapI18nPlaceholder;
    function assembleI18nBoundString(strings, bindingStartIndex, contextId) {
        if (bindingStartIndex === void 0) { bindingStartIndex = 0; }
        if (contextId === void 0) { contextId = 0; }
        if (!strings.length)
            return '';
        var acc = '';
        var lastIdx = strings.length - 1;
        for (var i = 0; i < lastIdx; i++) {
            acc += "" + strings[i] + wrapI18nPlaceholder(bindingStartIndex + i, contextId);
        }
        acc += strings[lastIdx];
        return acc;
    }
    exports.assembleI18nBoundString = assembleI18nBoundString;
    function getSeqNumberGenerator(startsAt) {
        if (startsAt === void 0) { startsAt = 0; }
        var current = startsAt;
        return function () { return current++; };
    }
    exports.getSeqNumberGenerator = getSeqNumberGenerator;
    function placeholdersToParams(placeholders) {
        var params = {};
        placeholders.forEach(function (values, key) {
            params[key] = o.literal(values.length > 1 ? "[" + values.join('|') + "]" : values[0]);
        });
        return params;
    }
    exports.placeholdersToParams = placeholdersToParams;
    function updatePlaceholderMap(map, name) {
        var values = [];
        for (var _i = 2; _i < arguments.length; _i++) {
            values[_i - 2] = arguments[_i];
        }
        var current = map.get(name) || [];
        current.push.apply(current, tslib_1.__spread(values));
        map.set(name, current);
    }
    exports.updatePlaceholderMap = updatePlaceholderMap;
    function assembleBoundTextPlaceholders(meta, bindingStartIndex, contextId) {
        if (bindingStartIndex === void 0) { bindingStartIndex = 0; }
        if (contextId === void 0) { contextId = 0; }
        var startIdx = bindingStartIndex;
        var placeholders = new Map();
        var node = meta instanceof i18n.Message ? meta.nodes.find(function (node) { return node instanceof i18n.Container; }) : meta;
        if (node) {
            node
                .children
                .filter(function (child) { return child instanceof i18n.Placeholder; })
                .forEach(function (child, idx) {
                var content = wrapI18nPlaceholder(startIdx + idx, contextId);
                updatePlaceholderMap(placeholders, child.name, content);
            });
        }
        return placeholders;
    }
    exports.assembleBoundTextPlaceholders = assembleBoundTextPlaceholders;
    /**
     * Format the placeholder names in a map of placeholders to expressions.
     *
     * The placeholder names are converted from "internal" format (e.g. `START_TAG_DIV_1`) to "external"
     * format (e.g. `startTagDiv_1`).
     *
     * @param params A map of placeholder names to expressions.
     * @param useCamelCase whether to camelCase the placeholder name when formatting.
     * @returns A new map of formatted placeholder names to expressions.
     */
    function i18nFormatPlaceholderNames(params, useCamelCase) {
        if (params === void 0) { params = {}; }
        var _params = {};
        if (params && Object.keys(params).length) {
            Object.keys(params).forEach(function (key) { return _params[formatI18nPlaceholderName(key, useCamelCase)] = params[key]; });
        }
        return _params;
    }
    exports.i18nFormatPlaceholderNames = i18nFormatPlaceholderNames;
    /**
     * Converts internal placeholder names to public-facing format
     * (for example to use in goog.getMsg call).
     * Example: `START_TAG_DIV_1` is converted to `startTagDiv_1`.
     *
     * @param name The placeholder name that should be formatted
     * @returns Formatted placeholder name
     */
    function formatI18nPlaceholderName(name, useCamelCase) {
        if (useCamelCase === void 0) { useCamelCase = true; }
        var publicName = xmb_1.toPublicName(name);
        if (!useCamelCase) {
            return publicName;
        }
        var chunks = publicName.split('_');
        if (chunks.length === 1) {
            // if no "_" found - just lowercase the value
            return name.toLowerCase();
        }
        var postfix;
        // eject last element if it's a number
        if (/^\d+$/.test(chunks[chunks.length - 1])) {
            postfix = chunks.pop();
        }
        var raw = chunks.shift().toLowerCase();
        if (chunks.length) {
            raw += chunks.map(function (c) { return c.charAt(0).toUpperCase() + c.slice(1).toLowerCase(); }).join('');
        }
        return postfix ? raw + "_" + postfix : raw;
    }
    exports.formatI18nPlaceholderName = formatI18nPlaceholderName;
    /**
     * Generates a prefix for translation const name.
     *
     * @param extra Additional local prefix that should be injected into translation var name
     * @returns Complete translation const prefix
     */
    function getTranslationConstPrefix(extra) {
        return ("" + CLOSURE_TRANSLATION_VAR_PREFIX + extra).toUpperCase();
    }
    exports.getTranslationConstPrefix = getTranslationConstPrefix;
    /**
     * Generate AST to declare a variable. E.g. `var I18N_1;`.
     * @param variable the name of the variable to declare.
     */
    function declareI18nVariable(variable) {
        return new o.DeclareVarStmt(variable.name, undefined, o.INFERRED_TYPE, undefined, variable.sourceSpan);
    }
    exports.declareI18nVariable = declareI18nVariable;
});
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoidXRpbC5qcyIsInNvdXJjZVJvb3QiOiIiLCJzb3VyY2VzIjpbIi4uLy4uLy4uLy4uLy4uLy4uLy4uLy4uLy4uL3BhY2thZ2VzL2NvbXBpbGVyL3NyYy9yZW5kZXIzL3ZpZXcvaTE4bi91dGlsLnRzIl0sIm5hbWVzIjpbXSwibWFwcGluZ3MiOiI7Ozs7Ozs7Ozs7Ozs7SUFBQTs7Ozs7O09BTUc7SUFDSCwwREFBK0M7SUFDL0Msa0VBQTJEO0lBRTNELDJEQUFnRDtJQUdoRCxzRUFBc0U7SUFDdEUsSUFBTSw4QkFBOEIsR0FBRyxNQUFNLENBQUM7SUFFOUM7Ozs7T0FJRztJQUNVLFFBQUEsc0JBQXNCLEdBQUcsT0FBTyxDQUFDO0lBRTlDLG1DQUFtQztJQUN0QixRQUFBLFNBQVMsR0FBRyxNQUFNLENBQUM7SUFDbkIsUUFBQSxnQkFBZ0IsR0FBRyxPQUFPLENBQUM7SUFFeEMsNkNBQTZDO0lBQ2hDLFFBQUEsbUJBQW1CLEdBQUcsTUFBTSxDQUFDO0lBRTFDLG9EQUFvRDtJQUN2QyxRQUFBLHVCQUF1QixHQUFHLFdBQVcsQ0FBQztJQUVuRCxnREFBZ0Q7SUFDbkMsUUFBQSx1QkFBdUIsR0FBRyxHQUFHLENBQUM7SUFFM0MsU0FBZ0IsZUFBZSxDQUFDLElBQVk7UUFDMUMsT0FBTyxJQUFJLEtBQUssaUJBQVMsSUFBSSxJQUFJLENBQUMsVUFBVSxDQUFDLHdCQUFnQixDQUFDLENBQUM7SUFDakUsQ0FBQztJQUZELDBDQUVDO0lBRUQsU0FBZ0IsY0FBYyxDQUFDLElBQW9CO1FBQ2pELE9BQU8sSUFBSSxZQUFZLElBQUksQ0FBQyxPQUFPLENBQUM7SUFDdEMsQ0FBQztJQUZELHdDQUVDO0lBRUQsU0FBZ0IsZUFBZSxDQUFDLElBQW9CO1FBQ2xELE9BQU8sY0FBYyxDQUFDLElBQUksQ0FBQyxJQUFJLElBQUksQ0FBQyxLQUFLLENBQUMsTUFBTSxLQUFLLENBQUMsSUFBSSxJQUFJLENBQUMsS0FBSyxDQUFDLENBQUMsQ0FBQyxZQUFZLElBQUksQ0FBQyxHQUFHLENBQUM7SUFD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QUFBLENBQUMsSUFBSSxPQUFBLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLENBQUMsV0FBVyxFQUFFLEdBQUcsQ0FBQyxDQUFDLEtBQUssQ0FBQyxDQUFDLENBQUMsQ0FBQyxXQUFXLEVBQUUsRUFBcEQsQ0FBb0QsQ0FBQyxDQUFDLElBQUksQ0FBQyxFQUFFLENBQUMsQ0FBQztTQUN2RjtRQUNELE9BQU8sT0FBTyxDQUFDLENBQUMsQ0FBSSxHQUFHLFNBQUksT0FBUyxDQUFDLENBQUMsQ0FBQyxHQUFHLENBQUM7SUFDN0MsQ0FBQztJQXBCRCw4REFvQkM7SUFFRDs7Ozs7T0FLRztJQUNILFNBQWdCLHlCQUF5QixDQUFDLEtBQWE7UUFDckQsT0FBTyxDQUFBLEtBQUcsOEJBQThCLEdBQUcsS0FBTyxDQUFBLENBQUMsV0FBVyxFQUFFLENBQUM7SUFDbkUsQ0FBQztJQUZELDhEQUVDO0lBRUQ7OztPQUdHO0lBQ0gsU0FBZ0IsbUJBQW1CLENBQUMsUUFBdUI7UUFDekQsT0FBTyxJQUFJLENBQUMsQ0FBQyxjQUFjLENBQ3ZCLFFBQVEsQ0FBQyxJQUFLLEVBQUUsU0FBUyxFQUFFLENBQUMsQ0FBQyxhQUFhLEVBQUUsU0FBUyxFQUFFLFFBQVEsQ0FBQyxVQUFVLENBQUMsQ0FBQztJQUNsRixDQUFDO0lBSEQsa0RBR0MiLCJzb3VyY2VzQ29udGVudCI6WyIvKipcbiAqIEBsaWNlbnNlXG4gKiBDb3B5cmlnaHQgR29vZ2xlIExMQyBBbGwgUmlnaHRzIFJlc2VydmVkLlxuICpcbiAqIFVzZSBvZiB0aGlzIHNvdXJjZSBjb2RlIGlzIGdvdmVybmVkIGJ5IGFuIE1JVC1zdHlsZSBsaWNlbnNlIHRoYXQgY2FuIGJlXG4gKiBmb3VuZCBpbiB0aGUgTElDRU5TRSBmaWxlIGF0IGh0dHBzOi8vYW5ndWxhci5pby9saWNlbnNlXG4gKi9cbmltcG9ydCAqIGFzIGkxOG4gZnJvbSAnLi4vLi4vLi4vaTE4bi9pMThuX2FzdCc7XG5pbXBvcnQge3RvUHVibGljTmFtZX0gZnJvbSAnLi4vLi4vLi4vaTE4bi9zZXJpYWxpemVycy94bWInO1xuaW1wb3J0ICogYXMgaHRtbCBmcm9tICcuLi8uLi8uLi9tbF9wYXJzZXIvYXN0JztcbmltcG9ydCAqIGFzIG8gZnJvbSAnLi4vLi4vLi4vb3V0cHV0L291dHB1dF9hc3QnO1xuaW1wb3J0ICogYXMgdCBmcm9tICcuLi8uLi9yM19hc3QnO1xuXG4vKiBDbG9zdXJlIHZhcmlhYmxlcyBob2xkaW5nIG1lc3NhZ2VzIG11c3QgYmUgbmFtZWQgYE1TR19bQS1aMC05XStgICovXG5jb25zdCBDTE9TVVJFX1RSQU5TTEFUSU9OX1ZBUl9QUkVGSVggPSAnTVNHXyc7XG5cbi8qKlxuICogUHJlZml4IGZvciBub24tYGdvb2cuZ2V0TXNnYCBpMThuLXJlbGF0ZWQgdmFycy5cbiAqIE5vdGU6IHRoZSBwcmVmaXggdXNlcyBsb3dlcmNhc2UgY2hhcmFjdGVycyBpbnRlbnRpb25hbGx5IGR1ZSB0byBhIENsb3N1cmUgYmVoYXZpb3IgdGhhdFxuICogY29uc2lkZXJzIHZhcmlhYmxlcyBsaWtlIGBJMThOXzBgIGFzIGNvbnN0YW50cyBhbmQgdGhyb3dzIGFuIGVycm9yIHdoZW4gdGhlaXIgdmFsdWUgY2hhbmdlcy5cbiAqL1xuZXhwb3J0IGNvbnN0IFRSQU5TTEFUSU9OX1ZBUl9QUkVGSVggPSAnaTE4bl8nO1xuXG4vKiogTmFtZSBvZiB0aGUgaTE4biBhdHRyaWJ1dGVzICoqL1xuZXhwb3J0IGNvbnN0IEkxOE5fQVRUUiA9ICdpMThuJztcbmV4cG9ydCBjb25zdCBJMThOX0FUVFJfUFJFRklYID0gJ2kxOG4tJztcblxuLyoqIFByZWZpeCBvZiB2YXIgZXhwcmVzc2lvbnMgdXNlZCBpbiBJQ1VzICovXG5leHBvcnQgY29uc3QgSTE4Tl9JQ1VfVkFSX1BSRUZJWCA9ICdWQVJfJztcblxuLyoqIFByZWZpeCBvZiBJQ1UgZXhwcmVzc2lvbnMgZm9yIHBvc3QgcHJvY2Vzc2luZyAqL1xuZXhwb3J0IGNvbnN0IEkxOE5fSUNVX01BUFBJTkdfUFJFRklYID0gJ0kxOE5fRVhQXyc7XG5cbi8qKiBQbGFjZWhvbGRlciB3cmFwcGVyIGZvciBpMThuIGV4cHJlc3Npb25zICoqL1xuZXhwb3J0IGNvbnN0IEkxOE5fUExBQ0VIT0xERVJfU1lNQk9MID0gJ++/vSc7XG5cbmV4cG9ydCBmdW5jdGlvbiBpc0kxOG5BdHRyaWJ1dGUobmFtZTogc3RyaW5nKTogYm9vbGVhbiB7XG4gIHJldHVybiBuYW1lID09PSBJMThOX0FUVFIgfHwgbmFtZS5zdGFydHNXaXRoKEkxOE5fQVRUUl9QUkVGSVgpO1xufVxuXG5leHBvcnQgZnVuY3Rpb24gaXNJMThuUm9vdE5vZGUobWV0YT86IGkxOG4uSTE4bk1ldGEpOiBtZXRhIGlzIGkxOG4uTWVzc2FnZSB7XG4gIHJldHVybiBtZXRhIGluc3RhbmNlb2YgaTE4bi5NZXNzYWdlO1xufVxuXG5leHBvcnQgZnVuY3Rpb24gaXNTaW5nbGVJMThuSWN1KG1ldGE/OiBpMThuLkkxOG5NZXRhKTogYm9vbGVhbiB7XG4gIHJldHVybiBpc0kxOG5Sb290Tm9kZShtZXRhKSAmJiBtZXRhLm5vZGVzLmxlbmd0aCA9PT0gMSAmJiBtZXRhLm5vZGVzWzBdIGluc3RhbmNlb2YgaTE4bi5JY3U7XG59XG5cbmV4cG9ydCBmdW5jdGlvbiBoYXNJMThuTWV0YShub2RlOiB0Lk5vZGUme2kxOG4/OiBpMThuLkkxOG5NZXRhfSk6IGJvb2xlYW4ge1xuICByZXR1cm4gISFub2RlLmkxOG47XG59XG5cbmV4cG9ydCBmdW5jdGlvbiBoYXNJMThuQXR0cnMoZWxlbWVudDogaHRtbC5FbGVtZW50KTogYm9vbGVhbiB7XG4gIHJldHVybiBlbGVtZW50LmF0dHJzLnNvbWUoKGF0dHI6IGh0bWwuQXR0cmlidXRlKSA9PiBpc0kxOG5BdHRyaWJ1dGUoYXR0ci5uYW1lKSk7XG59XG5cbmV4cG9ydCBmdW5jdGlvbiBpY3VGcm9tSTE4bk1lc3NhZ2UobWVzc2FnZTogaTE4bi5NZXNzYWdlKSB7XG4gIHJldHVybiBtZXNzYWdlLm5vZGVzWzBdIGFzIGkxOG4uSWN1UGxhY2Vob2xkZXI7XG59XG5cbmV4cG9ydCBmdW5jdGlvbiB3cmFwSTE4blBsYWNlaG9sZGVyKGNvbnRlbnQ6IHN0cmluZ3xudW1iZXIsIGNvbnRleHRJZDogbnVtYmVyID0gMCk6IHN0cmluZyB7XG4gIGNvbnN0IGJsb2NrSWQgPSBjb250ZXh0SWQgPiAwID8gYDoke2NvbnRleHRJZH1gIDogJyc7XG4gIHJldHVybiBgJHtJMThOX1BMQUNFSE9MREVSX1NZTUJPTH0ke2NvbnRlbnR9JHtibG9ja0lkfSR7STE4Tl9QTEFDRUhPTERFUl9TWU1CT0x9YDtcbn1cblxuZXhwb3J0IGZ1bmN0aW9uIGFzc2VtYmxlSTE4bkJvdW5kU3RyaW5nKFxuICAgIHN0cmluZ3M6IHN0cmluZ1tdLCBiaW5kaW5nU3RhcnRJbmRleDogbnVtYmVyID0gMCwgY29udGV4dElkOiBudW1iZXIgPSAwKTogc3RyaW5nIHtcbiAgaWYgKCFzdHJpbmdzLmxlbmd0aCkgcmV0dXJuICcnO1xuICBsZXQgYWNjID0gJyc7XG4gIGNvbnN0IGxhc3RJZHggPSBzdHJpbmdzLmxlbmd0aCAtIDE7XG4gIGZvciAobGV0IGkgPSAwOyBpIDwgbGFzdElkeDsgaSsrKSB7XG4gICAgYWNjICs9IGAke3N0cmluZ3NbaV19JHt3cmFwSTE4blBsYWNlaG9sZGVyKGJpbmRpbmdTdGFydEluZGV4ICsgaSwgY29udGV4dElkKX1gO1xuICB9XG4gIGFjYyArPSBzdHJpbmdzW2xhc3RJZHhdO1xuICByZXR1cm4gYWNjO1xufVxuXG5leHBvcnQgZnVuY3Rpb24gZ2V0U2VxTnVtYmVyR2VuZXJhdG9yKHN0YXJ0c0F0OiBudW1iZXIgPSAwKTogKCkgPT4gbnVtYmVyIHtcbiAgbGV0IGN1cnJlbnQgPSBzdGFydHNBdDtcbiAgcmV0dXJuICgpID0+IGN1cnJlbnQrKztcbn1cblxuZXhwb3J0IGZ1bmN0aW9uIHBsYWNlaG9sZGVyc1RvUGFyYW1zKHBsYWNlaG9sZGVyczogTWFwPHN0cmluZywgc3RyaW5nW10+KTpcbiAgICB7W25hbWU6IHN0cmluZ106IG8uTGl0ZXJhbEV4cHJ9IHtcbiAgY29uc3QgcGFyYW1zOiB7W25hbWU6IHN0cmluZ106IG8uTGl0ZXJhbEV4cHJ9ID0ge307XG4gIHBsYWNlaG9sZGVycy5mb3JFYWNoKCh2YWx1ZXM6IHN0cmluZ1tdLCBrZXk6IHN0cmluZykgPT4ge1xuICAgIHBhcmFtc1trZXldID0gby5saXRlcmFsKHZhbHVlcy5sZW5ndGggPiAxID8gYFske3ZhbHVlcy5qb2luKCd8Jyl9XWAgOiB2YWx1ZXNbMF0pO1xuICB9KTtcbiAgcmV0dXJuIHBhcmFtcztcbn1cblxuZXhwb3J0IGZ1bmN0aW9uIHVwZGF0ZVBsYWNlaG9sZGVyTWFwKG1hcDogTWFwPHN0cmluZywgYW55W10+LCBuYW1lOiBzdHJpbmcsIC4uLnZhbHVlczogYW55W10pIHtcbiAgY29uc3QgY3VycmVudCA9IG1hcC5nZXQobmFtZSkgfHwgW107XG4gIGN1cnJlbnQucHVzaCguLi52YWx1ZXMpO1xuICBtYXAuc2V0KG5hbWUsIGN1cnJlbnQpO1xufVxuXG5leHBvcnQgZnVuY3Rpb24gYXNzZW1ibGVCb3VuZFRleHRQbGFjZWhvbGRlcnMoXG4gICAgbWV0YTogaTE4bi5JMThuTWV0YSwgYmluZGluZ1N0YXJ0SW5kZXg6IG51bWJlciA9IDAsIGNvbnRleHRJZDogbnVtYmVyID0gMCk6IE1hcDxzdHJpbmcsIGFueVtdPiB7XG4gIGNvbnN0IHN0YXJ0SWR4ID0gYmluZGluZ1N0YXJ0SW5kZXg7XG4gIGNvbnN0IHBsYWNlaG9sZGVycyA9IG5ldyBNYXA8c3RyaW5nLCBhbnk+KCk7XG4gIGNvbnN0IG5vZGUgPVxuICAgICAgbWV0YSBpbnN0YW5jZW9mIGkxOG4uTWVzc2FnZSA/IG1ldGEubm9kZXMuZmluZChub2RlID0+IG5vZGUgaW5zdGFuY2VvZiBpMThuLkNvbnRhaW5lcikgOiBtZXRhO1xuICBpZiAobm9kZSkge1xuICAgIChub2RlIGFzIGkxOG4uQ29udGFpbmVyKVxuICAgICAgICAuY2hpbGRyZW5cbiAgICAgICAgLmZpbHRlcigoY2hpbGQ6IGkxOG4uTm9kZSk6IGNoaWxkIGlzIGkxOG4uUGxhY2Vob2xkZXIgPT4gY2hpbGQgaW5zdGFuY2VvZiBpMThuLlBsYWNlaG9sZGVyKVxuICAgICAgICAuZm9yRWFjaCgoY2hpbGQ6IGkxOG4uUGxhY2Vob2xkZXIsIGlkeDogbnVtYmVyKSA9PiB7XG4gICAgICAgICAgY29uc3QgY29udGVudCA9IHdyYXBJMThuUGxhY2Vob2xkZXIoc3RhcnRJZHggKyBpZHgsIGNvbnRleHRJZCk7XG4gICAgICAgICAgdXBkYXRlUGxhY2Vob2xkZXJNYXAocGxhY2Vob2xkZXJzLCBjaGlsZC5uYW1lLCBjb250ZW50KTtcbiAgICAgICAgfSk7XG4gIH1cbiAgcmV0dXJuIHBsYWNlaG9sZGVycztcbn1cblxuLyoqXG4gKiBGb3JtYXQgdGhlIHBsYWNlaG9sZGVyIG5hbWVzIGluIGEgbWFwIG9mIHBsYWNlaG9sZGVycyB0byBleHByZXNzaW9ucy5cbiAqXG4gKiBUaGUgcGxhY2Vob2xkZXIgbmFtZXMgYXJlIGNvbnZlcnRlZCBmcm9tIFwiaW50ZXJuYWxcIiBmb3JtYXQgKGUuZy4gYFNUQVJUX1RBR19ESVZfMWApIHRvIFwiZXh0ZXJuYWxcIlxuICogZm9ybWF0IChlLmcuIGBzdGFydFRhZ0Rpdl8xYCkuXG4gKlxuICogQHBhcmFtIHBhcmFtcyBBIG1hcCBvZiBwbGFjZWhvbGRlciBuYW1lcyB0byBleHByZXNzaW9ucy5cbiAqIEBwYXJhbSB1c2VDYW1lbENhc2Ugd2hldGhlciB0byBjYW1lbENhc2UgdGhlIHBsYWNlaG9sZGVyIG5hbWUgd2hlbiBmb3JtYXR0aW5nLlxuICogQHJldHVybnMgQSBuZXcgbWFwIG9mIGZvcm1hdHRlZCBwbGFjZWhvbGRlciBuYW1lcyB0byBleHByZXNzaW9ucy5cbiAqL1xuZXhwb3J0IGZ1bmN0aW9uIGkxOG5Gb3JtYXRQbGFjZWhvbGRlck5hbWVzKFxuICAgIHBhcmFtczoge1tuYW1lOiBzdHJpbmddOiBvLkV4cHJlc3Npb259ID0ge30sIHVzZUNhbWVsQ2FzZTogYm9vbGVhbikge1xuICBjb25zdCBfcGFyYW1zOiB7W2tleTogc3RyaW5nXTogby5FeHByZXNzaW9ufSA9IHt9O1xuICBpZiAocGFyYW1zICYmIE9iamVjdC5rZXlzKHBhcmFtcykubGVuZ3RoKSB7XG4gICAgT2JqZWN0LmtleXMocGFyYW1zKS5mb3JFYWNoKFxuICAgICAgICBrZXkgPT4gX3BhcmFtc1tmb3JtYXRJMThuUGxhY2Vob2xkZXJOYW1lKGtleSwgdXNlQ2FtZWxDYXNlKV0gPSBwYXJhbXNba2V5XSk7XG4gIH1cbiAgcmV0dXJuIF9wYXJhbXM7XG59XG5cbi8qKlxuICogQ29udmVydHMgaW50ZXJuYWwgcGxhY2Vob2xkZXIgbmFtZXMgdG8gcHVibGljLWZhY2luZyBmb3JtYXRcbiAqIChmb3IgZXhhbXBsZSB0byB1c2UgaW4gZ29vZy5nZXRNc2cgY2FsbCkuXG4gKiBFeGFtcGxlOiBgU1RBUlRfVEFHX0RJVl8xYCBpcyBjb252ZXJ0ZWQgdG8gYHN0YXJ0VGFnRGl2XzFgLlxuICpcbiAqIEBwYXJhbSBuYW1lIFRoZSBwbGFjZWhvbGRlciBuYW1lIHRoYXQgc2hvdWxkIGJlIGZvcm1hdHRlZFxuICogQHJldHVybnMgRm9ybWF0dGVkIHBsYWNlaG9sZGVyIG5hbWVcbiAqL1xuZXhwb3J0IGZ1bmN0aW9uIGZvcm1hdEkxOG5QbGFjZWhvbGRlck5hbWUobmFtZTogc3RyaW5nLCB1c2VDYW1lbENhc2U6IGJvb2xlYW4gPSB0cnVlKTogc3RyaW5nIHtcbiAgY29uc3QgcHVibGljTmFtZSA9IHRvUHVibGljTmFtZShuYW1lKTtcbiAgaWYgKCF1c2VDYW1lbENhc2UpIHtcbiAgICByZXR1cm4gcHVibGljTmFtZTtcbiAgfVxuICBjb25zdCBjaHVua3MgPSBwdWJsaWNOYW1lLnNwbGl0KCdfJyk7XG4gIGlmIChjaHVua3MubGVuZ3RoID09PSAxKSB7XG4gICAgLy8gaWYgbm8gXCJfXCIgZm91bmQgLSBqdXN0IGxvd2VyY2FzZSB0aGUgdmFsdWVcbiAgICByZXR1cm4gbmFtZS50b0xvd2VyQ2FzZSgpO1xuICB9XG4gIGxldCBwb3N0Zml4O1xuICAvLyBlamVjdCBsYXN0IGVsZW1lbnQgaWYgaXQncyBhIG51bWJlclxuICBpZiAoL15cXGQrJC8udGVzdChjaHVua3NbY2h1bmtzLmxlbmd0aCAtIDFdKSkge1xuICAgIHBvc3RmaXggPSBjaHVua3MucG9wKCk7XG4gIH1cbiAgbGV0IHJhdyA9IGNodW5rcy5zaGlmdCgpIS50b0xvd2VyQ2FzZSgpO1xuICBpZiAoY2h1bmtzLmxlbmd0aCkge1xuICAgIHJhdyArPSBjaHVua3MubWFwKGMgPT4gYy5jaGFyQXQoMCkudG9VcHBlckNhc2UoKSArIGMuc2xpY2UoMSkudG9Mb3dlckNhc2UoKSkuam9pbignJyk7XG4gIH1cbiAgcmV0dXJuIHBvc3RmaXggPyBgJHtyYXd9XyR7cG9zdGZpeH1gIDogcmF3O1xufVxuXG4vKipcbiAqIEdlbmVyYXRlcyBhIHByZWZpeCBmb3IgdHJhbnNsYXRpb24gY29uc3QgbmFtZS5cbiAqXG4gKiBAcGFyYW0gZXh0cmEgQWRkaXRpb25hbCBsb2NhbCBwcmVmaXggdGhhdCBzaG91bGQgYmUgaW5qZWN0ZWQgaW50byB0cmFuc2xhdGlvbiB2YXIgbmFtZVxuICogQHJldHVybnMgQ29tcGxldGUgdHJhbnNsYXRpb24gY29uc3QgcHJlZml4XG4gKi9cbmV4cG9ydCBmdW5jdGlvbiBnZXRUcmFuc2xhdGlvbkNvbnN0UHJlZml4KGV4dHJhOiBzdHJpbmcpOiBzdHJpbmcge1xuICByZXR1cm4gYCR7Q0xPU1VSRV9UUkFOU0xBVElPTl9WQVJfUFJFRklYfSR7ZXh0cmF9YC50b1VwcGVyQ2FzZSgpO1xufVxuXG4vKipcbiAqIEdlbmVyYXRlIEFTVCB0byBkZWNsYXJlIGEgdmFyaWFibGUuIEUuZy4gYHZhciBJMThOXzE7YC5cbiAqIEBwYXJhbSB2YXJpYWJsZSB0aGUgbmFtZSBvZiB0aGUgdmFyaWFibGUgdG8gZGVjbGFyZS5cbiAqL1xuZXhwb3J0IGZ1bmN0aW9uIGRlY2xhcmVJMThuVmFyaWFibGUodmFyaWFibGU6IG8uUmVhZFZhckV4cHIpOiBvLlN0YXRlbWVudCB7XG4gIHJldHVybiBuZXcgby5EZWNsYXJlVmFyU3RtdChcbiAgICAgIHZhcmlhYmxlLm5hbWUhLCB1bmRlZmluZWQsIG8uSU5GRVJSRURfVFlQRSwgdW5kZWZpbmVkLCB2YXJpYWJsZS5zb3VyY2VTcGFuKTtcbn1cbiJdfQ==