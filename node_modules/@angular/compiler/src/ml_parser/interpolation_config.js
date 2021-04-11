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
        define("@angular/compiler/src/ml_parser/interpolation_config", ["require", "exports", "@angular/compiler/src/assertions"], factory);
    }
})(function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.DEFAULT_INTERPOLATION_CONFIG = exports.InterpolationConfig = void 0;
    var assertions_1 = require("@angular/compiler/src/assertions");
    var InterpolationConfig = /** @class */ (function () {
        function InterpolationConfig(start, end) {
            this.start = start;
            this.end = end;
        }
        InterpolationConfig.fromArray = function (markers) {
            if (!markers) {
                return exports.DEFAULT_INTERPOLATION_CONFIG;
            }
            assertions_1.assertInterpolationSymbols('interpolation', markers);
            return new InterpolationConfig(markers[0], markers[1]);
        };
        return InterpolationConfig;
    }());
    exports.InterpolationConfig = InterpolationConfig;
    exports.DEFAULT_INTERPOLATION_CONFIG = new InterpolationConfig('{{', '}}');
});
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiaW50ZXJwb2xhdGlvbl9jb25maWcuanMiLCJzb3VyY2VSb290IjoiIiwic291cmNlcyI6WyIuLi8uLi8uLi8uLi8uLi8uLi8uLi9wYWNrYWdlcy9jb21waWxlci9zcmMvbWxfcGFyc2VyL2ludGVycG9sYXRpb25fY29uZmlnLnRzIl0sIm5hbWVzIjpbXSwibWFwcGluZ3MiOiJBQUFBOzs7Ozs7R0FNRzs7Ozs7Ozs7Ozs7OztJQUVILCtEQUF5RDtJQUV6RDtRQVVFLDZCQUFtQixLQUFhLEVBQVMsR0FBVztZQUFqQyxVQUFLLEdBQUwsS0FBSyxDQUFRO1lBQVMsUUFBRyxHQUFILEdBQUcsQ0FBUTtRQUFHLENBQUM7UUFUakQsNkJBQVMsR0FBaEIsVUFBaUIsT0FBOEI7WUFDN0MsSUFBSSxDQUFDLE9BQU8sRUFBRTtnQkFDWixPQUFPLG9DQUE0QixDQUFDO2FBQ3JDO1lBRUQsdUNBQTBCLENBQUMsZUFBZSxFQUFFLE9BQU8sQ0FBQyxDQUFDO1lBQ3JELE9BQU8sSUFBSSxtQkFBbUIsQ0FBQyxPQUFPLENBQUMsQ0FBQyxDQUFDLEVBQUUsT0FBTyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUM7UUFDekQsQ0FBQztRQUdILDBCQUFDO0lBQUQsQ0FBQyxBQVhELElBV0M7SUFYWSxrREFBbUI7SUFhbkIsUUFBQSw0QkFBNEIsR0FDckMsSUFBSSxtQkFBbUIsQ0FBQyxJQUFJLEVBQUUsSUFBSSxDQUFDLENBQUMiLCJzb3VyY2VzQ29udGVudCI6WyIvKipcbiAqIEBsaWNlbnNlXG4gKiBDb3B5cmlnaHQgR29vZ2xlIExMQyBBbGwgUmlnaHRzIFJlc2VydmVkLlxuICpcbiAqIFVzZSBvZiB0aGlzIHNvdXJjZSBjb2RlIGlzIGdvdmVybmVkIGJ5IGFuIE1JVC1zdHlsZSBsaWNlbnNlIHRoYXQgY2FuIGJlXG4gKiBmb3VuZCBpbiB0aGUgTElDRU5TRSBmaWxlIGF0IGh0dHBzOi8vYW5ndWxhci5pby9saWNlbnNlXG4gKi9cblxuaW1wb3J0IHthc3NlcnRJbnRlcnBvbGF0aW9uU3ltYm9sc30gZnJvbSAnLi4vYXNzZXJ0aW9ucyc7XG5cbmV4cG9ydCBjbGFzcyBJbnRlcnBvbGF0aW9uQ29uZmlnIHtcbiAgc3RhdGljIGZyb21BcnJheShtYXJrZXJzOiBbc3RyaW5nLCBzdHJpbmddfG51bGwpOiBJbnRlcnBvbGF0aW9uQ29uZmlnIHtcbiAgICBpZiAoIW1hcmtlcnMpIHtcbiAgICAgIHJldHVybiBERUZBVUxUX0lOVEVSUE9MQVRJT05fQ09ORklHO1xuICAgIH1cblxuICAgIGFzc2VydEludGVycG9sYXRpb25TeW1ib2xzKCdpbnRlcnBvbGF0aW9uJywgbWFya2Vycyk7XG4gICAgcmV0dXJuIG5ldyBJbnRlcnBvbGF0aW9uQ29uZmlnKG1hcmtlcnNbMF0sIG1hcmtlcnNbMV0pO1xuICB9XG5cbiAgY29uc3RydWN0b3IocHVibGljIHN0YXJ0OiBzdHJpbmcsIHB1YmxpYyBlbmQ6IHN0cmluZykge31cbn1cblxuZXhwb3J0IGNvbnN0IERFRkFVTFRfSU5URVJQT0xBVElPTl9DT05GSUc6IEludGVycG9sYXRpb25Db25maWcgPVxuICAgIG5ldyBJbnRlcnBvbGF0aW9uQ29uZmlnKCd7eycsICd9fScpO1xuIl19