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
        define("@angular/compiler/src/aot/formatted_error", ["require", "exports", "tslib", "@angular/compiler/src/util"], factory);
    }
})(function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.isFormattedError = exports.formattedError = void 0;
    var tslib_1 = require("tslib");
    var util_1 = require("@angular/compiler/src/util");
    var FORMATTED_MESSAGE = 'ngFormattedMessage';
    function indentStr(level) {
        if (level <= 0)
            return '';
        if (level < 6)
            return ['', ' ', '  ', '   ', '    ', '     '][level];
        var half = indentStr(Math.floor(level / 2));
        return half + half + (level % 2 === 1 ? ' ' : '');
    }
    function formatChain(chain, indent) {
        var e_1, _a;
        if (indent === void 0) { indent = 0; }
        if (!chain)
            return '';
        var position = chain.position ?
            chain.position.fileName + "(" + (chain.position.line + 1) + "," + (chain.position.column + 1) + ")" :
            '';
        var prefix = position && indent === 0 ? position + ": " : '';
        var postfix = position && indent !== 0 ? " at " + position : '';
        var message = "" + prefix + chain.message + postfix;
        if (chain.next) {
            try {
                for (var _b = tslib_1.__values(chain.next), _c = _b.next(); !_c.done; _c = _b.next()) {
                    var kid = _c.value;
                    message += '\n' + formatChain(kid, indent + 2);
                }
            }
            catch (e_1_1) { e_1 = { error: e_1_1 }; }
            finally {
                try {
                    if (_c && !_c.done && (_a = _b.return)) _a.call(_b);
                }
                finally { if (e_1) throw e_1.error; }
            }
        }
        return "" + indentStr(indent) + message;
    }
    function formattedError(chain) {
        var message = formatChain(chain) + '.';
        var error = util_1.syntaxError(message);
        error[FORMATTED_MESSAGE] = true;
        error.chain = chain;
        error.position = chain.position;
        return error;
    }
    exports.formattedError = formattedError;
    function isFormattedError(error) {
        return !!error[FORMATTED_MESSAGE];
    }
    exports.isFormattedError = isFormattedError;
});
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiZm9ybWF0dGVkX2Vycm9yLmpzIiwic291cmNlUm9vdCI6IiIsInNvdXJjZXMiOlsiLi4vLi4vLi4vLi4vLi4vLi4vLi4vcGFja2FnZXMvY29tcGlsZXIvc3JjL2FvdC9mb3JtYXR0ZWRfZXJyb3IudHMiXSwibmFtZXMiOltdLCJtYXBwaW5ncyI6IkFBQUE7Ozs7OztHQU1HOzs7Ozs7Ozs7Ozs7OztJQUVILG1EQUFvQztJQW1CcEMsSUFBTSxpQkFBaUIsR0FBRyxvQkFBb0IsQ0FBQztJQUUvQyxTQUFTLFNBQVMsQ0FBQyxLQUFhO1FBQzlCLElBQUksS0FBSyxJQUFJLENBQUM7WUFBRSxPQUFPLEVBQUUsQ0FBQztRQUMxQixJQUFJLEtBQUssR0FBRyxDQUFDO1lBQUUsT0FBTyxDQUFDLEVBQUUsRUFBRSxHQUFHLEVBQUUsSUFBSSxFQUFFLEtBQUssRUFBRSxNQUFNLEVBQUUsT0FBTyxDQUFDLENBQUMsS0FBSyxDQUFDLENBQUM7UUFDckUsSUFBTSxJQUFJLEdBQUcsU0FBUyxDQUFDLElBQUksQ0FBQyxLQUFLLENBQUMsS0FBSyxHQUFHLENBQUMsQ0FBQyxDQUFDLENBQUM7UUFDOUMsT0FBTyxJQUFJLEdBQUcsSUFBSSxHQUFHLENBQUMsS0FBSyxHQUFHLENBQUMsS0FBSyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUcsQ0FBQyxDQUFDLENBQUMsRUFBRSxDQUFDLENBQUM7SUFDcEQsQ0FBQztJQUVELFNBQVMsV0FBVyxDQUFDLEtBQXNDLEVBQUUsTUFBa0I7O1FBQWxCLHVCQUFBLEVBQUEsVUFBa0I7UUFDN0UsSUFBSSxDQUFDLEtBQUs7WUFBRSxPQUFPLEVBQUUsQ0FBQztRQUN0QixJQUFNLFFBQVEsR0FBRyxLQUFLLENBQUMsUUFBUSxDQUFDLENBQUM7WUFDMUIsS0FBSyxDQUFDLFFBQVEsQ0FBQyxRQUFRLFVBQUksS0FBSyxDQUFDLFFBQVEsQ0FBQyxJQUFJLEdBQUcsQ0FBQyxXQUFJLEtBQUssQ0FBQyxRQUFRLENBQUMsTUFBTSxHQUFHLENBQUMsT0FBRyxDQUFDLENBQUM7WUFDdkYsRUFBRSxDQUFDO1FBQ1AsSUFBTSxNQUFNLEdBQUcsUUFBUSxJQUFJLE1BQU0sS0FBSyxDQUFDLENBQUMsQ0FBQyxDQUFJLFFBQVEsT0FBSSxDQUFDLENBQUMsQ0FBQyxFQUFFLENBQUM7UUFDL0QsSUFBTSxPQUFPLEdBQUcsUUFBUSxJQUFJLE1BQU0sS0FBSyxDQUFDLENBQUMsQ0FBQyxDQUFDLFNBQU8sUUFBVSxDQUFDLENBQUMsQ0FBQyxFQUFFLENBQUM7UUFDbEUsSUFBSSxPQUFPLEdBQUcsS0FBRyxNQUFNLEdBQUcsS0FBSyxDQUFDLE9BQU8sR0FBRyxPQUFTLENBQUM7UUFFcEQsSUFBSSxLQUFLLENBQUMsSUFBSSxFQUFFOztnQkFDZCxLQUFrQixJQUFBLEtBQUEsaUJBQUEsS0FBSyxDQUFDLElBQUksQ0FBQSxnQkFBQSw0QkFBRTtvQkFBekIsSUFBTSxHQUFHLFdBQUE7b0JBQ1osT0FBTyxJQUFJLElBQUksR0FBRyxXQUFXLENBQUMsR0FBRyxFQUFFLE1BQU0sR0FBRyxDQUFDLENBQUMsQ0FBQztpQkFDaEQ7Ozs7Ozs7OztTQUNGO1FBRUQsT0FBTyxLQUFHLFNBQVMsQ0FBQyxNQUFNLENBQUMsR0FBRyxPQUFTLENBQUM7SUFDMUMsQ0FBQztJQUVELFNBQWdCLGNBQWMsQ0FBQyxLQUE0QjtRQUN6RCxJQUFNLE9BQU8sR0FBRyxXQUFXLENBQUMsS0FBSyxDQUFDLEdBQUcsR0FBRyxDQUFDO1FBQ3pDLElBQU0sS0FBSyxHQUFHLGtCQUFXLENBQUMsT0FBTyxDQUFtQixDQUFDO1FBQ3BELEtBQWEsQ0FBQyxpQkFBaUIsQ0FBQyxHQUFHLElBQUksQ0FBQztRQUN6QyxLQUFLLENBQUMsS0FBSyxHQUFHLEtBQUssQ0FBQztRQUNwQixLQUFLLENBQUMsUUFBUSxHQUFHLEtBQUssQ0FBQyxRQUFRLENBQUM7UUFDaEMsT0FBTyxLQUFLLENBQUM7SUFDZixDQUFDO0lBUEQsd0NBT0M7SUFFRCxTQUFnQixnQkFBZ0IsQ0FBQyxLQUFZO1FBQzNDLE9BQU8sQ0FBQyxDQUFFLEtBQWEsQ0FBQyxpQkFBaUIsQ0FBQyxDQUFDO0lBQzdDLENBQUM7SUFGRCw0Q0FFQyIsInNvdXJjZXNDb250ZW50IjpbIi8qKlxuICogQGxpY2Vuc2VcbiAqIENvcHlyaWdodCBHb29nbGUgTExDIEFsbCBSaWdodHMgUmVzZXJ2ZWQuXG4gKlxuICogVXNlIG9mIHRoaXMgc291cmNlIGNvZGUgaXMgZ292ZXJuZWQgYnkgYW4gTUlULXN0eWxlIGxpY2Vuc2UgdGhhdCBjYW4gYmVcbiAqIGZvdW5kIGluIHRoZSBMSUNFTlNFIGZpbGUgYXQgaHR0cHM6Ly9hbmd1bGFyLmlvL2xpY2Vuc2VcbiAqL1xuXG5pbXBvcnQge3N5bnRheEVycm9yfSBmcm9tICcuLi91dGlsJztcblxuZXhwb3J0IGludGVyZmFjZSBQb3NpdGlvbiB7XG4gIGZpbGVOYW1lOiBzdHJpbmc7XG4gIGxpbmU6IG51bWJlcjtcbiAgY29sdW1uOiBudW1iZXI7XG59XG5cbmV4cG9ydCBpbnRlcmZhY2UgRm9ybWF0dGVkTWVzc2FnZUNoYWluIHtcbiAgbWVzc2FnZTogc3RyaW5nO1xuICBwb3NpdGlvbj86IFBvc2l0aW9uO1xuICBuZXh0PzogRm9ybWF0dGVkTWVzc2FnZUNoYWluW107XG59XG5cbmV4cG9ydCB0eXBlIEZvcm1hdHRlZEVycm9yID0gRXJyb3Ime1xuICBjaGFpbjogRm9ybWF0dGVkTWVzc2FnZUNoYWluO1xuICBwb3NpdGlvbj86IFBvc2l0aW9uO1xufTtcblxuY29uc3QgRk9STUFUVEVEX01FU1NBR0UgPSAnbmdGb3JtYXR0ZWRNZXNzYWdlJztcblxuZnVuY3Rpb24gaW5kZW50U3RyKGxldmVsOiBudW1iZXIpOiBzdHJpbmcge1xuICBpZiAobGV2ZWwgPD0gMCkgcmV0dXJuICcnO1xuICBpZiAobGV2ZWwgPCA2KSByZXR1cm4gWycnLCAnICcsICcgICcsICcgICAnLCAnICAgICcsICcgICAgICddW2xldmVsXTtcbiAgY29uc3QgaGFsZiA9IGluZGVudFN0cihNYXRoLmZsb29yKGxldmVsIC8gMikpO1xuICByZXR1cm4gaGFsZiArIGhhbGYgKyAobGV2ZWwgJSAyID09PSAxID8gJyAnIDogJycpO1xufVxuXG5mdW5jdGlvbiBmb3JtYXRDaGFpbihjaGFpbjogRm9ybWF0dGVkTWVzc2FnZUNoYWlufHVuZGVmaW5lZCwgaW5kZW50OiBudW1iZXIgPSAwKTogc3RyaW5nIHtcbiAgaWYgKCFjaGFpbikgcmV0dXJuICcnO1xuICBjb25zdCBwb3NpdGlvbiA9IGNoYWluLnBvc2l0aW9uID9cbiAgICAgIGAke2NoYWluLnBvc2l0aW9uLmZpbGVOYW1lfSgke2NoYWluLnBvc2l0aW9uLmxpbmUgKyAxfSwke2NoYWluLnBvc2l0aW9uLmNvbHVtbiArIDF9KWAgOlxuICAgICAgJyc7XG4gIGNvbnN0IHByZWZpeCA9IHBvc2l0aW9uICYmIGluZGVudCA9PT0gMCA/IGAke3Bvc2l0aW9ufTogYCA6ICcnO1xuICBjb25zdCBwb3N0Zml4ID0gcG9zaXRpb24gJiYgaW5kZW50ICE9PSAwID8gYCBhdCAke3Bvc2l0aW9ufWAgOiAnJztcbiAgbGV0IG1lc3NhZ2UgPSBgJHtwcmVmaXh9JHtjaGFpbi5tZXNzYWdlfSR7cG9zdGZpeH1gO1xuXG4gIGlmIChjaGFpbi5uZXh0KSB7XG4gICAgZm9yIChjb25zdCBraWQgb2YgY2hhaW4ubmV4dCkge1xuICAgICAgbWVzc2FnZSArPSAnXFxuJyArIGZvcm1hdENoYWluKGtpZCwgaW5kZW50ICsgMik7XG4gICAgfVxuICB9XG5cbiAgcmV0dXJuIGAke2luZGVudFN0cihpbmRlbnQpfSR7bWVzc2FnZX1gO1xufVxuXG5leHBvcnQgZnVuY3Rpb24gZm9ybWF0dGVkRXJyb3IoY2hhaW46IEZvcm1hdHRlZE1lc3NhZ2VDaGFpbik6IEZvcm1hdHRlZEVycm9yIHtcbiAgY29uc3QgbWVzc2FnZSA9IGZvcm1hdENoYWluKGNoYWluKSArICcuJztcbiAgY29uc3QgZXJyb3IgPSBzeW50YXhFcnJvcihtZXNzYWdlKSBhcyBGb3JtYXR0ZWRFcnJvcjtcbiAgKGVycm9yIGFzIGFueSlbRk9STUFUVEVEX01FU1NBR0VdID0gdHJ1ZTtcbiAgZXJyb3IuY2hhaW4gPSBjaGFpbjtcbiAgZXJyb3IucG9zaXRpb24gPSBjaGFpbi5wb3NpdGlvbjtcbiAgcmV0dXJuIGVycm9yO1xufVxuXG5leHBvcnQgZnVuY3Rpb24gaXNGb3JtYXR0ZWRFcnJvcihlcnJvcjogRXJyb3IpOiBlcnJvciBpcyBGb3JtYXR0ZWRFcnJvciB7XG4gIHJldHVybiAhIShlcnJvciBhcyBhbnkpW0ZPUk1BVFRFRF9NRVNTQUdFXTtcbn1cbiJdfQ==