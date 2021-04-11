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
        define("@angular/compiler/src/i18n/i18n_html_parser", ["require", "exports", "tslib", "@angular/compiler/src/core", "@angular/compiler/src/ml_parser/interpolation_config", "@angular/compiler/src/ml_parser/parser", "@angular/compiler/src/i18n/digest", "@angular/compiler/src/i18n/extractor_merger", "@angular/compiler/src/i18n/serializers/xliff", "@angular/compiler/src/i18n/serializers/xliff2", "@angular/compiler/src/i18n/serializers/xmb", "@angular/compiler/src/i18n/serializers/xtb", "@angular/compiler/src/i18n/translation_bundle"], factory);
    }
})(function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.I18NHtmlParser = void 0;
    var tslib_1 = require("tslib");
    var core_1 = require("@angular/compiler/src/core");
    var interpolation_config_1 = require("@angular/compiler/src/ml_parser/interpolation_config");
    var parser_1 = require("@angular/compiler/src/ml_parser/parser");
    var digest_1 = require("@angular/compiler/src/i18n/digest");
    var extractor_merger_1 = require("@angular/compiler/src/i18n/extractor_merger");
    var xliff_1 = require("@angular/compiler/src/i18n/serializers/xliff");
    var xliff2_1 = require("@angular/compiler/src/i18n/serializers/xliff2");
    var xmb_1 = require("@angular/compiler/src/i18n/serializers/xmb");
    var xtb_1 = require("@angular/compiler/src/i18n/serializers/xtb");
    var translation_bundle_1 = require("@angular/compiler/src/i18n/translation_bundle");
    var I18NHtmlParser = /** @class */ (function () {
        function I18NHtmlParser(_htmlParser, translations, translationsFormat, missingTranslation, console) {
            if (missingTranslation === void 0) { missingTranslation = core_1.MissingTranslationStrategy.Warning; }
            this._htmlParser = _htmlParser;
            if (translations) {
                var serializer = createSerializer(translationsFormat);
                this._translationBundle =
                    translation_bundle_1.TranslationBundle.load(translations, 'i18n', serializer, missingTranslation, console);
            }
            else {
                this._translationBundle =
                    new translation_bundle_1.TranslationBundle({}, null, digest_1.digest, undefined, missingTranslation, console);
            }
        }
        I18NHtmlParser.prototype.parse = function (source, url, options) {
            if (options === void 0) { options = {}; }
            var interpolationConfig = options.interpolationConfig || interpolation_config_1.DEFAULT_INTERPOLATION_CONFIG;
            var parseResult = this._htmlParser.parse(source, url, tslib_1.__assign({ interpolationConfig: interpolationConfig }, options));
            if (parseResult.errors.length) {
                return new parser_1.ParseTreeResult(parseResult.rootNodes, parseResult.errors);
            }
            return extractor_merger_1.mergeTranslations(parseResult.rootNodes, this._translationBundle, interpolationConfig, [], {});
        };
        return I18NHtmlParser;
    }());
    exports.I18NHtmlParser = I18NHtmlParser;
    function createSerializer(format) {
        format = (format || 'xlf').toLowerCase();
        switch (format) {
            case 'xmb':
                return new xmb_1.Xmb();
            case 'xtb':
                return new xtb_1.Xtb();
            case 'xliff2':
            case 'xlf2':
                return new xliff2_1.Xliff2();
            case 'xliff':
            case 'xlf':
            default:
                return new xliff_1.Xliff();
        }
    }
});
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiaTE4bl9odG1sX3BhcnNlci5qcyIsInNvdXJjZVJvb3QiOiIiLCJzb3VyY2VzIjpbIi4uLy4uLy4uLy4uLy4uLy4uLy4uL3BhY2thZ2VzL2NvbXBpbGVyL3NyYy9pMThuL2kxOG5faHRtbF9wYXJzZXIudHMiXSwibmFtZXMiOltdLCJtYXBwaW5ncyI6IkFBQUE7Ozs7OztHQU1HOzs7Ozs7Ozs7Ozs7OztJQUVILG1EQUFtRDtJQUVuRCw2RkFBK0U7SUFFL0UsaUVBQW9EO0lBR3BELDREQUFnQztJQUNoQyxnRkFBcUQ7SUFFckQsc0VBQTBDO0lBQzFDLHdFQUE0QztJQUM1QyxrRUFBc0M7SUFDdEMsa0VBQXNDO0lBQ3RDLG9GQUF1RDtJQUV2RDtRQU1FLHdCQUNZLFdBQXVCLEVBQUUsWUFBcUIsRUFBRSxrQkFBMkIsRUFDbkYsa0JBQW1GLEVBQ25GLE9BQWlCO1lBRGpCLG1DQUFBLEVBQUEscUJBQWlELGlDQUEwQixDQUFDLE9BQU87WUFEM0UsZ0JBQVcsR0FBWCxXQUFXLENBQVk7WUFHakMsSUFBSSxZQUFZLEVBQUU7Z0JBQ2hCLElBQU0sVUFBVSxHQUFHLGdCQUFnQixDQUFDLGtCQUFrQixDQUFDLENBQUM7Z0JBQ3hELElBQUksQ0FBQyxrQkFBa0I7b0JBQ25CLHNDQUFpQixDQUFDLElBQUksQ0FBQyxZQUFZLEVBQUUsTUFBTSxFQUFFLFVBQVUsRUFBRSxrQkFBa0IsRUFBRSxPQUFPLENBQUMsQ0FBQzthQUMzRjtpQkFBTTtnQkFDTCxJQUFJLENBQUMsa0JBQWtCO29CQUNuQixJQUFJLHNDQUFpQixDQUFDLEVBQUUsRUFBRSxJQUFJLEVBQUUsZUFBTSxFQUFFLFNBQVMsRUFBRSxrQkFBa0IsRUFBRSxPQUFPLENBQUMsQ0FBQzthQUNyRjtRQUNILENBQUM7UUFFRCw4QkFBSyxHQUFMLFVBQU0sTUFBYyxFQUFFLEdBQVcsRUFBRSxPQUE2QjtZQUE3Qix3QkFBQSxFQUFBLFlBQTZCO1lBQzlELElBQU0sbUJBQW1CLEdBQUcsT0FBTyxDQUFDLG1CQUFtQixJQUFJLG1EQUE0QixDQUFDO1lBQ3hGLElBQU0sV0FBVyxHQUFHLElBQUksQ0FBQyxXQUFXLENBQUMsS0FBSyxDQUFDLE1BQU0sRUFBRSxHQUFHLHFCQUFHLG1CQUFtQixxQkFBQSxJQUFLLE9BQU8sRUFBRSxDQUFDO1lBRTNGLElBQUksV0FBVyxDQUFDLE1BQU0sQ0FBQyxNQUFNLEVBQUU7Z0JBQzdCLE9BQU8sSUFBSSx3QkFBZSxDQUFDLFdBQVcsQ0FBQyxTQUFTLEVBQUUsV0FBVyxDQUFDLE1BQU0sQ0FBQyxDQUFDO2FBQ3ZFO1lBRUQsT0FBTyxvQ0FBaUIsQ0FDcEIsV0FBVyxDQUFDLFNBQVMsRUFBRSxJQUFJLENBQUMsa0JBQWtCLEVBQUUsbUJBQW1CLEVBQUUsRUFBRSxFQUFFLEVBQUUsQ0FBQyxDQUFDO1FBQ25GLENBQUM7UUFDSCxxQkFBQztJQUFELENBQUMsQUEvQkQsSUErQkM7SUEvQlksd0NBQWM7SUFpQzNCLFNBQVMsZ0JBQWdCLENBQUMsTUFBZTtRQUN2QyxNQUFNLEdBQUcsQ0FBQyxNQUFNLElBQUksS0FBSyxDQUFDLENBQUMsV0FBVyxFQUFFLENBQUM7UUFFekMsUUFBUSxNQUFNLEVBQUU7WUFDZCxLQUFLLEtBQUs7Z0JBQ1IsT0FBTyxJQUFJLFNBQUcsRUFBRSxDQUFDO1lBQ25CLEtBQUssS0FBSztnQkFDUixPQUFPLElBQUksU0FBRyxFQUFFLENBQUM7WUFDbkIsS0FBSyxRQUFRLENBQUM7WUFDZCxLQUFLLE1BQU07Z0JBQ1QsT0FBTyxJQUFJLGVBQU0sRUFBRSxDQUFDO1lBQ3RCLEtBQUssT0FBTyxDQUFDO1lBQ2IsS0FBSyxLQUFLLENBQUM7WUFDWDtnQkFDRSxPQUFPLElBQUksYUFBSyxFQUFFLENBQUM7U0FDdEI7SUFDSCxDQUFDIiwic291cmNlc0NvbnRlbnQiOlsiLyoqXG4gKiBAbGljZW5zZVxuICogQ29weXJpZ2h0IEdvb2dsZSBMTEMgQWxsIFJpZ2h0cyBSZXNlcnZlZC5cbiAqXG4gKiBVc2Ugb2YgdGhpcyBzb3VyY2UgY29kZSBpcyBnb3Zlcm5lZCBieSBhbiBNSVQtc3R5bGUgbGljZW5zZSB0aGF0IGNhbiBiZVxuICogZm91bmQgaW4gdGhlIExJQ0VOU0UgZmlsZSBhdCBodHRwczovL2FuZ3VsYXIuaW8vbGljZW5zZVxuICovXG5cbmltcG9ydCB7TWlzc2luZ1RyYW5zbGF0aW9uU3RyYXRlZ3l9IGZyb20gJy4uL2NvcmUnO1xuaW1wb3J0IHtIdG1sUGFyc2VyfSBmcm9tICcuLi9tbF9wYXJzZXIvaHRtbF9wYXJzZXInO1xuaW1wb3J0IHtERUZBVUxUX0lOVEVSUE9MQVRJT05fQ09ORklHfSBmcm9tICcuLi9tbF9wYXJzZXIvaW50ZXJwb2xhdGlvbl9jb25maWcnO1xuaW1wb3J0IHtUb2tlbml6ZU9wdGlvbnN9IGZyb20gJy4uL21sX3BhcnNlci9sZXhlcic7XG5pbXBvcnQge1BhcnNlVHJlZVJlc3VsdH0gZnJvbSAnLi4vbWxfcGFyc2VyL3BhcnNlcic7XG5pbXBvcnQge0NvbnNvbGV9IGZyb20gJy4uL3V0aWwnO1xuXG5pbXBvcnQge2RpZ2VzdH0gZnJvbSAnLi9kaWdlc3QnO1xuaW1wb3J0IHttZXJnZVRyYW5zbGF0aW9uc30gZnJvbSAnLi9leHRyYWN0b3JfbWVyZ2VyJztcbmltcG9ydCB7U2VyaWFsaXplcn0gZnJvbSAnLi9zZXJpYWxpemVycy9zZXJpYWxpemVyJztcbmltcG9ydCB7WGxpZmZ9IGZyb20gJy4vc2VyaWFsaXplcnMveGxpZmYnO1xuaW1wb3J0IHtYbGlmZjJ9IGZyb20gJy4vc2VyaWFsaXplcnMveGxpZmYyJztcbmltcG9ydCB7WG1ifSBmcm9tICcuL3NlcmlhbGl6ZXJzL3htYic7XG5pbXBvcnQge1h0Yn0gZnJvbSAnLi9zZXJpYWxpemVycy94dGInO1xuaW1wb3J0IHtUcmFuc2xhdGlvbkJ1bmRsZX0gZnJvbSAnLi90cmFuc2xhdGlvbl9idW5kbGUnO1xuXG5leHBvcnQgY2xhc3MgSTE4Tkh0bWxQYXJzZXIgaW1wbGVtZW50cyBIdG1sUGFyc2VyIHtcbiAgLy8gQG92ZXJyaWRlXG4gIGdldFRhZ0RlZmluaXRpb246IGFueTtcblxuICBwcml2YXRlIF90cmFuc2xhdGlvbkJ1bmRsZTogVHJhbnNsYXRpb25CdW5kbGU7XG5cbiAgY29uc3RydWN0b3IoXG4gICAgICBwcml2YXRlIF9odG1sUGFyc2VyOiBIdG1sUGFyc2VyLCB0cmFuc2xhdGlvbnM/OiBzdHJpbmcsIHRyYW5zbGF0aW9uc0Zvcm1hdD86IHN0cmluZyxcbiAgICAgIG1pc3NpbmdUcmFuc2xhdGlvbjogTWlzc2luZ1RyYW5zbGF0aW9uU3RyYXRlZ3kgPSBNaXNzaW5nVHJhbnNsYXRpb25TdHJhdGVneS5XYXJuaW5nLFxuICAgICAgY29uc29sZT86IENvbnNvbGUpIHtcbiAgICBpZiAodHJhbnNsYXRpb25zKSB7XG4gICAgICBjb25zdCBzZXJpYWxpemVyID0gY3JlYXRlU2VyaWFsaXplcih0cmFuc2xhdGlvbnNGb3JtYXQpO1xuICAgICAgdGhpcy5fdHJhbnNsYXRpb25CdW5kbGUgPVxuICAgICAgICAgIFRyYW5zbGF0aW9uQnVuZGxlLmxvYWQodHJhbnNsYXRpb25zLCAnaTE4bicsIHNlcmlhbGl6ZXIsIG1pc3NpbmdUcmFuc2xhdGlvbiwgY29uc29sZSk7XG4gICAgfSBlbHNlIHtcbiAgICAgIHRoaXMuX3RyYW5zbGF0aW9uQnVuZGxlID1cbiAgICAgICAgICBuZXcgVHJhbnNsYXRpb25CdW5kbGUoe30sIG51bGwsIGRpZ2VzdCwgdW5kZWZpbmVkLCBtaXNzaW5nVHJhbnNsYXRpb24sIGNvbnNvbGUpO1xuICAgIH1cbiAgfVxuXG4gIHBhcnNlKHNvdXJjZTogc3RyaW5nLCB1cmw6IHN0cmluZywgb3B0aW9uczogVG9rZW5pemVPcHRpb25zID0ge30pOiBQYXJzZVRyZWVSZXN1bHQge1xuICAgIGNvbnN0IGludGVycG9sYXRpb25Db25maWcgPSBvcHRpb25zLmludGVycG9sYXRpb25Db25maWcgfHwgREVGQVVMVF9JTlRFUlBPTEFUSU9OX0NPTkZJRztcbiAgICBjb25zdCBwYXJzZVJlc3VsdCA9IHRoaXMuX2h0bWxQYXJzZXIucGFyc2Uoc291cmNlLCB1cmwsIHtpbnRlcnBvbGF0aW9uQ29uZmlnLCAuLi5vcHRpb25zfSk7XG5cbiAgICBpZiAocGFyc2VSZXN1bHQuZXJyb3JzLmxlbmd0aCkge1xuICAgICAgcmV0dXJuIG5ldyBQYXJzZVRyZWVSZXN1bHQocGFyc2VSZXN1bHQucm9vdE5vZGVzLCBwYXJzZVJlc3VsdC5lcnJvcnMpO1xuICAgIH1cblxuICAgIHJldHVybiBtZXJnZVRyYW5zbGF0aW9ucyhcbiAgICAgICAgcGFyc2VSZXN1bHQucm9vdE5vZGVzLCB0aGlzLl90cmFuc2xhdGlvbkJ1bmRsZSwgaW50ZXJwb2xhdGlvbkNvbmZpZywgW10sIHt9KTtcbiAgfVxufVxuXG5mdW5jdGlvbiBjcmVhdGVTZXJpYWxpemVyKGZvcm1hdD86IHN0cmluZyk6IFNlcmlhbGl6ZXIge1xuICBmb3JtYXQgPSAoZm9ybWF0IHx8ICd4bGYnKS50b0xvd2VyQ2FzZSgpO1xuXG4gIHN3aXRjaCAoZm9ybWF0KSB7XG4gICAgY2FzZSAneG1iJzpcbiAgICAgIHJldHVybiBuZXcgWG1iKCk7XG4gICAgY2FzZSAneHRiJzpcbiAgICAgIHJldHVybiBuZXcgWHRiKCk7XG4gICAgY2FzZSAneGxpZmYyJzpcbiAgICBjYXNlICd4bGYyJzpcbiAgICAgIHJldHVybiBuZXcgWGxpZmYyKCk7XG4gICAgY2FzZSAneGxpZmYnOlxuICAgIGNhc2UgJ3hsZic6XG4gICAgZGVmYXVsdDpcbiAgICAgIHJldHVybiBuZXcgWGxpZmYoKTtcbiAgfVxufVxuIl19