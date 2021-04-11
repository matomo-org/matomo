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
        define("@angular/compiler/src/directive_normalizer", ["require", "exports", "tslib", "@angular/compiler/src/compile_metadata", "@angular/compiler/src/config", "@angular/compiler/src/core", "@angular/compiler/src/ml_parser/ast", "@angular/compiler/src/ml_parser/interpolation_config", "@angular/compiler/src/style_url_resolver", "@angular/compiler/src/template_parser/template_preparser", "@angular/compiler/src/util"], factory);
    }
})(function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.DirectiveNormalizer = void 0;
    var tslib_1 = require("tslib");
    var compile_metadata_1 = require("@angular/compiler/src/compile_metadata");
    var config_1 = require("@angular/compiler/src/config");
    var core_1 = require("@angular/compiler/src/core");
    var html = require("@angular/compiler/src/ml_parser/ast");
    var interpolation_config_1 = require("@angular/compiler/src/ml_parser/interpolation_config");
    var style_url_resolver_1 = require("@angular/compiler/src/style_url_resolver");
    var template_preparser_1 = require("@angular/compiler/src/template_parser/template_preparser");
    var util_1 = require("@angular/compiler/src/util");
    var DirectiveNormalizer = /** @class */ (function () {
        function DirectiveNormalizer(_resourceLoader, _urlResolver, _htmlParser, _config) {
            this._resourceLoader = _resourceLoader;
            this._urlResolver = _urlResolver;
            this._htmlParser = _htmlParser;
            this._config = _config;
            this._resourceLoaderCache = new Map();
        }
        DirectiveNormalizer.prototype.clearCache = function () {
            this._resourceLoaderCache.clear();
        };
        DirectiveNormalizer.prototype.clearCacheFor = function (normalizedDirective) {
            var _this = this;
            if (!normalizedDirective.isComponent) {
                return;
            }
            var template = normalizedDirective.template;
            this._resourceLoaderCache.delete(template.templateUrl);
            template.externalStylesheets.forEach(function (stylesheet) {
                _this._resourceLoaderCache.delete(stylesheet.moduleUrl);
            });
        };
        DirectiveNormalizer.prototype._fetch = function (url) {
            var result = this._resourceLoaderCache.get(url);
            if (!result) {
                result = this._resourceLoader.get(url);
                this._resourceLoaderCache.set(url, result);
            }
            return result;
        };
        DirectiveNormalizer.prototype.normalizeTemplate = function (prenormData) {
            var _this = this;
            if (util_1.isDefined(prenormData.template)) {
                if (util_1.isDefined(prenormData.templateUrl)) {
                    throw util_1.syntaxError("'" + util_1.stringify(prenormData
                        .componentType) + "' component cannot define both template and templateUrl");
                }
                if (typeof prenormData.template !== 'string') {
                    throw util_1.syntaxError("The template specified for component " + util_1.stringify(prenormData.componentType) + " is not a string");
                }
            }
            else if (util_1.isDefined(prenormData.templateUrl)) {
                if (typeof prenormData.templateUrl !== 'string') {
                    throw util_1.syntaxError("The templateUrl specified for component " + util_1.stringify(prenormData.componentType) + " is not a string");
                }
            }
            else {
                throw util_1.syntaxError("No template specified for component " + util_1.stringify(prenormData.componentType));
            }
            if (util_1.isDefined(prenormData.preserveWhitespaces) &&
                typeof prenormData.preserveWhitespaces !== 'boolean') {
                throw util_1.syntaxError("The preserveWhitespaces option for component " + util_1.stringify(prenormData.componentType) + " must be a boolean");
            }
            return util_1.SyncAsync.then(this._preParseTemplate(prenormData), function (preparsedTemplate) { return _this._normalizeTemplateMetadata(prenormData, preparsedTemplate); });
        };
        DirectiveNormalizer.prototype._preParseTemplate = function (prenomData) {
            var _this = this;
            var template;
            var templateUrl;
            if (prenomData.template != null) {
                template = prenomData.template;
                templateUrl = prenomData.moduleUrl;
            }
            else {
                templateUrl = this._urlResolver.resolve(prenomData.moduleUrl, prenomData.templateUrl);
                template = this._fetch(templateUrl);
            }
            return util_1.SyncAsync.then(template, function (template) { return _this._preparseLoadedTemplate(prenomData, template, templateUrl); });
        };
        DirectiveNormalizer.prototype._preparseLoadedTemplate = function (prenormData, template, templateAbsUrl) {
            var isInline = !!prenormData.template;
            var interpolationConfig = interpolation_config_1.InterpolationConfig.fromArray(prenormData.interpolation);
            var templateUrl = compile_metadata_1.templateSourceUrl({ reference: prenormData.ngModuleType }, { type: { reference: prenormData.componentType } }, { isInline: isInline, templateUrl: templateAbsUrl });
            var rootNodesAndErrors = this._htmlParser.parse(template, templateUrl, { tokenizeExpansionForms: true, interpolationConfig: interpolationConfig });
            if (rootNodesAndErrors.errors.length > 0) {
                var errorString = rootNodesAndErrors.errors.join('\n');
                throw util_1.syntaxError("Template parse errors:\n" + errorString);
            }
            var templateMetadataStyles = this._normalizeStylesheet(new compile_metadata_1.CompileStylesheetMetadata({ styles: prenormData.styles, moduleUrl: prenormData.moduleUrl }));
            var visitor = new TemplatePreparseVisitor();
            html.visitAll(visitor, rootNodesAndErrors.rootNodes);
            var templateStyles = this._normalizeStylesheet(new compile_metadata_1.CompileStylesheetMetadata({ styles: visitor.styles, styleUrls: visitor.styleUrls, moduleUrl: templateAbsUrl }));
            var styles = templateMetadataStyles.styles.concat(templateStyles.styles);
            var inlineStyleUrls = templateMetadataStyles.styleUrls.concat(templateStyles.styleUrls);
            var styleUrls = this
                ._normalizeStylesheet(new compile_metadata_1.CompileStylesheetMetadata({ styleUrls: prenormData.styleUrls, moduleUrl: prenormData.moduleUrl }))
                .styleUrls;
            return {
                template: template,
                templateUrl: templateAbsUrl,
                isInline: isInline,
                htmlAst: rootNodesAndErrors,
                styles: styles,
                inlineStyleUrls: inlineStyleUrls,
                styleUrls: styleUrls,
                ngContentSelectors: visitor.ngContentSelectors,
            };
        };
        DirectiveNormalizer.prototype._normalizeTemplateMetadata = function (prenormData, preparsedTemplate) {
            var _this = this;
            return util_1.SyncAsync.then(this._loadMissingExternalStylesheets(preparsedTemplate.styleUrls.concat(preparsedTemplate.inlineStyleUrls)), function (externalStylesheets) { return _this._normalizeLoadedTemplateMetadata(prenormData, preparsedTemplate, externalStylesheets); });
        };
        DirectiveNormalizer.prototype._normalizeLoadedTemplateMetadata = function (prenormData, preparsedTemplate, stylesheets) {
            // Algorithm:
            // - produce exactly 1 entry per original styleUrl in
            // CompileTemplateMetadata.externalStylesheets with all styles inlined
            // - inline all styles that are referenced by the template into CompileTemplateMetadata.styles.
            // Reason: be able to determine how many stylesheets there are even without loading
            // the template nor the stylesheets, so we can create a stub for TypeScript always synchronously
            // (as resource loading may be async)
            var _this = this;
            var styles = tslib_1.__spread(preparsedTemplate.styles);
            this._inlineStyles(preparsedTemplate.inlineStyleUrls, stylesheets, styles);
            var styleUrls = preparsedTemplate.styleUrls;
            var externalStylesheets = styleUrls.map(function (styleUrl) {
                var stylesheet = stylesheets.get(styleUrl);
                var styles = tslib_1.__spread(stylesheet.styles);
                _this._inlineStyles(stylesheet.styleUrls, stylesheets, styles);
                return new compile_metadata_1.CompileStylesheetMetadata({ moduleUrl: styleUrl, styles: styles });
            });
            var encapsulation = prenormData.encapsulation;
            if (encapsulation == null) {
                encapsulation = this._config.defaultEncapsulation;
            }
            if (encapsulation === core_1.ViewEncapsulation.Emulated && styles.length === 0 &&
                styleUrls.length === 0) {
                encapsulation = core_1.ViewEncapsulation.None;
            }
            return new compile_metadata_1.CompileTemplateMetadata({
                encapsulation: encapsulation,
                template: preparsedTemplate.template,
                templateUrl: preparsedTemplate.templateUrl,
                htmlAst: preparsedTemplate.htmlAst,
                styles: styles,
                styleUrls: styleUrls,
                ngContentSelectors: preparsedTemplate.ngContentSelectors,
                animations: prenormData.animations,
                interpolation: prenormData.interpolation,
                isInline: preparsedTemplate.isInline,
                externalStylesheets: externalStylesheets,
                preserveWhitespaces: config_1.preserveWhitespacesDefault(prenormData.preserveWhitespaces, this._config.preserveWhitespaces),
            });
        };
        DirectiveNormalizer.prototype._inlineStyles = function (styleUrls, stylesheets, targetStyles) {
            var _this = this;
            styleUrls.forEach(function (styleUrl) {
                var stylesheet = stylesheets.get(styleUrl);
                stylesheet.styles.forEach(function (style) { return targetStyles.push(style); });
                _this._inlineStyles(stylesheet.styleUrls, stylesheets, targetStyles);
            });
        };
        DirectiveNormalizer.prototype._loadMissingExternalStylesheets = function (styleUrls, loadedStylesheets) {
            var _this = this;
            if (loadedStylesheets === void 0) { loadedStylesheets = new Map(); }
            return util_1.SyncAsync.then(util_1.SyncAsync.all(styleUrls.filter(function (styleUrl) { return !loadedStylesheets.has(styleUrl); })
                .map(function (styleUrl) { return util_1.SyncAsync.then(_this._fetch(styleUrl), function (loadedStyle) {
                var stylesheet = _this._normalizeStylesheet(new compile_metadata_1.CompileStylesheetMetadata({ styles: [loadedStyle], moduleUrl: styleUrl }));
                loadedStylesheets.set(styleUrl, stylesheet);
                return _this._loadMissingExternalStylesheets(stylesheet.styleUrls, loadedStylesheets);
            }); })), function (_) { return loadedStylesheets; });
        };
        DirectiveNormalizer.prototype._normalizeStylesheet = function (stylesheet) {
            var _this = this;
            var moduleUrl = stylesheet.moduleUrl;
            var allStyleUrls = stylesheet.styleUrls.filter(style_url_resolver_1.isStyleUrlResolvable)
                .map(function (url) { return _this._urlResolver.resolve(moduleUrl, url); });
            var allStyles = stylesheet.styles.map(function (style) {
                var styleWithImports = style_url_resolver_1.extractStyleUrls(_this._urlResolver, moduleUrl, style);
                allStyleUrls.push.apply(allStyleUrls, tslib_1.__spread(styleWithImports.styleUrls));
                return styleWithImports.style;
            });
            return new compile_metadata_1.CompileStylesheetMetadata({ styles: allStyles, styleUrls: allStyleUrls, moduleUrl: moduleUrl });
        };
        return DirectiveNormalizer;
    }());
    exports.DirectiveNormalizer = DirectiveNormalizer;
    var TemplatePreparseVisitor = /** @class */ (function () {
        function TemplatePreparseVisitor() {
            this.ngContentSelectors = [];
            this.styles = [];
            this.styleUrls = [];
            this.ngNonBindableStackCount = 0;
        }
        TemplatePreparseVisitor.prototype.visitElement = function (ast, context) {
            var preparsedElement = template_preparser_1.preparseElement(ast);
            switch (preparsedElement.type) {
                case template_preparser_1.PreparsedElementType.NG_CONTENT:
                    if (this.ngNonBindableStackCount === 0) {
                        this.ngContentSelectors.push(preparsedElement.selectAttr);
                    }
                    break;
                case template_preparser_1.PreparsedElementType.STYLE:
                    var textContent_1 = '';
                    ast.children.forEach(function (child) {
                        if (child instanceof html.Text) {
                            textContent_1 += child.value;
                        }
                    });
                    this.styles.push(textContent_1);
                    break;
                case template_preparser_1.PreparsedElementType.STYLESHEET:
                    this.styleUrls.push(preparsedElement.hrefAttr);
                    break;
                default:
                    break;
            }
            if (preparsedElement.nonBindable) {
                this.ngNonBindableStackCount++;
            }
            html.visitAll(this, ast.children);
            if (preparsedElement.nonBindable) {
                this.ngNonBindableStackCount--;
            }
            return null;
        };
        TemplatePreparseVisitor.prototype.visitExpansion = function (ast, context) {
            html.visitAll(this, ast.cases);
        };
        TemplatePreparseVisitor.prototype.visitExpansionCase = function (ast, context) {
            html.visitAll(this, ast.expression);
        };
        TemplatePreparseVisitor.prototype.visitComment = function (ast, context) {
            return null;
        };
        TemplatePreparseVisitor.prototype.visitAttribute = function (ast, context) {
            return null;
        };
        TemplatePreparseVisitor.prototype.visitText = function (ast, context) {
            return null;
        };
        return TemplatePreparseVisitor;
    }());
});
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiZGlyZWN0aXZlX25vcm1hbGl6ZXIuanMiLCJzb3VyY2VSb290IjoiIiwic291cmNlcyI6WyIuLi8uLi8uLi8uLi8uLi8uLi9wYWNrYWdlcy9jb21waWxlci9zcmMvZGlyZWN0aXZlX25vcm1hbGl6ZXIudHMiXSwibmFtZXMiOltdLCJtYXBwaW5ncyI6IkFBQUE7Ozs7OztHQU1HOzs7Ozs7Ozs7Ozs7OztJQUVILDJFQUFtSTtJQUNuSSx1REFBb0U7SUFDcEUsbURBQXlDO0lBQ3pDLDBEQUF3QztJQUV4Qyw2RkFBcUU7SUFHckUsK0VBQTRFO0lBQzVFLCtGQUEyRjtJQUUzRixtREFBb0U7SUFnQnBFO1FBR0UsNkJBQ1ksZUFBK0IsRUFBVSxZQUF5QixFQUNsRSxXQUF1QixFQUFVLE9BQXVCO1lBRHhELG9CQUFlLEdBQWYsZUFBZSxDQUFnQjtZQUFVLGlCQUFZLEdBQVosWUFBWSxDQUFhO1lBQ2xFLGdCQUFXLEdBQVgsV0FBVyxDQUFZO1lBQVUsWUFBTyxHQUFQLE9BQU8sQ0FBZ0I7WUFKNUQseUJBQW9CLEdBQUcsSUFBSSxHQUFHLEVBQTZCLENBQUM7UUFJRyxDQUFDO1FBRXhFLHdDQUFVLEdBQVY7WUFDRSxJQUFJLENBQUMsb0JBQW9CLENBQUMsS0FBSyxFQUFFLENBQUM7UUFDcEMsQ0FBQztRQUVELDJDQUFhLEdBQWIsVUFBYyxtQkFBNkM7WUFBM0QsaUJBU0M7WUFSQyxJQUFJLENBQUMsbUJBQW1CLENBQUMsV0FBVyxFQUFFO2dCQUNwQyxPQUFPO2FBQ1I7WUFDRCxJQUFNLFFBQVEsR0FBRyxtQkFBbUIsQ0FBQyxRQUFVLENBQUM7WUFDaEQsSUFBSSxDQUFDLG9CQUFvQixDQUFDLE1BQU0sQ0FBQyxRQUFRLENBQUMsV0FBWSxDQUFDLENBQUM7WUFDeEQsUUFBUSxDQUFDLG1CQUFtQixDQUFDLE9BQU8sQ0FBQyxVQUFDLFVBQVU7Z0JBQzlDLEtBQUksQ0FBQyxvQkFBb0IsQ0FBQyxNQUFNLENBQUMsVUFBVSxDQUFDLFNBQVUsQ0FBQyxDQUFDO1lBQzFELENBQUMs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LEdBQW1CLEVBQUUsT0FBWTtZQUM5QyxPQUFPLElBQUksQ0FBQztRQUNkLENBQUM7UUFDRCwyQ0FBUyxHQUFULFVBQVUsR0FBYyxFQUFFLE9BQVk7WUFDcEMsT0FBTyxJQUFJLENBQUM7UUFDZCxDQUFDO1FBQ0gsOEJBQUM7SUFBRCxDQUFDLEFBeERELElBd0RDIiwic291cmNlc0NvbnRlbnQiOlsiLyoqXG4gKiBAbGljZW5zZVxuICogQ29weXJpZ2h0IEdvb2dsZSBMTEMgQWxsIFJpZ2h0cyBSZXNlcnZlZC5cbiAqXG4gKiBVc2Ugb2YgdGhpcyBzb3VyY2UgY29kZSBpcyBnb3Zlcm5lZCBieSBhbiBNSVQtc3R5bGUgbGljZW5zZSB0aGF0IGNhbiBiZVxuICogZm91bmQgaW4gdGhlIExJQ0VOU0UgZmlsZSBhdCBodHRwczovL2FuZ3VsYXIuaW8vbGljZW5zZVxuICovXG5cbmltcG9ydCB7Q29tcGlsZURpcmVjdGl2ZU1ldGFkYXRhLCBDb21waWxlU3R5bGVzaGVldE1ldGFkYXRhLCBDb21waWxlVGVtcGxhdGVNZXRhZGF0YSwgdGVtcGxhdGVTb3VyY2VVcmx9IGZyb20gJy4vY29tcGlsZV9tZXRhZGF0YSc7XG5pbXBvcnQge0NvbXBpbGVyQ29uZmlnLCBwcmVzZXJ2ZVdoaXRlc3BhY2VzRGVmYXVsdH0gZnJvbSAnLi9jb25maWcnO1xuaW1wb3J0IHtWaWV3RW5jYXBzdWxhdGlvbn0gZnJvbSAnLi9jb3JlJztcbmltcG9ydCAqIGFzIGh0bWwgZnJvbSAnLi9tbF9wYXJzZXIvYXN0JztcbmltcG9ydCB7SHRtbFBhcnNlcn0gZnJvbSAnLi9tbF9wYXJzZXIvaHRtbF9wYXJzZXInO1xuaW1wb3J0IHtJbnRlcnBvbGF0aW9uQ29uZmlnfSBmcm9tICcuL21sX3BhcnNlci9pbnRlcnBvbGF0aW9uX2NvbmZpZyc7XG5pbXBvcnQge1BhcnNlVHJlZVJlc3VsdCBhcyBIdG1sUGFyc2VUcmVlUmVzdWx0fSBmcm9tICcuL21sX3BhcnNlci9wYXJzZXInO1xuaW1wb3J0IHtSZXNvdXJjZUxvYWRlcn0gZnJvbSAnLi9yZXNvdXJjZV9sb2FkZXInO1xuaW1wb3J0IHtleHRyYWN0U3R5bGVVcmxzLCBpc1N0eWxlVXJsUmVzb2x2YWJsZX0gZnJvbSAnLi9zdHlsZV91cmxfcmVzb2x2ZXInO1xuaW1wb3J0IHtQcmVwYXJzZWRFbGVtZW50VHlwZSwgcHJlcGFyc2VFbGVtZW50fSBmcm9tICcuL3RlbXBsYXRlX3BhcnNlci90ZW1wbGF0ZV9wcmVwYXJzZXInO1xuaW1wb3J0IHtVcmxSZXNvbHZlcn0gZnJvbSAnLi91cmxfcmVzb2x2ZXInO1xuaW1wb3J0IHtpc0RlZmluZWQsIHN0cmluZ2lmeSwgU3luY0FzeW5jLCBzeW50YXhFcnJvcn0gZnJvbSAnLi91dGlsJztcblxuZXhwb3J0IGludGVyZmFjZSBQcmVub3JtYWxpemVkVGVtcGxhdGVNZXRhZGF0YSB7XG4gIG5nTW9kdWxlVHlwZTogYW55O1xuICBjb21wb25lbnRUeXBlOiBhbnk7XG4gIG1vZHVsZVVybDogc3RyaW5nO1xuICB0ZW1wbGF0ZTogc3RyaW5nfG51bGw7XG4gIHRlbXBsYXRlVXJsOiBzdHJpbmd8bnVsbDtcbiAgc3R5bGVzOiBzdHJpbmdbXTtcbiAgc3R5bGVVcmxzOiBzdHJpbmdbXTtcbiAgaW50ZXJwb2xhdGlvbjogW3N0cmluZywgc3RyaW5nXXxudWxsO1xuICBlbmNhcHN1bGF0aW9uOiBWaWV3RW5jYXBzdWxhdGlvbnxudWxsO1xuICBhbmltYXRpb25zOiBhbnlbXTtcbiAgcHJlc2VydmVXaGl0ZXNwYWNlczogYm9vbGVhbnxudWxsO1xufVxuXG5leHBvcnQgY2xhc3MgRGlyZWN0aXZlTm9ybWFsaXplciB7XG4gIHByaXZhdGUgX3Jlc291cmNlTG9hZGVyQ2FjaGUgPSBuZXcgTWFwPHN0cmluZywgU3luY0FzeW5jPHN0cmluZz4+KCk7XG5cbiAgY29uc3RydWN0b3IoXG4gICAgICBwcml2YXRlIF9yZXNvdXJjZUxvYWRlcjogUmVzb3VyY2VMb2FkZXIsIHByaXZhdGUgX3VybFJlc29sdmVyOiBVcmxSZXNvbHZlcixcbiAgICAgIHByaXZhdGUgX2h0bWxQYXJzZXI6IEh0bWxQYXJzZXIsIHByaXZhdGUgX2NvbmZpZzogQ29tcGlsZXJDb25maWcpIHt9XG5cbiAgY2xlYXJDYWNoZSgpOiB2b2lkIHtcbiAgICB0aGlzLl9yZXNvdXJjZUxvYWRlckNhY2hlLmNsZWFyKCk7XG4gIH1cblxuICBjbGVhckNhY2hlRm9yKG5vcm1hbGl6ZWREaXJlY3RpdmU6IENvbXBpbGVEaXJlY3RpdmVNZXRhZGF0YSk6IHZvaWQge1xuICAgIGlmICghbm9ybWFsaXplZERpcmVjdGl2ZS5pc0NvbXBvbmVudCkge1xuICAgICAgcmV0dXJuO1xuICAgIH1cbiAgICBjb25zdCB0ZW1wbGF0ZSA9IG5vcm1hbGl6ZWREaXJlY3RpdmUudGVtcGxhdGUgITtcbiAgICB0aGlzLl9yZXNvdXJjZUxvYWRlckNhY2hlLmRlbGV0ZSh0ZW1wbGF0ZS50ZW1wbGF0ZVVybCEpO1xuICAgIHRlbXBsYXRlLmV4dGVybmFsU3R5bGVzaGVldHMuZm9yRWFjaCgoc3R5bGVzaGVldCkgPT4ge1xuICAgICAgdGhpcy5fcmVzb3VyY2VMb2FkZXJDYWNoZS5kZWxldGUoc3R5bGVzaGVldC5tb2R1bGVVcmwhKTtcbiAgICB9KTtcbiAgfVxuXG4gIHByaXZhdGUgX2ZldGNoKHVybDogc3RyaW5nKTogU3luY0FzeW5jPHN0cmluZz4ge1xuICAgIGxldCByZXN1bHQgPSB0aGlzLl9yZXNvdXJjZUxvYWRlckNhY2hlLmdldCh1cmwpO1xuICAgIGlmICghcmVzdWx0KSB7XG4gICAgICByZXN1bHQgPSB0aGlzLl9yZXNvdXJjZUxvYWRlci5nZXQodXJsKTtcbiAgICAgIHRoaXMuX3Jlc291cmNlTG9hZGVyQ2FjaGUuc2V0KHVybCwgcmVzdWx0KTtcbiAgICB9XG4gICAgcmV0dXJuIHJlc3VsdDtcbiAgfVxuXG4gIG5vcm1hbGl6ZVRlbXBsYXRlKHByZW5vcm1EYXRhOiBQcmVub3JtYWxpemVkVGVtcGxhdGVNZXRhZGF0YSk6XG4gICAgICBTeW5jQXN5bmM8Q29tcGlsZVRlbXBsYXRlTWV0YWRhdGE+IHtcbiAgICBpZiAoaXNEZWZpbmVkKHByZW5vcm1EYXRhLnRlbXBsYXRlKSkge1xuICAgICAgaWYgKGlzRGVmaW5lZChwcmVub3JtRGF0YS50ZW1wbGF0ZVVybCkpIHtcbiAgICAgICAgdGhyb3cgc3ludGF4RXJyb3IoYCcke1xuICAgICAgICAgICAgc3RyaW5naWZ5KHByZW5vcm1EYXRhXG4gICAgICAgICAgICAgICAgICAgICAgICAgIC5jb21wb25lbnRUeXBlKX0nIGNvbXBvbmVudCBjYW5ub3QgZGVmaW5lIGJvdGggdGVtcGxhdGUgYW5kIHRlbXBsYXRlVXJsYCk7XG4gICAgICB9XG4gICAgICBpZiAodHlwZW9mIHByZW5vcm1EYXRhLnRlbXBsYXRlICE9PSAnc3RyaW5nJykge1xuICAgICAgICB0aHJvdyBzeW50YXhFcnJvcihgVGhlIHRlbXBsYXRlIHNwZWNpZmllZCBmb3IgY29tcG9uZW50ICR7XG4gICAgICAgICAgICBzdHJpbmdpZnkocHJlbm9ybURhdGEuY29tcG9uZW50VHlwZSl9IGlzIG5vdCBhIHN0cmluZ2ApO1xuICAgICAgfVxuICAgIH0gZWxzZSBpZiAoaXNEZWZpbmVkKHByZW5vcm1EYXRhLnRlbXBsYXRlVXJsKSkge1xuICAgICAgaWYgKHR5cGVvZiBwcmVub3JtRGF0YS50ZW1wbGF0ZVVybCAhPT0gJ3N0cmluZycpIHtcbiAgICAgICAgdGhyb3cgc3ludGF4RXJyb3IoYFRoZSB0ZW1wbGF0ZVVybCBzcGVjaWZpZWQgZm9yIGNvbXBvbmVudCAke1xuICAgICAgICAgICAgc3RyaW5naWZ5KHByZW5vcm1EYXRhLmNvbXBvbmVudFR5cGUpfSBpcyBub3QgYSBzdHJpbmdgKTtcbiAgICAgIH1cbiAgICB9IGVsc2Uge1xuICAgICAgdGhyb3cgc3ludGF4RXJyb3IoXG4gICAgICAgICAgYE5vIHRlbXBsYXRlIHNwZWNpZmllZCBmb3IgY29tcG9uZW50ICR7c3RyaW5naWZ5KHByZW5vcm1EYXRhLmNvbXBvbmVudFR5cGUpfWApO1xuICAgIH1cblxuICAgIGlmIChpc0RlZmluZWQocHJlbm9ybURhdGEucHJlc2VydmVXaGl0ZXNwYWNlcykgJiZcbiAgICAgICAgdHlwZW9mIHByZW5vcm1EYXRhLnByZXNlcnZlV2hpdGVzcGFjZXMgIT09ICdib29sZWFuJykge1xuICAgICAgdGhyb3cgc3ludGF4RXJyb3IoYFRoZSBwcmVzZXJ2ZVdoaXRlc3BhY2VzIG9wdGlvbiBmb3IgY29tcG9uZW50ICR7XG4gICAgICAgICAgc3RyaW5naWZ5KHByZW5vcm1EYXRhLmNvbXBvbmVudFR5cGUpfSBtdXN0IGJlIGEgYm9vbGVhbmApO1xuICAgIH1cblxuICAgIHJldHVybiBTeW5jQXN5bmMudGhlbihcbiAgICAgICAgdGhpcy5fcHJlUGFyc2VUZW1wbGF0ZShwcmVub3JtRGF0YSksXG4gICAgICAgIChwcmVwYXJzZWRUZW1wbGF0ZSkgPT4gdGhpcy5fbm9ybWFsaXplVGVtcGxhdGVNZXRhZGF0YShwcmVub3JtRGF0YSwgcHJlcGFyc2VkVGVtcGxhdGUpKTtcbiAgfVxuXG4gIHByaXZhdGUgX3ByZVBhcnNlVGVtcGxhdGUocHJlbm9tRGF0YTogUHJlbm9ybWFsaXplZFRlbXBsYXRlTWV0YWRhdGEpOlxuICAgICAgU3luY0FzeW5jPFByZXBhcnNlZFRlbXBsYXRlPiB7XG4gICAgbGV0IHRlbXBsYXRlOiBTeW5jQXN5bmM8c3RyaW5nPjtcbiAgICBsZXQgdGVtcGxhdGVVcmw6IHN0cmluZztcbiAgICBpZiAocHJlbm9tRGF0YS50ZW1wbGF0ZSAhPSBudWxsKSB7XG4gICAgICB0ZW1wbGF0ZSA9IHByZW5vbURhdGEudGVtcGxhdGU7XG4gICAgICB0ZW1wbGF0ZVVybCA9IHByZW5vbURhdGEubW9kdWxlVXJsO1xuICAgIH0gZWxzZSB7XG4gICAgICB0ZW1wbGF0ZVVybCA9IHRoaXMuX3VybFJlc29sdmVyLnJlc29sdmUocHJlbm9tRGF0YS5tb2R1bGVVcmwsIHByZW5vbURhdGEudGVtcGxhdGVVcmwhKTtcbiAgICAgIHRlbXBsYXRlID0gdGhpcy5fZmV0Y2godGVtcGxhdGVVcmwpO1xuICAgIH1cbiAgICByZXR1cm4gU3luY0FzeW5jLnRoZW4oXG4gICAgICAgIHRlbXBsYXRlLCAodGVtcGxhdGUpID0+IHRoaXMuX3ByZXBhcnNlTG9hZGVkVGVtcGxhdGUocHJlbm9tRGF0YSwgdGVtcGxhdGUsIHRlbXBsYXRlVXJsKSk7XG4gIH1cblxuICBwcml2YXRlIF9wcmVwYXJzZUxvYWRlZFRlbXBsYXRlKFxuICAgICAgcHJlbm9ybURhdGE6IFByZW5vcm1hbGl6ZWRUZW1wbGF0ZU1ldGFkYXRhLCB0ZW1wbGF0ZTogc3RyaW5nLFxuICAgICAgdGVtcGxhdGVBYnNVcmw6IHN0cmluZyk6IFByZXBhcnNlZFRlbXBsYXRlIHtcbiAgICBjb25zdCBpc0lubGluZSA9ICEhcHJlbm9ybURhdGEudGVtcGxhdGU7XG4gICAgY29uc3QgaW50ZXJwb2xhdGlvbkNvbmZpZyA9IEludGVycG9sYXRpb25Db25maWcuZnJvbUFycmF5KHByZW5vcm1EYXRhLmludGVycG9sYXRpb24hKTtcbiAgICBjb25zdCB0ZW1wbGF0ZVVybCA9IHRlbXBsYXRlU291cmNlVXJsKFxuICAgICAgICB7cmVmZXJlbmNlOiBwcmVub3JtRGF0YS5uZ01vZHVsZVR5cGV9LCB7dHlwZToge3JlZmVyZW5jZTogcHJlbm9ybURhdGEuY29tcG9uZW50VHlwZX19LFxuICAgICAgICB7aXNJbmxpbmUsIHRlbXBsYXRlVXJsOiB0ZW1wbGF0ZUFic1VybH0pO1xuICAgIGNvbnN0IHJvb3ROb2Rlc0FuZEVycm9ycyA9IHRoaXMuX2h0bWxQYXJzZXIucGFyc2UoXG4gICAgICAgIHRlbXBsYXRlLCB0ZW1wbGF0ZVVybCwge3Rva2VuaXplRXhwYW5zaW9uRm9ybXM6IHRydWUsIGludGVycG9sYXRpb25Db25maWd9KTtcbiAgICBpZiAocm9vdE5vZGVzQW5kRXJyb3JzLmVycm9ycy5sZW5ndGggPiAwKSB7XG4gICAgICBjb25zdCBlcnJvclN0cmluZyA9IHJvb3ROb2Rlc0FuZEVycm9ycy5lcnJvcnMuam9pbignXFxuJyk7XG4gICAgICB0aHJvdyBzeW50YXhFcnJvcihgVGVtcGxhdGUgcGFyc2UgZXJyb3JzOlxcbiR7ZXJyb3JTdHJpbmd9YCk7XG4gICAgfVxuXG4gICAgY29uc3QgdGVtcGxhdGVNZXRhZGF0YVN0eWxlcyA9IHRoaXMuX25vcm1hbGl6ZVN0eWxlc2hlZXQobmV3IENvbXBpbGVTdHlsZXNoZWV0TWV0YWRhdGEoXG4gICAgICAgIHtzdHlsZXM6IHByZW5vcm1EYXRhLnN0eWxlcywgbW9kdWxlVXJsOiBwcmVub3JtRGF0YS5tb2R1bGVVcmx9KSk7XG5cbiAgICBjb25zdCB2aXNpdG9yID0gbmV3IFRlbXBsYXRlUHJlcGFyc2VWaXNpdG9yKCk7XG4gICAgaHRtbC52aXNpdEFsbCh2aXNpdG9yLCByb290Tm9kZXNBbmRFcnJvcnMucm9vdE5vZGVzKTtcbiAgICBjb25zdCB0ZW1wbGF0ZVN0eWxlcyA9IHRoaXMuX25vcm1hbGl6ZVN0eWxlc2hlZXQobmV3IENvbXBpbGVTdHlsZXNoZWV0TWV0YWRhdGEoXG4gICAgICAgIHtzdHlsZXM6IHZpc2l0b3Iuc3R5bGVzLCBzdHlsZVVybHM6IHZpc2l0b3Iuc3R5bGVVcmxzLCBtb2R1bGVVcmw6IHRlbXBsYXRlQWJzVXJsfSkpO1xuXG4gICAgY29uc3Qgc3R5bGVzID0gdGVtcGxhdGVNZXRhZGF0YVN0eWxlcy5zdHlsZXMuY29uY2F0KHRlbXBsYXRlU3R5bGVzLnN0eWxlcyk7XG5cbiAgICBjb25zdCBpbmxpbmVTdHlsZVVybHMgPSB0ZW1wbGF0ZU1ldGFkYXRhU3R5bGVzLnN0eWxlVXJscy5jb25jYXQodGVtcGxhdGVTdHlsZXMuc3R5bGVVcmxzKTtcbiAgICBjb25zdCBzdHlsZVVybHMgPSB0aGlzXG4gICAgICAgICAgICAgICAgICAgICAgICAgIC5fbm9ybWFsaXplU3R5bGVzaGVldChuZXcgQ29tcGlsZVN0eWxlc2hlZXRNZXRhZGF0YShcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIHtzdHlsZVVybHM6IHByZW5vcm1EYXRhLnN0eWxlVXJscywgbW9kdWxlVXJsOiBwcmVub3JtRGF0YS5tb2R1bGVVcmx9KSlcbiAgICAgICAgICAgICAgICAgICAgICAgICAgLnN0eWxlVXJscztcbiAgICByZXR1cm4ge1xuICAgICAgdGVtcGxhdGUsXG4gICAgICB0ZW1wbGF0ZVVybDogdGVtcGxhdGVBYnNVcmwsXG4gICAgICBpc0lubGluZSxcbiAgICAgIGh0bWxBc3Q6IHJvb3ROb2Rlc0FuZEVycm9ycyxcbiAgICAgIHN0eWxlcyxcbiAgICAgIGlubGluZVN0eWxlVXJscyxcbiAgICAgIHN0eWxlVXJscyxcbiAgICAgIG5nQ29udGVudFNlbGVjdG9yczogdmlzaXRvci5uZ0NvbnRlbnRTZWxlY3RvcnMsXG4gICAgfTtcbiAgfVxuXG4gIHByaXZhdGUgX25vcm1hbGl6ZVRlbXBsYXRlTWV0YWRhdGEoXG4gICAgICBwcmVub3JtRGF0YTogUHJlbm9ybWFsaXplZFRlbXBsYXRlTWV0YWRhdGEsXG4gICAgICBwcmVwYXJzZWRUZW1wbGF0ZTogUHJlcGFyc2VkVGVtcGxhdGUpOiBTeW5jQXN5bmM8Q29tcGlsZVRlbXBsYXRlTWV0YWRhdGE+IHtcbiAgICByZXR1cm4gU3luY0FzeW5jLnRoZW4oXG4gICAgICAgIHRoaXMuX2xvYWRNaXNzaW5nRXh0ZXJuYWxTdHlsZXNoZWV0cyhcbiAgICAgICAgICAgIHByZXBhcnNlZFRlbXBsYXRlLnN0eWxlVXJscy5jb25jYXQocHJlcGFyc2VkVGVtcGxhdGUuaW5saW5lU3R5bGVVcmxzKSksXG4gICAgICAgIChleHRlcm5hbFN0eWxlc2hlZXRzKSA9PiB0aGlzLl9ub3JtYWxpemVMb2FkZWRUZW1wbGF0ZU1ldGFkYXRhKFxuICAgICAgICAgICAgcHJlbm9ybURhdGEsIHByZXBhcnNlZFRlbXBsYXRlLCBleHRlcm5hbFN0eWxlc2hlZXRzKSk7XG4gIH1cblxuICBwcml2YXRlIF9ub3JtYWxpemVMb2FkZWRUZW1wbGF0ZU1ldGFkYXRhKFxuICAgICAgcHJlbm9ybURhdGE6IFByZW5vcm1hbGl6ZWRUZW1wbGF0ZU1ldGFkYXRhLCBwcmVwYXJzZWRUZW1wbGF0ZTogUHJlcGFyc2VkVGVtcGxhdGUsXG4gICAgICBzdHlsZXNoZWV0czogTWFwPHN0cmluZywgQ29tcGlsZVN0eWxlc2hlZXRNZXRhZGF0YT4pOiBDb21waWxlVGVtcGxhdGVNZXRhZGF0YSB7XG4gICAgLy8gQWxnb3JpdGhtOlxuICAgIC8vIC0gcHJvZHVjZSBleGFjdGx5IDEgZW50cnkgcGVyIG9yaWdpbmFsIHN0eWxlVXJsIGluXG4gICAgLy8gQ29tcGlsZVRlbXBsYXRlTWV0YWRhdGEuZXh0ZXJuYWxTdHlsZXNoZWV0cyB3aXRoIGFsbCBzdHlsZXMgaW5saW5lZFxuICAgIC8vIC0gaW5saW5lIGFsbCBzdHlsZXMgdGhhdCBhcmUgcmVmZXJlbmNlZCBieSB0aGUgdGVtcGxhdGUgaW50byBDb21waWxlVGVtcGxhdGVNZXRhZGF0YS5zdHlsZXMuXG4gICAgLy8gUmVhc29uOiBiZSBhYmxlIHRvIGRldGVybWluZSBob3cgbWFueSBzdHlsZXNoZWV0cyB0aGVyZSBhcmUgZXZlbiB3aXRob3V0IGxvYWRpbmdcbiAgICAvLyB0aGUgdGVtcGxhdGUgbm9yIHRoZSBzdHlsZXNoZWV0cywgc28gd2UgY2FuIGNyZWF0ZSBhIHN0dWIgZm9yIFR5cGVTY3JpcHQgYWx3YXlzIHN5bmNocm9ub3VzbHlcbiAgICAvLyAoYXMgcmVzb3VyY2UgbG9hZGluZyBtYXkgYmUgYXN5bmMpXG5cbiAgICBjb25zdCBzdHlsZXMgPSBbLi4ucHJlcGFyc2VkVGVtcGxhdGUuc3R5bGVzXTtcbiAgICB0aGlzLl9pbmxpbmVTdHlsZXMocHJlcGFyc2VkVGVtcGxhdGUuaW5saW5lU3R5bGVVcmxzLCBzdHlsZXNoZWV0cywgc3R5bGVzKTtcbiAgICBjb25zdCBzdHlsZVVybHMgPSBwcmVwYXJzZWRUZW1wbGF0ZS5zdHlsZVVybHM7XG5cbiAgICBjb25zdCBleHRlcm5hbFN0eWxlc2hlZXRzID0gc3R5bGVVcmxzLm1hcChzdHlsZVVybCA9PiB7XG4gICAgICBjb25zdCBzdHlsZXNoZWV0ID0gc3R5bGVzaGVldHMuZ2V0KHN0eWxlVXJsKSE7XG4gICAgICBjb25zdCBzdHlsZXMgPSBbLi4uc3R5bGVzaGVldC5zdHlsZXNdO1xuICAgICAgdGhpcy5faW5saW5lU3R5bGVzKHN0eWxlc2hlZXQuc3R5bGVVcmxzLCBzdHlsZXNoZWV0cywgc3R5bGVzKTtcbiAgICAgIHJldHVybiBuZXcgQ29tcGlsZVN0eWxlc2hlZXRNZXRhZGF0YSh7bW9kdWxlVXJsOiBzdHlsZVVybCwgc3R5bGVzOiBzdHlsZXN9KTtcbiAgICB9KTtcblxuICAgIGxldCBlbmNhcHN1bGF0aW9uID0gcHJlbm9ybURhdGEuZW5jYXBzdWxhdGlvbjtcbiAgICBpZiAoZW5jYXBzdWxhdGlvbiA9PSBudWxsKSB7XG4gICAgICBlbmNhcHN1bGF0aW9uID0gdGhpcy5fY29uZmlnLmRlZmF1bHRFbmNhcHN1bGF0aW9uO1xuICAgIH1cbiAgICBpZiAoZW5jYXBzdWxhdGlvbiA9PT0gVmlld0VuY2Fwc3VsYXRpb24uRW11bGF0ZWQgJiYgc3R5bGVzLmxlbmd0aCA9PT0gMCAmJlxuICAgICAgICBzdHlsZVVybHMubGVuZ3RoID09PSAwKSB7XG4gICAgICBlbmNhcHN1bGF0aW9uID0gVmlld0VuY2Fwc3VsYXRpb24uTm9uZTtcbiAgICB9XG4gICAgcmV0dXJuIG5ldyBDb21waWxlVGVtcGxhdGVNZXRhZGF0YSh7XG4gICAgICBlbmNhcHN1bGF0aW9uLFxuICAgICAgdGVtcGxhdGU6IHByZXBhcnNlZFRlbXBsYXRlLnRlbXBsYXRlLFxuICAgICAgdGVtcGxhdGVVcmw6IHByZXBhcnNlZFRlbXBsYXRlLnRlbXBsYXRlVXJsLFxuICAgICAgaHRtbEFzdDogcHJlcGFyc2VkVGVtcGxhdGUuaHRtbEFzdCxcbiAgICAgIHN0eWxlcyxcbiAgICAgIHN0eWxlVXJscyxcbiAgICAgIG5nQ29udGVudFNlbGVjdG9yczogcHJlcGFyc2VkVGVtcGxhdGUubmdDb250ZW50U2VsZWN0b3JzLFxuICAgICAgYW5pbWF0aW9uczogcHJlbm9ybURhdGEuYW5pbWF0aW9ucyxcbiAgICAgIGludGVycG9sYXRpb246IHByZW5vcm1EYXRhLmludGVycG9sYXRpb24sXG4gICAgICBpc0lubGluZTogcHJlcGFyc2VkVGVtcGxhdGUuaXNJbmxpbmUsXG4gICAgICBleHRlcm5hbFN0eWxlc2hlZXRzLFxuICAgICAgcHJlc2VydmVXaGl0ZXNwYWNlczogcHJlc2VydmVXaGl0ZXNwYWNlc0RlZmF1bHQoXG4gICAgICAgICAgcHJlbm9ybURhdGEucHJlc2VydmVXaGl0ZXNwYWNlcywgdGhpcy5fY29uZmlnLnByZXNlcnZlV2hpdGVzcGFjZXMpLFxuICAgIH0pO1xuICB9XG5cbiAgcHJpdmF0ZSBfaW5saW5lU3R5bGVzKFxuICAgICAgc3R5bGVVcmxzOiBzdHJpbmdbXSwgc3R5bGVzaGVldHM6IE1hcDxzdHJpbmcsIENvbXBpbGVTdHlsZXNoZWV0TWV0YWRhdGE+LFxuICAgICAgdGFyZ2V0U3R5bGVzOiBzdHJpbmdbXSkge1xuICAgIHN0eWxlVXJscy5mb3JFYWNoKHN0eWxlVXJsID0+IHtcbiAgICAgIGNvbnN0IHN0eWxlc2hlZXQgPSBzdHlsZXNoZWV0cy5nZXQoc3R5bGVVcmwpITtcbiAgICAgIHN0eWxlc2hlZXQuc3R5bGVzLmZvckVhY2goc3R5bGUgPT4gdGFyZ2V0U3R5bGVzLnB1c2goc3R5bGUpKTtcbiAgICAgIHRoaXMuX2lubGluZVN0eWxlcyhzdHlsZXNoZWV0LnN0eWxlVXJscywgc3R5bGVzaGVldHMsIHRhcmdldFN0eWxlcyk7XG4gICAgfSk7XG4gIH1cblxuICBwcml2YXRlIF9sb2FkTWlzc2luZ0V4dGVybmFsU3R5bGVzaGVldHMoXG4gICAgICBzdHlsZVVybHM6IHN0cmluZ1tdLFxuICAgICAgbG9hZGVkU3R5bGVzaGVldHM6XG4gICAgICAgICAgTWFwPHN0cmluZywgQ29tcGlsZVN0eWxlc2hlZXRNZXRhZGF0YT4gPSBuZXcgTWFwPHN0cmluZywgQ29tcGlsZVN0eWxlc2hlZXRNZXRhZGF0YT4oKSk6XG4gICAgICBTeW5jQXN5bmM8TWFwPHN0cmluZywgQ29tcGlsZVN0eWxlc2hlZXRNZXRhZGF0YT4+IHtcbiAgICByZXR1cm4gU3luY0FzeW5jLnRoZW4oXG4gICAgICAgIFN5bmNBc3luYy5hbGwoc3R5bGVVcmxzLmZpbHRlcigoc3R5bGVVcmwpID0+ICFsb2FkZWRTdHlsZXNoZWV0cy5oYXMoc3R5bGVVcmwpKVxuICAgICAgICAgICAgICAgICAgICAgICAgICAubWFwKFxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgc3R5bGVVcmwgPT4gU3luY0FzeW5jLnRoZW4oXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgdGhpcy5fZmV0Y2goc3R5bGVVcmwpLFxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIChsb2FkZWRTdHlsZSkgPT4ge1xuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgY29uc3Qgc3R5bGVzaGVldCA9XG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgdGhpcy5fbm9ybWFsaXplU3R5bGVzaGVldChuZXcgQ29tcGlsZVN0eWxlc2hlZXRNZXRhZGF0YShcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAge3N0eWxlczogW2xvYWRlZFN0eWxlXSwgbW9kdWxlVXJsOiBzdHlsZVVybH0pKTtcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIGxvYWRlZFN0eWxlc2hlZXRzLnNldChzdHlsZVVybCwgc3R5bGVzaGVldCk7XG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICByZXR1cm4gdGhpcy5fbG9hZE1pc3NpbmdFeHRlcm5hbFN0eWxlc2hlZXRzKFxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIHN0eWxlc2hlZXQuc3R5bGVVcmxzLCBsb2FkZWRTdHlsZXNoZWV0cyk7XG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgfSkpKSxcbiAgICAgICAgKF8pID0+IGxvYWRlZFN0eWxlc2hlZXRzKTtcbiAgfVxuXG4gIHByaXZhdGUgX25vcm1hbGl6ZVN0eWxlc2hlZXQoc3R5bGVzaGVldDogQ29tcGlsZVN0eWxlc2hlZXRNZXRhZGF0YSk6IENvbXBpbGVTdHlsZXNoZWV0TWV0YWRhdGEge1xuICAgIGNvbnN0IG1vZHVsZVVybCA9IHN0eWxlc2hlZXQubW9kdWxlVXJsITtcbiAgICBjb25zdCBhbGxTdHlsZVVybHMgPSBzdHlsZXNoZWV0LnN0eWxlVXJscy5maWx0ZXIoaXNTdHlsZVVybFJlc29sdmFibGUpXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgIC5tYXAodXJsID0+IHRoaXMuX3VybFJlc29sdmVyLnJlc29sdmUobW9kdWxlVXJsLCB1cmwpKTtcblxuICAgIGNvbnN0IGFsbFN0eWxlcyA9IHN0eWxlc2hlZXQuc3R5bGVzLm1hcChzdHlsZSA9PiB7XG4gICAgICBjb25zdCBzdHlsZVdpdGhJbXBvcnRzID0gZXh0cmFjdFN0eWxlVXJscyh0aGlzLl91cmxSZXNvbHZlciwgbW9kdWxlVXJsLCBzdHlsZSk7XG4gICAgICBhbGxTdHlsZVVybHMucHVzaCguLi5zdHlsZVdpdGhJbXBvcnRzLnN0eWxlVXJscyk7XG4gICAgICByZXR1cm4gc3R5bGVXaXRoSW1wb3J0cy5zdHlsZTtcbiAgICB9KTtcblxuICAgIHJldHVybiBuZXcgQ29tcGlsZVN0eWxlc2hlZXRNZXRhZGF0YShcbiAgICAgICAge3N0eWxlczogYWxsU3R5bGVzLCBzdHlsZVVybHM6IGFsbFN0eWxlVXJscywgbW9kdWxlVXJsOiBtb2R1bGVVcmx9KTtcbiAgfVxufVxuXG5pbnRlcmZhY2UgUHJlcGFyc2VkVGVtcGxhdGUge1xuICB0ZW1wbGF0ZTogc3RyaW5nO1xuICB0ZW1wbGF0ZVVybDogc3RyaW5nO1xuICBpc0lubGluZTogYm9vbGVhbjtcbiAgaHRtbEFzdDogSHRtbFBhcnNlVHJlZVJlc3VsdDtcbiAgc3R5bGVzOiBzdHJpbmdbXTtcbiAgaW5saW5lU3R5bGVVcmxzOiBzdHJpbmdbXTtcbiAgc3R5bGVVcmxzOiBzdHJpbmdbXTtcbiAgbmdDb250ZW50U2VsZWN0b3JzOiBzdHJpbmdbXTtcbn1cblxuY2xhc3MgVGVtcGxhdGVQcmVwYXJzZVZpc2l0b3IgaW1wbGVtZW50cyBodG1sLlZpc2l0b3Ige1xuICBuZ0NvbnRlbnRTZWxlY3RvcnM6IHN0cmluZ1tdID0gW107XG4gIHN0eWxlczogc3RyaW5nW10gPSBbXTtcbiAgc3R5bGVVcmxzOiBzdHJpbmdbXSA9IFtdO1xuICBuZ05vbkJpbmRhYmxlU3RhY2tDb3VudDogbnVtYmVyID0gMDtcblxuICB2aXNpdEVsZW1lbnQoYXN0OiBodG1sLkVsZW1lbnQsIGNvbnRleHQ6IGFueSk6IGFueSB7XG4gICAgY29uc3QgcHJlcGFyc2VkRWxlbWVudCA9IHByZXBhcnNlRWxlbWVudChhc3QpO1xuICAgIHN3aXRjaCAocHJlcGFyc2VkRWxlbWVudC50eXBlKSB7XG4gICAgICBjYXNlIFByZXBhcnNlZEVsZW1lbnRUeXBlLk5HX0NPTlRFTlQ6XG4gICAgICAgIGlmICh0aGlzLm5nTm9uQmluZGFibGVTdGFja0NvdW50ID09PSAwKSB7XG4gICAgICAgICAgdGhpcy5uZ0NvbnRlbnRTZWxlY3RvcnMucHVzaChwcmVwYXJzZWRFbGVtZW50LnNlbGVjdEF0dHIpO1xuICAgICAgICB9XG4gICAgICAgIGJyZWFrO1xuICAgICAgY2FzZSBQcmVwYXJzZWRFbGVtZW50VHlwZS5TVFlMRTpcbiAgICAgICAgbGV0IHRleHRDb250ZW50ID0gJyc7XG4gICAgICAgIGFzdC5jaGlsZHJlbi5mb3JFYWNoKGNoaWxkID0+IHtcbiAgICAgICAgICBpZiAoY2hpbGQgaW5zdGFuY2VvZiBodG1sLlRleHQpIHtcbiAgICAgICAgICAgIHRleHRDb250ZW50ICs9IGNoaWxkLnZhbHVlO1xuICAgICAgICAgIH1cbiAgICAgICAgfSk7XG4gICAgICAgIHRoaXMuc3R5bGVzLnB1c2godGV4dENvbnRlbnQpO1xuICAgICAgICBicmVhaztcbiAgICAgIGNhc2UgUHJlcGFyc2VkRWxlbWVudFR5cGUuU1RZTEVTSEVFVDpcbiAgICAgICAgdGhpcy5zdHlsZVVybHMucHVzaChwcmVwYXJzZWRFbGVtZW50LmhyZWZBdHRyKTtcbiAgICAgICAgYnJlYWs7XG4gICAgICBkZWZhdWx0OlxuICAgICAgICBicmVhaztcbiAgICB9XG4gICAgaWYgKHByZXBhcnNlZEVsZW1lbnQubm9uQmluZGFibGUpIHtcbiAgICAgIHRoaXMubmdOb25CaW5kYWJsZVN0YWNrQ291bnQrKztcbiAgICB9XG4gICAgaHRtbC52aXNpdEFsbCh0aGlzLCBhc3QuY2hpbGRyZW4pO1xuICAgIGlmIChwcmVwYXJzZWRFbGVtZW50Lm5vbkJpbmRhYmxlKSB7XG4gICAgICB0aGlzLm5nTm9uQmluZGFibGVTdGFja0NvdW50LS07XG4gICAgfVxuICAgIHJldHVybiBudWxsO1xuICB9XG5cbiAgdmlzaXRFeHBhbnNpb24oYXN0OiBodG1sLkV4cGFuc2lvbiwgY29udGV4dDogYW55KTogYW55IHtcbiAgICBodG1sLnZpc2l0QWxsKHRoaXMsIGFzdC5jYXNlcyk7XG4gIH1cblxuICB2aXNpdEV4cGFuc2lvbkNhc2UoYXN0OiBodG1sLkV4cGFuc2lvbkNhc2UsIGNvbnRleHQ6IGFueSk6IGFueSB7XG4gICAgaHRtbC52aXNpdEFsbCh0aGlzLCBhc3QuZXhwcmVzc2lvbik7XG4gIH1cblxuICB2aXNpdENvbW1lbnQoYXN0OiBodG1sLkNvbW1lbnQsIGNvbnRleHQ6IGFueSk6IGFueSB7XG4gICAgcmV0dXJuIG51bGw7XG4gIH1cbiAgdmlzaXRBdHRyaWJ1dGUoYXN0OiBodG1sLkF0dHJpYnV0ZSwgY29udGV4dDogYW55KTogYW55IHtcbiAgICByZXR1cm4gbnVsbDtcbiAgfVxuICB2aXNpdFRleHQoYXN0OiBodG1sLlRleHQsIGNvbnRleHQ6IGFueSk6IGFueSB7XG4gICAgcmV0dXJuIG51bGw7XG4gIH1cbn1cbiJdfQ==