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
        define("@angular/compiler/src/compile_metadata", ["require", "exports", "@angular/compiler/src/aot/static_symbol", "@angular/compiler/src/util"], factory);
    }
})(function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.templateJitUrl = exports.ngModuleJitUrl = exports.sharedStylesheetJitUrl = exports.templateSourceUrl = exports.flatten = exports.ProviderMeta = exports.TransitiveCompileNgModuleMetadata = exports.CompileNgModuleMetadata = exports.CompileShallowModuleMetadata = exports.CompilePipeMetadata = exports.CompileDirectiveMetadata = exports.CompileTemplateMetadata = exports.CompileStylesheetMetadata = exports.tokenReference = exports.tokenName = exports.CompileSummaryKind = exports.componentFactoryName = exports.hostViewClassName = exports.rendererTypeName = exports.viewClassName = exports.identifierModuleUrl = exports.identifierName = exports.sanitizeIdentifier = void 0;
    var static_symbol_1 = require("@angular/compiler/src/aot/static_symbol");
    var util_1 = require("@angular/compiler/src/util");
    // group 0: "[prop] or (event) or @trigger"
    // group 1: "prop" from "[prop]"
    // group 2: "event" from "(event)"
    // group 3: "@trigger" from "@trigger"
    var HOST_REG_EXP = /^(?:(?:\[([^\]]+)\])|(?:\(([^\)]+)\)))|(\@[-\w]+)$/;
    function sanitizeIdentifier(name) {
        return name.replace(/\W/g, '_');
    }
    exports.sanitizeIdentifier = sanitizeIdentifier;
    var _anonymousTypeIndex = 0;
    function identifierName(compileIdentifier) {
        if (!compileIdentifier || !compileIdentifier.reference) {
            return null;
        }
        var ref = compileIdentifier.reference;
        if (ref instanceof static_symbol_1.StaticSymbol) {
            return ref.name;
        }
        if (ref['__anonymousType']) {
            return ref['__anonymousType'];
        }
        var identifier = util_1.stringify(ref);
        if (identifier.indexOf('(') >= 0) {
            // case: anonymous functions!
            identifier = "anonymous_" + _anonymousTypeIndex++;
            ref['__anonymousType'] = identifier;
        }
        else {
            identifier = sanitizeIdentifier(identifier);
        }
        return identifier;
    }
    exports.identifierName = identifierName;
    function identifierModuleUrl(compileIdentifier) {
        var ref = compileIdentifier.reference;
        if (ref instanceof static_symbol_1.StaticSymbol) {
            return ref.filePath;
        }
        // Runtime type
        return "./" + util_1.stringify(ref);
    }
    exports.identifierModuleUrl = identifierModuleUrl;
    function viewClassName(compType, embeddedTemplateIndex) {
        return "View_" + identifierName({ reference: compType }) + "_" + embeddedTemplateIndex;
    }
    exports.viewClassName = viewClassName;
    function rendererTypeName(compType) {
        return "RenderType_" + identifierName({ reference: compType });
    }
    exports.rendererTypeName = rendererTypeName;
    function hostViewClassName(compType) {
        return "HostView_" + identifierName({ reference: compType });
    }
    exports.hostViewClassName = hostViewClassName;
    function componentFactoryName(compType) {
        return identifierName({ reference: compType }) + "NgFactory";
    }
    exports.componentFactoryName = componentFactoryName;
    var CompileSummaryKind;
    (function (CompileSummaryKind) {
        CompileSummaryKind[CompileSummaryKind["Pipe"] = 0] = "Pipe";
        CompileSummaryKind[CompileSummaryKind["Directive"] = 1] = "Directive";
        CompileSummaryKind[CompileSummaryKind["NgModule"] = 2] = "NgModule";
        CompileSummaryKind[CompileSummaryKind["Injectable"] = 3] = "Injectable";
    })(CompileSummaryKind = exports.CompileSummaryKind || (exports.CompileSummaryKind = {}));
    function tokenName(token) {
        return token.value != null ? sanitizeIdentifier(token.value) : identifierName(token.identifier);
    }
    exports.tokenName = tokenName;
    function tokenReference(token) {
        if (token.identifier != null) {
            return token.identifier.reference;
        }
        else {
            return token.value;
        }
    }
    exports.tokenReference = tokenReference;
    /**
     * Metadata about a stylesheet
     */
    var CompileStylesheetMetadata = /** @class */ (function () {
        function CompileStylesheetMetadata(_a) {
            var _b = _a === void 0 ? {} : _a, moduleUrl = _b.moduleUrl, styles = _b.styles, styleUrls = _b.styleUrls;
            this.moduleUrl = moduleUrl || null;
            this.styles = _normalizeArray(styles);
            this.styleUrls = _normalizeArray(styleUrls);
        }
        return CompileStylesheetMetadata;
    }());
    exports.CompileStylesheetMetadata = CompileStylesheetMetadata;
    /**
     * Metadata regarding compilation of a template.
     */
    var CompileTemplateMetadata = /** @class */ (function () {
        function CompileTemplateMetadata(_a) {
            var encapsulation = _a.encapsulation, template = _a.template, templateUrl = _a.templateUrl, htmlAst = _a.htmlAst, styles = _a.styles, styleUrls = _a.styleUrls, externalStylesheets = _a.externalStylesheets, animations = _a.animations, ngContentSelectors = _a.ngContentSelectors, interpolation = _a.interpolation, isInline = _a.isInline, preserveWhitespaces = _a.preserveWhitespaces;
            this.encapsulation = encapsulation;
            this.template = template;
            this.templateUrl = templateUrl;
            this.htmlAst = htmlAst;
            this.styles = _normalizeArray(styles);
            this.styleUrls = _normalizeArray(styleUrls);
            this.externalStylesheets = _normalizeArray(externalStylesheets);
            this.animations = animations ? flatten(animations) : [];
            this.ngContentSelectors = ngContentSelectors || [];
            if (interpolation && interpolation.length != 2) {
                throw new Error("'interpolation' should have a start and an end symbol.");
            }
            this.interpolation = interpolation;
            this.isInline = isInline;
            this.preserveWhitespaces = preserveWhitespaces;
        }
        CompileTemplateMetadata.prototype.toSummary = function () {
            return {
                ngContentSelectors: this.ngContentSelectors,
                encapsulation: this.encapsulation,
                styles: this.styles,
                animations: this.animations
            };
        };
        return CompileTemplateMetadata;
    }());
    exports.CompileTemplateMetadata = CompileTemplateMetadata;
    /**
     * Metadata regarding compilation of a directive.
     */
    var CompileDirectiveMetadata = /** @class */ (function () {
        function CompileDirectiveMetadata(_a) {
            var isHost = _a.isHost, type = _a.type, isComponent = _a.isComponent, selector = _a.selector, exportAs = _a.exportAs, changeDetection = _a.changeDetection, inputs = _a.inputs, outputs = _a.outputs, hostListeners = _a.hostListeners, hostProperties = _a.hostProperties, hostAttributes = _a.hostAttributes, providers = _a.providers, viewProviders = _a.viewProviders, queries = _a.queries, guards = _a.guards, viewQueries = _a.viewQueries, entryComponents = _a.entryComponents, template = _a.template, componentViewType = _a.componentViewType, rendererType = _a.rendererType, componentFactory = _a.componentFactory;
            this.isHost = !!isHost;
            this.type = type;
            this.isComponent = isComponent;
            this.selector = selector;
            this.exportAs = exportAs;
            this.changeDetection = changeDetection;
            this.inputs = inputs;
            this.outputs = outputs;
            this.hostListeners = hostListeners;
            this.hostProperties = hostProperties;
            this.hostAttributes = hostAttributes;
            this.providers = _normalizeArray(providers);
            this.viewProviders = _normalizeArray(viewProviders);
            this.queries = _normalizeArray(queries);
            this.guards = guards;
            this.viewQueries = _normalizeArray(viewQueries);
            this.entryComponents = _normalizeArray(entryComponents);
            this.template = template;
            this.componentViewType = componentViewType;
            this.rendererType = rendererType;
            this.componentFactory = componentFactory;
        }
        CompileDirectiveMetadata.create = function (_a) {
            var isHost = _a.isHost, type = _a.type, isComponent = _a.isComponent, selector = _a.selector, exportAs = _a.exportAs, changeDetection = _a.changeDetection, inputs = _a.inputs, outputs = _a.outputs, host = _a.host, providers = _a.providers, viewProviders = _a.viewProviders, queries = _a.queries, guards = _a.guards, viewQueries = _a.viewQueries, entryComponents = _a.entryComponents, template = _a.template, componentViewType = _a.componentViewType, rendererType = _a.rendererType, componentFactory = _a.componentFactory;
            var hostListeners = {};
            var hostProperties = {};
            var hostAttributes = {};
            if (host != null) {
                Object.keys(host).forEach(function (key) {
                    var value = host[key];
                    var matches = key.match(HOST_REG_EXP);
                    if (matches === null) {
                        hostAttributes[key] = value;
                    }
                    else if (matches[1] != null) {
                        hostProperties[matches[1]] = value;
                    }
                    else if (matches[2] != null) {
                        hostListeners[matches[2]] = value;
                    }
                });
            }
            var inputsMap = {};
            if (inputs != null) {
                inputs.forEach(function (bindConfig) {
                    // canonical syntax: `dirProp: elProp`
                    // if there is no `:`, use dirProp = elProp
                    var parts = util_1.splitAtColon(bindConfig, [bindConfig, bindConfig]);
                    inputsMap[parts[0]] = parts[1];
                });
            }
            var outputsMap = {};
            if (outputs != null) {
                outputs.forEach(function (bindConfig) {
                    // canonical syntax: `dirProp: elProp`
                    // if there is no `:`, use dirProp = elProp
                    var parts = util_1.splitAtColon(bindConfig, [bindConfig, bindConfig]);
                    outputsMap[parts[0]] = parts[1];
                });
            }
            return new CompileDirectiveMetadata({
                isHost: isHost,
                type: type,
                isComponent: !!isComponent,
                selector: selector,
                exportAs: exportAs,
                changeDetection: changeDetection,
                inputs: inputsMap,
                outputs: outputsMap,
                hostListeners: hostListeners,
                hostProperties: hostProperties,
                hostAttributes: hostAttributes,
                providers: providers,
                viewProviders: viewProviders,
                queries: queries,
                guards: guards,
                viewQueries: viewQueries,
                entryComponents: entryComponents,
                template: template,
                componentViewType: componentViewType,
                rendererType: rendererType,
                componentFactory: componentFactory,
            });
        };
        CompileDirectiveMetadata.prototype.toSummary = function () {
            return {
                summaryKind: CompileSummaryKind.Directive,
                type: this.type,
                isComponent: this.isComponent,
                selector: this.selector,
                exportAs: this.exportAs,
                inputs: this.inputs,
                outputs: this.outputs,
                hostListeners: this.hostListeners,
                hostProperties: this.hostProperties,
                hostAttributes: this.hostAttributes,
                providers: this.providers,
                viewProviders: this.viewProviders,
                queries: this.queries,
                guards: this.guards,
                viewQueries: this.viewQueries,
                entryComponents: this.entryComponents,
                changeDetection: this.changeDetection,
                template: this.template && this.template.toSummary(),
                componentViewType: this.componentViewType,
                rendererType: this.rendererType,
                componentFactory: this.componentFactory
            };
        };
        return CompileDirectiveMetadata;
    }());
    exports.CompileDirectiveMetadata = CompileDirectiveMetadata;
    var CompilePipeMetadata = /** @class */ (function () {
        function CompilePipeMetadata(_a) {
            var type = _a.type, name = _a.name, pure = _a.pure;
            this.type = type;
            this.name = name;
            this.pure = !!pure;
        }
        CompilePipeMetadata.prototype.toSummary = function () {
            return {
                summaryKind: CompileSummaryKind.Pipe,
                type: this.type,
                name: this.name,
                pure: this.pure
            };
        };
        return CompilePipeMetadata;
    }());
    exports.CompilePipeMetadata = CompilePipeMetadata;
    var CompileShallowModuleMetadata = /** @class */ (function () {
        function CompileShallowModuleMetadata() {
        }
        return CompileShallowModuleMetadata;
    }());
    exports.CompileShallowModuleMetadata = CompileShallowModuleMetadata;
    /**
     * Metadata regarding compilation of a module.
     */
    var CompileNgModuleMetadata = /** @class */ (function () {
        function CompileNgModuleMetadata(_a) {
            var type = _a.type, providers = _a.providers, declaredDirectives = _a.declaredDirectives, exportedDirectives = _a.exportedDirectives, declaredPipes = _a.declaredPipes, exportedPipes = _a.exportedPipes, entryComponents = _a.entryComponents, bootstrapComponents = _a.bootstrapComponents, importedModules = _a.importedModules, exportedModules = _a.exportedModules, schemas = _a.schemas, transitiveModule = _a.transitiveModule, id = _a.id;
            this.type = type || null;
            this.declaredDirectives = _normalizeArray(declaredDirectives);
            this.exportedDirectives = _normalizeArray(exportedDirectives);
            this.declaredPipes = _normalizeArray(declaredPipes);
            this.exportedPipes = _normalizeArray(exportedPipes);
            this.providers = _normalizeArray(providers);
            this.entryComponents = _normalizeArray(entryComponents);
            this.bootstrapComponents = _normalizeArray(bootstrapComponents);
            this.importedModules = _normalizeArray(importedModules);
            this.exportedModules = _normalizeArray(exportedModules);
            this.schemas = _normalizeArray(schemas);
            this.id = id || null;
            this.transitiveModule = transitiveModule || null;
        }
        CompileNgModuleMetadata.prototype.toSummary = function () {
            var module = this.transitiveModule;
            return {
                summaryKind: CompileSummaryKind.NgModule,
                type: this.type,
                entryComponents: module.entryComponents,
                providers: module.providers,
                modules: module.modules,
                exportedDirectives: module.exportedDirectives,
                exportedPipes: module.exportedPipes
            };
        };
        return CompileNgModuleMetadata;
    }());
    exports.CompileNgModuleMetadata = CompileNgModuleMetadata;
    var TransitiveCompileNgModuleMetadata = /** @class */ (function () {
        function TransitiveCompileNgModuleMetadata() {
            this.directivesSet = new Set();
            this.directives = [];
            this.exportedDirectivesSet = new Set();
            this.exportedDirectives = [];
            this.pipesSet = new Set();
            this.pipes = [];
            this.exportedPipesSet = new Set();
            this.exportedPipes = [];
            this.modulesSet = new Set();
            this.modules = [];
            this.entryComponentsSet = new Set();
            this.entryComponents = [];
            this.providers = [];
        }
        TransitiveCompileNgModuleMetadata.prototype.addProvider = function (provider, module) {
            this.providers.push({ provider: provider, module: module });
        };
        TransitiveCompileNgModuleMetadata.prototype.addDirective = function (id) {
            if (!this.directivesSet.has(id.reference)) {
                this.directivesSet.add(id.reference);
                this.directives.push(id);
            }
        };
        TransitiveCompileNgModuleMetadata.prototype.addExportedDirective = function (id) {
            if (!this.exportedDirectivesSet.has(id.reference)) {
                this.exportedDirectivesSet.add(id.reference);
                this.exportedDirectives.push(id);
            }
        };
        TransitiveCompileNgModuleMetadata.prototype.addPipe = function (id) {
            if (!this.pipesSet.has(id.reference)) {
                this.pipesSet.add(id.reference);
                this.pipes.push(id);
            }
        };
        TransitiveCompileNgModuleMetadata.prototype.addExportedPipe = function (id) {
            if (!this.exportedPipesSet.has(id.reference)) {
                this.exportedPipesSet.add(id.reference);
                this.exportedPipes.push(id);
            }
        };
        TransitiveCompileNgModuleMetadata.prototype.addModule = function (id) {
            if (!this.modulesSet.has(id.reference)) {
                this.modulesSet.add(id.reference);
                this.modules.push(id);
            }
        };
        TransitiveCompileNgModuleMetadata.prototype.addEntryComponent = function (ec) {
            if (!this.entryComponentsSet.has(ec.componentType)) {
                this.entryComponentsSet.add(ec.componentType);
                this.entryComponents.push(ec);
            }
        };
        return TransitiveCompileNgModuleMetadata;
    }());
    exports.TransitiveCompileNgModuleMetadata = TransitiveCompileNgModuleMetadata;
    function _normalizeArray(obj) {
        return obj || [];
    }
    var ProviderMeta = /** @class */ (function () {
        function ProviderMeta(token, _a) {
            var useClass = _a.useClass, useValue = _a.useValue, useExisting = _a.useExisting, useFactory = _a.useFactory, deps = _a.deps, multi = _a.multi;
            this.token = token;
            this.useClass = useClass || null;
            this.useValue = useValue;
            this.useExisting = useExisting;
            this.useFactory = useFactory || null;
            this.dependencies = deps || null;
            this.multi = !!multi;
        }
        return ProviderMeta;
    }());
    exports.ProviderMeta = ProviderMeta;
    function flatten(list) {
        return list.reduce(function (flat, item) {
            var flatItem = Array.isArray(item) ? flatten(item) : item;
            return flat.concat(flatItem);
        }, []);
    }
    exports.flatten = flatten;
    function jitSourceUrl(url) {
        // Note: We need 3 "/" so that ng shows up as a separate domain
        // in the chrome dev tools.
        return url.replace(/(\w+:\/\/[\w:-]+)?(\/+)?/, 'ng:///');
    }
    function templateSourceUrl(ngModuleType, compMeta, templateMeta) {
        var url;
        if (templateMeta.isInline) {
            if (compMeta.type.reference instanceof static_symbol_1.StaticSymbol) {
                // Note: a .ts file might contain multiple components with inline templates,
                // so we need to give them unique urls, as these will be used for sourcemaps.
                url = compMeta.type.reference.filePath + "." + compMeta.type.reference.name + ".html";
            }
            else {
                url = identifierName(ngModuleType) + "/" + identifierName(compMeta.type) + ".html";
            }
        }
        else {
            url = templateMeta.templateUrl;
        }
        return compMeta.type.reference instanceof static_symbol_1.StaticSymbol ? url : jitSourceUrl(url);
    }
    exports.templateSourceUrl = templateSourceUrl;
    function sharedStylesheetJitUrl(meta, id) {
        var pathParts = meta.moduleUrl.split(/\/\\/g);
        var baseName = pathParts[pathParts.length - 1];
        return jitSourceUrl("css/" + id + baseName + ".ngstyle.js");
    }
    exports.sharedStylesheetJitUrl = sharedStylesheetJitUrl;
    function ngModuleJitUrl(moduleMeta) {
        return jitSourceUrl(identifierName(moduleMeta.type) + "/module.ngfactory.js");
    }
    exports.ngModuleJitUrl = ngModuleJitUrl;
    function templateJitUrl(ngModuleType, compMeta) {
        return jitSourceUrl(identifierName(ngModuleType) + "/" + identifierName(compMeta.type) + ".ngfactory.js");
    }
    exports.templateJitUrl = templateJitUrl;
});
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiY29tcGlsZV9tZXRhZGF0YS5qcyIsInNvdXJjZVJvb3QiOiIiLCJzb3VyY2VzIjpbIi4uLy4uLy4uLy4uLy4uLy4uL3BhY2thZ2VzL2NvbXBpbGVyL3NyYy9jb21waWxlX21ldGFkYXRhLnRzIl0sIm5hbWVzIjpbXSwibWFwcGluZ3MiOiJBQUFBOzs7Ozs7R0FNRzs7Ozs7Ozs7Ozs7OztJQUVILHlFQUFpRDtJQUlqRCxtREFBK0M7SUFFL0MsMkNBQTJDO0lBQzNDLGdDQUFnQztJQUNoQyxrQ0FBa0M7SUFDbEMsc0NBQXNDO0lBQ3RDLElBQU0sWUFBWSxHQUFHLG9EQUFvRCxDQUFDO0lBRTFFLFNBQWdCLGtCQUFrQixDQUFDLElBQVk7UUFDN0MsT0FBTyxJQUFJLENBQUMsT0FBTyxDQUFDLEtBQUssRUFBRSxHQUFHLENBQUMsQ0FBQztJQUNsQyxDQUFDO0lBRkQsZ0RBRUM7SUFFRCxJQUFJLG1CQUFtQixHQUFHLENBQUMsQ0FBQztJQUU1QixTQUFnQixjQUFjLENBQUMsaUJBQTJEO1FBRXhGLElBQUksQ0FBQyxpQkFBaUIsSUFBSSxDQUFDLGlCQUFpQixDQUFDLFNBQVMsRUFBRTtZQUN0RCxPQUFPLElBQUksQ0FBQztTQUNiO1FBQ0QsSUFBTSxHQUFHLEdBQUcsaUJBQWlCLENBQUMsU0FBUyxDQUFDO1FBQ3hDLElBQUksR0FBRyxZQUFZLDRCQUFZLEVBQUU7WUFDL0IsT0FBTyxHQUFHLENBQUMsSUFBSSxDQUFDO1NBQ2pCO1FBQ0QsSUFBSSxHQUFHLENBQUMsaUJBQWlCLENBQUMsRUFBRTtZQUMxQixPQUFPLEdBQUcsQ0FBQyxpQkFBaUIsQ0FBQyxDQUFDO1NBQy9CO1FBQ0QsSUFBSSxVQUFVLEdBQUcsZ0JBQVMsQ0FBQyxHQUFHLENBQUMsQ0FBQztRQUNoQyxJQUFJLFVBQVUsQ0FBQyxPQUFPLENBQUMsR0FBRyxDQUFDLElBQUksQ0FBQyxFQUFFO1lBQ2hDLDZCQUE2QjtZQUM3QixVQUFVLEdBQUcsZUFBYSxtQkFBbUIsRUFBSSxDQUFDO1lBQ2xELEdBQUcsQ0FB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QUpELHdDQUlDIiwic291cmNlc0NvbnRlbnQiOlsiLyoqXG4gKiBAbGljZW5zZVxuICogQ29weXJpZ2h0IEdvb2dsZSBMTEMgQWxsIFJpZ2h0cyBSZXNlcnZlZC5cbiAqXG4gKiBVc2Ugb2YgdGhpcyBzb3VyY2UgY29kZSBpcyBnb3Zlcm5lZCBieSBhbiBNSVQtc3R5bGUgbGljZW5zZSB0aGF0IGNhbiBiZVxuICogZm91bmQgaW4gdGhlIExJQ0VOU0UgZmlsZSBhdCBodHRwczovL2FuZ3VsYXIuaW8vbGljZW5zZVxuICovXG5cbmltcG9ydCB7U3RhdGljU3ltYm9sfSBmcm9tICcuL2FvdC9zdGF0aWNfc3ltYm9sJztcbmltcG9ydCB7Q2hhbmdlRGV0ZWN0aW9uU3RyYXRlZ3ksIFNjaGVtYU1ldGFkYXRhLCBUeXBlLCBWaWV3RW5jYXBzdWxhdGlvbn0gZnJvbSAnLi9jb3JlJztcbmltcG9ydCB7TGlmZWN5Y2xlSG9va3N9IGZyb20gJy4vbGlmZWN5Y2xlX3JlZmxlY3Rvcic7XG5pbXBvcnQge1BhcnNlVHJlZVJlc3VsdCBhcyBIdG1sUGFyc2VUcmVlUmVzdWx0fSBmcm9tICcuL21sX3BhcnNlci9wYXJzZXInO1xuaW1wb3J0IHtzcGxpdEF0Q29sb24sIHN0cmluZ2lmeX0gZnJvbSAnLi91dGlsJztcblxuLy8gZ3JvdXAgMDogXCJbcHJvcF0gb3IgKGV2ZW50KSBvciBAdHJpZ2dlclwiXG4vLyBncm91cCAxOiBcInByb3BcIiBmcm9tIFwiW3Byb3BdXCJcbi8vIGdyb3VwIDI6IFwiZXZlbnRcIiBmcm9tIFwiKGV2ZW50KVwiXG4vLyBncm91cCAzOiBcIkB0cmlnZ2VyXCIgZnJvbSBcIkB0cmlnZ2VyXCJcbmNvbnN0IEhPU1RfUkVHX0VYUCA9IC9eKD86KD86XFxbKFteXFxdXSspXFxdKXwoPzpcXCgoW15cXCldKylcXCkpKXwoXFxAWy1cXHddKykkLztcblxuZXhwb3J0IGZ1bmN0aW9uIHNhbml0aXplSWRlbnRpZmllcihuYW1lOiBzdHJpbmcpOiBzdHJpbmcge1xuICByZXR1cm4gbmFtZS5yZXBsYWNlKC9cXFcvZywgJ18nKTtcbn1cblxubGV0IF9hbm9ueW1vdXNUeXBlSW5kZXggPSAwO1xuXG5leHBvcnQgZnVuY3Rpb24gaWRlbnRpZmllck5hbWUoY29tcGlsZUlkZW50aWZpZXI6IENvbXBpbGVJZGVudGlmaWVyTWV0YWRhdGF8bnVsbHx1bmRlZmluZWQpOiBzdHJpbmd8XG4gICAgbnVsbCB7XG4gIGlmICghY29tcGlsZUlkZW50aWZpZXIgfHwgIWNvbXBpbGVJZGVudGlmaWVyLnJlZmVyZW5jZSkge1xuICAgIHJldHVybiBudWxsO1xuICB9XG4gIGNvbnN0IHJlZiA9IGNvbXBpbGVJZGVudGlmaWVyLnJlZmVyZW5jZTtcbiAgaWYgKHJlZiBpbnN0YW5jZW9mIFN0YXRpY1N5bWJvbCkge1xuICAgIHJldHVybiByZWYubmFtZTtcbiAgfVxuICBpZiAocmVmWydfX2Fub255bW91c1R5cGUnXSkge1xuICAgIHJldHVybiByZWZbJ19fYW5vbnltb3VzVHlwZSddO1xuICB9XG4gIGxldCBpZGVudGlmaWVyID0gc3RyaW5naWZ5KHJlZik7XG4gIGlmIChpZGVudGlmaWVyLmluZGV4T2YoJygnKSA+PSAwKSB7XG4gICAgLy8gY2FzZTogYW5vbnltb3VzIGZ1bmN0aW9ucyFcbiAgICBpZGVudGlmaWVyID0gYGFub255bW91c18ke19hbm9ueW1vdXNUeXBlSW5kZXgrK31gO1xuICAgIHJlZlsnX19hbm9ueW1vdXNUeXBlJ10gPSBpZGVudGlmaWVyO1xuICB9IGVsc2Uge1xuICAgIGlkZW50aWZpZXIgPSBzYW5pdGl6ZUlkZW50aWZpZXIoaWRlbnRpZmllcik7XG4gIH1cbiAgcmV0dXJuIGlkZW50aWZpZXI7XG59XG5cbmV4cG9ydCBmdW5jdGlvbiBpZGVudGlmaWVyTW9kdWxlVXJsKGNvbXBpbGVJZGVudGlmaWVyOiBDb21waWxlSWRlbnRpZmllck1ldGFkYXRhKTogc3RyaW5nIHtcbiAgY29uc3QgcmVmID0gY29tcGlsZUlkZW50aWZpZXIucmVmZXJlbmNlO1xuICBpZiAocmVmIGluc3RhbmNlb2YgU3RhdGljU3ltYm9sKSB7XG4gICAgcmV0dXJuIHJlZi5maWxlUGF0aDtcbiAgfVxuICAvLyBSdW50aW1lIHR5cGVcbiAgcmV0dXJuIGAuLyR7c3RyaW5naWZ5KHJlZil9YDtcbn1cblxuZXhwb3J0IGZ1bmN0aW9uIHZpZXdDbGFzc05hbWUoY29tcFR5cGU6IGFueSwgZW1iZWRkZWRUZW1wbGF0ZUluZGV4OiBudW1iZXIpOiBzdHJpbmcge1xuICByZXR1cm4gYFZpZXdfJHtpZGVudGlmaWVyTmFtZSh7cmVmZXJlbmNlOiBjb21wVHlwZX0pfV8ke2VtYmVkZGVkVGVtcGxhdGVJbmRleH1gO1xufVxuXG5leHBvcnQgZnVuY3Rpb24gcmVuZGVyZXJUeXBlTmFtZShjb21wVHlwZTogYW55KTogc3RyaW5nIHtcbiAgcmV0dXJuIGBSZW5kZXJUeXBlXyR7aWRlbnRpZmllck5hbWUoe3JlZmVyZW5jZTogY29tcFR5cGV9KX1gO1xufVxuXG5leHBvcnQgZnVuY3Rpb24gaG9zdFZpZXdDbGFzc05hbWUoY29tcFR5cGU6IGFueSk6IHN0cmluZyB7XG4gIHJldHVybiBgSG9zdFZpZXdfJHtpZGVudGlmaWVyTmFtZSh7cmVmZXJlbmNlOiBjb21wVHlwZX0pfWA7XG59XG5cbmV4cG9ydCBmdW5jdGlvbiBjb21wb25lbnRGYWN0b3J5TmFtZShjb21wVHlwZTogYW55KTogc3RyaW5nIHtcbiAgcmV0dXJuIGAke2lkZW50aWZpZXJOYW1lKHtyZWZlcmVuY2U6IGNvbXBUeXBlfSl9TmdGYWN0b3J5YDtcbn1cblxuZXhwb3J0IGludGVyZmFjZSBQcm94eUNsYXNzIHtcbiAgc2V0RGVsZWdhdGUoZGVsZWdhdGU6IGFueSk6IHZvaWQ7XG59XG5cbmV4cG9ydCBpbnRlcmZhY2UgQ29tcGlsZUlkZW50aWZpZXJNZXRhZGF0YSB7XG4gIHJlZmVyZW5jZTogYW55O1xufVxuXG5leHBvcnQgZW51bSBDb21waWxlU3VtbWFyeUtpbmQge1xuICBQaXBlLFxuICBEaXJlY3RpdmUsXG4gIE5nTW9kdWxlLFxuICBJbmplY3RhYmxlXG59XG5cbi8qKlxuICogQSBDb21waWxlU3VtbWFyeSBpcyB0aGUgZGF0YSBuZWVkZWQgdG8gdXNlIGEgZGlyZWN0aXZlIC8gcGlwZSAvIG1vZHVsZVxuICogaW4gb3RoZXIgbW9kdWxlcyAvIGNvbXBvbmVudHMuIEhvd2V2ZXIsIHRoaXMgZGF0YSBpcyBub3QgZW5vdWdoIHRvIGNvbXBpbGVcbiAqIHRoZSBkaXJlY3RpdmUgLyBtb2R1bGUgaXRzZWxmLlxuICovXG5leHBvcnQgaW50ZXJmYWNlIENvbXBpbGVUeXBlU3VtbWFyeSB7XG4gIHN1bW1hcnlLaW5kOiBDb21waWxlU3VtbWFyeUtpbmR8bnVsbDtcbiAgdHlwZTogQ29tcGlsZVR5cGVNZXRhZGF0YTtcbn1cblxuZXhwb3J0IGludGVyZmFjZSBDb21waWxlRGlEZXBlbmRlbmN5TWV0YWRhdGEge1xuICBpc0F0dHJpYnV0ZT86IGJvb2xlYW47XG4gIGlzU2VsZj86IGJvb2xlYW47XG4gIGlzSG9zdD86IGJvb2xlYW47XG4gIGlzU2tpcFNlbGY/OiBib29sZWFuO1xuICBpc09wdGlvbmFsPzogYm9vbGVhbjtcbiAgaXNWYWx1ZT86IGJvb2xlYW47XG4gIHRva2VuPzogQ29tcGlsZVRva2VuTWV0YWRhdGE7XG4gIHZhbHVlPzogYW55O1xufVxuXG5leHBvcnQgaW50ZXJmYWNlIENvbXBpbGVQcm92aWRlck1ldGFkYXRhIHtcbiAgdG9rZW46IENvbXBpbGVUb2tlbk1ldGFkYXRhO1xuICB1c2VDbGFzcz86IENvbXBpbGVUeXBlTWV0YWRhdGE7XG4gIHVzZVZhbHVlPzogYW55O1xuICB1c2VFeGlzdGluZz86IENvbXBpbGVUb2tlbk1ldGFkYXRhO1xuICB1c2VGYWN0b3J5PzogQ29tcGlsZUZhY3RvcnlNZXRhZGF0YTtcbiAgZGVwcz86IENvbXBpbGVEaURlcGVuZGVuY3lNZXRhZGF0YVtdO1xuICBtdWx0aT86IGJvb2xlYW47XG59XG5cbmV4cG9ydCBpbnRlcmZhY2UgQ29tcGlsZUZhY3RvcnlNZXRhZGF0YSBleHRlbmRzIENvbXBpbGVJZGVudGlmaWVyTWV0YWRhdGEge1xuICBkaURlcHM6IENvbXBpbGVEaURlcGVuZGVuY3lNZXRhZGF0YVtdO1xuICByZWZlcmVuY2U6IGFueTtcbn1cblxuZXhwb3J0IGZ1bmN0aW9uIHRva2VuTmFtZSh0b2tlbjogQ29tcGlsZVRva2VuTWV0YWRhdGEpIHtcbiAgcmV0dXJuIHRva2VuLnZhbHVlICE9IG51bGwgPyBzYW5pdGl6ZUlkZW50aWZpZXIodG9rZW4udmFsdWUpIDogaWRlbnRpZmllck5hbWUodG9rZW4uaWRlbnRpZmllcik7XG59XG5cbmV4cG9ydCBmdW5jdGlvbiB0b2tlblJlZmVyZW5jZSh0b2tlbjogQ29tcGlsZVRva2VuTWV0YWRhdGEpIHtcbiAgaWYgKHRva2VuLmlkZW50aWZpZXIgIT0gbnVsbCkge1xuICAgIHJldHVybiB0b2tlbi5pZGVudGlmaWVyLnJlZmVyZW5jZTtcbiAgfSBlbHNlIHtcbiAgICByZXR1cm4gdG9rZW4udmFsdWU7XG4gIH1cbn1cblxuZXhwb3J0IGludGVyZmFjZSBDb21waWxlVG9rZW5NZXRhZGF0YSB7XG4gIHZhbHVlPzogYW55O1xuICBpZGVudGlmaWVyPzogQ29tcGlsZUlkZW50aWZpZXJNZXRhZGF0YXxDb21waWxlVHlwZU1ldGFkYXRhO1xufVxuXG5leHBvcnQgaW50ZXJmYWNlIENvbXBpbGVJbmplY3RhYmxlTWV0YWRhdGEge1xuICBzeW1ib2w6IFN0YXRpY1N5bWJvbDtcbiAgdHlwZTogQ29tcGlsZVR5cGVNZXRhZGF0YTtcblxuICBwcm92aWRlZEluPzogU3RhdGljU3ltYm9sO1xuXG4gIHVzZVZhbHVlPzogYW55O1xuICB1c2VDbGFzcz86IFN0YXRpY1N5bWJvbDtcbiAgdXNlRXhpc3Rpbmc/OiBTdGF0aWNTeW1ib2w7XG4gIHVzZUZhY3Rvcnk/OiBTdGF0aWNTeW1ib2w7XG4gIGRlcHM/OiBhbnlbXTtcbn1cblxuLyoqXG4gKiBNZXRhZGF0YSByZWdhcmRpbmcgY29tcGlsYXRpb24gb2YgYSB0eXBlLlxuICovXG5leHBvcnQgaW50ZXJmYWNlIENvbXBpbGVUeXBlTWV0YWRhdGEgZXh0ZW5kcyBDb21waWxlSWRlbnRpZmllck1ldGFkYXRhIHtcbiAgZGlEZXBzOiBDb21waWxlRGlEZXBlbmRlbmN5TWV0YWRhdGFbXTtcbiAgbGlmZWN5Y2xlSG9va3M6IExpZmVjeWNsZUhvb2tzW107XG4gIHJlZmVyZW5jZTogYW55O1xufVxuXG5leHBvcnQgaW50ZXJmYWNlIENvbXBpbGVRdWVyeU1ldGFkYXRhIHtcbiAgc2VsZWN0b3JzOiBBcnJheTxDb21waWxlVG9rZW5NZXRhZGF0YT47XG4gIGRlc2NlbmRhbnRzOiBib29sZWFuO1xuICBmaXJzdDogYm9vbGVhbjtcbiAgcHJvcGVydHlOYW1lOiBzdHJpbmc7XG4gIHJlYWQ6IENvbXBpbGVUb2tlbk1ldGFkYXRhO1xuICBzdGF0aWM/OiBib29sZWFuO1xuICBlbWl0RGlzdGluY3RDaGFuZ2VzT25seT86IGJvb2xlYW47XG59XG5cbi8qKlxuICogTWV0YWRhdGEgYWJvdXQgYSBzdHlsZXNoZWV0XG4gKi9cbmV4cG9ydCBjbGFzcyBDb21waWxlU3R5bGVzaGVldE1ldGFkYXRhIHtcbiAgbW9kdWxlVXJsOiBzdHJpbmd8bnVsbDtcbiAgc3R5bGVzOiBzdHJpbmdbXTtcbiAgc3R5bGVVcmxzOiBzdHJpbmdbXTtcbiAgY29uc3RydWN0b3IoXG4gICAgICB7bW9kdWxlVXJsLCBzdHlsZXMsIHN0eWxlVXJsc306XG4gICAgICAgICAge21vZHVsZVVybD86IHN0cmluZywgc3R5bGVzPzogc3RyaW5nW10sIHN0eWxlVXJscz86IHN0cmluZ1tdfSA9IHt9KSB7XG4gICAgdGhpcy5tb2R1bGVVcmwgPSBtb2R1bGVVcmwgfHwgbnVsbDtcbiAgICB0aGlzLnN0eWxlcyA9IF9ub3JtYWxpemVBcnJheShzdHlsZXMpO1xuICAgIHRoaXMuc3R5bGVVcmxzID0gX25vcm1hbGl6ZUFycmF5KHN0eWxlVXJscyk7XG4gIH1cbn1cblxuLyoqXG4gKiBTdW1tYXJ5IE1ldGFkYXRhIHJlZ2FyZGluZyBjb21waWxhdGlvbiBvZiBhIHRlbXBsYXRlLlxuICovXG5leHBvcnQgaW50ZXJmYWNlIENvbXBpbGVUZW1wbGF0ZVN1bW1hcnkge1xuICBuZ0NvbnRlbnRTZWxlY3RvcnM6IHN0cmluZ1tdO1xuICBlbmNhcHN1bGF0aW9uOiBWaWV3RW5jYXBzdWxhdGlvbnxudWxsO1xuICBzdHlsZXM6IHN0cmluZ1tdO1xuICBhbmltYXRpb25zOiBhbnlbXXxudWxsO1xufVxuXG4vKipcbiAqIE1ldGFkYXRhIHJlZ2FyZGluZyBjb21waWxhdGlvbiBvZiBhIHRlbXBsYXRlLlxuICovXG5leHBvcnQgY2xhc3MgQ29tcGlsZVRlbXBsYXRlTWV0YWRhdGEge1xuICBlbmNhcHN1bGF0aW9uOiBWaWV3RW5jYXBzdWxhdGlvbnxudWxsO1xuICB0ZW1wbGF0ZTogc3RyaW5nfG51bGw7XG4gIHRlbXBsYXRlVXJsOiBzdHJpbmd8bnVsbDtcbiAgaHRtbEFzdDogSHRtbFBhcnNlVHJlZVJlc3VsdHxudWxsO1xuICBpc0lubGluZTogYm9vbGVhbjtcbiAgc3R5bGVzOiBzdHJpbmdbXTtcbiAgc3R5bGVVcmxzOiBzdHJpbmdbXTtcbiAgZXh0ZXJuYWxTdHlsZXNoZWV0czogQ29tcGlsZVN0eWxlc2hlZXRNZXRhZGF0YVtdO1xuICBhbmltYXRpb25zOiBhbnlbXTtcbiAgbmdDb250ZW50U2VsZWN0b3JzOiBzdHJpbmdbXTtcbiAgaW50ZXJwb2xhdGlvbjogW3N0cmluZywgc3RyaW5nXXxudWxsO1xuICBwcmVzZXJ2ZVdoaXRlc3BhY2VzOiBib29sZWFuO1xuICBjb25zdHJ1Y3Rvcih7XG4gICAgZW5jYXBzdWxhdGlvbixcbiAgICB0ZW1wbGF0ZSxcbiAgICB0ZW1wbGF0ZVVybCxcbiAgICBodG1sQXN0LFxuICAgIHN0eWxlcyxcbiAgICBzdHlsZVVybHMsXG4gICAgZXh0ZXJuYWxTdHlsZXNoZWV0cyxcbiAgICBhbmltYXRpb25zLFxuICAgIG5nQ29udGVudFNlbGVjdG9ycyxcbiAgICBpbnRlcnBvbGF0aW9uLFxuICAgIGlzSW5saW5lLFxuICAgIHByZXNlcnZlV2hpdGVzcGFjZXNcbiAgfToge1xuICAgIGVuY2Fwc3VsYXRpb246IFZpZXdFbmNhcHN1bGF0aW9ufG51bGwsXG4gICAgdGVtcGxhdGU6IHN0cmluZ3xudWxsLFxuICAgIHRlbXBsYXRlVXJsOiBzdHJpbmd8bnVsbCxcbiAgICBodG1sQXN0OiBIdG1sUGFyc2VUcmVlUmVzdWx0fG51bGwsXG4gICAgc3R5bGVzOiBzdHJpbmdbXSxcbiAgICBzdHlsZVVybHM6IHN0cmluZ1tdLFxuICAgIGV4dGVybmFsU3R5bGVzaGVldHM6IENvbXBpbGVTdHlsZXNoZWV0TWV0YWRhdGFbXSxcbiAgICBuZ0NvbnRlbnRTZWxlY3RvcnM6IHN0cmluZ1tdLFxuICAgIGFuaW1hdGlvbnM6IGFueVtdLFxuICAgIGludGVycG9sYXRpb246IFtzdHJpbmcsIHN0cmluZ118bnVsbCxcbiAgICBpc0lubGluZTogYm9vbGVhbixcbiAgICBwcmVzZXJ2ZVdoaXRlc3BhY2VzOiBib29sZWFuXG4gIH0pIHtcbiAgICB0aGlzLmVuY2Fwc3VsYXRpb24gPSBlbmNhcHN1bGF0aW9uO1xuICAgIHRoaXMudGVtcGxhdGUgPSB0ZW1wbGF0ZTtcbiAgICB0aGlzLnRlbXBsYXRlVXJsID0gdGVtcGxhdGVVcmw7XG4gICAgdGhpcy5odG1sQXN0ID0gaHRtbEFzdDtcbiAgICB0aGlzLnN0eWxlcyA9IF9ub3JtYWxpemVBcnJheShzdHlsZXMpO1xuICAgIHRoaXMuc3R5bGVVcmxzID0gX25vcm1hbGl6ZUFycmF5KHN0eWxlVXJscyk7XG4gICAgdGhpcy5leHRlcm5hbFN0eWxlc2hlZXRzID0gX25vcm1hbGl6ZUFycmF5KGV4dGVybmFsU3R5bGVzaGVldHMpO1xuICAgIHRoaXMuYW5pbWF0aW9ucyA9IGFuaW1hdGlvbnMgPyBmbGF0dGVuKGFuaW1hdGlvbnMpIDogW107XG4gICAgdGhpcy5uZ0NvbnRlbnRTZWxlY3RvcnMgPSBuZ0NvbnRlbnRTZWxlY3RvcnMgfHwgW107XG4gICAgaWYgKGludGVycG9sYXRpb24gJiYgaW50ZXJwb2xhdGlvbi5sZW5ndGggIT0gMikge1xuICAgICAgdGhyb3cgbmV3IEVycm9yKGAnaW50ZXJwb2xhdGlvbicgc2hvdWxkIGhhdmUgYSBzdGFydCBhbmQgYW4gZW5kIHN5bWJvbC5gKTtcbiAgICB9XG4gICAgdGhpcy5pbnRlcnBvbGF0aW9uID0gaW50ZXJwb2xhdGlvbjtcbiAgICB0aGlzLmlzSW5saW5lID0gaXNJbmxpbmU7XG4gICAgdGhpcy5wcmVzZXJ2ZVdoaXRlc3BhY2VzID0gcHJlc2VydmVXaGl0ZXNwYWNlcztcbiAgfVxuXG4gIHRvU3VtbWFyeSgpOiBDb21waWxlVGVtcGxhdGVTdW1tYXJ5IHtcbiAgICByZXR1cm4ge1xuICAgICAgbmdDb250ZW50U2VsZWN0b3JzOiB0aGlzLm5nQ29udGVudFNlbGVjdG9ycyxcbiAgICAgIGVuY2Fwc3VsYXRpb246IHRoaXMuZW5jYXBzdWxhdGlvbixcbiAgICAgIHN0eWxlczogdGhpcy5zdHlsZXMsXG4gICAgICBhbmltYXRpb25zOiB0aGlzLmFuaW1hdGlvbnNcbiAgICB9O1xuICB9XG59XG5cbmV4cG9ydCBpbnRlcmZhY2UgQ29tcGlsZUVudHJ5Q29tcG9uZW50TWV0YWRhdGEge1xuICBjb21wb25lbnRUeXBlOiBhbnk7XG4gIGNvbXBvbmVudEZhY3Rvcnk6IFN0YXRpY1N5bWJvbHxvYmplY3Q7XG59XG5cbi8vIE5vdGU6IFRoaXMgc2hvdWxkIG9ubHkgdXNlIGludGVyZmFjZXMgYXMgbmVzdGVkIGRhdGEgdHlwZXNcbi8vIGFzIHdlIG5lZWQgdG8gYmUgYWJsZSB0byBzZXJpYWxpemUgdGhpcyBmcm9tL3RvIEpTT04hXG5leHBvcnQgaW50ZXJmYWNlIENvbXBpbGVEaXJlY3RpdmVTdW1tYXJ5IGV4dGVuZHMgQ29tcGlsZVR5cGVTdW1tYXJ5IHtcbiAgdHlwZTogQ29tcGlsZVR5cGVNZXRhZGF0YTtcbiAgaXNDb21wb25lbnQ6IGJvb2xlYW47XG4gIHNlbGVjdG9yOiBzdHJpbmd8bnVsbDtcbiAgZXhwb3J0QXM6IHN0cmluZ3xudWxsO1xuICBpbnB1dHM6IHtba2V5OiBzdHJpbmddOiBzdHJpbmd9O1xuICBvdXRwdXRzOiB7W2tleTogc3RyaW5nXTogc3RyaW5nfTtcbiAgaG9zdExpc3RlbmVyczoge1trZXk6IHN0cmluZ106IHN0cmluZ307XG4gIGhvc3RQcm9wZXJ0aWVzOiB7W2tleTogc3RyaW5nXTogc3RyaW5nfTtcbiAgaG9zdEF0dHJpYnV0ZXM6IHtba2V5OiBzdHJpbmddOiBzdHJpbmd9O1xuICBwcm92aWRlcnM6IENvbXBpbGVQcm92aWRlck1ldGFkYXRhW107XG4gIHZpZXdQcm92aWRlcnM6IENvbXBpbGVQcm92aWRlck1ldGFkYXRhW107XG4gIHF1ZXJpZXM6IENvbXBpbGVRdWVyeU1ldGFkYXRhW107XG4gIGd1YXJkczoge1trZXk6IHN0cmluZ106IGFueX07XG4gIHZpZXdRdWVyaWVzOiBDb21waWxlUXVlcnlNZXRhZGF0YVtdO1xuICBlbnRyeUNvbXBvbmVudHM6IENvbXBpbGVFbnRyeUNvbXBvbmVudE1ldGFkYXRhW107XG4gIGNoYW5nZURldGVjdGlvbjogQ2hhbmdlRGV0ZWN0aW9uU3RyYXRlZ3l8bnVsbDtcbiAgdGVtcGxhdGU6IENvbXBpbGVUZW1wbGF0ZVN1bW1hcnl8bnVsbDtcbiAgY29tcG9uZW50Vmlld1R5cGU6IFN0YXRpY1N5bWJvbHxQcm94eUNsYXNzfG51bGw7XG4gIHJlbmRlcmVyVHlwZTogU3RhdGljU3ltYm9sfG9iamVjdHxudWxsO1xuICBjb21wb25lbnRGYWN0b3J5OiBTdGF0aWNTeW1ib2x8b2JqZWN0fG51bGw7XG59XG5cbi8qKlxuICogTWV0YWRhdGEgcmVnYXJkaW5nIGNvbXBpbGF0aW9uIG9mIGEgZGlyZWN0aXZlLlxuICovXG5leHBvcnQgY2xhc3MgQ29tcGlsZURpcmVjdGl2ZU1ldGFkYXRhIHtcbiAgc3RhdGljIGNyZWF0ZSh7XG4gICAgaXNIb3N0LFxuICAgIHR5cGUsXG4gICAgaXNDb21wb25lbnQsXG4gICAgc2VsZWN0b3IsXG4gICAgZXhwb3J0QXMsXG4gICAgY2hhbmdlRGV0ZWN0aW9uLFxuICAgIGlucHV0cyxcbiAgICBvdXRwdXRzLFxuICAgIGhvc3QsXG4gICAgcHJvdmlkZXJzLFxuICAgIHZpZXdQcm92aWRlcnMsXG4gICAgcXVlcmllcyxcbiAgICBndWFyZHMsXG4gICAgdmlld1F1ZXJpZXMsXG4gICAgZW50cnlDb21wb25lbnRzLFxuICAgIHRlbXBsYXRlLFxuICAgIGNvbXBvbmVudFZpZXdUeXBlLFxuICAgIHJlbmRlcmVyVHlwZSxcbiAgICBjb21wb25lbnRGYWN0b3J5XG4gIH06IHtcbiAgICBpc0hvc3Q6IGJvb2xlYW4sXG4gICAgdHlwZTogQ29tcGlsZVR5cGVNZXRhZGF0YSxcbiAgICBpc0NvbXBvbmVudDogYm9vbGVhbixcbiAgICBzZWxlY3Rvcjogc3RyaW5nfG51bGwsXG4gICAgZXhwb3J0QXM6IHN0cmluZ3xudWxsLFxuICAgIGNoYW5nZURldGVjdGlvbjogQ2hhbmdlRGV0ZWN0aW9uU3RyYXRlZ3l8bnVsbCxcbiAgICBpbnB1dHM6IHN0cmluZ1tdLFxuICAgIG91dHB1dHM6IHN0cmluZ1tdLFxuICAgIGhvc3Q6IHtba2V5OiBzdHJpbmddOiBzdHJpbmd9LFxuICAgIHByb3ZpZGVyczogQ29tcGlsZVByb3ZpZGVyTWV0YWRhdGFbXSxcbiAgICB2aWV3UHJvdmlkZXJzOiBDb21waWxlUHJvdmlkZXJNZXRhZGF0YVtdLFxuICAgIHF1ZXJpZXM6IENvbXBpbGVRdWVyeU1ldGFkYXRhW10sXG4gICAgZ3VhcmRzOiB7W2tleTogc3RyaW5nXTogYW55fTtcbiAgICB2aWV3UXVlcmllczogQ29tcGlsZVF1ZXJ5TWV0YWRhdGFbXSxcbiAgICBlbnRyeUNvbXBvbmVudHM6IENvbXBpbGVFbnRyeUNvbXBvbmVudE1ldGFkYXRhW10sXG4gICAgdGVtcGxhdGU6IENvbXBpbGVUZW1wbGF0ZU1ldGFkYXRhLFxuICAgIGNvbXBvbmVudFZpZXdUeXBlOiBTdGF0aWNTeW1ib2x8UHJveHlDbGFzc3xudWxsLFxuICAgIHJlbmRlcmVyVHlwZTogU3RhdGljU3ltYm9sfG9iamVjdHxudWxsLFxuICAgIGNvbXBvbmVudEZhY3Rvcnk6IFN0YXRpY1N5bWJvbHxvYmplY3R8bnVsbCxcbiAgfSk6IENvbXBpbGVEaXJlY3RpdmVNZXRhZGF0YSB7XG4gICAgY29uc3QgaG9zdExpc3RlbmVyczoge1trZXk6IHN0cmluZ106IHN0cmluZ30gPSB7fTtcbiAgICBjb25zdCBob3N0UHJvcGVydGllczoge1trZXk6IHN0cmluZ106IHN0cmluZ30gPSB7fTtcbiAgICBjb25zdCBob3N0QXR0cmlidXRlczoge1trZXk6IHN0cmluZ106IHN0cmluZ30gPSB7fTtcbiAgICBpZiAoaG9zdCAhPSBudWxsKSB7XG4gICAgICBPYmplY3Qua2V5cyhob3N0KS5mb3JFYWNoKGtleSA9PiB7XG4gICAgICAgIGNvbnN0IHZhbHVlID0gaG9zdFtrZXldO1xuICAgICAgICBjb25zdCBtYXRjaGVzID0ga2V5Lm1hdGNoKEhPU1RfUkVHX0VYUCk7XG4gICAgICAgIGlmIChtYXRjaGVzID09PSBudWxsKSB7XG4gICAgICAgICAgaG9zdEF0dHJpYnV0ZXNba2V5XSA9IHZhbHVlO1xuICAgICAgICB9IGVsc2UgaWYgKG1hdGNoZXNbMV0gIT0gbnVsbCkge1xuICAgICAgICAgIGhvc3RQcm9wZXJ0aWVzW21hdGNoZXNbMV1dID0gdmFsdWU7XG4gICAgICAgIH0gZWxzZSBpZiAobWF0Y2hlc1syXSAhPSBudWxsKSB7XG4gICAgICAgICAgaG9zdExpc3RlbmVyc1ttYXRjaGVzWzJdXSA9IHZhbHVlO1xuICAgICAgICB9XG4gICAgICB9KTtcbiAgICB9XG4gICAgY29uc3QgaW5wdXRzTWFwOiB7W2tleTogc3RyaW5nXTogc3RyaW5nfSA9IHt9O1xuICAgIGlmIChpbnB1dHMgIT0gbnVsbCkge1xuICAgICAgaW5wdXRzLmZvckVhY2goKGJpbmRDb25maWc6IHN0cmluZykgPT4ge1xuICAgICAgICAvLyBjYW5vbmljYWwgc3ludGF4OiBgZGlyUHJvcDogZWxQcm9wYFxuICAgICAgICAvLyBpZiB0aGVyZSBpcyBubyBgOmAsIHVzZSBkaXJQcm9wID0gZWxQcm9wXG4gICAgICAgIGNvbnN0IHBhcnRzID0gc3BsaXRBdENvbG9uKGJpbmRDb25maWcsIFtiaW5kQ29uZmlnLCBiaW5kQ29uZmlnXSk7XG4gICAgICAgIGlucHV0c01hcFtwYXJ0c1swXV0gPSBwYXJ0c1sxXTtcbiAgICAgIH0pO1xuICAgIH1cbiAgICBjb25zdCBvdXRwdXRzTWFwOiB7W2tleTogc3RyaW5nXTogc3RyaW5nfSA9IHt9O1xuICAgIGlmIChvdXRwdXRzICE9IG51bGwpIHtcbiAgICAgIG91dHB1dHMuZm9yRWFjaCgoYmluZENvbmZpZzogc3RyaW5nKSA9PiB7XG4gICAgICAgIC8vIGNhbm9uaWNhbCBzeW50YXg6IGBkaXJQcm9wOiBlbFByb3BgXG4gICAgICAgIC8vIGlmIHRoZXJlIGlzIG5vIGA6YCwgdXNlIGRpclByb3AgPSBlbFByb3BcbiAgICAgICAgY29uc3QgcGFydHMgPSBzcGxpdEF0Q29sb24oYmluZENvbmZpZywgW2JpbmRDb25maWcsIGJpbmRDb25maWddKTtcbiAgICAgICAgb3V0cHV0c01hcFtwYXJ0c1swXV0gPSBwYXJ0c1sxXTtcbiAgICAgIH0pO1xuICAgIH1cblxuICAgIHJldHVybiBuZXcgQ29tcGlsZURpcmVjdGl2ZU1ldGFkYXRhKHtcbiAgICAgIGlzSG9zdCxcbiAgICAgIHR5cGUsXG4gICAgICBpc0NvbXBvbmVudDogISFpc0NvbXBvbmVudCxcbiAgICAgIHNlbGVjdG9yLFxuICAgICAgZXhwb3J0QXMsXG4gICAgICBjaGFuZ2VEZXRlY3Rpb24sXG4gICAgICBpbnB1dHM6IGlucHV0c01hcCxcbiAgICAgIG91dHB1dHM6IG91dHB1dHNNYXAsXG4gICAgICBob3N0TGlzdGVuZXJzLFxuICAgICAgaG9zdFByb3BlcnRpZXMsXG4gICAgICBob3N0QXR0cmlidXRlcyxcbiAgICAgIHByb3ZpZGVycyxcbiAgICAgIHZpZXdQcm92aWRlcnMsXG4gICAgICBxdWVyaWVzLFxuICAgICAgZ3VhcmRzLFxuICAgICAgdmlld1F1ZXJpZXMsXG4gICAgICBlbnRyeUNvbXBvbmVudHMsXG4gICAgICB0ZW1wbGF0ZSxcbiAgICAgIGNvbXBvbmVudFZpZXdUeXBlLFxuICAgICAgcmVuZGVyZXJUeXBlLFxuICAgICAgY29tcG9uZW50RmFjdG9yeSxcbiAgICB9KTtcbiAgfVxuICBpc0hvc3Q6IGJvb2xlYW47XG4gIHR5cGU6IENvbXBpbGVUeXBlTWV0YWRhdGE7XG4gIGlzQ29tcG9uZW50OiBib29sZWFuO1xuICBzZWxlY3Rvcjogc3RyaW5nfG51bGw7XG4gIGV4cG9ydEFzOiBzdHJpbmd8bnVsbDtcbiAgY2hhbmdlRGV0ZWN0aW9uOiBDaGFuZ2VEZXRlY3Rpb25TdHJhdGVneXxudWxsO1xuICBpbnB1dHM6IHtba2V5OiBzdHJpbmddOiBzdHJpbmd9O1xuICBvdXRwdXRzOiB7W2tleTogc3RyaW5nXTogc3RyaW5nfTtcbiAgaG9zdExpc3RlbmVyczoge1trZXk6IHN0cmluZ106IHN0cmluZ307XG4gIGhvc3RQcm9wZXJ0aWVzOiB7W2tleTogc3RyaW5nXTogc3RyaW5nfTtcbiAgaG9zdEF0dHJpYnV0ZXM6IHtba2V5OiBzdHJpbmddOiBzdHJpbmd9O1xuICBwcm92aWRlcnM6IENvbXBpbGVQcm92aWRlck1ldGFkYXRhW107XG4gIHZpZXdQcm92aWRlcnM6IENvbXBpbGVQcm92aWRlck1ldGFkYXRhW107XG4gIHF1ZXJpZXM6IENvbXBpbGVRdWVyeU1ldGFkYXRhW107XG4gIGd1YXJkczoge1trZXk6IHN0cmluZ106IGFueX07XG4gIHZpZXdRdWVyaWVzOiBDb21waWxlUXVlcnlNZXRhZGF0YVtdO1xuICBlbnRyeUNvbXBvbmVudHM6IENvbXBpbGVFbnRyeUNvbXBvbmVudE1ldGFkYXRhW107XG5cbiAgdGVtcGxhdGU6IENvbXBpbGVUZW1wbGF0ZU1ldGFkYXRhfG51bGw7XG5cbiAgY29tcG9uZW50Vmlld1R5cGU6IFN0YXRpY1N5bWJvbHxQcm94eUNsYXNzfG51bGw7XG4gIHJlbmRlcmVyVHlwZTogU3RhdGljU3ltYm9sfG9iamVjdHxudWxsO1xuICBjb21wb25lbnRGYWN0b3J5OiBTdGF0aWNTeW1ib2x8b2JqZWN0fG51bGw7XG5cbiAgY29uc3RydWN0b3Ioe1xuICAgIGlzSG9zdCxcbiAgICB0eXBlLFxuICAgIGlzQ29tcG9uZW50LFxuICAgIHNlbGVjdG9yLFxuICAgIGV4cG9ydEFzLFxuICAgIGNoYW5nZURldGVjdGlvbixcbiAgICBpbnB1dHMsXG4gICAgb3V0cHV0cyxcbiAgICBob3N0TGlzdGVuZXJzLFxuICAgIGhvc3RQcm9wZXJ0aWVzLFxuICAgIGhvc3RBdHRyaWJ1dGVzLFxuICAgIHByb3ZpZGVycyxcbiAgICB2aWV3UHJvdmlkZXJzLFxuICAgIHF1ZXJpZXMsXG4gICAgZ3VhcmRzLFxuICAgIHZpZXdRdWVyaWVzLFxuICAgIGVudHJ5Q29tcG9uZW50cyxcbiAgICB0ZW1wbGF0ZSxcbiAgICBjb21wb25lbnRWaWV3VHlwZSxcbiAgICByZW5kZXJlclR5cGUsXG4gICAgY29tcG9uZW50RmFjdG9yeVxuICB9OiB7XG4gICAgaXNIb3N0OiBib29sZWFuLFxuICAgIHR5cGU6IENvbXBpbGVUeXBlTWV0YWRhdGEsXG4gICAgaXNDb21wb25lbnQ6IGJvb2xlYW4sXG4gICAgc2VsZWN0b3I6IHN0cmluZ3xudWxsLFxuICAgIGV4cG9ydEFzOiBzdHJpbmd8bnVsbCxcbiAgICBjaGFuZ2VEZXRlY3Rpb246IENoYW5nZURldGVjdGlvblN0cmF0ZWd5fG51bGwsXG4gICAgaW5wdXRzOiB7W2tleTogc3RyaW5nXTogc3RyaW5nfSxcbiAgICBvdXRwdXRzOiB7W2tleTogc3RyaW5nXTogc3RyaW5nfSxcbiAgICBob3N0TGlzdGVuZXJzOiB7W2tleTogc3RyaW5nXTogc3RyaW5nfSxcbiAgICBob3N0UHJvcGVydGllczoge1trZXk6IHN0cmluZ106IHN0cmluZ30sXG4gICAgaG9zdEF0dHJpYnV0ZXM6IHtba2V5OiBzdHJpbmddOiBzdHJpbmd9LFxuICAgIHByb3ZpZGVyczogQ29tcGlsZVByb3ZpZGVyTWV0YWRhdGFbXSxcbiAgICB2aWV3UHJvdmlkZXJzOiBDb21waWxlUHJvdmlkZXJNZXRhZGF0YVtdLFxuICAgIHF1ZXJpZXM6IENvbXBpbGVRdWVyeU1ldGFkYXRhW10sXG4gICAgZ3VhcmRzOiB7W2tleTogc3RyaW5nXTogYW55fSxcbiAgICB2aWV3UXVlcmllczogQ29tcGlsZVF1ZXJ5TWV0YWRhdGFbXSxcbiAgICBlbnRyeUNvbXBvbmVudHM6IENvbXBpbGVFbnRyeUNvbXBvbmVudE1ldGFkYXRhW10sXG4gICAgdGVtcGxhdGU6IENvbXBpbGVUZW1wbGF0ZU1ldGFkYXRhfG51bGwsXG4gICAgY29tcG9uZW50Vmlld1R5cGU6IFN0YXRpY1N5bWJvbHxQcm94eUNsYXNzfG51bGwsXG4gICAgcmVuZGVyZXJUeXBlOiBTdGF0aWNTeW1ib2x8b2JqZWN0fG51bGwsXG4gICAgY29tcG9uZW50RmFjdG9yeTogU3RhdGljU3ltYm9sfG9iamVjdHxudWxsLFxuICB9KSB7XG4gICAgdGhpcy5pc0hvc3QgPSAhIWlzSG9zdDtcbiAgICB0aGlzLnR5cGUgPSB0eXBlO1xuICAgIHRoaXMuaXNDb21wb25lbnQgPSBpc0NvbXBvbmVudDtcbiAgICB0aGlzLnNlbGVjdG9yID0gc2VsZWN0b3I7XG4gICAgdGhpcy5leHBvcnRBcyA9IGV4cG9ydEFzO1xuICAgIHRoaXMuY2hhbmdlRGV0ZWN0aW9uID0gY2hhbmdlRGV0ZWN0aW9uO1xuICAgIHRoaXMuaW5wdXRzID0gaW5wdXRzO1xuICAgIHRoaXMub3V0cHV0cyA9IG91dHB1dHM7XG4gICAgdGhpcy5ob3N0TGlzdGVuZXJzID0gaG9zdExpc3RlbmVycztcbiAgICB0aGlzLmhvc3RQcm9wZXJ0aWVzID0gaG9zdFByb3BlcnRpZXM7XG4gICAgdGhpcy5ob3N0QXR0cmlidXRlcyA9IGhvc3RBdHRyaWJ1dGVzO1xuICAgIHRoaXMucHJvdmlkZXJzID0gX25vcm1hbGl6ZUFycmF5KHByb3ZpZGVycyk7XG4gICAgdGhpcy52aWV3UHJvdmlkZXJzID0gX25vcm1hbGl6ZUFycmF5KHZpZXdQcm92aWRlcnMpO1xuICAgIHRoaXMucXVlcmllcyA9IF9ub3JtYWxpemVBcnJheShxdWVyaWVzKTtcbiAgICB0aGlzLmd1YXJkcyA9IGd1YXJkcztcbiAgICB0aGlzLnZpZXdRdWVyaWVzID0gX25vcm1hbGl6ZUFycmF5KHZpZXdRdWVyaWVzKTtcbiAgICB0aGlzLmVudHJ5Q29tcG9uZW50cyA9IF9ub3JtYWxpemVBcnJheShlbnRyeUNvbXBvbmVudHMpO1xuICAgIHRoaXMudGVtcGxhdGUgPSB0ZW1wbGF0ZTtcblxuICAgIHRoaXMuY29tcG9uZW50Vmlld1R5cGUgPSBjb21wb25lbnRWaWV3VHlwZTtcbiAgICB0aGlzLnJlbmRlcmVyVHlwZSA9IHJlbmRlcmVyVHlwZTtcbiAgICB0aGlzLmNvbXBvbmVudEZhY3RvcnkgPSBjb21wb25lbnRGYWN0b3J5O1xuICB9XG5cbiAgdG9TdW1tYXJ5KCk6IENvbXBpbGVEaXJlY3RpdmVTdW1tYXJ5IHtcbiAgICByZXR1cm4ge1xuICAgICAgc3VtbWFyeUtpbmQ6IENvbXBpbGVTdW1tYXJ5S2luZC5EaXJlY3RpdmUsXG4gICAgICB0eXBlOiB0aGlzLnR5cGUsXG4gICAgICBpc0NvbXBvbmVudDogdGhpcy5pc0NvbXBvbmVudCxcbiAgICAgIHNlbGVjdG9yOiB0aGlzLnNlbGVjdG9yLFxuICAgICAgZXhwb3J0QXM6IHRoaXMuZXhwb3J0QXMsXG4gICAgICBpbnB1dHM6IHRoaXMuaW5wdXRzLFxuICAgICAgb3V0cHV0czogdGhpcy5vdXRwdXRzLFxuICAgICAgaG9zdExpc3RlbmVyczogdGhpcy5ob3N0TGlzdGVuZXJzLFxuICAgICAgaG9zdFByb3BlcnRpZXM6IHRoaXMuaG9zdFByb3BlcnRpZXMsXG4gICAgICBob3N0QXR0cmlidXRlczogdGhpcy5ob3N0QXR0cmlidXRlcyxcbiAgICAgIHByb3ZpZGVyczogdGhpcy5wcm92aWRlcnMsXG4gICAgICB2aWV3UHJvdmlkZXJzOiB0aGlzLnZpZXdQcm92aWRlcnMsXG4gICAgICBxdWVyaWVzOiB0aGlzLnF1ZXJpZXMsXG4gICAgICBndWFyZHM6IHRoaXMuZ3VhcmRzLFxuICAgICAgdmlld1F1ZXJpZXM6IHRoaXMudmlld1F1ZXJpZXMsXG4gICAgICBlbnRyeUNvbXBvbmVudHM6IHRoaXMuZW50cnlDb21wb25lbnRzLFxuICAgICAgY2hhbmdlRGV0ZWN0aW9uOiB0aGlzLmNoYW5nZURldGVjdGlvbixcbiAgICAgIHRlbXBsYXRlOiB0aGlzLnRlbXBsYXRlICYmIHRoaXMudGVtcGxhdGUudG9TdW1tYXJ5KCksXG4gICAgICBjb21wb25lbnRWaWV3VHlwZTogdGhpcy5jb21wb25lbnRWaWV3VHlwZSxcbiAgICAgIHJlbmRlcmVyVHlwZTogdGhpcy5yZW5kZXJlclR5cGUsXG4gICAgICBjb21wb25lbnRGYWN0b3J5OiB0aGlzLmNvbXBvbmVudEZhY3RvcnlcbiAgICB9O1xuICB9XG59XG5cbmV4cG9ydCBpbnRlcmZhY2UgQ29tcGlsZVBpcGVTdW1tYXJ5IGV4dGVuZHMgQ29tcGlsZVR5cGVTdW1tYXJ5IHtcbiAgdHlwZTogQ29tcGlsZVR5cGVNZXRhZGF0YTtcbiAgbmFtZTogc3RyaW5nO1xuICBwdXJlOiBib29sZWFuO1xufVxuXG5leHBvcnQgY2xhc3MgQ29tcGlsZVBpcGVNZXRhZGF0YSB7XG4gIHR5cGU6IENvbXBpbGVUeXBlTWV0YWRhdGE7XG4gIG5hbWU6IHN0cmluZztcbiAgcHVyZTogYm9vbGVhbjtcblxuICBjb25zdHJ1Y3Rvcih7dHlwZSwgbmFtZSwgcHVyZX06IHtcbiAgICB0eXBlOiBDb21waWxlVHlwZU1ldGFkYXRhLFxuICAgIG5hbWU6IHN0cmluZyxcbiAgICBwdXJlOiBib29sZWFuLFxuICB9KSB7XG4gICAgdGhpcy50eXBlID0gdHlwZTtcbiAgICB0aGlzLm5hbWUgPSBuYW1lO1xuICAgIHRoaXMucHVyZSA9ICEhcHVyZTtcbiAgfVxuXG4gIHRvU3VtbWFyeSgpOiBDb21waWxlUGlwZVN1bW1hcnkge1xuICAgIHJldHVybiB7XG4gICAgICBzdW1tYXJ5S2luZDogQ29tcGlsZVN1bW1hcnlLaW5kLlBpcGUsXG4gICAgICB0eXBlOiB0aGlzLnR5cGUsXG4gICAgICBuYW1lOiB0aGlzLm5hbWUsXG4gICAgICBwdXJlOiB0aGlzLnB1cmVcbiAgICB9O1xuICB9XG59XG5cbi8vIE5vdGU6IFRoaXMgc2hvdWxkIG9ubHkgdXNlIGludGVyZmFjZXMgYXMgbmVzdGVkIGRhdGEgdHlwZXNcbi8vIGFzIHdlIG5lZWQgdG8gYmUgYWJsZSB0byBzZXJpYWxpemUgdGhpcyBmcm9tL3RvIEpTT04hXG5leHBvcnQgaW50ZXJmYWNlIENvbXBpbGVOZ01vZHVsZVN1bW1hcnkgZXh0ZW5kcyBDb21waWxlVHlwZVN1bW1hcnkge1xuICB0eXBlOiBDb21waWxlVHlwZU1ldGFkYXRhO1xuXG4gIC8vIE5vdGU6IFRoaXMgaXMgdHJhbnNpdGl2ZSBvdmVyIHRoZSBleHBvcnRlZCBtb2R1bGVzLlxuICBleHBvcnRlZERpcmVjdGl2ZXM6IENvbXBpbGVJZGVudGlmaWVyTWV0YWRhdGFbXTtcbiAgLy8gTm90ZTogVGhpcyBpcyB0cmFuc2l0aXZlIG92ZXIgdGhlIGV4cG9ydGVkIG1vZHVsZXMuXG4gIGV4cG9ydGVkUGlwZXM6IENvbXBpbGVJZGVudGlmaWVyTWV0YWRhdGFbXTtcblxuICAvLyBOb3RlOiBUaGlzIGlzIHRyYW5zaXRpdmUuXG4gIGVudHJ5Q29tcG9uZW50czogQ29tcGlsZUVudHJ5Q29tcG9uZW50TWV0YWRhdGFbXTtcbiAgLy8gTm90ZTogVGhpcyBpcyB0cmFuc2l0aXZlLlxuICBwcm92aWRlcnM6IHtwcm92aWRlcjogQ29tcGlsZVByb3ZpZGVyTWV0YWRhdGEsIG1vZHVsZTogQ29tcGlsZUlkZW50aWZpZXJNZXRhZGF0YX1bXTtcbiAgLy8gTm90ZTogVGhpcyBpcyB0cmFuc2l0aXZlLlxuICBtb2R1bGVzOiBDb21waWxlVHlwZU1ldGFkYXRhW107XG59XG5cbmV4cG9ydCBjbGFzcyBDb21waWxlU2hhbGxvd01vZHVsZU1ldGFkYXRhIHtcbiAgLy8gVE9ETyhpc3N1ZS8yNDU3MSk6IHJlbW92ZSAnIScuXG4gIHR5cGUhOiBDb21waWxlVHlwZU1ldGFkYXRhO1xuXG4gIHJhd0V4cG9ydHM6IGFueTtcbiAgcmF3SW1wb3J0czogYW55O1xuICByYXdQcm92aWRlcnM6IGFueTtcbn1cblxuLyoqXG4gKiBNZXRhZGF0YSByZWdhcmRpbmcgY29tcGlsYXRpb24gb2YgYSBtb2R1bGUuXG4gKi9cbmV4cG9ydCBjbGFzcyBDb21waWxlTmdNb2R1bGVNZXRhZGF0YSB7XG4gIHR5cGU6IENvbXBpbGVUeXBlTWV0YWRhdGE7XG4gIGRlY2xhcmVkRGlyZWN0aXZlczogQ29tcGlsZUlkZW50aWZpZXJNZXRhZGF0YVtdO1xuICBleHBvcnRlZERpcmVjdGl2ZXM6IENvbXBpbGVJZGVudGlmaWVyTWV0YWRhdGFbXTtcbiAgZGVjbGFyZWRQaXBlczogQ29tcGlsZUlkZW50aWZpZXJNZXRhZGF0YVtdO1xuXG4gIGV4cG9ydGVkUGlwZXM6IENvbXBpbGVJZGVudGlmaWVyTWV0YWRhdGFbXTtcbiAgZW50cnlDb21wb25lbnRzOiBDb21waWxlRW50cnlDb21wb25lbnRNZXRhZGF0YVtdO1xuICBib290c3RyYXBDb21wb25lbnRzOiBDb21waWxlSWRlbnRpZmllck1ldGFkYXRhW107XG4gIHByb3ZpZGVyczogQ29tcGlsZVByb3ZpZGVyTWV0YWRhdGFbXTtcblxuICBpbXBvcnRlZE1vZHVsZXM6IENvbXBpbGVOZ01vZHVsZVN1bW1hcnlbXTtcbiAgZXhwb3J0ZWRNb2R1bGVzOiBDb21waWxlTmdNb2R1bGVTdW1tYXJ5W107XG4gIHNjaGVtYXM6IFNjaGVtYU1ldGFkYXRhW107XG4gIGlkOiBzdHJpbmd8bnVsbDtcblxuICB0cmFuc2l0aXZlTW9kdWxlOiBUcmFuc2l0aXZlQ29tcGlsZU5nTW9kdWxlTWV0YWRhdGE7XG5cbiAgY29uc3RydWN0b3Ioe1xuICAgIHR5cGUsXG4gICAgcHJvdmlkZXJzLFxuICAgIGRlY2xhcmVkRGlyZWN0aXZlcyxcbiAgICBleHBvcnRlZERpcmVjdGl2ZXMsXG4gICAgZGVjbGFyZWRQaXBlcyxcbiAgICBleHBvcnRlZFBpcGVzLFxuICAgIGVudHJ5Q29tcG9uZW50cyxcbiAgICBib290c3RyYXBDb21wb25lbnRzLFxuICAgIGltcG9ydGVkTW9kdWxlcyxcbiAgICBleHBvcnRlZE1vZHVsZXMsXG4gICAgc2NoZW1hcyxcbiAgICB0cmFuc2l0aXZlTW9kdWxlLFxuICAgIGlkXG4gIH06IHtcbiAgICB0eXBlOiBDb21waWxlVHlwZU1ldGFkYXRhLFxuICAgIHByb3ZpZGVyczogQ29tcGlsZVByb3ZpZGVyTWV0YWRhdGFbXSxcbiAgICBkZWNsYXJlZERpcmVjdGl2ZXM6IENvbXBpbGVJZGVudGlmaWVyTWV0YWRhdGFbXSxcbiAgICBleHBvcnRlZERpcmVjdGl2ZXM6IENvbXBpbGVJZGVudGlmaWVyTWV0YWRhdGFbXSxcbiAgICBkZWNsYXJlZFBpcGVzOiBDb21waWxlSWRlbnRpZmllck1ldGFkYXRhW10sXG4gICAgZXhwb3J0ZWRQaXBlczogQ29tcGlsZUlkZW50aWZpZXJNZXRhZGF0YVtdLFxuICAgIGVudHJ5Q29tcG9uZW50czogQ29tcGlsZUVudHJ5Q29tcG9uZW50TWV0YWRhdGFbXSxcbiAgICBib290c3RyYXBDb21wb25lbnRzOiBDb21waWxlSWRlbnRpZmllck1ldGFkYXRhW10sXG4gICAgaW1wb3J0ZWRNb2R1bGVzOiBDb21waWxlTmdNb2R1bGVTdW1tYXJ5W10sXG4gICAgZXhwb3J0ZWRNb2R1bGVzOiBDb21waWxlTmdNb2R1bGVTdW1tYXJ5W10sXG4gICAgdHJhbnNpdGl2ZU1vZHVsZTogVHJhbnNpdGl2ZUNvbXBpbGVOZ01vZHVsZU1ldGFkYXRhLFxuICAgIHNjaGVtYXM6IFNjaGVtYU1ldGFkYXRhW10sXG4gICAgaWQ6IHN0cmluZ3xudWxsXG4gIH0pIHtcbiAgICB0aGlzLnR5cGUgPSB0eXBlIHx8IG51bGw7XG4gICAgdGhpcy5kZWNsYXJlZERpcmVjdGl2ZXMgPSBfbm9ybWFsaXplQXJyYXkoZGVjbGFyZWREaXJlY3RpdmVzKTtcbiAgICB0aGlzLmV4cG9ydGVkRGlyZWN0aXZlcyA9IF9ub3JtYWxpemVBcnJheShleHBvcnRlZERpcmVjdGl2ZXMpO1xuICAgIHRoaXMuZGVjbGFyZWRQaXBlcyA9IF9ub3JtYWxpemVBcnJheShkZWNsYXJlZFBpcGVzKTtcbiAgICB0aGlzLmV4cG9ydGVkUGlwZXMgPSBfbm9ybWFsaXplQXJyYXkoZXhwb3J0ZWRQaXBlcyk7XG4gICAgdGhpcy5wcm92aWRlcnMgPSBfbm9ybWFsaXplQXJyYXkocHJvdmlkZXJzKTtcbiAgICB0aGlzLmVudHJ5Q29tcG9uZW50cyA9IF9ub3JtYWxpemVBcnJheShlbnRyeUNvbXBvbmVudHMpO1xuICAgIHRoaXMuYm9vdHN0cmFwQ29tcG9uZW50cyA9IF9ub3JtYWxpemVBcnJheShib290c3RyYXBDb21wb25lbnRzKTtcbiAgICB0aGlzLmltcG9ydGVkTW9kdWxlcyA9IF9ub3JtYWxpemVBcnJheShpbXBvcnRlZE1vZHVsZXMpO1xuICAgIHRoaXMuZXhwb3J0ZWRNb2R1bGVzID0gX25vcm1hbGl6ZUFycmF5KGV4cG9ydGVkTW9kdWxlcyk7XG4gICAgdGhpcy5zY2hlbWFzID0gX25vcm1hbGl6ZUFycmF5KHNjaGVtYXMpO1xuICAgIHRoaXMuaWQgPSBpZCB8fCBudWxsO1xuICAgIHRoaXMudHJhbnNpdGl2ZU1vZHVsZSA9IHRyYW5zaXRpdmVNb2R1bGUgfHwgbnVsbDtcbiAgfVxuXG4gIHRvU3VtbWFyeSgpOiBDb21waWxlTmdNb2R1bGVTdW1tYXJ5IHtcbiAgICBjb25zdCBtb2R1bGUgPSB0aGlzLnRyYW5zaXRpdmVNb2R1bGUhO1xuICAgIHJldHVybiB7XG4gICAgICBzdW1tYXJ5S2luZDogQ29tcGlsZVN1bW1hcnlLaW5kLk5nTW9kdWxlLFxuICAgICAgdHlwZTogdGhpcy50eXBlLFxuICAgICAgZW50cnlDb21wb25lbnRzOiBtb2R1bGUuZW50cnlDb21wb25lbnRzLFxuICAgICAgcHJvdmlkZXJzOiBtb2R1bGUucHJvdmlkZXJzLFxuICAgICAgbW9kdWxlczogbW9kdWxlLm1vZHVsZXMsXG4gICAgICBleHBvcnRlZERpcmVjdGl2ZXM6IG1vZHVsZS5leHBvcnRlZERpcmVjdGl2ZXMsXG4gICAgICBleHBvcnRlZFBpcGVzOiBtb2R1bGUuZXhwb3J0ZWRQaXBlc1xuICAgIH07XG4gIH1cbn1cblxuZXhwb3J0IGNsYXNzIFRyYW5zaXRpdmVDb21waWxlTmdNb2R1bGVNZXRhZGF0YSB7XG4gIGRpcmVjdGl2ZXNTZXQgPSBuZXcgU2V0PGFueT4oKTtcbiAgZGlyZWN0aXZlczogQ29tcGlsZUlkZW50aWZpZXJNZXRhZGF0YVtdID0gW107XG4gIGV4cG9ydGVkRGlyZWN0aXZlc1NldCA9IG5ldyBTZXQ8YW55PigpO1xuICBleHBvcnRlZERpcmVjdGl2ZXM6IENvbXBpbGVJZGVudGlmaWVyTWV0YWRhdGFbXSA9IFtdO1xuICBwaXBlc1NldCA9IG5ldyBTZXQ8YW55PigpO1xuICBwaXBlczogQ29tcGlsZUlkZW50aWZpZXJNZXRhZGF0YVtdID0gW107XG4gIGV4cG9ydGVkUGlwZXNTZXQgPSBuZXcgU2V0PGFueT4oKTtcbiAgZXhwb3J0ZWRQaXBlczogQ29tcGlsZUlkZW50aWZpZXJNZXRhZGF0YVtdID0gW107XG4gIG1vZHVsZXNTZXQgPSBuZXcgU2V0PGFueT4oKTtcbiAgbW9kdWxlczogQ29tcGlsZVR5cGVNZXRhZGF0YVtdID0gW107XG4gIGVudHJ5Q29tcG9uZW50c1NldCA9IG5ldyBTZXQ8YW55PigpO1xuICBlbnRyeUNvbXBvbmVudHM6IENvbXBpbGVFbnRyeUNvbXBvbmVudE1ldGFkYXRhW10gPSBbXTtcblxuICBwcm92aWRlcnM6IHtwcm92aWRlcjogQ29tcGlsZVByb3ZpZGVyTWV0YWRhdGEsIG1vZHVsZTogQ29tcGlsZUlkZW50aWZpZXJNZXRhZGF0YX1bXSA9IFtdO1xuXG4gIGFkZFByb3ZpZGVyKHByb3ZpZGVyOiBDb21waWxlUHJvdmlkZXJNZXRhZGF0YSwgbW9kdWxlOiBDb21waWxlSWRlbnRpZmllck1ldGFkYXRhKSB7XG4gICAgdGhpcy5wcm92aWRlcnMucHVzaCh7cHJvdmlkZXI6IHByb3ZpZGVyLCBtb2R1bGU6IG1vZHVsZX0pO1xuICB9XG5cbiAgYWRkRGlyZWN0aXZlKGlkOiBDb21waWxlSWRlbnRpZmllck1ldGFkYXRhKSB7XG4gICAgaWYgKCF0aGlzLmRpcmVjdGl2ZXNTZXQuaGFzKGlkLnJlZmVyZW5jZSkpIHtcbiAgICAgIHRoaXMuZGlyZWN0aXZlc1NldC5hZGQoaWQucmVmZXJlbmNlKTtcbiAgICAgIHRoaXMuZGlyZWN0aXZlcy5wdXNoKGlkKTtcbiAgICB9XG4gIH1cbiAgYWRkRXhwb3J0ZWREaXJlY3RpdmUoaWQ6IENvbXBpbGVJZGVudGlmaWVyTWV0YWRhdGEpIHtcbiAgICBpZiAoIXRoaXMuZXhwb3J0ZWREaXJlY3RpdmVzU2V0LmhhcyhpZC5yZWZlcmVuY2UpKSB7XG4gICAgICB0aGlzLmV4cG9ydGVkRGlyZWN0aXZlc1NldC5hZGQoaWQucmVmZXJlbmNlKTtcbiAgICAgIHRoaXMuZXhwb3J0ZWREaXJlY3RpdmVzLnB1c2goaWQpO1xuICAgIH1cbiAgfVxuICBhZGRQaXBlKGlkOiBDb21waWxlSWRlbnRpZmllck1ldGFkYXRhKSB7XG4gICAgaWYgKCF0aGlzLnBpcGVzU2V0LmhhcyhpZC5yZWZlcmVuY2UpKSB7XG4gICAgICB0aGlzLnBpcGVzU2V0LmFkZChpZC5yZWZlcmVuY2UpO1xuICAgICAgdGhpcy5waXBlcy5wdXNoKGlkKTtcbiAgICB9XG4gIH1cbiAgYWRkRXhwb3J0ZWRQaXBlKGlkOiBDb21waWxlSWRlbnRpZmllck1ldGFkYXRhKSB7XG4gICAgaWYgKCF0aGlzLmV4cG9ydGVkUGlwZXNTZXQuaGFzKGlkLnJlZmVyZW5jZSkpIHtcbiAgICAgIHRoaXMuZXhwb3J0ZWRQaXBlc1NldC5hZGQoaWQucmVmZXJlbmNlKTtcbiAgICAgIHRoaXMuZXhwb3J0ZWRQaXBlcy5wdXNoKGlkKTtcbiAgICB9XG4gIH1cbiAgYWRkTW9kdWxlKGlkOiBDb21waWxlVHlwZU1ldGFkYXRhKSB7XG4gICAgaWYgKCF0aGlzLm1vZHVsZXNTZXQuaGFzKGlkLnJlZmVyZW5jZSkpIHtcbiAgICAgIHRoaXMubW9kdWxlc1NldC5hZGQoaWQucmVmZXJlbmNlKTtcbiAgICAgIHRoaXMubW9kdWxlcy5wdXNoKGlkKTtcbiAgICB9XG4gIH1cbiAgYWRkRW50cnlDb21wb25lbnQoZWM6IENvbXBpbGVFbnRyeUNvbXBvbmVudE1ldGFkYXRhKSB7XG4gICAgaWYgKCF0aGlzLmVudHJ5Q29tcG9uZW50c1NldC5oYXMoZWMuY29tcG9uZW50VHlwZSkpIHtcbiAgICAgIHRoaXMuZW50cnlDb21wb25lbnRzU2V0LmFkZChlYy5jb21wb25lbnRUeXBlKTtcbiAgICAgIHRoaXMuZW50cnlDb21wb25lbnRzLnB1c2goZWMpO1xuICAgIH1cbiAgfVxufVxuXG5mdW5jdGlvbiBfbm9ybWFsaXplQXJyYXkob2JqOiBhbnlbXXx1bmRlZmluZWR8bnVsbCk6IGFueVtdIHtcbiAgcmV0dXJuIG9iaiB8fCBbXTtcbn1cblxuZXhwb3J0IGNsYXNzIFByb3ZpZGVyTWV0YSB7XG4gIHRva2VuOiBhbnk7XG4gIHVzZUNsYXNzOiBUeXBlfG51bGw7XG4gIHVzZVZhbHVlOiBhbnk7XG4gIHVzZUV4aXN0aW5nOiBhbnk7XG4gIHVzZUZhY3Rvcnk6IEZ1bmN0aW9ufG51bGw7XG4gIGRlcGVuZGVuY2llczogT2JqZWN0W118bnVsbDtcbiAgbXVsdGk6IGJvb2xlYW47XG5cbiAgY29uc3RydWN0b3IodG9rZW46IGFueSwge3VzZUNsYXNzLCB1c2VWYWx1ZSwgdXNlRXhpc3RpbmcsIHVzZUZhY3RvcnksIGRlcHMsIG11bHRpfToge1xuICAgIHVzZUNsYXNzPzogVHlwZSxcbiAgICB1c2VWYWx1ZT86IGFueSxcbiAgICB1c2VFeGlzdGluZz86IGFueSxcbiAgICB1c2VGYWN0b3J5PzogRnVuY3Rpb258bnVsbCxcbiAgICBkZXBzPzogT2JqZWN0W118bnVsbCxcbiAgICBtdWx0aT86IGJvb2xlYW5cbiAgfSkge1xuICAgIHRoaXMudG9rZW4gPSB0b2tlbjtcbiAgICB0aGlzLnVzZUNsYXNzID0gdXNlQ2xhc3MgfHwgbnVsbDtcbiAgICB0aGlzLnVzZVZhbHVlID0gdXNlVmFsdWU7XG4gICAgdGhpcy51c2VFeGlzdGluZyA9IHVzZUV4aXN0aW5nO1xuICAgIHRoaXMudXNlRmFjdG9yeSA9IHVzZUZhY3RvcnkgfHwgbnVsbDtcbiAgICB0aGlzLmRlcGVuZGVuY2llcyA9IGRlcHMgfHwgbnVsbDtcbiAgICB0aGlzLm11bHRpID0gISFtdWx0aTtcbiAgfVxufVxuXG5leHBvcnQgZnVuY3Rpb24gZmxhdHRlbjxUPihsaXN0OiBBcnJheTxUfFRbXT4pOiBUW10ge1xuICByZXR1cm4gbGlzdC5yZWR1Y2UoKGZsYXQ6IGFueVtdLCBpdGVtOiBUfFRbXSk6IFRbXSA9PiB7XG4gICAgY29uc3QgZmxhdEl0ZW0gPSBBcnJheS5pc0FycmF5KGl0ZW0pID8gZmxhdHRlbihpdGVtKSA6IGl0ZW07XG4gICAgcmV0dXJuICg8VFtdPmZsYXQpLmNvbmNhdChmbGF0SXRlbSk7XG4gIH0sIFtdKTtcbn1cblxuZnVuY3Rpb24gaml0U291cmNlVXJsKHVybDogc3RyaW5nKSB7XG4gIC8vIE5vdGU6IFdlIG5lZWQgMyBcIi9cIiBzbyB0aGF0IG5nIHNob3dzIHVwIGFzIGEgc2VwYXJhdGUgZG9tYWluXG4gIC8vIGluIHRoZSBjaHJvbWUgZGV2IHRvb2xzLlxuICByZXR1cm4gdXJsLnJlcGxhY2UoLyhcXHcrOlxcL1xcL1tcXHc6LV0rKT8oXFwvKyk/LywgJ25nOi8vLycpO1xufVxuXG5leHBvcnQgZnVuY3Rpb24gdGVtcGxhdGVTb3VyY2VVcmwoXG4gICAgbmdNb2R1bGVUeXBlOiBDb21waWxlSWRlbnRpZmllck1ldGFkYXRhLCBjb21wTWV0YToge3R5cGU6IENvbXBpbGVJZGVudGlmaWVyTWV0YWRhdGF9LFxuICAgIHRlbXBsYXRlTWV0YToge2lzSW5saW5lOiBib29sZWFuLCB0ZW1wbGF0ZVVybDogc3RyaW5nfG51bGx9KSB7XG4gIGxldCB1cmw6IHN0cmluZztcbiAgaWYgKHRlbXBsYXRlTWV0YS5pc0lubGluZSkge1xuICAgIGlmIChjb21wTWV0YS50eXBlLnJlZmVyZW5jZSBpbnN0YW5jZW9mIFN0YXRpY1N5bWJvbCkge1xuICAgICAgLy8gTm90ZTogYSAudHMgZmlsZSBtaWdodCBjb250YWluIG11bHRpcGxlIGNvbXBvbmVudHMgd2l0aCBpbmxpbmUgdGVtcGxhdGVzLFxuICAgICAgLy8gc28gd2UgbmVlZCB0byBnaXZlIHRoZW0gdW5pcXVlIHVybHMsIGFzIHRoZXNlIHdpbGwgYmUgdXNlZCBmb3Igc291cmNlbWFwcy5cbiAgICAgIHVybCA9IGAke2NvbXBNZXRhLnR5cGUucmVmZXJlbmNlLmZpbGVQYXRofS4ke2NvbXBNZXRhLnR5cGUucmVmZXJlbmNlLm5hbWV9Lmh0bWxgO1xuICAgIH0gZWxzZSB7XG4gICAgICB1cmwgPSBgJHtpZGVudGlmaWVyTmFtZShuZ01vZHVsZVR5cGUpfS8ke2lkZW50aWZpZXJOYW1lKGNvbXBNZXRhLnR5cGUpfS5odG1sYDtcbiAgICB9XG4gIH0gZWxzZSB7XG4gICAgdXJsID0gdGVtcGxhdGVNZXRhLnRlbXBsYXRlVXJsITtcbiAgfVxuICByZXR1cm4gY29tcE1ldGEudHlwZS5yZWZlcmVuY2UgaW5zdGFuY2VvZiBTdGF0aWNTeW1ib2wgPyB1cmwgOiBqaXRTb3VyY2VVcmwodXJsKTtcbn1cblxuZXhwb3J0IGZ1bmN0aW9uIHNoYXJlZFN0eWxlc2hlZXRKaXRVcmwobWV0YTogQ29tcGlsZVN0eWxlc2hlZXRNZXRhZGF0YSwgaWQ6IG51bWJlcikge1xuICBjb25zdCBwYXRoUGFydHMgPSBtZXRhLm1vZHVsZVVybCEuc3BsaXQoL1xcL1xcXFwvZyk7XG4gIGNvbnN0IGJhc2VOYW1lID0gcGF0aFBhcnRzW3BhdGhQYXJ0cy5sZW5ndGggLSAxXTtcbiAgcmV0dXJuIGppdFNvdXJjZVVybChgY3NzLyR7aWR9JHtiYXNlTmFtZX0ubmdzdHlsZS5qc2ApO1xufVxuXG5leHBvcnQgZnVuY3Rpb24gbmdNb2R1bGVKaXRVcmwobW9kdWxlTWV0YTogQ29tcGlsZU5nTW9kdWxlTWV0YWRhdGEpOiBzdHJpbmcge1xuICByZXR1cm4gaml0U291cmNlVXJsKGAke2lkZW50aWZpZXJOYW1lKG1vZHVsZU1ldGEudHlwZSl9L21vZHVsZS5uZ2ZhY3RvcnkuanNgKTtcbn1cblxuZXhwb3J0IGZ1bmN0aW9uIHRlbXBsYXRlSml0VXJsKFxuICAgIG5nTW9kdWxlVHlwZTogQ29tcGlsZUlkZW50aWZpZXJNZXRhZGF0YSwgY29tcE1ldGE6IENvbXBpbGVEaXJlY3RpdmVNZXRhZGF0YSk6IHN0cmluZyB7XG4gIHJldHVybiBqaXRTb3VyY2VVcmwoXG4gICAgICBgJHtpZGVudGlmaWVyTmFtZShuZ01vZHVsZVR5cGUpfS8ke2lkZW50aWZpZXJOYW1lKGNvbXBNZXRhLnR5cGUpfS5uZ2ZhY3RvcnkuanNgKTtcbn1cbiJdfQ==