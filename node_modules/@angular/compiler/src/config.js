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
        define("@angular/compiler/src/config", ["require", "exports", "@angular/compiler/src/core", "@angular/compiler/src/util"], factory);
    }
})(function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.preserveWhitespacesDefault = exports.CompilerConfig = void 0;
    var core_1 = require("@angular/compiler/src/core");
    var util_1 = require("@angular/compiler/src/util");
    var CompilerConfig = /** @class */ (function () {
        function CompilerConfig(_a) {
            var _b = _a === void 0 ? {} : _a, _c = _b.defaultEncapsulation, defaultEncapsulation = _c === void 0 ? core_1.ViewEncapsulation.Emulated : _c, _d = _b.useJit, useJit = _d === void 0 ? true : _d, _e = _b.jitDevMode, jitDevMode = _e === void 0 ? false : _e, _f = _b.missingTranslation, missingTranslation = _f === void 0 ? null : _f, preserveWhitespaces = _b.preserveWhitespaces, strictInjectionParameters = _b.strictInjectionParameters;
            this.defaultEncapsulation = defaultEncapsulation;
            this.useJit = !!useJit;
            this.jitDevMode = !!jitDevMode;
            this.missingTranslation = missingTranslation;
            this.preserveWhitespaces = preserveWhitespacesDefault(util_1.noUndefined(preserveWhitespaces));
            this.strictInjectionParameters = strictInjectionParameters === true;
        }
        return CompilerConfig;
    }());
    exports.CompilerConfig = CompilerConfig;
    function preserveWhitespacesDefault(preserveWhitespacesOption, defaultSetting) {
        if (defaultSetting === void 0) { defaultSetting = false; }
        return preserveWhitespacesOption === null ? defaultSetting : preserveWhitespacesOption;
    }
    exports.preserveWhitespacesDefault = preserveWhitespacesDefault;
});
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiY29uZmlnLmpzIiwic291cmNlUm9vdCI6IiIsInNvdXJjZXMiOlsiLi4vLi4vLi4vLi4vLi4vLi4vcGFja2FnZXMvY29tcGlsZXIvc3JjL2NvbmZpZy50cyJdLCJuYW1lcyI6W10sIm1hcHBpbmdzIjoiQUFBQTs7Ozs7O0dBTUc7Ozs7Ozs7Ozs7Ozs7SUFFSCxtREFBcUU7SUFDckUsbURBQW1DO0lBRW5DO1FBUUUsd0JBQVksRUFjTjtnQkFkTSxxQkFjUixFQUFFLEtBQUEsRUFiSiw0QkFBaUQsRUFBakQsb0JBQW9CLG1CQUFHLHdCQUFpQixDQUFDLFFBQVEsS0FBQSxFQUNqRCxjQUFhLEVBQWIsTUFBTSxtQkFBRyxJQUFJLEtBQUEsRUFDYixrQkFBa0IsRUFBbEIsVUFBVSxtQkFBRyxLQUFLLEtBQUEsRUFDbEIsMEJBQXlCLEVBQXpCLGtCQUFrQixtQkFBRyxJQUFJLEtBQUEsRUFDekIsbUJBQW1CLHlCQUFBLEVBQ25CLHlCQUF5QiwrQkFBQTtZQVN6QixJQUFJLENBQUMsb0JBQW9CLEdBQUcsb0JBQW9CLENBQUM7WUFDakQsSUFBSSxDQUFDLE1BQU0sR0FBRyxDQUFDLENBQUMsTUFBTSxDQUFDO1lBQ3ZCLElBQUksQ0FBQyxVQUFVLEdBQUcsQ0FBQyxDQUFDLFVBQVUsQ0FBQztZQUMvQixJQUFJLENBQUMsa0JBQWtCLEdBQUcsa0JBQWtCLENBQUM7WUFDN0MsSUFBSSxDQUFDLG1CQUFtQixHQUFHLDBCQUEwQixDQUFDLGtCQUFXLENBQUMsbUJBQW1CLENBQUMsQ0FBQyxDQUFDO1lBQ3hGLElBQUksQ0FBQyx5QkFBeUIsR0FBRyx5QkFBeUIsS0FBSyxJQUFJLENBQUM7UUFDdEUsQ0FBQztRQUNILHFCQUFDO0lBQUQsQ0FBQyxBQTlCRCxJQThCQztJQTlCWSx3Q0FBYztJQWdDM0IsU0FBZ0IsMEJBQTBCLENBQ3RDLHlCQUF1QyxFQUFFLGNBQXNCO1FBQXRCLCtCQUFBLEVBQUEsc0JBQXNCO1FBQ2pFLE9BQU8seUJBQXlCLEtBQUssSUFBSSxDQUFDLENBQUMsQ0FBQyxjQUFjLENBQUMsQ0FBQyxDQUFDLHlCQUF5QixDQUFDO0lBQ3pGLENBQUM7SUFIRCxnRUFHQyIsInNvdXJjZXNDb250ZW50IjpbIi8qKlxuICogQGxpY2Vuc2VcbiAqIENvcHlyaWdodCBHb29nbGUgTExDIEFsbCBSaWdodHMgUmVzZXJ2ZWQuXG4gKlxuICogVXNlIG9mIHRoaXMgc291cmNlIGNvZGUgaXMgZ292ZXJuZWQgYnkgYW4gTUlULXN0eWxlIGxpY2Vuc2UgdGhhdCBjYW4gYmVcbiAqIGZvdW5kIGluIHRoZSBMSUNFTlNFIGZpbGUgYXQgaHR0cHM6Ly9hbmd1bGFyLmlvL2xpY2Vuc2VcbiAqL1xuXG5pbXBvcnQge01pc3NpbmdUcmFuc2xhdGlvblN0cmF0ZWd5LCBWaWV3RW5jYXBzdWxhdGlvbn0gZnJvbSAnLi9jb3JlJztcbmltcG9ydCB7bm9VbmRlZmluZWR9IGZyb20gJy4vdXRpbCc7XG5cbmV4cG9ydCBjbGFzcyBDb21waWxlckNvbmZpZyB7XG4gIHB1YmxpYyBkZWZhdWx0RW5jYXBzdWxhdGlvbjogVmlld0VuY2Fwc3VsYXRpb258bnVsbDtcbiAgcHVibGljIHVzZUppdDogYm9vbGVhbjtcbiAgcHVibGljIGppdERldk1vZGU6IGJvb2xlYW47XG4gIHB1YmxpYyBtaXNzaW5nVHJhbnNsYXRpb246IE1pc3NpbmdUcmFuc2xhdGlvblN0cmF0ZWd5fG51bGw7XG4gIHB1YmxpYyBwcmVzZXJ2ZVdoaXRlc3BhY2VzOiBib29sZWFuO1xuICBwdWJsaWMgc3RyaWN0SW5qZWN0aW9uUGFyYW1ldGVyczogYm9vbGVhbjtcblxuICBjb25zdHJ1Y3Rvcih7XG4gICAgZGVmYXVsdEVuY2Fwc3VsYXRpb24gPSBWaWV3RW5jYXBzdWxhdGlvbi5FbXVsYXRlZCxcbiAgICB1c2VKaXQgPSB0cnVlLFxuICAgIGppdERldk1vZGUgPSBmYWxzZSxcbiAgICBtaXNzaW5nVHJhbnNsYXRpb24gPSBudWxsLFxuICAgIHByZXNlcnZlV2hpdGVzcGFjZXMsXG4gICAgc3RyaWN0SW5qZWN0aW9uUGFyYW1ldGVyc1xuICB9OiB7XG4gICAgZGVmYXVsdEVuY2Fwc3VsYXRpb24/OiBWaWV3RW5jYXBzdWxhdGlvbixcbiAgICB1c2VKaXQ/OiBib29sZWFuLFxuICAgIGppdERldk1vZGU/OiBib29sZWFuLFxuICAgIG1pc3NpbmdUcmFuc2xhdGlvbj86IE1pc3NpbmdUcmFuc2xhdGlvblN0cmF0ZWd5fG51bGwsXG4gICAgcHJlc2VydmVXaGl0ZXNwYWNlcz86IGJvb2xlYW4sXG4gICAgc3RyaWN0SW5qZWN0aW9uUGFyYW1ldGVycz86IGJvb2xlYW4sXG4gIH0gPSB7fSkge1xuICAgIHRoaXMuZGVmYXVsdEVuY2Fwc3VsYXRpb24gPSBkZWZhdWx0RW5jYXBzdWxhdGlvbjtcbiAgICB0aGlzLnVzZUppdCA9ICEhdXNlSml0O1xuICAgIHRoaXMuaml0RGV2TW9kZSA9ICEhaml0RGV2TW9kZTtcbiAgICB0aGlzLm1pc3NpbmdUcmFuc2xhdGlvbiA9IG1pc3NpbmdUcmFuc2xhdGlvbjtcbiAgICB0aGlzLnByZXNlcnZlV2hpdGVzcGFjZXMgPSBwcmVzZXJ2ZVdoaXRlc3BhY2VzRGVmYXVsdChub1VuZGVmaW5lZChwcmVzZXJ2ZVdoaXRlc3BhY2VzKSk7XG4gICAgdGhpcy5zdHJpY3RJbmplY3Rpb25QYXJhbWV0ZXJzID0gc3RyaWN0SW5qZWN0aW9uUGFyYW1ldGVycyA9PT0gdHJ1ZTtcbiAgfVxufVxuXG5leHBvcnQgZnVuY3Rpb24gcHJlc2VydmVXaGl0ZXNwYWNlc0RlZmF1bHQoXG4gICAgcHJlc2VydmVXaGl0ZXNwYWNlc09wdGlvbjogYm9vbGVhbnxudWxsLCBkZWZhdWx0U2V0dGluZyA9IGZhbHNlKTogYm9vbGVhbiB7XG4gIHJldHVybiBwcmVzZXJ2ZVdoaXRlc3BhY2VzT3B0aW9uID09PSBudWxsID8gZGVmYXVsdFNldHRpbmcgOiBwcmVzZXJ2ZVdoaXRlc3BhY2VzT3B0aW9uO1xufVxuIl19