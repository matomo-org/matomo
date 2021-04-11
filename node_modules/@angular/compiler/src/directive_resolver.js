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
        define("@angular/compiler/src/directive_resolver", ["require", "exports", "tslib", "@angular/compiler/src/core", "@angular/compiler/src/util"], factory);
    }
})(function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.findLast = exports.DirectiveResolver = void 0;
    var tslib_1 = require("tslib");
    var core_1 = require("@angular/compiler/src/core");
    var util_1 = require("@angular/compiler/src/util");
    var QUERY_METADATA_IDENTIFIERS = [
        core_1.createViewChild,
        core_1.createViewChildren,
        core_1.createContentChild,
        core_1.createContentChildren,
    ];
    /*
     * Resolve a `Type` for {@link Directive}.
     *
     * This interface can be overridden by the application developer to create custom behavior.
     *
     * See {@link Compiler}
     */
    var DirectiveResolver = /** @class */ (function () {
        function DirectiveResolver(_reflector) {
            this._reflector = _reflector;
        }
        DirectiveResolver.prototype.isDirective = function (type) {
            var typeMetadata = this._reflector.annotations(util_1.resolveForwardRef(type));
            return typeMetadata && typeMetadata.some(isDirectiveMetadata);
        };
        DirectiveResolver.prototype.resolve = function (type, throwIfNotFound) {
            if (throwIfNotFound === void 0) { throwIfNotFound = true; }
            var typeMetadata = this._reflector.annotations(util_1.resolveForwardRef(type));
            if (typeMetadata) {
                var metadata = findLast(typeMetadata, isDirectiveMetadata);
                if (metadata) {
                    var propertyMetadata = this._reflector.propMetadata(type);
                    var guards = this._reflector.guards(type);
                    return this._mergeWithPropertyMetadata(metadata, propertyMetadata, guards, type);
                }
            }
            if (throwIfNotFound) {
                throw new Error("No Directive annotation found on " + util_1.stringify(type));
            }
            return null;
        };
        DirectiveResolver.prototype._mergeWithPropertyMetadata = function (dm, propertyMetadata, guards, directiveType) {
            var inputs = [];
            var outputs = [];
            var host = {};
            var queries = {};
            Object.keys(propertyMetadata).forEach(function (propName) {
                var input = findLast(propertyMetadata[propName], function (a) { return core_1.createInput.isTypeOf(a); });
                if (input) {
                    if (input.bindingPropertyName) {
                        inputs.push(propName + ": " + input.bindingPropertyName);
                    }
                    else {
                        inputs.push(propName);
                    }
                }
                var output = findLast(propertyMetadata[propName], function (a) { return core_1.createOutput.isTypeOf(a); });
                if (output) {
                    if (output.bindingPropertyName) {
                        outputs.push(propName + ": " + output.bindingPropertyName);
                    }
                    else {
                        outputs.push(propName);
                    }
                }
                var hostBindings = propertyMetadata[propName].filter(function (a) { return core_1.createHostBinding.isTypeOf(a); });
                hostBindings.forEach(function (hostBinding) {
                    if (hostBinding.hostPropertyName) {
                        var startWith = hostBinding.hostPropertyName[0];
                        if (startWith === '(') {
                            throw new Error("@HostBinding can not bind to events. Use @HostListener instead.");
                        }
                        else if (startWith === '[') {
                            throw new Error("@HostBinding parameter should be a property name, 'class.<name>', or 'attr.<name>'.");
                        }
                        host["[" + hostBinding.hostPropertyName + "]"] = propName;
                    }
                    else {
                        host["[" + propName + "]"] = propName;
                    }
                });
                var hostListeners = propertyMetadata[propName].filter(function (a) { return core_1.createHostListener.isTypeOf(a); });
                hostListeners.forEach(function (hostListener) {
                    var args = hostListener.args || [];
                    host["(" + hostListener.eventName + ")"] = propName + "(" + args.join(',') + ")";
                });
                var query = findLast(propertyMetadata[propName], function (a) { return QUERY_METADATA_IDENTIFIERS.some(function (i) { return i.isTypeOf(a); }); });
                if (query) {
                    queries[propName] = query;
                }
            });
            return this._merge(dm, inputs, outputs, host, queries, guards, directiveType);
        };
        DirectiveResolver.prototype._extractPublicName = function (def) {
            return util_1.splitAtColon(def, [null, def])[1].trim();
        };
        DirectiveResolver.prototype._dedupeBindings = function (bindings) {
            var names = new Set();
            var publicNames = new Set();
            var reversedResult = [];
            // go last to first to allow later entries to overwrite previous entries
            for (var i = bindings.length - 1; i >= 0; i--) {
                var binding = bindings[i];
                var name_1 = this._extractPublicName(binding);
                publicNames.add(name_1);
                if (!names.has(name_1)) {
                    names.add(name_1);
                    reversedResult.push(binding);
                }
            }
            return reversedResult.reverse();
        };
        DirectiveResolver.prototype._merge = function (directive, inputs, outputs, host, queries, guards, directiveType) {
            var mergedInputs = this._dedupeBindings(directive.inputs ? directive.inputs.concat(inputs) : inputs);
            var mergedOutputs = this._dedupeBindings(directive.outputs ? directive.outputs.concat(outputs) : outputs);
            var mergedHost = directive.host ? tslib_1.__assign(tslib_1.__assign({}, directive.host), host) : host;
            var mergedQueries = directive.queries ? tslib_1.__assign(tslib_1.__assign({}, directive.queries), queries) : queries;
            if (core_1.createComponent.isTypeOf(directive)) {
                var comp = directive;
                return core_1.createComponent({
                    selector: comp.selector,
                    inputs: mergedInputs,
                    outputs: mergedOutputs,
                    host: mergedHost,
                    exportAs: comp.exportAs,
                    moduleId: comp.moduleId,
                    queries: mergedQueries,
                    changeDetection: comp.changeDetection,
                    providers: comp.providers,
                    viewProviders: comp.viewProviders,
                    entryComponents: comp.entryComponents,
                    template: comp.template,
                    templateUrl: comp.templateUrl,
                    styles: comp.styles,
                    styleUrls: comp.styleUrls,
                    encapsulation: comp.encapsulation,
                    animations: comp.animations,
                    interpolation: comp.interpolation,
                    preserveWhitespaces: directive.preserveWhitespaces,
                });
            }
            else {
                return core_1.createDirective({
                    selector: directive.selector,
                    inputs: mergedInputs,
                    outputs: mergedOutputs,
                    host: mergedHost,
                    exportAs: directive.exportAs,
                    queries: mergedQueries,
                    providers: directive.providers,
                    guards: guards
                });
            }
        };
        return DirectiveResolver;
    }());
    exports.DirectiveResolver = DirectiveResolver;
    function isDirectiveMetadata(type) {
        return core_1.createDirective.isTypeOf(type) || core_1.createComponent.isTypeOf(type);
    }
    function findLast(arr, condition) {
        for (var i = arr.length - 1; i >= 0; i--) {
            if (condition(arr[i])) {
                return arr[i];
            }
        }
        return null;
    }
    exports.findLast = findLast;
});
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiZGlyZWN0aXZlX3Jlc29sdmVyLmpzIiwic291cmNlUm9vdCI6IiIsInNvdXJjZXMiOlsiLi4vLi4vLi4vLi4vLi4vLi4vcGFja2FnZXMvY29tcGlsZXIvc3JjL2RpcmVjdGl2ZV9yZXNvbHZlci50cyJdLCJuYW1lcyI6W10sIm1hcHBpbmdzIjoiQUFBQTs7Ozs7O0dBTUc7Ozs7Ozs7Ozs7Ozs7O0lBR0gsbURBQXNPO0lBQ3RPLG1EQUFrRTtJQUVsRSxJQUFNLDBCQUEwQixHQUFHO1FBQ2pDLHNCQUFlO1FBQ2YseUJBQWtCO1FBQ2xCLHlCQUFrQjtRQUNsQiw0QkFBcUI7S0FDdEIsQ0FBQztJQUVGOzs7Ozs7T0FNRztJQUNIO1FBQ0UsMkJBQW9CLFVBQTRCO1lBQTVCLGVBQVUsR0FBVixVQUFVLENBQWtCO1FBQUcsQ0FBQztRQUVwRCx1Q0FBVyxHQUFYLFVBQVksSUFBVTtZQUNwQixJQUFNLFlBQVksR0FBRyxJQUFJLENBQUMsVUFBVSxDQUFDLFdBQVcsQ0FBQyx3QkFBaUIsQ0FBQyxJQUFJLENBQUMsQ0FBQyxDQUFDO1lBQzFFLE9BQU8sWUFBWSxJQUFJLFlBQVksQ0FBQyxJQUFJLENBQUMsbUJBQW1CLENBQUMsQ0FBQztRQUNoRSxDQUFDO1FBUUQsbUNBQU8sR0FBUCxVQUFRLElBQVUsRUFBRSxlQUFzQjtZQUF0QixnQ0FBQSxFQUFBLHNCQUFzQjtZQUN4QyxJQUFNLFlBQVksR0FBRyxJQUFJLENBQUMsVUFBVSxDQUFDLFdBQVcsQ0FBQyx3QkFBaUIsQ0FBQyxJQUFJLENBQUMsQ0FBQyxDQUFDO1lBQzFFLElBQUksWUFBWSxFQUFFO2dCQUNoQixJQUFNLFFBQVEsR0FBRyxRQUFRLENBQUMsWUFBWSxFQUFFLG1CQUFtQixDQUFDLENBQUM7Z0JBQzdELElBQUksUUFBUSxFQUFFO29CQUNaLElBQU0sZ0JBQWdCLEdBQUcsSUFBSSxDQUFDLFVBQVUsQ0FBQyxZQUFZLENBQUMsSUFBSSxDQUFDLENBQUM7b0JBQzVELElBQU0sTUFBTSxHQUFHLElBQUksQ0FBQyxVQUFVLENBQUMsTUFBTSxDQUFDLElBQUksQ0FBQyxDQUFDO29CQUM1QyxPQUFPLElBQUksQ0FBQywwQkFBMEIsQ0FBQyxRQUFRLEVBQUUsZ0JBQWdCLEVBQUUsTUFBTSxFQUFFLElBQUksQ0FBQyxDQUFDO2lCQUNsRjthQUNGO1lBRUQsSUFBSSxlQUFlLEVBQUU7Z0JBQ25CLE1BQU0sSUFBSSxLQUFLLENBQUMsc0NBQW9DLGdCQUFTLENBQUMsSUFBSSxDQUFHLENBQUMsQ0FBQzthQUN4RTtZQUVELE9BQU8sSUFBSSxDQUFDO1FBQ2QsQ0FBQztRQUVPLHNEQUEwQixHQUFsQyxVQUNJLEVBQWEsRUFBRSxnQkFBd0MsRUFBRSxNQUE0QixFQUNyRixhQUFtQjtZQUNyQixJQUFNLE1BQU0sR0FBYSxFQUFFLENBQUM7WUFDNUIsSUFBTSxPQUFPLEdBQWEsRUFBRSxDQUFDO1lBQzdCLElBQU0sSUFBSSxHQUE0QixFQUFFLENBQUM7WUFDekMsSUFBTSxPQUFPLEdBQXlCLEVBQUUsQ0FBQztZQUN6QyxNQUFNLENBQUMsSUFBSSxDQUFDLGdCQUFnQixDQUFDLENBQUMsT0FBTyxDQUFDLFVBQUMsUUFBZ0I7Z0JBQ3JELElBQU0sS0FBSyxHQUFHLFFBQVEsQ0FBQyxnQkFBZ0IsQ0FBQyxRQUFRLENBQUMsRUFBRSxVQUFDLENBQUMsSUFBSyxPQUFBLGtCQUFXLENBQUMsUUFBUSxDQUFDLENBQUMsQ0FBQyxFQUF2QixDQUF1QixDQUFDLENBQUM7Z0JBQ25GLElBQUksS0FBSyxFQUFFO29CQUNULElBQUksS0FBSyxDQUFDLG1CQUFtQixFQUFFO3dCQUM3QixNQUFNLENBQUMsSUFBSSxDQUFJLFFBQVEsVUFBSyxLQUFLLENBQUMsbUJBQXFCLENBQUMsQ0FBQztxQkFDMUQ7eUJBQU07d0JBQ0wsTUFBTSxDQUFDLElBQUksQ0FBQyxRQUFRLENBQUMsQ0FBQztxQkFDdkI7aUJBQ0Y7Z0JBQ0QsSUFBTSxNQUFNLEdBQUcsUUFBUSxDQUFDLGdCQUFnQixDQUFDLFFBQVEsQ0FBQyxFQUFFLFVBQUMsQ0FBQyxJQUFLLE9BQUEsbUJBQVksQ0FBQyxRQUFRLENBQUMsQ0FBQyxDQUFDLEVBQXhCLENBQXdCLENBQUMsQ0FBQztnQkFDckYsSUFBSSxNQUFNLEVBQUU7b0JBQ1YsSUFBSSxNQUFNLENBQUMsbUJBQW1CLEVBQUU7d0JBQzlCLE9BQU8sQ0FBQyxJQUFJLENBQUksUUFBUSxVQUFLLE1BQU0sQ0FBQyxtQkFBcUIsQ0FBQyxDQUFDO3FCQUM1RDt5QkFBTTt3QkFDTCxPQUFPLENBQUMsSUFBSSxDQUFDLFFBQVEsQ0FBQyxDQUFDO3FCQUN4QjtpQkFDRjtnQkFDRCxJQUFNLFlBQVksR0FBRyxnQkFBZ0IsQ0FBQyxRQUFRLENBQUMsQ0FBQyxNQUFNLENBQUMsVUFBQSxDQUFDLElBQUksT0FBQSx3QkFBaUIsQ0FBQyxRQUFRLENBQUMsQ0FBQyxDQUFDLEVBQTdCLENBQTZCLENBQUMsQ0FBQztnQkFDM0YsWUFBWSxDQUFDLE9BQU8sQ0FBQyxVQUFBLFdBQVc7b0JBQzlCLElBQUksV0FBVyxDQUFDLGdCQUFnQixFQUFFO3dCQUNoQyxJQUFNLFNBQVMsR0FBRyxXQUFXLENBQUMsZ0JBQWdCLENBQUMsQ0FBQyxDQUFDLENBQUM7d0JBQ2xELElBQUksU0FBUyxLQUFLLEdBQUcsRUFBRTs0QkFDckIsTUFBTSxJQUFJLEtBQUssQ0FBQyxpRUFBaUUsQ0FBQyxDQUFDO3lCQUNwRjs2QkFBTSxJQUFJLFNBQVMsS0FBSyxHQUFHLEVBQUU7NEJBQzVCLE1BQU0sSUFBSSxLQUFLLENBQ1gscUZBQXFGLENBQUMsQ0FBQzt5QkFDNUY7d0JBQ0QsSUFBSSxDQUFDLE1BQUksV0FBVyxDQUFDLGdCQUFnQixNQUFHLENBQUMsR0FBRyxRQUFRLENBQUM7cUJBQ3REO3lCQUFNO3dCQUNMLElBQUksQ0FBQyxNQUFJLFFBQVEsTUFBRyxDQUFDLEdBQUcsUUFBUSxDQUFDO3FCQUNsQztnQkFDSCxDQUFDLENBQUMsQ0FBQztnQkFDSCxJQUFNLGFBQWEsR0FBRyxnQkFBZ0IsQ0FBQyxRQUFRLENBQUMsQ0FBQyxNQUFNLENBQUMsVUFBQSxDQUFDLElBQUksT0FBQSx5QkFBa0IsQ0FBQyxRQUFRLENBQUMsQ0FBQyxDQUFDLEVBQTlCLENBQThCLENBQUMsQ0FBQztnQkFDN0YsYUFBYSxDQUFDLE9BQU8sQ0FBQyxVQUFBLFlBQVk7b0JBQ2hDLElBQU0sSUFBSSxHQUFHLFlBQVksQ0FBQyxJQUFJLElBQUksRUFBRSxDQUFDO29CQUNyQyxJQUFJLENBQUMsTUFBSSxZQUFZLENBQUMsU0FBUyxNQUFHLENBQUMsR0FBTSxRQUFRLFNBQUksSUFBSSxDQUFDLElBQUksQ0FBQyxHQUFHLENBQUMsTUFBRyxDQUFDO2dCQUN6RSxDQUFDLENBQUMsQ0FBQztnQkFDSCxJQUFNLEtBQUssR0FBRyxRQUFRLENBQ2xCLGdCQUFnQixDQUFDLFFBQVEsQ0FBQyxFQUFFLFVBQUMsQ0FBQyxJQUFLLE9BQUEsMEJBQTBCLENBQUMsSUFBSSxDQUFDLFVBQUEsQ0FBQyxJQUFJLE9BQUEsQ0FBQyxDQUFDLFFBQVEsQ0FBQyxDQUFDLENBQUMsRUFBYixDQUFhLENBQUMsRUFBbkQsQ0FBbUQsQ0FBQyxDQUFDO2dCQUM1RixJQUFJLEtBQUssRUFBRTtvQkFDVCxPQUFPLENBQUMsUUFBUSxDQUFDLEdBQUcsS0FBSyxDQUFDO2lCQUMzQjtZQUNILENBQUMsQ0FBQyxDQUFDO1lBQ0gsT0FBTyxJQUFJLENBQUMsTUFBTSxDQUFDLEVBQUUsRUFBRSxNQUFNLEVBQUUsT0FBTyxFQUFFLElBQUksRUFBRSxPQUFPLEVBQUUsTUFBTSxFQUFFLGFBQWEsQ0FBQyxDQUFDO1FBQ2hGLENBQUM7UUFFTyw4Q0FBa0IsR0FBMUIsVUFBMkIsR0FBVztZQUNwQyxPQUFPLG1CQUFZLENBQUMsR0FBRyxFQUFFLENBQUMsSUFBSyxFQUFFLEdBQUcsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsSUFBSSxFQUFFLENBQUM7UUFDbkQsQ0FBQztRQUVPLDJDQUFlLEdBQXZCLFVBQXdCLFFBQWtCO1lBQ3hDLElBQU0sS0FBSyxHQUFHLElBQUksR0FBRyxFQUFVLENBQUM7WUFDaEMsSUFBTSxXQUFXLEdBQUcsSUFBSSxHQUFHLEVBQVUsQ0FBQztZQUN0QyxJQUFNLGNBQWMsR0FBYSxFQUFFLENBQUM7WUFDcEMsd0VBQXdFO1lBQ3hFLEtBQUssSUFBSSxDQUFDLEdBQUcsUUFBUSxDQUFDLE1BQU0sR0FBRyxDQUFDLEVBQUUsQ0FBQyxJQUFJLENBQUMsRUFBRSxDQUFDLEVBQUUsRUFBRTtnQkFDN0MsSUFBTSxPQUFPLEdBQUcsUUFBUSxDQUFDLENBQUMsQ0FBQyxDQUFDO2dCQUM1QixJQUFNLE1BQUksR0FBRyxJQUFJLENBQUMsa0JBQWtCLENBQUMsT0FBTyxDQUFDLENBQUM7Z0JBQzlDLFdBQVcsQ0FBQyxHQUFHLENBQUMsTUFBSSxDQUFDLENBQUM7Z0JBQ3RCLElBQUksQ0FBQyxLQUFLLENBQUMsR0FBRyxDQUFDLE1BQUksQ0FBQyxFQUFFO29CQUNwQixLQUFLLENBQUMsR0FBRyxDQUFDLE1BQUksQ0FBQyxDQUFDO29CQUNoQixjQUFjLENBQUMsSUFBSSxDQUFDLE9BQU8sQ0FBQyxDQUFDO2lCQUM5QjthQUNGO1lBQ0QsT0FBTyxjQUFjLENBQUMsT0FBTyxFQUFFLENBQUM7UUFDbEMsQ0FBQztRQUVPLGtDQUFNLEdBQWQsVUFDSSxTQUFvQixFQUFFLE1BQWdCLEVBQUUsT0FBaUIsRUFBRSxJQUE2QixFQUN4RixPQUE2QixFQUFFLE1BQTRCLEVBQUUsYUFBbUI7WUFDbEYsSUFBTSxZQUFZLEdBQ2QsSUFBSSxDQUFDLGVBQWUsQ0FBQyxTQUFTLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxTQUFTLENBQUMsTUFBTSxDQUFDLE1BQU0sQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUM7WUFDdEYsSUFBTSxhQUFhLEdBQ2YsSUFBSSxDQUFDLGVBQWUsQ0FBQyxTQUFTLENBQUMsT0FBTyxDQUFDLENBQUMsQ0FBQyxTQUFTLENBQUMsT0FBTyxDQUFDLE1BQU0sQ0FBQyxPQUFPLENBQUMsQ0FBQyxDQUFDLENBQUMsT0FBTyxDQUFDLENBQUM7WUFDMUYsSUFBTSxVQUFVLEdBQUcsU0FBUyxDQUFDLElBQUksQ0FBQyxDQUFDLHVDQUFLLFNBQVMsQ0FBQyxJQUFJLEdBQUssSUFBSSxFQUFFLENBQUMsQ0FBQyxJQUFJLENBQUM7WUFDeEUsSUFBTSxhQUFhLEdBQUcsU0FBUyxDQUFDLE9BQU8sQ0FBQyxDQUFDLHVDQUFLLFNBQVMsQ0FBQyxPQUFPLEdBQUssT0FBTyxFQUFFLENBQUMsQ0FBQyxPQUFPLENBQUM7WUFDdkYsSUFBSSxzQkFBZSxDQUFDLFFBQVEsQ0FBQyxTQUFTLENBQUMsRUFBRTtnQkFDdkMsSUFBTSxJQUFJLEdBQUcsU0FBc0IsQ0FBQztnQkFDcEMsT0FBTyxzQkFBZSxDQUFDO29CQUNyQixRQUFRLEVBQUUsSUFBSSxDQUFDLFFBQVE7b0JBQ3ZCLE1BQU0sRUFBRSxZQUFZO29CQUNwQixPQUFPLEVBQUUsYUFBYTtvQkFDdEIsSUFBSSxFQUFFLFVBQVU7b0JBQ2hCLFFBQVEsRUFBRSxJQUFJLENBQUMsUUFBUTtvQkFDdkIsUUFBUSxFQUFFLElBQUksQ0FBQyxRQUFRO29CQUN2QixPQUFPLEVBQUUsYUFBYTtvQkFDdEIsZUFBZSxFQUFFLElBQUksQ0FBQyxlQUFlO29CQUNyQyxTQUFTLEVBQUUsSUFBSSxDQUFDLFNBQVM7b0JBQ3pCLGFBQWEsRUFBRSxJQUFJLENBQUMsYUFBYTtvQkFDakMsZUFBZSxFQUFFLElBQUksQ0FBQyxlQUFlO29CQUNyQyxRQUFRLEVBQUUsSUFBSSxDQUFDLFFBQVE7b0JBQ3ZCLFdBQVcsRUFBRSxJQUFJLENBQUMsV0FBVztvQkFDN0IsTUFBTSxFQUFFLElBQUksQ0FBQyxNQUFNO29CQUNuQixTQUFTLEVBQUUsSUFBSSxDQUFDLFNBQVM7b0JBQ3pCLGFBQWEsRUFBRSxJQUFJLENBQUMsYUFBYTtvQkFDakMsVUFBVSxFQUFFLElBQUksQ0FBQyxVQUFVO29CQUMzQixhQUFhLEVBQUUsSUFBSSxDQUFDLGFBQWE7b0JBQ2pDLG1CQUFtQixFQUFFLFNBQVMsQ0FBQyxtQkFBbUI7aUJBQ25ELENBQUMsQ0FBQzthQUNKO2lCQUFNO2dCQUNMLE9BQU8sc0JBQWUsQ0FBQztvQkFDckIsUUFBUSxFQUFFLFNBQVMsQ0FBQyxRQUFRO29CQUM1QixNQUFNLEVBQUUsWUFBWTtvQkFDcEIsT0FBTyxFQUFFLGFBQWE7b0JBQ3RCLElBQUksRUFBRSxVQUFVO29CQUNoQixRQUFRLEVBQUUsU0FBUyxDQUFDLFFBQVE7b0JBQzVCLE9BQU8sRUFBRSxhQUFhO29CQUN0QixTQUFTLEVBQUUsU0FBUyxDQUFDLFNBQVM7b0JBQzlCLE1BQU0sUUFBQTtpQkFDUCxDQUFDLENBQUM7YUFDSjtRQUNILENBQUM7UUFDSCx3QkFBQztJQUFELENBQUMsQUF2SkQsSUF1SkM7SUF2SlksOENBQWlCO0lBeUo5QixTQUFTLG1CQUFtQixDQUFDLElBQVM7UUFDcEMsT0FBTyxzQkFBZSxDQUFDLFFBQVEsQ0FBQyxJQUFJLENBQUMsSUFBSSxzQkFBZSxDQUFDLFFBQVEsQ0FBQyxJQUFJLENBQUMsQ0FBQztJQUMxRSxDQUFDO0lBRUQsU0FBZ0IsUUFBUSxDQUFJLEdBQVEsRUFBRSxTQUFnQztRQUNwRSxLQUFLLElBQUksQ0FBQyxHQUFHLEdBQUcsQ0FBQyxNQUFNLEdBQUcsQ0FBQyxFQUFFLENBQUMsSUFBSSxDQUFDLEVBQUUsQ0FBQyxFQUFFLEVBQUU7WUFDeEMsSUFBSSxTQUFTLENBQUMsR0FBRyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUU7Z0JBQ3JCLE9BQU8sR0FBRyxDQUFDLENBQUMsQ0FBQyxDQUFDO2FBQ2Y7U0FDRjtRQUNELE9BQU8sSUFBSSxDQUFDO0lBQ2QsQ0FBQztJQVBELDRCQU9DIiwic291cmNlc0NvbnRlbnQiOlsiLyoqXG4gKiBAbGljZW5zZVxuICogQ29weXJpZ2h0IEdvb2dsZSBMTEMgQWxsIFJpZ2h0cyBSZXNlcnZlZC5cbiAqXG4gKiBVc2Ugb2YgdGhpcyBzb3VyY2UgY29kZSBpcyBnb3Zlcm5lZCBieSBhbiBNSVQtc3R5bGUgbGljZW5zZSB0aGF0IGNhbiBiZVxuICogZm91bmQgaW4gdGhlIExJQ0VOU0UgZmlsZSBhdCBodHRwczovL2FuZ3VsYXIuaW8vbGljZW5zZVxuICovXG5cbmltcG9ydCB7Q29tcGlsZVJlZmxlY3Rvcn0gZnJvbSAnLi9jb21waWxlX3JlZmxlY3Rvcic7XG5pbXBvcnQge0NvbXBvbmVudCwgY3JlYXRlQ29tcG9uZW50LCBjcmVhdGVDb250ZW50Q2hpbGQsIGNyZWF0ZUNvbnRlbnRDaGlsZHJlbiwgY3JlYXRlRGlyZWN0aXZlLCBjcmVhdGVIb3N0QmluZGluZywgY3JlYXRlSG9zdExpc3RlbmVyLCBjcmVhdGVJbnB1dCwgY3JlYXRlT3V0cHV0LCBjcmVhdGVWaWV3Q2hpbGQsIGNyZWF0ZVZpZXdDaGlsZHJlbiwgRGlyZWN0aXZlLCBUeXBlfSBmcm9tICcuL2NvcmUnO1xuaW1wb3J0IHtyZXNvbHZlRm9yd2FyZFJlZiwgc3BsaXRBdENvbG9uLCBzdHJpbmdpZnl9IGZyb20gJy4vdXRpbCc7XG5cbmNvbnN0IFFVRVJZX01FVEFEQVRBX0lERU5USUZJRVJTID0gW1xuICBjcmVhdGVWaWV3Q2hpbGQsXG4gIGNyZWF0ZVZpZXdDaGlsZHJlbixcbiAgY3JlYXRlQ29udGVudENoaWxkLFxuICBjcmVhdGVDb250ZW50Q2hpbGRyZW4sXG5dO1xuXG4vKlxuICogUmVzb2x2ZSBhIGBUeXBlYCBmb3Ige0BsaW5rIERpcmVjdGl2ZX0uXG4gKlxuICogVGhpcyBpbnRlcmZhY2UgY2FuIGJlIG92ZXJyaWRkZW4gYnkgdGhlIGFwcGxpY2F0aW9uIGRldmVsb3BlciB0byBjcmVhdGUgY3VzdG9tIGJlaGF2aW9yLlxuICpcbiAqIFNlZSB7QGxpbmsgQ29tcGlsZXJ9XG4gKi9cbmV4cG9ydCBjbGFzcyBEaXJlY3RpdmVSZXNvbHZlciB7XG4gIGNvbnN0cnVjdG9yKHByaXZhdGUgX3JlZmxlY3RvcjogQ29tcGlsZVJlZmxlY3Rvcikge31cblxuICBpc0RpcmVjdGl2ZSh0eXBlOiBUeXBlKSB7XG4gICAgY29uc3QgdHlwZU1ldGFkYXRhID0gdGhpcy5fcmVmbGVjdG9yLmFubm90YXRpb25zKHJlc29sdmVGb3J3YXJkUmVmKHR5cGUpKTtcbiAgICByZXR1cm4gdHlwZU1ldGFkYXRhICYmIHR5cGVNZXRhZGF0YS5zb21lKGlzRGlyZWN0aXZlTWV0YWRhdGEpO1xuICB9XG5cbiAgLyoqXG4gICAqIFJldHVybiB7QGxpbmsgRGlyZWN0aXZlfSBmb3IgYSBnaXZlbiBgVHlwZWAuXG4gICAqL1xuICByZXNvbHZlKHR5cGU6IFR5cGUpOiBEaXJlY3RpdmU7XG4gIHJlc29sdmUodHlwZTogVHlwZSwgdGhyb3dJZk5vdEZvdW5kOiB0cnVlKTogRGlyZWN0aXZlO1xuICByZXNvbHZlKHR5cGU6IFR5cGUsIHRocm93SWZOb3RGb3VuZDogYm9vbGVhbik6IERpcmVjdGl2ZXxudWxsO1xuICByZXNvbHZlKHR5cGU6IFR5cGUsIHRocm93SWZOb3RGb3VuZCA9IHRydWUpOiBEaXJlY3RpdmV8bnVsbCB7XG4gICAgY29uc3QgdHlwZU1ldGFkYXRhID0gdGhpcy5fcmVmbGVjdG9yLmFubm90YXRpb25zKHJlc29sdmVGb3J3YXJkUmVmKHR5cGUpKTtcbiAgICBpZiAodHlwZU1ldGFkYXRhKSB7XG4gICAgICBjb25zdCBtZXRhZGF0YSA9IGZpbmRMYXN0KHR5cGVNZXRhZGF0YSwgaXNEaXJlY3RpdmVNZXRhZGF0YSk7XG4gICAgICBpZiAobWV0YWRhdGEpIHtcbiAgICAgICAgY29uc3QgcHJvcGVydHlNZXRhZGF0YSA9IHRoaXMuX3JlZmxlY3Rvci5wcm9wTWV0YWRhdGEodHlwZSk7XG4gICAgICAgIGNvbnN0IGd1YXJkcyA9IHRoaXMuX3JlZmxlY3Rvci5ndWFyZHModHlwZSk7XG4gICAgICAgIHJldHVybiB0aGlzLl9tZXJnZVdpdGhQcm9wZXJ0eU1ldGFkYXRhKG1ldGFkYXRhLCBwcm9wZXJ0eU1ldGFkYXRhLCBndWFyZHMsIHR5cGUpO1xuICAgICAgfVxuICAgIH1cblxuICAgIGlmICh0aHJvd0lmTm90Rm91bmQpIHtcbiAgICAgIHRocm93IG5ldyBFcnJvcihgTm8gRGlyZWN0aXZlIGFubm90YXRpb24gZm91bmQgb24gJHtzdHJpbmdpZnkodHlwZSl9YCk7XG4gICAgfVxuXG4gICAgcmV0dXJuIG51bGw7XG4gIH1cblxuICBwcml2YXRlIF9tZXJnZVdpdGhQcm9wZXJ0eU1ldGFkYXRhKFxuICAgICAgZG06IERpcmVjdGl2ZSwgcHJvcGVydHlNZXRhZGF0YToge1trZXk6IHN0cmluZ106IGFueVtdfSwgZ3VhcmRzOiB7W2tleTogc3RyaW5nXTogYW55fSxcbiAgICAgIGRpcmVjdGl2ZVR5cGU6IFR5cGUpOiBEaXJlY3RpdmUge1xuICAgIGNvbnN0IGlucHV0czogc3RyaW5nW10gPSBbXTtcbiAgICBjb25zdCBvdXRwdXRzOiBzdHJpbmdbXSA9IFtdO1xuICAgIGNvbnN0IGhvc3Q6IHtba2V5OiBzdHJpbmddOiBzdHJpbmd9ID0ge307XG4gICAgY29uc3QgcXVlcmllczoge1trZXk6IHN0cmluZ106IGFueX0gPSB7fTtcbiAgICBPYmplY3Qua2V5cyhwcm9wZXJ0eU1ldGFkYXRhKS5mb3JFYWNoKChwcm9wTmFtZTogc3RyaW5nKSA9PiB7XG4gICAgICBjb25zdCBpbnB1dCA9IGZpbmRMYXN0KHByb3BlcnR5TWV0YWRhdGFbcHJvcE5hbWVdLCAoYSkgPT4gY3JlYXRlSW5wdXQuaXNUeXBlT2YoYSkpO1xuICAgICAgaWYgKGlucHV0KSB7XG4gICAgICAgIGlmIChpbnB1dC5iaW5kaW5nUHJvcGVydHlOYW1lKSB7XG4gICAgICAgICAgaW5wdXRzLnB1c2goYCR7cHJvcE5hbWV9OiAke2lucHV0LmJpbmRpbmdQcm9wZXJ0eU5hbWV9YCk7XG4gICAgICAgIH0gZWxzZSB7XG4gICAgICAgICAgaW5wdXRzLnB1c2gocHJvcE5hbWUpO1xuICAgICAgICB9XG4gICAgICB9XG4gICAgICBjb25zdCBvdXRwdXQgPSBmaW5kTGFzdChwcm9wZXJ0eU1ldGFkYXRhW3Byb3BOYW1lXSwgKGEpID0+IGNyZWF0ZU91dHB1dC5pc1R5cGVPZihhKSk7XG4gICAgICBpZiAob3V0cHV0KSB7XG4gICAgICAgIGlmIChvdXRwdXQuYmluZGluZ1Byb3BlcnR5TmFtZSkge1xuICAgICAgICAgIG91dHB1dHMucHVzaChgJHtwcm9wTmFtZX06ICR7b3V0cHV0LmJpbmRpbmdQcm9wZXJ0eU5hbWV9YCk7XG4gICAgICAgIH0gZWxzZSB7XG4gICAgICAgICAgb3V0cHV0cy5wdXNoKHByb3BOYW1lKTtcbiAgICAgICAgfVxuICAgICAgfVxuICAgICAgY29uc3QgaG9zdEJpbmRpbmdzID0gcHJvcGVydHlNZXRhZGF0YVtwcm9wTmFtZV0uZmlsdGVyKGEgPT4gY3JlYXRlSG9zdEJpbmRpbmcuaXNUeXBlT2YoYSkpO1xuICAgICAgaG9zdEJpbmRpbmdzLmZvckVhY2goaG9zdEJpbmRpbmcgPT4ge1xuICAgICAgICBpZiAoaG9zdEJpbmRpbmcuaG9zdFByb3BlcnR5TmFtZSkge1xuICAgICAgICAgIGNvbnN0IHN0YXJ0V2l0aCA9IGhvc3RCaW5kaW5nLmhvc3RQcm9wZXJ0eU5hbWVbMF07XG4gICAgICAgICAgaWYgKHN0YXJ0V2l0aCA9PT0gJygnKSB7XG4gICAgICAgICAgICB0aHJvdyBuZXcgRXJyb3IoYEBIb3N0QmluZGluZyBjYW4gbm90IGJpbmQgdG8gZXZlbnRzLiBVc2UgQEhvc3RMaXN0ZW5lciBpbnN0ZWFkLmApO1xuICAgICAgICAgIH0gZWxzZSBpZiAoc3RhcnRXaXRoID09PSAnWycpIHtcbiAgICAgICAgICAgIHRocm93IG5ldyBFcnJvcihcbiAgICAgICAgICAgICAgICBgQEhvc3RCaW5kaW5nIHBhcmFtZXRlciBzaG91bGQgYmUgYSBwcm9wZXJ0eSBuYW1lLCAnY2xhc3MuPG5hbWU+Jywgb3IgJ2F0dHIuPG5hbWU+Jy5gKTtcbiAgICAgICAgICB9XG4gICAgICAgICAgaG9zdFtgWyR7aG9zdEJpbmRpbmcuaG9zdFByb3BlcnR5TmFtZX1dYF0gPSBwcm9wTmFtZTtcbiAgICAgICAgfSBlbHNlIHtcbiAgICAgICAgICBob3N0W2BbJHtwcm9wTmFtZX1dYF0gPSBwcm9wTmFtZTtcbiAgICAgICAgfVxuICAgICAgfSk7XG4gICAgICBjb25zdCBob3N0TGlzdGVuZXJzID0gcHJvcGVydHlNZXRhZGF0YVtwcm9wTmFtZV0uZmlsdGVyKGEgPT4gY3JlYXRlSG9zdExpc3RlbmVyLmlzVHlwZU9mKGEpKTtcbiAgICAgIGhvc3RMaXN0ZW5lcnMuZm9yRWFjaChob3N0TGlzdGVuZXIgPT4ge1xuICAgICAgICBjb25zdCBhcmdzID0gaG9zdExpc3RlbmVyLmFyZ3MgfHwgW107XG4gICAgICAgIGhvc3RbYCgke2hvc3RMaXN0ZW5lci5ldmVudE5hbWV9KWBdID0gYCR7cHJvcE5hbWV9KCR7YXJncy5qb2luKCcsJyl9KWA7XG4gICAgICB9KTtcbiAgICAgIGNvbnN0IHF1ZXJ5ID0gZmluZExhc3QoXG4gICAgICAgICAgcHJvcGVydHlNZXRhZGF0YVtwcm9wTmFtZV0sIChhKSA9PiBRVUVSWV9NRVRBREFUQV9JREVOVElGSUVSUy5zb21lKGkgPT4gaS5pc1R5cGVPZihhKSkpO1xuICAgICAgaWYgKHF1ZXJ5KSB7XG4gICAgICAgIHF1ZXJpZXNbcHJvcE5hbWVdID0gcXVlcnk7XG4gICAgICB9XG4gICAgfSk7XG4gICAgcmV0dXJuIHRoaXMuX21lcmdlKGRtLCBpbnB1dHMsIG91dHB1dHMsIGhvc3QsIHF1ZXJpZXMsIGd1YXJkcywgZGlyZWN0aXZlVHlwZSk7XG4gIH1cblxuICBwcml2YXRlIF9leHRyYWN0UHVibGljTmFtZShkZWY6IHN0cmluZykge1xuICAgIHJldHVybiBzcGxpdEF0Q29sb24oZGVmLCBbbnVsbCEsIGRlZl0pWzFdLnRyaW0oKTtcbiAgfVxuXG4gIHByaXZhdGUgX2RlZHVwZUJpbmRpbmdzKGJpbmRpbmdzOiBzdHJpbmdbXSk6IHN0cmluZ1tdIHtcbiAgICBjb25zdCBuYW1lcyA9IG5ldyBTZXQ8c3RyaW5nPigpO1xuICAgIGNvbnN0IHB1YmxpY05hbWVzID0gbmV3IFNldDxzdHJpbmc+KCk7XG4gICAgY29uc3QgcmV2ZXJzZWRSZXN1bHQ6IHN0cmluZ1tdID0gW107XG4gICAgLy8gZ28gbGFzdCB0byBmaXJzdCB0byBhbGxvdyBsYXRlciBlbnRyaWVzIHRvIG92ZXJ3cml0ZSBwcmV2aW91cyBlbnRyaWVzXG4gICAgZm9yIChsZXQgaSA9IGJpbmRpbmdzLmxlbmd0aCAtIDE7IGkgPj0gMDsgaS0tKSB7XG4gICAgICBjb25zdCBiaW5kaW5nID0gYmluZGluZ3NbaV07XG4gICAgICBjb25zdCBuYW1lID0gdGhpcy5fZXh0cmFjdFB1YmxpY05hbWUoYmluZGluZyk7XG4gICAgICBwdWJsaWNOYW1lcy5hZGQobmFtZSk7XG4gICAgICBpZiAoIW5hbWVzLmhhcyhuYW1lKSkge1xuICAgICAgICBuYW1lcy5hZGQobmFtZSk7XG4gICAgICAgIHJldmVyc2VkUmVzdWx0LnB1c2goYmluZGluZyk7XG4gICAgICB9XG4gICAgfVxuICAgIHJldHVybiByZXZlcnNlZFJlc3VsdC5yZXZlcnNlKCk7XG4gIH1cblxuICBwcml2YXRlIF9tZXJnZShcbiAgICAgIGRpcmVjdGl2ZTogRGlyZWN0aXZlLCBpbnB1dHM6IHN0cmluZ1tdLCBvdXRwdXRzOiBzdHJpbmdbXSwgaG9zdDoge1trZXk6IHN0cmluZ106IHN0cmluZ30sXG4gICAgICBxdWVyaWVzOiB7W2tleTogc3RyaW5nXTogYW55fSwgZ3VhcmRzOiB7W2tleTogc3RyaW5nXTogYW55fSwgZGlyZWN0aXZlVHlwZTogVHlwZSk6IERpcmVjdGl2ZSB7XG4gICAgY29uc3QgbWVyZ2VkSW5wdXRzID1cbiAgICAgICAgdGhpcy5fZGVkdXBlQmluZGluZ3MoZGlyZWN0aXZlLmlucHV0cyA/IGRpcmVjdGl2ZS5pbnB1dHMuY29uY2F0KGlucHV0cykgOiBpbnB1dHMpO1xuICAgIGNvbnN0IG1lcmdlZE91dHB1dHMgPVxuICAgICAgICB0aGlzLl9kZWR1cGVCaW5kaW5ncyhkaXJlY3RpdmUub3V0cHV0cyA/IGRpcmVjdGl2ZS5vdXRwdXRzLmNvbmNhdChvdXRwdXRzKSA6IG91dHB1dHMpO1xuICAgIGNvbnN0IG1lcmdlZEhvc3QgPSBkaXJlY3RpdmUuaG9zdCA/IHsuLi5kaXJlY3RpdmUuaG9zdCwgLi4uaG9zdH0gOiBob3N0O1xuICAgIGNvbnN0IG1lcmdlZFF1ZXJpZXMgPSBkaXJlY3RpdmUucXVlcmllcyA/IHsuLi5kaXJlY3RpdmUucXVlcmllcywgLi4ucXVlcmllc30gOiBxdWVyaWVzO1xuICAgIGlmIChjcmVhdGVDb21wb25lbnQuaXNUeXBlT2YoZGlyZWN0aXZlKSkge1xuICAgICAgY29uc3QgY29tcCA9IGRpcmVjdGl2ZSBhcyBDb21wb25lbnQ7XG4gICAgICByZXR1cm4gY3JlYXRlQ29tcG9uZW50KHtcbiAgICAgICAgc2VsZWN0b3I6IGNvbXAuc2VsZWN0b3IsXG4gICAgICAgIGlucHV0czogbWVyZ2VkSW5wdXRzLFxuICAgICAgICBvdXRwdXRzOiBtZXJnZWRPdXRwdXRzLFxuICAgICAgICBob3N0OiBtZXJnZWRIb3N0LFxuICAgICAgICBleHBvcnRBczogY29tcC5leHBvcnRBcyxcbiAgICAgICAgbW9kdWxlSWQ6IGNvbXAubW9kdWxlSWQsXG4gICAgICAgIHF1ZXJpZXM6IG1lcmdlZFF1ZXJpZXMsXG4gICAgICAgIGNoYW5nZURldGVjdGlvbjogY29tcC5jaGFuZ2VEZXRlY3Rpb24sXG4gICAgICAgIHByb3ZpZGVyczogY29tcC5wcm92aWRlcnMsXG4gICAgICAgIHZpZXdQcm92aWRlcnM6IGNvbXAudmlld1Byb3ZpZGVycyxcbiAgICAgICAgZW50cnlDb21wb25lbnRzOiBjb21wLmVudHJ5Q29tcG9uZW50cyxcbiAgICAgICAgdGVtcGxhdGU6IGNvbXAudGVtcGxhdGUsXG4gICAgICAgIHRlbXBsYXRlVXJsOiBjb21wLnRlbXBsYXRlVXJsLFxuICAgICAgICBzdHlsZXM6IGNvbXAuc3R5bGVzLFxuICAgICAgICBzdHlsZVVybHM6IGNvbXAuc3R5bGVVcmxzLFxuICAgICAgICBlbmNhcHN1bGF0aW9uOiBjb21wLmVuY2Fwc3VsYXRpb24sXG4gICAgICAgIGFuaW1hdGlvbnM6IGNvbXAuYW5pbWF0aW9ucyxcbiAgICAgICAgaW50ZXJwb2xhdGlvbjogY29tcC5pbnRlcnBvbGF0aW9uLFxuICAgICAgICBwcmVzZXJ2ZVdoaXRlc3BhY2VzOiBkaXJlY3RpdmUucHJlc2VydmVXaGl0ZXNwYWNlcyxcbiAgICAgIH0pO1xuICAgIH0gZWxzZSB7XG4gICAgICByZXR1cm4gY3JlYXRlRGlyZWN0aXZlKHtcbiAgICAgICAgc2VsZWN0b3I6IGRpcmVjdGl2ZS5zZWxlY3RvcixcbiAgICAgICAgaW5wdXRzOiBtZXJnZWRJbnB1dHMsXG4gICAgICAgIG91dHB1dHM6IG1lcmdlZE91dHB1dHMsXG4gICAgICAgIGhvc3Q6IG1lcmdlZEhvc3QsXG4gICAgICAgIGV4cG9ydEFzOiBkaXJlY3RpdmUuZXhwb3J0QXMsXG4gICAgICAgIHF1ZXJpZXM6IG1lcmdlZFF1ZXJpZXMsXG4gICAgICAgIHByb3ZpZGVyczogZGlyZWN0aXZlLnByb3ZpZGVycyxcbiAgICAgICAgZ3VhcmRzXG4gICAgICB9KTtcbiAgICB9XG4gIH1cbn1cblxuZnVuY3Rpb24gaXNEaXJlY3RpdmVNZXRhZGF0YSh0eXBlOiBhbnkpOiB0eXBlIGlzIERpcmVjdGl2ZSB7XG4gIHJldHVybiBjcmVhdGVEaXJlY3RpdmUuaXNUeXBlT2YodHlwZSkgfHwgY3JlYXRlQ29tcG9uZW50LmlzVHlwZU9mKHR5cGUpO1xufVxuXG5leHBvcnQgZnVuY3Rpb24gZmluZExhc3Q8VD4oYXJyOiBUW10sIGNvbmRpdGlvbjogKHZhbHVlOiBUKSA9PiBib29sZWFuKTogVHxudWxsIHtcbiAgZm9yIChsZXQgaSA9IGFyci5sZW5ndGggLSAxOyBpID49IDA7IGktLSkge1xuICAgIGlmIChjb25kaXRpb24oYXJyW2ldKSkge1xuICAgICAgcmV0dXJuIGFycltpXTtcbiAgICB9XG4gIH1cbiAgcmV0dXJuIG51bGw7XG59XG4iXX0=