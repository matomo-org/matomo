/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
import { identifierName, ngModuleJitUrl, sharedStylesheetJitUrl, templateJitUrl, templateSourceUrl } from '../compile_metadata';
import { ConstantPool } from '../constant_pool';
import * as ir from '../output/output_ast';
import { interpretStatements } from '../output/output_interpreter';
import { stringify, SyncAsync } from '../util';
/**
 * An internal module of the Angular compiler that begins with component types,
 * extracts templates, and eventually produces a compiled version of the component
 * ready for linking into an application.
 *
 * @security  When compiling templates at runtime, you must ensure that the entire template comes
 * from a trusted source. Attacker-controlled data introduced by a template could expose your
 * application to XSS risks.  For more detail, see the [Security Guide](https://g.co/ng/security).
 */
export class JitCompiler {
    constructor(_metadataResolver, _templateParser, _styleCompiler, _viewCompiler, _ngModuleCompiler, _summaryResolver, _reflector, _jitEvaluator, _compilerConfig, _console, getExtraNgModuleProviders) {
        this._metadataResolver = _metadataResolver;
        this._templateParser = _templateParser;
        this._styleCompiler = _styleCompiler;
        this._viewCompiler = _viewCompiler;
        this._ngModuleCompiler = _ngModuleCompiler;
        this._summaryResolver = _summaryResolver;
        this._reflector = _reflector;
        this._jitEvaluator = _jitEvaluator;
        this._compilerConfig = _compilerConfig;
        this._console = _console;
        this.getExtraNgModuleProviders = getExtraNgModuleProviders;
        this._compiledTemplateCache = new Map();
        this._compiledHostTemplateCache = new Map();
        this._compiledDirectiveWrapperCache = new Map();
        this._compiledNgModuleCache = new Map();
        this._sharedStylesheetCount = 0;
        this._addedAotSummaries = new Set();
    }
    compileModuleSync(moduleType) {
        return SyncAsync.assertSync(this._compileModuleAndComponents(moduleType, true));
    }
    compileModuleAsync(moduleType) {
        return Promise.resolve(this._compileModuleAndComponents(moduleType, false));
    }
    compileModuleAndAllComponentsSync(moduleType) {
        return SyncAsync.assertSync(this._compileModuleAndAllComponents(moduleType, true));
    }
    compileModuleAndAllComponentsAsync(moduleType) {
        return Promise.resolve(this._compileModuleAndAllComponents(moduleType, false));
    }
    getComponentFactory(component) {
        const summary = this._metadataResolver.getDirectiveSummary(component);
        return summary.componentFactory;
    }
    loadAotSummaries(summaries) {
        this.clearCache();
        this._addAotSummaries(summaries);
    }
    _addAotSummaries(fn) {
        if (this._addedAotSummaries.has(fn)) {
            return;
        }
        this._addedAotSummaries.add(fn);
        const summaries = fn();
        for (let i = 0; i < summaries.length; i++) {
            const entry = summaries[i];
            if (typeof entry === 'function') {
                this._addAotSummaries(entry);
            }
            else {
                const summary = entry;
                this._summaryResolver.addSummary({ symbol: summary.type.reference, metadata: null, type: summary });
            }
        }
    }
    hasAotSummary(ref) {
        return !!this._summaryResolver.resolveSummary(ref);
    }
    _filterJitIdentifiers(ids) {
        return ids.map(mod => mod.reference).filter((ref) => !this.hasAotSummary(ref));
    }
    _compileModuleAndComponents(moduleType, isSync) {
        return SyncAsync.then(this._loadModules(moduleType, isSync), () => {
            this._compileComponents(moduleType, null);
            return this._compileModule(moduleType);
        });
    }
    _compileModuleAndAllComponents(moduleType, isSync) {
        return SyncAsync.then(this._loadModules(moduleType, isSync), () => {
            const componentFactories = [];
            this._compileComponents(moduleType, componentFactories);
            return {
                ngModuleFactory: this._compileModule(moduleType),
                componentFactories: componentFactories
            };
        });
    }
    _loadModules(mainModule, isSync) {
        const loading = [];
        const mainNgModule = this._metadataResolver.getNgModuleMetadata(mainModule);
        // Note: for runtime compilation, we want to transitively compile all modules,
        // so we also need to load the declared directives / pipes for all nested modules.
        this._filterJitIdentifiers(mainNgModule.transitiveModule.modules).forEach((nestedNgModule) => {
            // getNgModuleMetadata only returns null if the value passed in is not an NgModule
            const moduleMeta = this._metadataResolver.getNgModuleMetadata(nestedNgModule);
            this._filterJitIdentifiers(moduleMeta.declaredDirectives).forEach((ref) => {
                const promise = this._metadataResolver.loadDirectiveMetadata(moduleMeta.type.reference, ref, isSync);
                if (promise) {
                    loading.push(promise);
                }
            });
            this._filterJitIdentifiers(moduleMeta.declaredPipes)
                .forEach((ref) => this._metadataResolver.getOrLoadPipeMetadata(ref));
        });
        return SyncAsync.all(loading);
    }
    _compileModule(moduleType) {
        let ngModuleFactory = this._compiledNgModuleCache.get(moduleType);
        if (!ngModuleFactory) {
            const moduleMeta = this._metadataResolver.getNgModuleMetadata(moduleType);
            // Always provide a bound Compiler
            const extraProviders = this.getExtraNgModuleProviders(moduleMeta.type.reference);
            const outputCtx = createOutputContext();
            const compileResult = this._ngModuleCompiler.compile(outputCtx, moduleMeta, extraProviders);
            ngModuleFactory = this._interpretOrJit(ngModuleJitUrl(moduleMeta), outputCtx.statements)[compileResult.ngModuleFactoryVar];
            this._compiledNgModuleCache.set(moduleMeta.type.reference, ngModuleFactory);
        }
        return ngModuleFactory;
    }
    /**
     * @internal
     */
    _compileComponents(mainModule, allComponentFactories) {
        const ngModule = this._metadataResolver.getNgModuleMetadata(mainModule);
        const moduleByJitDirective = new Map();
        const templates = new Set();
        const transJitModules = this._filterJitIdentifiers(ngModule.transitiveModule.modules);
        transJitModules.forEach((localMod) => {
            const localModuleMeta = this._metadataResolver.getNgModuleMetadata(localMod);
            this._filterJitIdentifiers(localModuleMeta.declaredDirectives).forEach((dirRef) => {
                moduleByJitDirective.set(dirRef, localModuleMeta);
                const dirMeta = this._metadataResolver.getDirectiveMetadata(dirRef);
                if (dirMeta.isComponent) {
                    templates.add(this._createCompiledTemplate(dirMeta, localModuleMeta));
                    if (allComponentFactories) {
                        const template = this._createCompiledHostTemplate(dirMeta.type.reference, localModuleMeta);
                        templates.add(template);
                        allComponentFactories.push(dirMeta.componentFactory);
                    }
                }
            });
        });
        transJitModules.forEach((localMod) => {
            const localModuleMeta = this._metadataResolver.getNgModuleMetadata(localMod);
            this._filterJitIdentifiers(localModuleMeta.declaredDirectives).forEach((dirRef) => {
                const dirMeta = this._metadataResolver.getDirectiveMetadata(dirRef);
                if (dirMeta.isComponent) {
                    dirMeta.entryComponents.forEach((entryComponentType) => {
                        const moduleMeta = moduleByJitDirective.get(entryComponentType.componentType);
                        templates.add(this._createCompiledHostTemplate(entryComponentType.componentType, moduleMeta));
                    });
                }
            });
            localModuleMeta.entryComponents.forEach((entryComponentType) => {
                if (!this.hasAotSummary(entryComponentType.componentType)) {
                    const moduleMeta = moduleByJitDirective.get(entryComponentType.componentType);
                    templates.add(this._createCompiledHostTemplate(entryComponentType.componentType, moduleMeta));
                }
            });
        });
        templates.forEach((template) => this._compileTemplate(template));
    }
    clearCacheFor(type) {
        this._compiledNgModuleCache.delete(type);
        this._metadataResolver.clearCacheFor(type);
        this._compiledHostTemplateCache.delete(type);
        const compiledTemplate = this._compiledTemplateCache.get(type);
        if (compiledTemplate) {
            this._compiledTemplateCache.delete(type);
        }
    }
    clearCache() {
        // Note: don't clear the _addedAotSummaries, as they don't change!
        this._metadataResolver.clearCache();
        this._compiledTemplateCache.clear();
        this._compiledHostTemplateCache.clear();
        this._compiledNgModuleCache.clear();
    }
    _createCompiledHostTemplate(compType, ngModule) {
        if (!ngModule) {
            throw new Error(`Component ${stringify(compType)} is not part of any NgModule or the module has not been imported into your module.`);
        }
        let compiledTemplate = this._compiledHostTemplateCache.get(compType);
        if (!compiledTemplate) {
            const compMeta = this._metadataResolver.getDirectiveMetadata(compType);
            assertComponent(compMeta);
            const hostMeta = this._metadataResolver.getHostComponentMetadata(compMeta, compMeta.componentFactory.viewDefFactory);
            compiledTemplate =
                new CompiledTemplate(true, compMeta.type, hostMeta, ngModule, [compMeta.type]);
            this._compiledHostTemplateCache.set(compType, compiledTemplate);
        }
        return compiledTemplate;
    }
    _createCompiledTemplate(compMeta, ngModule) {
        let compiledTemplate = this._compiledTemplateCache.get(compMeta.type.reference);
        if (!compiledTemplate) {
            assertComponent(compMeta);
            compiledTemplate = new CompiledTemplate(false, compMeta.type, compMeta, ngModule, ngModule.transitiveModule.directives);
            this._compiledTemplateCache.set(compMeta.type.reference, compiledTemplate);
        }
        return compiledTemplate;
    }
    _compileTemplate(template) {
        if (template.isCompiled) {
            return;
        }
        const compMeta = template.compMeta;
        const externalStylesheetsByModuleUrl = new Map();
        const outputContext = createOutputContext();
        const componentStylesheet = this._styleCompiler.compileComponent(outputContext, compMeta);
        compMeta.template.externalStylesheets.forEach((stylesheetMeta) => {
            const compiledStylesheet = this._styleCompiler.compileStyles(createOutputContext(), compMeta, stylesheetMeta);
            externalStylesheetsByModuleUrl.set(stylesheetMeta.moduleUrl, compiledStylesheet);
        });
        this._resolveStylesCompileResult(componentStylesheet, externalStylesheetsByModuleUrl);
        const pipes = template.ngModule.transitiveModule.pipes.map(pipe => this._metadataResolver.getPipeSummary(pipe.reference));
        const { template: parsedTemplate, pipes: usedPipes } = this._parseTemplate(compMeta, template.ngModule, template.directives);
        const compileResult = this._viewCompiler.compileComponent(outputContext, compMeta, parsedTemplate, ir.variable(componentStylesheet.stylesVar), usedPipes);
        const evalResult = this._interpretOrJit(templateJitUrl(template.ngModule.type, template.compMeta), outputContext.statements);
        const viewClass = evalResult[compileResult.viewClassVar];
        const rendererType = evalResult[compileResult.rendererTypeVar];
        template.compiled(viewClass, rendererType);
    }
    _parseTemplate(compMeta, ngModule, directiveIdentifiers) {
        // Note: ! is ok here as components always have a template.
        const preserveWhitespaces = compMeta.template.preserveWhitespaces;
        const directives = directiveIdentifiers.map(dir => this._metadataResolver.getDirectiveSummary(dir.reference));
        const pipes = ngModule.transitiveModule.pipes.map(pipe => this._metadataResolver.getPipeSummary(pipe.reference));
        return this._templateParser.parse(compMeta, compMeta.template.htmlAst, directives, pipes, ngModule.schemas, templateSourceUrl(ngModule.type, compMeta, compMeta.template), preserveWhitespaces);
    }
    _resolveStylesCompileResult(result, externalStylesheetsByModuleUrl) {
        result.dependencies.forEach((dep, i) => {
            const nestedCompileResult = externalStylesheetsByModuleUrl.get(dep.moduleUrl);
            const nestedStylesArr = this._resolveAndEvalStylesCompileResult(nestedCompileResult, externalStylesheetsByModuleUrl);
            dep.setValue(nestedStylesArr);
        });
    }
    _resolveAndEvalStylesCompileResult(result, externalStylesheetsByModuleUrl) {
        this._resolveStylesCompileResult(result, externalStylesheetsByModuleUrl);
        return this._interpretOrJit(sharedStylesheetJitUrl(result.meta, this._sharedStylesheetCount++), result.outputCtx.statements)[result.stylesVar];
    }
    _interpretOrJit(sourceUrl, statements) {
        if (!this._compilerConfig.useJit) {
            return interpretStatements(statements, this._reflector);
        }
        else {
            return this._jitEvaluator.evaluateStatements(sourceUrl, statements, this._reflector, this._compilerConfig.jitDevMode);
        }
    }
}
class CompiledTemplate {
    constructor(isHost, compType, compMeta, ngModule, directives) {
        this.isHost = isHost;
        this.compType = compType;
        this.compMeta = compMeta;
        this.ngModule = ngModule;
        this.directives = directives;
        this._viewClass = null;
        this.isCompiled = false;
    }
    compiled(viewClass, rendererType) {
        this._viewClass = viewClass;
        this.compMeta.componentViewType.setDelegate(viewClass);
        for (let prop in rendererType) {
            this.compMeta.rendererType[prop] = rendererType[prop];
        }
        this.isCompiled = true;
    }
}
function assertComponent(meta) {
    if (!meta.isComponent) {
        throw new Error(`Could not compile '${identifierName(meta.type)}' because it is not a component.`);
    }
}
function createOutputContext() {
    const importExpr = (symbol) => ir.importExpr({ name: identifierName(symbol), moduleName: null, runtime: symbol });
    return { statements: [], genFilePath: '', importExpr, constantPool: new ConstantPool() };
}
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiY29tcGlsZXIuanMiLCJzb3VyY2VSb290IjoiIiwic291cmNlcyI6WyIuLi8uLi8uLi8uLi8uLi8uLi8uLi9wYWNrYWdlcy9jb21waWxlci9zcmMvaml0L2NvbXBpbGVyLnRzIl0sIm5hbWVzIjpbXSwibWFwcGluZ3MiOiJBQUFBOzs7Ozs7R0FNRztBQUVILE9BQU8sRUFBMkssY0FBYyxFQUFFLGNBQWMsRUFBNEIsc0JBQXNCLEVBQUUsY0FBYyxFQUFFLGlCQUFpQixFQUFDLE1BQU0scUJBQXFCLENBQUM7QUFHbFUsT0FBTyxFQUFDLFlBQVksRUFBQyxNQUFNLGtCQUFrQixDQUFDO0FBSTlDLE9BQU8sS0FBSyxFQUFFLE1BQU0sc0JBQXNCLENBQUM7QUFDM0MsT0FBTyxFQUFDLG1CQUFtQixFQUFDLE1BQU0sOEJBQThCLENBQUM7QUFNakUsT0FBTyxFQUF5QixTQUFTLEVBQUUsU0FBUyxFQUFDLE1BQU0sU0FBUyxDQUFDO0FBUXJFOzs7Ozs7OztHQVFHO0FBQ0gsTUFBTSxPQUFPLFdBQVc7SUFRdEIsWUFDWSxpQkFBMEMsRUFBVSxlQUErQixFQUNuRixjQUE2QixFQUFVLGFBQTJCLEVBQ2xFLGlCQUFtQyxFQUFVLGdCQUF1QyxFQUNwRixVQUE0QixFQUFVLGFBQTJCLEVBQ2pFLGVBQStCLEVBQVUsUUFBaUIsRUFDMUQseUJBQXVFO1FBTHZFLHNCQUFpQixHQUFqQixpQkFBaUIsQ0FBeUI7UUFBVSxvQkFBZSxHQUFmLGVBQWUsQ0FBZ0I7UUFDbkYsbUJBQWMsR0FBZCxjQUFjLENBQWU7UUFBVSxrQkFBYSxHQUFiLGFBQWEsQ0FBYztRQUNsRSxzQkFBaUIsR0FBakIsaUJBQWlCLENBQWtCO1FBQVUscUJBQWdCLEdBQWhCLGdCQUFnQixDQUF1QjtRQUNwRixlQUFVLEdBQVYsVUFBVSxDQUFrQjtRQUFVLGtCQUFhLEdBQWIsYUFBYSxDQUFjO1FBQ2pFLG9CQUFlLEdBQWYsZUFBZSxDQUFnQjtRQUFVLGFBQVEsR0FBUixRQUFRLENBQVM7UUFDMUQsOEJBQXlCLEdBQXpC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QUcsWUFBWSxDQUFDLElBQUksQ0FBQyxDQUFDO1NBQzlEO1FBQ0QsSUFBSSxDQUFDLFVBQVUsR0FBRyxJQUFJLENBQUM7SUFDekIsQ0FBQztDQUNGO0FBRUQsU0FBUyxlQUFlLENBQUMsSUFBOEI7SUFDckQsSUFBSSxDQUFDLElBQUksQ0FBQyxXQUFXLEVBQUU7UUFDckIsTUFBTSxJQUFJLEtBQUssQ0FDWCxzQkFBc0IsY0FBYyxDQUFDLElBQUksQ0FBQyxJQUFJLENBQUMsa0NBQWtDLENBQUMsQ0FBQztLQUN4RjtBQUNILENBQUM7QUFFRCxTQUFTLG1CQUFtQjtJQUMxQixNQUFNLFVBQVUsR0FBRyxDQUFDLE1BQVcsRUFBRSxFQUFFLENBQy9CLEVBQUUsQ0FBQyxVQUFVLENBQUMsRUFBQyxJQUFJLEVBQUUsY0FBYyxDQUFDLE1BQU0sQ0FBQyxFQUFFLFVBQVUsRUFBRSxJQUFJLEVBQUUsT0FBTyxFQUFFLE1BQU0sRUFBQyxDQUFDLENBQUM7SUFDckYsT0FBTyxFQUFDLFVBQVUsRUFBRSxFQUFFLEVBQUUsV0FBVyxFQUFFLEVBQUUsRUFBRSxVQUFVLEVBQUUsWUFBWSxFQUFFLElBQUksWUFBWSxFQUFFLEVBQUMsQ0FBQztBQUN6RixDQUFDIiwic291cmNlc0NvbnRlbnQiOlsiLyoqXG4gKiBAbGljZW5zZVxuICogQ29weXJpZ2h0IEdvb2dsZSBMTEMgQWxsIFJpZ2h0cyBSZXNlcnZlZC5cbiAqXG4gKiBVc2Ugb2YgdGhpcyBzb3VyY2UgY29kZSBpcyBnb3Zlcm5lZCBieSBhbiBNSVQtc3R5bGUgbGljZW5zZSB0aGF0IGNhbiBiZVxuICogZm91bmQgaW4gdGhlIExJQ0VOU0UgZmlsZSBhdCBodHRwczovL2FuZ3VsYXIuaW8vbGljZW5zZVxuICovXG5cbmltcG9ydCB7Q29tcGlsZURpcmVjdGl2ZU1ldGFkYXRhLCBDb21waWxlSWRlbnRpZmllck1ldGFkYXRhLCBDb21waWxlTmdNb2R1bGVNZXRhZGF0YSwgQ29tcGlsZVBpcGVTdW1tYXJ5LCBDb21waWxlUHJvdmlkZXJNZXRhZGF0YSwgQ29tcGlsZVN0eWxlc2hlZXRNZXRhZGF0YSwgQ29tcGlsZVR5cGVTdW1tYXJ5LCBpZGVudGlmaWVyTmFtZSwgbmdNb2R1bGVKaXRVcmwsIFByb3ZpZGVyTWV0YSwgUHJveHlDbGFzcywgc2hhcmVkU3R5bGVzaGVldEppdFVybCwgdGVtcGxhdGVKaXRVcmwsIHRlbXBsYXRlU291cmNlVXJsfSBmcm9tICcuLi9jb21waWxlX21ldGFkYXRhJztcbmltcG9ydCB7Q29tcGlsZVJlZmxlY3Rvcn0gZnJvbSAnLi4vY29tcGlsZV9yZWZsZWN0b3InO1xuaW1wb3J0IHtDb21waWxlckNvbmZpZ30gZnJvbSAnLi4vY29uZmlnJztcbmltcG9ydCB7Q29uc3RhbnRQb29sfSBmcm9tICcuLi9jb25zdGFudF9wb29sJztcbmltcG9ydCB7VHlwZX0gZnJvbSAnLi4vY29yZSc7XG5pbXBvcnQge0NvbXBpbGVNZXRhZGF0YVJlc29sdmVyfSBmcm9tICcuLi9tZXRhZGF0YV9yZXNvbHZlcic7XG5pbXBvcnQge05nTW9kdWxlQ29tcGlsZXJ9IGZyb20gJy4uL25nX21vZHVsZV9jb21waWxlcic7XG5pbXBvcnQgKiBhcyBpciBmcm9tICcuLi9vdXRwdXQvb3V0cHV0X2FzdCc7XG5pbXBvcnQge2ludGVycHJldFN0YXRlbWVudHN9IGZyb20gJy4uL291dHB1dC9vdXRwdXRfaW50ZXJwcmV0ZXInO1xuaW1wb3J0IHtKaXRFdmFsdWF0b3J9IGZyb20gJy4uL291dHB1dC9vdXRwdXRfaml0JztcbmltcG9ydCB7Q29tcGlsZWRTdHlsZXNoZWV0LCBTdHlsZUNvbXBpbGVyfSBmcm9tICcuLi9zdHlsZV9jb21waWxlcic7XG5pbXBvcnQge1N1bW1hcnlSZXNvbHZlcn0gZnJvbSAnLi4vc3VtbWFyeV9yZXNvbHZlcic7XG5pbXBvcnQge1RlbXBsYXRlQXN0fSBmcm9tICcuLi90ZW1wbGF0ZV9wYXJzZXIvdGVtcGxhdGVfYXN0JztcbmltcG9ydCB7VGVtcGxhdGVQYXJzZXJ9IGZyb20gJy4uL3RlbXBsYXRlX3BhcnNlci90ZW1wbGF0ZV9wYXJzZXInO1xuaW1wb3J0IHtDb25zb2xlLCBPdXRwdXRDb250ZXh0LCBzdHJpbmdpZnksIFN5bmNBc3luY30gZnJvbSAnLi4vdXRpbCc7XG5pbXBvcnQge1ZpZXdDb21waWxlcn0gZnJvbSAnLi4vdmlld19jb21waWxlci92aWV3X2NvbXBpbGVyJztcblxuZXhwb3J0IGludGVyZmFjZSBNb2R1bGVXaXRoQ29tcG9uZW50RmFjdG9yaWVzIHtcbiAgbmdNb2R1bGVGYWN0b3J5OiBvYmplY3Q7XG4gIGNvbXBvbmVudEZhY3Rvcmllczogb2JqZWN0W107XG59XG5cbi8qKlxuICogQW4gaW50ZXJuYWwgbW9kdWxlIG9mIHRoZSBBbmd1bGFyIGNvbXBpbGVyIHRoYXQgYmVnaW5zIHdpdGggY29tcG9uZW50IHR5cGVzLFxuICogZXh0cmFjdHMgdGVtcGxhdGVzLCBhbmQgZXZlbnR1YWxseSBwcm9kdWNlcyBhIGNvbXBpbGVkIHZlcnNpb24gb2YgdGhlIGNvbXBvbmVudFxuICogcmVhZHkgZm9yIGxpbmtpbmcgaW50byBhbiBhcHBsaWNhdGlvbi5cbiAqXG4gKiBAc2VjdXJpdHkgIFdoZW4gY29tcGlsaW5nIHRlbXBsYXRlcyBhdCBydW50aW1lLCB5b3UgbXVzdCBlbnN1cmUgdGhhdCB0aGUgZW50aXJlIHRlbXBsYXRlIGNvbWVzXG4gKiBmcm9tIGEgdHJ1c3RlZCBzb3VyY2UuIEF0dGFja2VyLWNvbnRyb2xsZWQgZGF0YSBpbnRyb2R1Y2VkIGJ5IGEgdGVtcGxhdGUgY291bGQgZXhwb3NlIHlvdXJcbiAqIGFwcGxpY2F0aW9uIHRvIFhTUyByaXNrcy4gIEZvciBtb3JlIGRldGFpbCwgc2VlIHRoZSBbU2VjdXJpdHkgR3VpZGVdKGh0dHBzOi8vZy5jby9uZy9zZWN1cml0eSkuXG4gKi9cbmV4cG9ydCBjbGFzcyBKaXRDb21waWxlciB7XG4gIHByaXZhdGUgX2NvbXBpbGVkVGVtcGxhdGVDYWNoZSA9IG5ldyBNYXA8VHlwZSwgQ29tcGlsZWRUZW1wbGF0ZT4oKTtcbiAgcHJpdmF0ZSBfY29tcGlsZWRIb3N0VGVtcGxhdGVDYWNoZSA9IG5ldyBNYXA8VHlwZSwgQ29tcGlsZWRUZW1wbGF0ZT4oKTtcbiAgcHJpdmF0ZSBfY29tcGlsZWREaXJlY3RpdmVXcmFwcGVyQ2FjaGUgPSBuZXcgTWFwPFR5cGUsIFR5cGU+KCk7XG4gIHByaXZhdGUgX2NvbXBpbGVkTmdNb2R1bGVDYWNoZSA9IG5ldyBNYXA8VHlwZSwgb2JqZWN0PigpO1xuICBwcml2YXRlIF9zaGFyZWRTdHlsZXNoZWV0Q291bnQgPSAwO1xuICBwcml2YXRlIF9hZGRlZEFvdFN1bW1hcmllcyA9IG5ldyBTZXQ8KCkgPT4gYW55W10+KCk7XG5cbiAgY29uc3RydWN0b3IoXG4gICAgICBwcml2YXRlIF9tZXRhZGF0YVJlc29sdmVyOiBDb21waWxlTWV0YWRhdGFSZXNvbHZlciwgcHJpdmF0ZSBfdGVtcGxhdGVQYXJzZXI6IFRlbXBsYXRlUGFyc2VyLFxuICAgICAgcHJpdmF0ZSBfc3R5bGVDb21waWxlcjogU3R5bGVDb21waWxlciwgcHJpdmF0ZSBfdmlld0NvbXBpbGVyOiBWaWV3Q29tcGlsZXIsXG4gICAgICBwcml2YXRlIF9uZ01vZHVsZUNvbXBpbGVyOiBOZ01vZHVsZUNvbXBpbGVyLCBwcml2YXRlIF9zdW1tYXJ5UmVzb2x2ZXI6IFN1bW1hcnlSZXNvbHZlcjxUeXBlPixcbiAgICAgIHByaXZhdGUgX3JlZmxlY3RvcjogQ29tcGlsZVJlZmxlY3RvciwgcHJpdmF0ZSBfaml0RXZhbHVhdG9yOiBKaXRFdmFsdWF0b3IsXG4gICAgICBwcml2YXRlIF9jb21waWxlckNvbmZpZzogQ29tcGlsZXJDb25maWcsIHByaXZhdGUgX2NvbnNvbGU6IENvbnNvbGUsXG4gICAgICBwcml2YXRlIGdldEV4dHJhTmdNb2R1bGVQcm92aWRlcnM6IChuZ01vZHVsZTogYW55KSA9PiBDb21waWxlUHJvdmlkZXJNZXRhZGF0YVtdKSB7fVxuXG4gIGNvbXBpbGVNb2R1bGVTeW5jKG1vZHVsZVR5cGU6IFR5cGUpOiBvYmplY3Qge1xuICAgIHJldHVybiBTeW5jQXN5bmMuYXNzZXJ0U3luYyh0aGlzLl9jb21waWxlTW9kdWxlQW5kQ29tcG9uZW50cyhtb2R1bGVUeXBlLCB0cnVlKSk7XG4gIH1cblxuICBjb21waWxlTW9kdWxlQXN5bmMobW9kdWxlVHlwZTogVHlwZSk6IFByb21pc2U8b2JqZWN0PiB7XG4gICAgcmV0dXJuIFByb21pc2UucmVzb2x2ZSh0aGlzLl9jb21waWxlTW9kdWxlQW5kQ29tcG9uZW50cyhtb2R1bGVUeXBlLCBmYWxzZSkpO1xuICB9XG5cbiAgY29tcGlsZU1vZHVsZUFuZEFsbENvbXBvbmVudHNTeW5jKG1vZHVsZVR5cGU6IFR5cGUpOiBNb2R1bGVXaXRoQ29tcG9uZW50RmFjdG9yaWVzIHtcbiAgICByZXR1cm4gU3luY0FzeW5jLmFzc2VydFN5bmModGhpcy5fY29tcGlsZU1vZHVsZUFuZEFsbENvbXBvbmVudHMobW9kdWxlVHlwZSwgdHJ1ZSkpO1xuICB9XG5cbiAgY29tcGlsZU1vZHVsZUFuZEFsbENvbXBvbmVudHNBc3luYyhtb2R1bGVUeXBlOiBUeXBlKTogUHJvbWlzZTxNb2R1bGVXaXRoQ29tcG9uZW50RmFjdG9yaWVzPiB7XG4gICAgcmV0dXJuIFByb21pc2UucmVzb2x2ZSh0aGlzLl9jb21waWxlTW9kdWxlQW5kQWxsQ29tcG9uZW50cyhtb2R1bGVUeXBlLCBmYWxzZSkpO1xuICB9XG5cbiAgZ2V0Q29tcG9uZW50RmFjdG9yeShjb21wb25lbnQ6IFR5cGUpOiBvYmplY3Qge1xuICAgIGNvbnN0IHN1bW1hcnkgPSB0aGlzLl9tZXRhZGF0YVJlc29sdmVyLmdldERpcmVjdGl2ZVN1bW1hcnkoY29tcG9uZW50KTtcbiAgICByZXR1cm4gc3VtbWFyeS5jb21wb25lbnRGYWN0b3J5IGFzIG9iamVjdDtcbiAgfVxuXG4gIGxvYWRBb3RTdW1tYXJpZXMoc3VtbWFyaWVzOiAoKSA9PiBhbnlbXSkge1xuICAgIHRoaXMuY2xlYXJDYWNoZSgpO1xuICAgIHRoaXMuX2FkZEFvdFN1bW1hcmllcyhzdW1tYXJpZXMpO1xuICB9XG5cbiAgcHJpdmF0ZSBfYWRkQW90U3VtbWFyaWVzKGZuOiAoKSA9PiBhbnlbXSkge1xuICAgIGlmICh0aGlzLl9hZGRlZEFvdFN1bW1hcmllcy5oYXMoZm4pKSB7XG4gICAgICByZXR1cm47XG4gICAgfVxuICAgIHRoaXMuX2FkZGVkQW90U3VtbWFyaWVzLmFkZChmbik7XG4gICAgY29uc3Qgc3VtbWFyaWVzID0gZm4oKTtcbiAgICBmb3IgKGxldCBpID0gMDsgaSA8IHN1bW1hcmllcy5sZW5ndGg7IGkrKykge1xuICAgICAgY29uc3QgZW50cnkgPSBzdW1tYXJpZXNbaV07XG4gICAgICBpZiAodHlwZW9mIGVudHJ5ID09PSAnZnVuY3Rpb24nKSB7XG4gICAgICAgIHRoaXMuX2FkZEFvdFN1bW1hcmllcyhlbnRyeSk7XG4gICAgICB9IGVsc2Uge1xuICAgICAgICBjb25zdCBzdW1tYXJ5ID0gZW50cnkgYXMgQ29tcGlsZVR5cGVTdW1tYXJ5O1xuICAgICAgICB0aGlzLl9zdW1tYXJ5UmVzb2x2ZXIuYWRkU3VtbWFyeShcbiAgICAgICAgICAgIHtzeW1ib2w6IHN1bW1hcnkudHlwZS5yZWZlcmVuY2UsIG1ldGFkYXRhOiBudWxsLCB0eXBlOiBzdW1tYXJ5fSk7XG4gICAgICB9XG4gICAgfVxuICB9XG5cbiAgaGFzQW90U3VtbWFyeShyZWY6IFR5cGUpIHtcbiAgICByZXR1cm4gISF0aGlzLl9zdW1tYXJ5UmVzb2x2ZXIucmVzb2x2ZVN1bW1hcnkocmVmKTtcbiAgfVxuXG4gIHByaXZhdGUgX2ZpbHRlckppdElkZW50aWZpZXJzKGlkczogQ29tcGlsZUlkZW50aWZpZXJNZXRhZGF0YVtdKTogYW55W10ge1xuICAgIHJldHVybiBpZHMubWFwKG1vZCA9PiBtb2QucmVmZXJlbmNlKS5maWx0ZXIoKHJlZikgPT4gIXRoaXMuaGFzQW90U3VtbWFyeShyZWYpKTtcbiAgfVxuXG4gIHByaXZhdGUgX2NvbXBpbGVNb2R1bGVBbmRDb21wb25lbnRzKG1vZHVsZVR5cGU6IFR5cGUsIGlzU3luYzogYm9vbGVhbik6IFN5bmNBc3luYzxvYmplY3Q+IHtcbiAgICByZXR1cm4gU3luY0FzeW5jLnRoZW4odGhpcy5fbG9hZE1vZHVsZXMobW9kdWxlVHlwZSwgaXNTeW5jKSwgKCkgPT4ge1xuICAgICAgdGhpcy5fY29tcGlsZUNvbXBvbmVudHMobW9kdWxlVHlwZSwgbnVsbCk7XG4gICAgICByZXR1cm4gdGhpcy5fY29tcGlsZU1vZHVsZShtb2R1bGVUeXBlKTtcbiAgICB9KTtcbiAgfVxuXG4gIHByaXZhdGUgX2NvbXBpbGVNb2R1bGVBbmRBbGxDb21wb25lbnRzKG1vZHVsZVR5cGU6IFR5cGUsIGlzU3luYzogYm9vbGVhbik6XG4gICAgICBTeW5jQXN5bmM8TW9kdWxlV2l0aENvbXBvbmVudEZhY3Rvcmllcz4ge1xuICAgIHJldHVybiBTeW5jQXN5bmMudGhlbih0aGlzLl9sb2FkTW9kdWxlcyhtb2R1bGVUeXBlLCBpc1N5bmMpLCAoKSA9PiB7XG4gICAgICBjb25zdCBjb21wb25lbnRGYWN0b3JpZXM6IG9iamVjdFtdID0gW107XG4gICAgICB0aGlzLl9jb21waWxlQ29tcG9uZW50cyhtb2R1bGVUeXBlLCBjb21wb25lbnRGYWN0b3JpZXMpO1xuICAgICAgcmV0dXJuIHtcbiAgICAgICAgbmdNb2R1bGVGYWN0b3J5OiB0aGlzLl9jb21waWxlTW9kdWxlKG1vZHVsZVR5cGUpLFxuICAgICAgICBjb21wb25lbnRGYWN0b3JpZXM6IGNvbXBvbmVudEZhY3Rvcmllc1xuICAgICAgfTtcbiAgICB9KTtcbiAgfVxuXG4gIHByaXZhdGUgX2xvYWRNb2R1bGVzKG1haW5Nb2R1bGU6IGFueSwgaXNTeW5jOiBib29sZWFuKTogU3luY0FzeW5jPGFueT4ge1xuICAgIGNvbnN0IGxvYWRpbmc6IFByb21pc2U8YW55PltdID0gW107XG4gICAgY29uc3QgbWFpbk5nTW9kdWxlID0gdGhpcy5fbWV0YWRhdGFSZXNvbHZlci5nZXROZ01vZHVsZU1ldGFkYXRhKG1haW5Nb2R1bGUpITtcbiAgICAvLyBOb3RlOiBmb3IgcnVudGltZSBjb21waWxhdGlvbiwgd2Ugd2FudCB0byB0cmFuc2l0aXZlbHkgY29tcGlsZSBhbGwgbW9kdWxlcyxcbiAgICAvLyBzbyB3ZSBhbHNvIG5lZWQgdG8gbG9hZCB0aGUgZGVjbGFyZWQgZGlyZWN0aXZlcyAvIHBpcGVzIGZvciBhbGwgbmVzdGVkIG1vZHVsZXMuXG4gICAgdGhpcy5fZmlsdGVySml0SWRlbnRpZmllcnMobWFpbk5nTW9kdWxlLnRyYW5zaXRpdmVNb2R1bGUubW9kdWxlcykuZm9yRWFjaCgobmVzdGVkTmdNb2R1bGUpID0+IHtcbiAgICAgIC8vIGdldE5nTW9kdWxlTWV0YWRhdGEgb25seSByZXR1cm5zIG51bGwgaWYgdGhlIHZhbHVlIHBhc3NlZCBpbiBpcyBub3QgYW4gTmdNb2R1bGVcbiAgICAgIGNvbnN0IG1vZHVsZU1ldGEgPSB0aGlzLl9tZXRhZGF0YVJlc29sdmVyLmdldE5nTW9kdWxlTWV0YWRhdGEobmVzdGVkTmdNb2R1bGUpITtcbiAgICAgIHRoaXMuX2ZpbHRlckppdElkZW50aWZpZXJzKG1vZHVsZU1ldGEuZGVjbGFyZWREaXJlY3RpdmVzKS5mb3JFYWNoKChyZWYpID0+IHtcbiAgICAgICAgY29uc3QgcHJvbWlzZSA9XG4gICAgICAgICAgICB0aGlzLl9tZXRhZGF0YVJlc29sdmVyLmxvYWREaXJlY3RpdmVNZXRhZGF0YShtb2R1bGVNZXRhLnR5cGUucmVmZXJlbmNlLCByZWYsIGlzU3luYyk7XG4gICAgICAgIGlmIChwcm9taXNlKSB7XG4gICAgICAgICAgbG9hZGluZy5wdXNoKHByb21pc2UpO1xuICAgICAgICB9XG4gICAgICB9KTtcbiAgICAgIHRoaXMuX2ZpbHRlckppdElkZW50aWZpZXJzKG1vZHVsZU1ldGEuZGVjbGFyZWRQaXBlcylcbiAgICAgICAgICAuZm9yRWFjaCgocmVmKSA9PiB0aGlzLl9tZXRhZGF0YVJlc29sdmVyLmdldE9yTG9hZFBpcGVNZXRhZGF0YShyZWYpKTtcbiAgICB9KTtcbiAgICByZXR1cm4gU3luY0FzeW5jLmFsbChsb2FkaW5nKTtcbiAgfVxuXG4gIHByaXZhdGUgX2NvbXBpbGVNb2R1bGUobW9kdWxlVHlwZTogVHlwZSk6IG9iamVjdCB7XG4gICAgbGV0IG5nTW9kdWxlRmFjdG9yeSA9IHRoaXMuX2NvbXBpbGVkTmdNb2R1bGVDYWNoZS5nZXQobW9kdWxlVHlwZSkhO1xuICAgIGlmICghbmdNb2R1bGVGYWN0b3J5KSB7XG4gICAgICBjb25zdCBtb2R1bGVNZXRhID0gdGhpcy5fbWV0YWRhdGFSZXNvbHZlci5nZXROZ01vZHVsZU1ldGFkYXRhKG1vZHVsZVR5cGUpITtcbiAgICAgIC8vIEFsd2F5cyBwcm92aWRlIGEgYm91bmQgQ29tcGlsZXJcbiAgICAgIGNvbnN0IGV4dHJhUHJvdmlkZXJzID0gdGhpcy5nZXRFeHRyYU5nTW9kdWxlUHJvdmlkZXJzKG1vZHVsZU1ldGEudHlwZS5yZWZlcmVuY2UpO1xuICAgICAgY29uc3Qgb3V0cHV0Q3R4ID0gY3JlYXRlT3V0cHV0Q29udGV4dCgpO1xuICAgICAgY29uc3QgY29tcGlsZVJlc3VsdCA9IHRoaXMuX25nTW9kdWxlQ29tcGlsZXIuY29tcGlsZShvdXRwdXRDdHgsIG1vZHVsZU1ldGEsIGV4dHJhUHJvdmlkZXJzKTtcbiAgICAgIG5nTW9kdWxlRmFjdG9yeSA9IHRoaXMuX2ludGVycHJldE9ySml0KFxuICAgICAgICAgIG5nTW9kdWxlSml0VXJsKG1vZHVsZU1ldGEpLCBvdXRwdXRDdHguc3RhdGVtZW50cylbY29tcGlsZVJlc3VsdC5uZ01vZHVsZUZhY3RvcnlWYXJdO1xuICAgICAgdGhpcy5fY29tcGlsZWROZ01vZHVsZUNhY2hlLnNldChtb2R1bGVNZXRhLnR5cGUucmVmZXJlbmNlLCBuZ01vZHVsZUZhY3RvcnkpO1xuICAgIH1cbiAgICByZXR1cm4gbmdNb2R1bGVGYWN0b3J5O1xuICB9XG5cbiAgLyoqXG4gICAqIEBpbnRlcm5hbFxuICAgKi9cbiAgX2NvbXBpbGVDb21wb25lbnRzKG1haW5Nb2R1bGU6IFR5cGUsIGFsbENvbXBvbmVudEZhY3Rvcmllczogb2JqZWN0W118bnVsbCkge1xuICAgIGNvbnN0IG5nTW9kdWxlID0gdGhpcy5fbWV0YWRhdGFSZXNvbHZlci5nZXROZ01vZHVsZU1ldGFkYXRhKG1haW5Nb2R1bGUpITtcbiAgICBjb25zdCBtb2R1bGVCeUppdERpcmVjdGl2ZSA9IG5ldyBNYXA8YW55LCBDb21waWxlTmdNb2R1bGVNZXRhZGF0YT4oKTtcbiAgICBjb25zdCB0ZW1wbGF0ZXMgPSBuZXcgU2V0PENvbXBpbGVkVGVtcGxhdGU+KCk7XG5cbiAgICBjb25zdCB0cmFuc0ppdE1vZHVsZXMgPSB0aGlzLl9maWx0ZXJKaXRJZGVudGlmaWVycyhuZ01vZHVsZS50cmFuc2l0aXZlTW9kdWxlLm1vZHVsZXMpO1xuICAgIHRyYW5zSml0TW9kdWxlcy5mb3JFYWNoKChsb2NhbE1vZCkgPT4ge1xuICAgICAgY29uc3QgbG9jYWxNb2R1bGVNZXRhID0gdGhpcy5fbWV0YWRhdGFSZXNvbHZlci5nZXROZ01vZHVsZU1ldGFkYXRhKGxvY2FsTW9kKSE7XG4gICAgICB0aGlzLl9maWx0ZXJKaXRJZGVudGlmaWVycyhsb2NhbE1vZHVsZU1ldGEuZGVjbGFyZWREaXJlY3RpdmVzKS5mb3JFYWNoKChkaXJSZWYpID0+IHtcbiAgICAgICAgbW9kdWxlQnlKaXREaXJlY3RpdmUuc2V0KGRpclJlZiwgbG9jYWxNb2R1bGVNZXRhKTtcbiAgICAgICAgY29uc3QgZGlyTWV0YSA9IHRoaXMuX21ldGFkYXRhUmVzb2x2ZXIuZ2V0RGlyZWN0aXZlTWV0YWRhdGEoZGlyUmVmKTtcbiAgICAgICAgaWYgKGRpck1ldGEuaXNDb21wb25lbnQpIHtcbiAgICAgICAgICB0ZW1wbGF0ZXMuYWRkKHRoaXMuX2NyZWF0ZUNvbXBpbGVkVGVtcGxhdGUoZGlyTWV0YSwgbG9jYWxNb2R1bGVNZXRhKSk7XG4gICAgICAgICAgaWYgKGFsbENvbXBvbmVudEZhY3Rvcmllcykge1xuICAgICAgICAgICAgY29uc3QgdGVtcGxhdGUgPVxuICAgICAgICAgICAgICAgIHRoaXMuX2NyZWF0ZUNvbXBpbGVkSG9zdFRlbXBsYXRlKGRpck1ldGEudHlwZS5yZWZlcmVuY2UsIGxvY2FsTW9kdWxlTWV0YSk7XG4gICAgICAgICAgICB0ZW1wbGF0ZXMuYWRkKHRlbXBsYXRlKTtcbiAgICAgICAgICAgIGFsbENvbXBvbmVudEZhY3Rvcmllcy5wdXNoKGRpck1ldGEuY29tcG9uZW50RmFjdG9yeSBhcyBvYmplY3QpO1xuICAgICAgICAgIH1cbiAgICAgICAgfVxuICAgICAgfSk7XG4gICAgfSk7XG4gICAgdHJhbnNKaXRNb2R1bGVzLmZvckVhY2goKGxvY2FsTW9kKSA9PiB7XG4gICAgICBjb25zdCBsb2NhbE1vZHVsZU1ldGEgPSB0aGlzLl9tZXRhZGF0YVJlc29sdmVyLmdldE5nTW9kdWxlTWV0YWRhdGEobG9jYWxNb2QpITtcbiAgICAgIHRoaXMuX2ZpbHRlckppdElkZW50aWZpZXJzKGxvY2FsTW9kdWxlTWV0YS5kZWNsYXJlZERpcmVjdGl2ZXMpLmZvckVhY2goKGRpclJlZikgPT4ge1xuICAgICAgICBjb25zdCBkaXJNZXRhID0gdGhpcy5fbWV0YWRhdGFSZXNvbHZlci5nZXREaXJlY3RpdmVNZXRhZGF0YShkaXJSZWYpO1xuICAgICAgICBpZiAoZGlyTWV0YS5pc0NvbXBvbmVudCkge1xuICAgICAgICAgIGRpck1ldGEuZW50cnlDb21wb25lbnRzLmZvckVhY2goKGVudHJ5Q29tcG9uZW50VHlwZSkgPT4ge1xuICAgICAgICAgICAgY29uc3QgbW9kdWxlTWV0YSA9IG1vZHVsZUJ5Sml0RGlyZWN0aXZlLmdldChlbnRyeUNvbXBvbmVudFR5cGUuY29tcG9uZW50VHlwZSkhO1xuICAgICAgICAgICAgdGVtcGxhdGVzLmFkZChcbiAgICAgICAgICAgICAgICB0aGlzLl9jcmVhdGVDb21waWxlZEhvc3RUZW1wbGF0ZShlbnRyeUNvbXBvbmVudFR5cGUuY29tcG9uZW50VHlwZSwgbW9kdWxlTWV0YSkpO1xuICAgICAgICAgIH0pO1xuICAgICAgICB9XG4gICAgICB9KTtcbiAgICAgIGxvY2FsTW9kdWxlTWV0YS5lbnRyeUNvbXBvbmVudHMuZm9yRWFjaCgoZW50cnlDb21wb25lbnRUeXBlKSA9PiB7XG4gICAgICAgIGlmICghdGhpcy5oYXNBb3RTdW1tYXJ5KGVudHJ5Q29tcG9uZW50VHlwZS5jb21wb25lbnRUeXBlKSkge1xuICAgICAgICAgIGNvbnN0IG1vZHVsZU1ldGEgPSBtb2R1bGVCeUppdERpcmVjdGl2ZS5nZXQoZW50cnlDb21wb25lbnRUeXBlLmNvbXBvbmVudFR5cGUpITtcbiAgICAgICAgICB0ZW1wbGF0ZXMuYWRkKFxuICAgICAgICAgICAgICB0aGlzLl9jcmVhdGVDb21waWxlZEhvc3RUZW1wbGF0ZShlbnRyeUNvbXBvbmVudFR5cGUuY29tcG9uZW50VHlwZSwgbW9kdWxlTWV0YSkpO1xuICAgICAgICB9XG4gICAgICB9KTtcbiAgICB9KTtcbiAgICB0ZW1wbGF0ZXMuZm9yRWFjaCgodGVtcGxhdGUpID0+IHRoaXMuX2NvbXBpbGVUZW1wbGF0ZSh0ZW1wbGF0ZSkpO1xuICB9XG5cbiAgY2xlYXJDYWNoZUZvcih0eXBlOiBUeXBlKSB7XG4gICAgdGhpcy5fY29tcGlsZWROZ01vZHVsZUNhY2hlLmRlbGV0ZSh0eXBlKTtcbiAgICB0aGlzLl9tZXRhZGF0YVJlc29sdmVyLmNsZWFyQ2FjaGVGb3IodHlwZSk7XG4gICAgdGhpcy5fY29tcGlsZWRIb3N0VGVtcGxhdGVDYWNoZS5kZWxldGUodHlwZSk7XG4gICAgY29uc3QgY29tcGlsZWRUZW1wbGF0ZSA9IHRoaXMuX2NvbXBpbGVkVGVtcGxhdGVDYWNoZS5nZXQodHlwZSk7XG4gICAgaWYgKGNvbXBpbGVkVGVtcGxhdGUpIHtcbiAgICAgIHRoaXMuX2NvbXBpbGVkVGVtcGxhdGVDYWNoZS5kZWxldGUodHlwZSk7XG4gICAgfVxuICB9XG5cbiAgY2xlYXJDYWNoZSgpOiB2b2lkIHtcbiAgICAvLyBOb3RlOiBkb24ndCBjbGVhciB0aGUgX2FkZGVkQW90U3VtbWFyaWVzLCBhcyB0aGV5IGRvbid0IGNoYW5nZSFcbiAgICB0aGlzLl9tZXRhZGF0YVJlc29sdmVyLmNsZWFyQ2FjaGUoKTtcbiAgICB0aGlzLl9jb21waWxlZFRlbXBsYXRlQ2FjaGUuY2xlYXIoKTtcbiAgICB0aGlzLl9jb21waWxlZEhvc3RUZW1wbGF0ZUNhY2hlLmNsZWFyKCk7XG4gICAgdGhpcy5fY29tcGlsZWROZ01vZHVsZUNhY2hlLmNsZWFyKCk7XG4gIH1cblxuICBwcml2YXRlIF9jcmVhdGVDb21waWxlZEhvc3RUZW1wbGF0ZShjb21wVHlwZTogVHlwZSwgbmdNb2R1bGU6IENvbXBpbGVOZ01vZHVsZU1ldGFkYXRhKTpcbiAgICAgIENvbXBpbGVkVGVtcGxhdGUge1xuICAgIGlmICghbmdNb2R1bGUpIHtcbiAgICAgIHRocm93IG5ldyBFcnJvcihgQ29tcG9uZW50ICR7XG4gICAgICAgICAgc3RyaW5naWZ5KFxuICAgICAgICAgICAgICBjb21wVHlwZSl9IGlzIG5vdCBwYXJ0IG9mIGFueSBOZ01vZHVsZSBvciB0aGUgbW9kdWxlIGhhcyBub3QgYmVlbiBpbXBvcnRlZCBpbnRvIHlvdXIgbW9kdWxlLmApO1xuICAgIH1cbiAgICBsZXQgY29tcGlsZWRUZW1wbGF0ZSA9IHRoaXMuX2NvbXBpbGVkSG9zdFRlbXBsYXRlQ2FjaGUuZ2V0KGNvbXBUeXBlKTtcbiAgICBpZiAoIWNvbXBpbGVkVGVtcGxhdGUpIHtcbiAgICAgIGNvbnN0IGNvbXBNZXRhID0gdGhpcy5fbWV0YWRhdGFSZXNvbHZlci5nZXREaXJlY3RpdmVNZXRhZGF0YShjb21wVHlwZSk7XG4gICAgICBhc3NlcnRDb21wb25lbnQoY29tcE1ldGEpO1xuXG4gICAgICBjb25zdCBob3N0TWV0YSA9IHRoaXMuX21ldGFkYXRhUmVzb2x2ZXIuZ2V0SG9zdENvbXBvbmVudE1ldGFkYXRhKFxuICAgICAgICAgIGNvbXBNZXRhLCAoY29tcE1ldGEuY29tcG9uZW50RmFjdG9yeSBhcyBhbnkpLnZpZXdEZWZGYWN0b3J5KTtcbiAgICAgIGNvbXBpbGVkVGVtcGxhdGUgPVxuICAgICAgICAgIG5ldyBDb21waWxlZFRlbXBsYXRlKHRydWUsIGNvbXBNZXRhLnR5cGUsIGhvc3RNZXRhLCBuZ01vZHVsZSwgW2NvbXBNZXRhLnR5cGVdKTtcbiAgICAgIHRoaXMuX2NvbXBpbGVkSG9zdFRlbXBsYXRlQ2FjaGUuc2V0KGNvbXBUeXBlLCBjb21waWxlZFRlbXBsYXRlKTtcbiAgICB9XG4gICAgcmV0dXJuIGNvbXBpbGVkVGVtcGxhdGU7XG4gIH1cblxuICBwcml2YXRlIF9jcmVhdGVDb21waWxlZFRlbXBsYXRlKFxuICAgICAgY29tcE1ldGE6IENvbXBpbGVEaXJlY3RpdmVNZXRhZGF0YSwgbmdNb2R1bGU6IENvbXBpbGVOZ01vZHVsZU1ldGFkYXRhKTogQ29tcGlsZWRUZW1wbGF0ZSB7XG4gICAgbGV0IGNvbXBpbGVkVGVtcGxhdGUgPSB0aGlzLl9jb21waWxlZFRlbXBsYXRlQ2FjaGUuZ2V0KGNvbXBNZXRhLnR5cGUucmVmZXJlbmNlKTtcbiAgICBpZiAoIWNvbXBpbGVkVGVtcGxhdGUpIHtcbiAgICAgIGFzc2VydENvbXBvbmVudChjb21wTWV0YSk7XG4gICAgICBjb21waWxlZFRlbXBsYXRlID0gbmV3IENvbXBpbGVkVGVtcGxhdGUoXG4gICAgICAgICAgZmFsc2UsIGNvbXBNZXRhLnR5cGUsIGNvbXBNZXRhLCBuZ01vZHVsZSwgbmdNb2R1bGUudHJhbnNpdGl2ZU1vZHVsZS5kaXJlY3RpdmVzKTtcbiAgICAgIHRoaXMuX2NvbXBpbGVkVGVtcGxhdGVDYWNoZS5zZXQoY29tcE1ldGEudHlwZS5yZWZlcmVuY2UsIGNvbXBpbGVkVGVtcGxhdGUpO1xuICAgIH1cbiAgICByZXR1cm4gY29tcGlsZWRUZW1wbGF0ZTtcbiAgfVxuXG4gIHByaXZhdGUgX2NvbXBpbGVUZW1wbGF0ZSh0ZW1wbGF0ZTogQ29tcGlsZWRUZW1wbGF0ZSkge1xuICAgIGlmICh0ZW1wbGF0ZS5pc0NvbXBpbGVkKSB7XG4gICAgICByZXR1cm47XG4gICAgfVxuICAgIGNvbnN0IGNvbXBNZXRhID0gdGVtcGxhdGUuY29tcE1ldGE7XG4gICAgY29uc3QgZXh0ZXJuYWxTdHlsZXNoZWV0c0J5TW9kdWxlVXJsID0gbmV3IE1hcDxzdHJpbmcsIENvbXBpbGVkU3R5bGVzaGVldD4oKTtcbiAgICBjb25zdCBvdXRwdXRDb250ZXh0ID0gY3JlYXRlT3V0cHV0Q29udGV4dCgpO1xuICAgIGNvbnN0IGNvbXBvbmVudFN0eWxlc2hlZXQgPSB0aGlzLl9zdHlsZUNvbXBpbGVyLmNvbXBpbGVDb21wb25lbnQob3V0cHV0Q29udGV4dCwgY29tcE1ldGEpO1xuICAgIGNvbXBNZXRhLnRlbXBsYXRlICEuZXh0ZXJuYWxTdHlsZXNoZWV0cy5mb3JFYWNoKChzdHlsZXNoZWV0TWV0YSkgPT4ge1xuICAgICAgY29uc3QgY29tcGlsZWRTdHlsZXNoZWV0ID1cbiAgICAgICAgICB0aGlzLl9zdHlsZUNvbXBpbGVyLmNvbXBpbGVTdHlsZXMoY3JlYXRlT3V0cHV0Q29udGV4dCgpLCBjb21wTWV0YSwgc3R5bGVzaGVldE1ldGEpO1xuICAgICAgZXh0ZXJuYWxTdHlsZXNoZWV0c0J5TW9kdWxlVXJsLnNldChzdHlsZXNoZWV0TWV0YS5tb2R1bGVVcmwhLCBjb21waWxlZFN0eWxlc2hlZXQpO1xuICAgIH0pO1xuICAgIHRoaXMuX3Jlc29sdmVTdHlsZXNDb21waWxlUmVzdWx0KGNvbXBvbmVudFN0eWxlc2hlZXQsIGV4dGVybmFsU3R5bGVzaGVldHNCeU1vZHVsZVVybCk7XG4gICAgY29uc3QgcGlwZXMgPSB0ZW1wbGF0ZS5uZ01vZHVsZS50cmFuc2l0aXZlTW9kdWxlLnBpcGVzLm1hcChcbiAgICAgICAgcGlwZSA9PiB0aGlzLl9tZXRhZGF0YVJlc29sdmVyLmdldFBpcGVTdW1tYXJ5KHBpcGUucmVmZXJlbmNlKSk7XG4gICAgY29uc3Qge3RlbXBsYXRlOiBwYXJzZWRUZW1wbGF0ZSwgcGlwZXM6IHVzZWRQaXBlc30gPVxuICAgICAgICB0aGlzLl9wYXJzZVRlbXBsYXRlKGNvbXBNZXRhLCB0ZW1wbGF0ZS5uZ01vZHVsZSwgdGVtcGxhdGUuZGlyZWN0aXZlcyk7XG4gICAgY29uc3QgY29tcGlsZVJlc3VsdCA9IHRoaXMuX3ZpZXdDb21waWxlci5jb21waWxlQ29tcG9uZW50KFxuICAgICAgICBvdXRwdXRDb250ZXh0LCBjb21wTWV0YSwgcGFyc2VkVGVtcGxhdGUsIGlyLnZhcmlhYmxlKGNvbXBvbmVudFN0eWxlc2hlZXQuc3R5bGVzVmFyKSxcbiAgICAgICAgdXNlZFBpcGVzKTtcbiAgICBjb25zdCBldmFsUmVzdWx0ID0gdGhpcy5faW50ZXJwcmV0T3JKaXQoXG4gICAgICAgIHRlbXBsYXRlSml0VXJsKHRlbXBsYXRlLm5nTW9kdWxlLnR5cGUsIHRlbXBsYXRlLmNvbXBNZXRhKSwgb3V0cHV0Q29udGV4dC5zdGF0ZW1lbnRzKTtcbiAgICBjb25zdCB2aWV3Q2xhc3MgPSBldmFsUmVzdWx0W2NvbXBpbGVSZXN1bHQudmlld0NsYXNzVmFyXTtcbiAgICBjb25zdCByZW5kZXJlclR5cGUgPSBldmFsUmVzdWx0W2NvbXBpbGVSZXN1bHQucmVuZGVyZXJUeXBlVmFyXTtcbiAgICB0ZW1wbGF0ZS5jb21waWxlZCh2aWV3Q2xhc3MsIHJlbmRlcmVyVHlwZSk7XG4gIH1cblxuICBwcml2YXRlIF9wYXJzZVRlbXBsYXRlKFxuICAgICAgY29tcE1ldGE6IENvbXBpbGVEaXJlY3RpdmVNZXRhZGF0YSwgbmdNb2R1bGU6IENvbXBpbGVOZ01vZHVsZU1ldGFkYXRhLFxuICAgICAgZGlyZWN0aXZlSWRlbnRpZmllcnM6IENvbXBpbGVJZGVudGlmaWVyTWV0YWRhdGFbXSk6XG4gICAgICB7dGVtcGxhdGU6IFRlbXBsYXRlQXN0W10sIHBpcGVzOiBDb21waWxlUGlwZVN1bW1hcnlbXX0ge1xuICAgIC8vIE5vdGU6ICEgaXMgb2sgaGVyZSBhcyBjb21wb25lbnRzIGFsd2F5cyBoYXZlIGEgdGVtcGxhdGUuXG4gICAgY29uc3QgcHJlc2VydmVXaGl0ZXNwYWNlcyA9IGNvbXBNZXRhLnRlbXBsYXRlICEucHJlc2VydmVXaGl0ZXNwYWNlcztcbiAgICBjb25zdCBkaXJlY3RpdmVzID1cbiAgICAgICAgZGlyZWN0aXZlSWRlbnRpZmllcnMubWFwKGRpciA9PiB0aGlzLl9tZXRhZGF0YVJlc29sdmVyLmdldERpcmVjdGl2ZVN1bW1hcnkoZGlyLnJlZmVyZW5jZSkpO1xuICAgIGNvbnN0IHBpcGVzID0gbmdNb2R1bGUudHJhbnNpdGl2ZU1vZHVsZS5waXBlcy5tYXAoXG4gICAgICAgIHBpcGUgPT4gdGhpcy5fbWV0YWRhdGFSZXNvbHZlci5nZXRQaXBlU3VtbWFyeShwaXBlLnJlZmVyZW5jZSkpO1xuICAgIHJldHVybiB0aGlzLl90ZW1wbGF0ZVBhcnNlci5wYXJzZShcbiAgICAgICAgY29tcE1ldGEsIGNvbXBNZXRhLnRlbXBsYXRlICEuaHRtbEFzdCEsIGRpcmVjdGl2ZXMsIHBpcGVzLCBuZ01vZHVsZS5zY2hlbWFzLFxuICAgICAgICB0ZW1wbGF0ZVNvdXJjZVVybChuZ01vZHVsZS50eXBlLCBjb21wTWV0YSwgY29tcE1ldGEudGVtcGxhdGUgISksIHByZXNlcnZlV2hpdGVzcGFjZXMpO1xuICB9XG5cbiAgcHJpdmF0ZSBfcmVzb2x2ZVN0eWxlc0NvbXBpbGVSZXN1bHQoXG4gICAgICByZXN1bHQ6IENvbXBpbGVkU3R5bGVzaGVldCwgZXh0ZXJuYWxTdHlsZXNoZWV0c0J5TW9kdWxlVXJsOiBNYXA8c3RyaW5nLCBDb21waWxlZFN0eWxlc2hlZXQ+KSB7XG4gICAgcmVzdWx0LmRlcGVuZGVuY2llcy5mb3JFYWNoKChkZXAsIGkpID0+IHtcbiAgICAgIGNvbnN0IG5lc3RlZENvbXBpbGVSZXN1bHQgPSBleHRlcm5hbFN0eWxlc2hlZXRzQnlNb2R1bGVVcmwuZ2V0KGRlcC5tb2R1bGVVcmwpITtcbiAgICAgIGNvbnN0IG5lc3RlZFN0eWxlc0FyciA9IHRoaXMuX3Jlc29sdmVBbmRFdmFsU3R5bGVzQ29tcGlsZVJlc3VsdChcbiAgICAgICAgICBuZXN0ZWRDb21waWxlUmVzdWx0LCBleHRlcm5hbFN0eWxlc2hlZXRzQnlNb2R1bGVVcmwpO1xuICAgICAgZGVwLnNldFZhbHVlKG5lc3RlZFN0eWxlc0Fycik7XG4gICAgfSk7XG4gIH1cblxuICBwcml2YXRlIF9yZXNvbHZlQW5kRXZhbFN0eWxlc0NvbXBpbGVSZXN1bHQoXG4gICAgICByZXN1bHQ6IENvbXBpbGVkU3R5bGVzaGVldCxcbiAgICAgIGV4dGVybmFsU3R5bGVzaGVldHNCeU1vZHVsZVVybDogTWFwPHN0cmluZywgQ29tcGlsZWRTdHlsZXNoZWV0Pik6IHN0cmluZ1tdIHtcbiAgICB0aGlzLl9yZXNvbHZlU3R5bGVzQ29tcGlsZVJlc3VsdChyZXN1bHQsIGV4dGVybmFsU3R5bGVzaGVldHNCeU1vZHVsZVVybCk7XG4gICAgcmV0dXJuIHRoaXMuX2ludGVycHJldE9ySml0KFxuICAgICAgICBzaGFyZWRTdHlsZXNoZWV0Sml0VXJsKHJlc3VsdC5tZXRhLCB0aGlzLl9zaGFyZWRTdHlsZXNoZWV0Q291bnQrKyksXG4gICAgICAgIHJlc3VsdC5vdXRwdXRDdHguc3RhdGVtZW50cylbcmVzdWx0LnN0eWxlc1Zhcl07XG4gIH1cblxuICBwcml2YXRlIF9pbnRlcnByZXRPckppdChzb3VyY2VVcmw6IHN0cmluZywgc3RhdGVtZW50czogaXIuU3RhdGVtZW50W10pOiBhbnkge1xuICAgIGlmICghdGhpcy5fY29tcGlsZXJDb25maWcudXNlSml0KSB7XG4gICAgICByZXR1cm4gaW50ZXJwcmV0U3RhdGVtZW50cyhzdGF0ZW1lbnRzLCB0aGlzLl9yZWZsZWN0b3IpO1xuICAgIH0gZWxzZSB7XG4gICAgICByZXR1cm4gdGhpcy5faml0RXZhbHVhdG9yLmV2YWx1YXRlU3RhdGVtZW50cyhcbiAgICAgICAgICBzb3VyY2VVcmwsIHN0YXRlbWVudHMsIHRoaXMuX3JlZmxlY3RvciwgdGhpcy5fY29tcGlsZXJDb25maWcuaml0RGV2TW9kZSk7XG4gICAgfVxuICB9XG59XG5cbmNsYXNzIENvbXBpbGVkVGVtcGxhdGUge1xuICBwcml2YXRlIF92aWV3Q2xhc3M6IEZ1bmN0aW9uID0gbnVsbCE7XG4gIGlzQ29tcGlsZWQgPSBmYWxzZTtcblxuICBjb25zdHJ1Y3RvcihcbiAgICAgIHB1YmxpYyBpc0hvc3Q6IGJvb2xlYW4sIHB1YmxpYyBjb21wVHlwZTogQ29tcGlsZUlkZW50aWZpZXJNZXRhZGF0YSxcbiAgICAgIHB1YmxpYyBjb21wTWV0YTogQ29tcGlsZURpcmVjdGl2ZU1ldGFkYXRhLCBwdWJsaWMgbmdNb2R1bGU6IENvbXBpbGVOZ01vZHVsZU1ldGFkYXRhLFxuICAgICAgcHVibGljIGRpcmVjdGl2ZXM6IENvbXBpbGVJZGVudGlmaWVyTWV0YWRhdGFbXSkge31cblxuICBjb21waWxlZCh2aWV3Q2xhc3M6IEZ1bmN0aW9uLCByZW5kZXJlclR5cGU6IGFueSkge1xuICAgIHRoaXMuX3ZpZXdDbGFzcyA9IHZpZXdDbGFzcztcbiAgICAoPFByb3h5Q2xhc3M+dGhpcy5jb21wTWV0YS5jb21wb25lbnRWaWV3VHlwZSkuc2V0RGVsZWdhdGUodmlld0NsYXNzKTtcbiAgICBmb3IgKGxldCBwcm9wIGluIHJlbmRlcmVyVHlwZSkge1xuICAgICAgKDxhbnk+dGhpcy5jb21wTWV0YS5yZW5kZXJlclR5cGUpW3Byb3BdID0gcmVuZGVyZXJUeXBlW3Byb3BdO1xuICAgIH1cbiAgICB0aGlzLmlzQ29tcGlsZWQgPSB0cnVlO1xuICB9XG59XG5cbmZ1bmN0aW9uIGFzc2VydENvbXBvbmVudChtZXRhOiBDb21waWxlRGlyZWN0aXZlTWV0YWRhdGEpIHtcbiAgaWYgKCFtZXRhLmlzQ29tcG9uZW50KSB7XG4gICAgdGhyb3cgbmV3IEVycm9yKFxuICAgICAgICBgQ291bGQgbm90IGNvbXBpbGUgJyR7aWRlbnRpZmllck5hbWUobWV0YS50eXBlKX0nIGJlY2F1c2UgaXQgaXMgbm90IGEgY29tcG9uZW50LmApO1xuICB9XG59XG5cbmZ1bmN0aW9uIGNyZWF0ZU91dHB1dENvbnRleHQoKTogT3V0cHV0Q29udGV4dCB7XG4gIGNvbnN0IGltcG9ydEV4cHIgPSAoc3ltYm9sOiBhbnkpID0+XG4gICAgICBpci5pbXBvcnRFeHByKHtuYW1lOiBpZGVudGlmaWVyTmFtZShzeW1ib2wpLCBtb2R1bGVOYW1lOiBudWxsLCBydW50aW1lOiBzeW1ib2x9KTtcbiAgcmV0dXJuIHtzdGF0ZW1lbnRzOiBbXSwgZ2VuRmlsZVBhdGg6ICcnLCBpbXBvcnRFeHByLCBjb25zdGFudFBvb2w6IG5ldyBDb25zdGFudFBvb2woKX07XG59XG4iXX0=