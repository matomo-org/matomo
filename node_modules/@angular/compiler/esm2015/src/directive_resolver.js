/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
import { createComponent, createContentChild, createContentChildren, createDirective, createHostBinding, createHostListener, createInput, createOutput, createViewChild, createViewChildren } from './core';
import { resolveForwardRef, splitAtColon, stringify } from './util';
const QUERY_METADATA_IDENTIFIERS = [
    createViewChild,
    createViewChildren,
    createContentChild,
    createContentChildren,
];
/*
 * Resolve a `Type` for {@link Directive}.
 *
 * This interface can be overridden by the application developer to create custom behavior.
 *
 * See {@link Compiler}
 */
export class DirectiveResolver {
    constructor(_reflector) {
        this._reflector = _reflector;
    }
    isDirective(type) {
        const typeMetadata = this._reflector.annotations(resolveForwardRef(type));
        return typeMetadata && typeMetadata.some(isDirectiveMetadata);
    }
    resolve(type, throwIfNotFound = true) {
        const typeMetadata = this._reflector.annotations(resolveForwardRef(type));
        if (typeMetadata) {
            const metadata = findLast(typeMetadata, isDirectiveMetadata);
            if (metadata) {
                const propertyMetadata = this._reflector.propMetadata(type);
                const guards = this._reflector.guards(type);
                return this._mergeWithPropertyMetadata(metadata, propertyMetadata, guards, type);
            }
        }
        if (throwIfNotFound) {
            throw new Error(`No Directive annotation found on ${stringify(type)}`);
        }
        return null;
    }
    _mergeWithPropertyMetadata(dm, propertyMetadata, guards, directiveType) {
        const inputs = [];
        const outputs = [];
        const host = {};
        const queries = {};
        Object.keys(propertyMetadata).forEach((propName) => {
            const input = findLast(propertyMetadata[propName], (a) => createInput.isTypeOf(a));
            if (input) {
                if (input.bindingPropertyName) {
                    inputs.push(`${propName}: ${input.bindingPropertyName}`);
                }
                else {
                    inputs.push(propName);
                }
            }
            const output = findLast(propertyMetadata[propName], (a) => createOutput.isTypeOf(a));
            if (output) {
                if (output.bindingPropertyName) {
                    outputs.push(`${propName}: ${output.bindingPropertyName}`);
                }
                else {
                    outputs.push(propName);
                }
            }
            const hostBindings = propertyMetadata[propName].filter(a => createHostBinding.isTypeOf(a));
            hostBindings.forEach(hostBinding => {
                if (hostBinding.hostPropertyName) {
                    const startWith = hostBinding.hostPropertyName[0];
                    if (startWith === '(') {
                        throw new Error(`@HostBinding can not bind to events. Use @HostListener instead.`);
                    }
                    else if (startWith === '[') {
                        throw new Error(`@HostBinding parameter should be a property name, 'class.<name>', or 'attr.<name>'.`);
                    }
                    host[`[${hostBinding.hostPropertyName}]`] = propName;
                }
                else {
                    host[`[${propName}]`] = propName;
                }
            });
            const hostListeners = propertyMetadata[propName].filter(a => createHostListener.isTypeOf(a));
            hostListeners.forEach(hostListener => {
                const args = hostListener.args || [];
                host[`(${hostListener.eventName})`] = `${propName}(${args.join(',')})`;
            });
            const query = findLast(propertyMetadata[propName], (a) => QUERY_METADATA_IDENTIFIERS.some(i => i.isTypeOf(a)));
            if (query) {
                queries[propName] = query;
            }
        });
        return this._merge(dm, inputs, outputs, host, queries, guards, directiveType);
    }
    _extractPublicName(def) {
        return splitAtColon(def, [null, def])[1].trim();
    }
    _dedupeBindings(bindings) {
        const names = new Set();
        const publicNames = new Set();
        const reversedResult = [];
        // go last to first to allow later entries to overwrite previous entries
        for (let i = bindings.length - 1; i >= 0; i--) {
            const binding = bindings[i];
            const name = this._extractPublicName(binding);
            publicNames.add(name);
            if (!names.has(name)) {
                names.add(name);
                reversedResult.push(binding);
            }
        }
        return reversedResult.reverse();
    }
    _merge(directive, inputs, outputs, host, queries, guards, directiveType) {
        const mergedInputs = this._dedupeBindings(directive.inputs ? directive.inputs.concat(inputs) : inputs);
        const mergedOutputs = this._dedupeBindings(directive.outputs ? directive.outputs.concat(outputs) : outputs);
        const mergedHost = directive.host ? Object.assign(Object.assign({}, directive.host), host) : host;
        const mergedQueries = directive.queries ? Object.assign(Object.assign({}, directive.queries), queries) : queries;
        if (createComponent.isTypeOf(directive)) {
            const comp = directive;
            return createComponent({
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
            return createDirective({
                selector: directive.selector,
                inputs: mergedInputs,
                outputs: mergedOutputs,
                host: mergedHost,
                exportAs: directive.exportAs,
                queries: mergedQueries,
                providers: directive.providers,
                guards
            });
        }
    }
}
function isDirectiveMetadata(type) {
    return createDirective.isTypeOf(type) || createComponent.isTypeOf(type);
}
export function findLast(arr, condition) {
    for (let i = arr.length - 1; i >= 0; i--) {
        if (condition(arr[i])) {
            return arr[i];
        }
    }
    return null;
}
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiZGlyZWN0aXZlX3Jlc29sdmVyLmpzIiwic291cmNlUm9vdCI6IiIsInNvdXJjZXMiOlsiLi4vLi4vLi4vLi4vLi4vLi4vcGFja2FnZXMvY29tcGlsZXIvc3JjL2RpcmVjdGl2ZV9yZXNvbHZlci50cyJdLCJuYW1lcyI6W10sIm1hcHBpbmdzIjoiQUFBQTs7Ozs7O0dBTUc7QUFHSCxPQUFPLEVBQVksZUFBZSxFQUFFLGtCQUFrQixFQUFFLHFCQUFxQixFQUFFLGVBQWUsRUFBRSxpQkFBaUIsRUFBRSxrQkFBa0IsRUFBRSxXQUFXLEVBQUUsWUFBWSxFQUFFLGVBQWUsRUFBRSxrQkFBa0IsRUFBa0IsTUFBTSxRQUFRLENBQUM7QUFDdE8sT0FBTyxFQUFDLGlCQUFpQixFQUFFLFlBQVksRUFBRSxTQUFTLEVBQUMsTUFBTSxRQUFRLENBQUM7QUFFbEUsTUFBTSwwQkFBMEIsR0FBRztJQUNqQyxlQUFlO0lBQ2Ysa0JBQWtCO0lBQ2xCLGtCQUFrQjtJQUNsQixxQkFBcUI7Q0FDdEIsQ0FBQztBQUVGOzs7Ozs7R0FNRztBQUNILE1BQU0sT0FBTyxpQkFBaUI7SUFDNUIsWUFBb0IsVUFBNEI7UUFBNUIsZUFBVSxHQUFWLFVBQVUsQ0FBa0I7SUFBRyxDQUFDO0lBRXBELFdBQVcsQ0FBQyxJQUFVO1FBQ3BCLE1BQU0sWUFBWSxHQUFHLElBQUksQ0FBQyxVQUFVLENBQUMsV0FBVyxDQUFDLGlCQUFpQixDQUFDLElBQUksQ0FBQyxDQUFDLENBQUM7UUFDMUUsT0FBTyxZQUFZLElBQUksWUFBWSxDQUFDLElBQUksQ0FBQyxtQkFBbUIsQ0FBQyxDQUFDO0lBQ2hFLENBQUM7SUFRRCxPQUFPLENBQUMsSUFBVSxFQUFFLGVBQWUsR0FBRyxJQUFJO1FBQ3hDLE1BQU0sWUFBWSxHQUFHLElBQUksQ0FBQyxVQUFVLENBQUMsV0FBVyxDQUFDLGlCQUFpQixDQUFDLElBQUksQ0FBQyxDQUFDLENBQUM7UUFDMUUsSUFBSSxZQUFZLEVBQUU7WUFDaEIsTUFBTSxRQUFRLEdBQUcsUUFBUSxDQUFD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QW1CLEVBQUUsU0FBUyxDQUFDLG1CQUFtQjthQUNuRCxDQUFDLENBQUM7U0FDSjthQUFNO1lBQ0wsT0FBTyxlQUFlLENBQUM7Z0JBQ3JCLFFBQVEsRUFBRSxTQUFTLENBQUMsUUFBUTtnQkFDNUIsTUFBTSxFQUFFLFlBQVk7Z0JBQ3BCLE9BQU8sRUFBRSxhQUFhO2dCQUN0QixJQUFJLEVBQUUsVUFBVTtnQkFDaEIsUUFBUSxFQUFFLFNBQVMsQ0FBQyxRQUFRO2dCQUM1QixPQUFPLEVBQUUsYUFBYTtnQkFDdEIsU0FBUyxFQUFFLFNBQVMsQ0FBQyxTQUFTO2dCQUM5QixNQUFNO2FBQ1AsQ0FBQyxDQUFDO1NBQ0o7SUFDSCxDQUFDO0NBQ0Y7QUFFRCxTQUFTLG1CQUFtQixDQUFDLElBQVM7SUFDcEMsT0FBTyxlQUFlLENBQUMsUUFBUSxDQUFDLElBQUksQ0FBQyxJQUFJLGVBQWUsQ0FBQyxRQUFRLENBQUMsSUFBSSxDQUFDLENBQUM7QUFDMUUsQ0FBQztBQUVELE1BQU0sVUFBVSxRQUFRLENBQUksR0FBUSxFQUFFLFNBQWdDO0lBQ3BFLEtBQUssSUFBSSxDQUFDLEdBQUcsR0FBRyxDQUFDLE1BQU0sR0FBRyxDQUFDLEVBQUUsQ0FBQyxJQUFJLENBQUMsRUFBRSxDQUFDLEVBQUUsRUFBRTtRQUN4QyxJQUFJLFNBQVMsQ0FBQyxHQUFHLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBRTtZQUNyQixPQUFPLEdBQUcsQ0FBQyxDQUFDLENBQUMsQ0FBQztTQUNmO0tBQ0Y7SUFDRCxPQUFPLElBQUksQ0FBQztBQUNkLENBQUMiLCJzb3VyY2VzQ29udGVudCI6WyIvKipcbiAqIEBsaWNlbnNlXG4gKiBDb3B5cmlnaHQgR29vZ2xlIExMQyBBbGwgUmlnaHRzIFJlc2VydmVkLlxuICpcbiAqIFVzZSBvZiB0aGlzIHNvdXJjZSBjb2RlIGlzIGdvdmVybmVkIGJ5IGFuIE1JVC1zdHlsZSBsaWNlbnNlIHRoYXQgY2FuIGJlXG4gKiBmb3VuZCBpbiB0aGUgTElDRU5TRSBmaWxlIGF0IGh0dHBzOi8vYW5ndWxhci5pby9saWNlbnNlXG4gKi9cblxuaW1wb3J0IHtDb21waWxlUmVmbGVjdG9yfSBmcm9tICcuL2NvbXBpbGVfcmVmbGVjdG9yJztcbmltcG9ydCB7Q29tcG9uZW50LCBjcmVhdGVDb21wb25lbnQsIGNyZWF0ZUNvbnRlbnRDaGlsZCwgY3JlYXRlQ29udGVudENoaWxkcmVuLCBjcmVhdGVEaXJlY3RpdmUsIGNyZWF0ZUhvc3RCaW5kaW5nLCBjcmVhdGVIb3N0TGlzdGVuZXIsIGNyZWF0ZUlucHV0LCBjcmVhdGVPdXRwdXQsIGNyZWF0ZVZpZXdDaGlsZCwgY3JlYXRlVmlld0NoaWxkcmVuLCBEaXJlY3RpdmUsIFR5cGV9IGZyb20gJy4vY29yZSc7XG5pbXBvcnQge3Jlc29sdmVGb3J3YXJkUmVmLCBzcGxpdEF0Q29sb24sIHN0cmluZ2lmeX0gZnJvbSAnLi91dGlsJztcblxuY29uc3QgUVVFUllfTUVUQURBVEFfSURFTlRJRklFUlMgPSBbXG4gIGNyZWF0ZVZpZXdDaGlsZCxcbiAgY3JlYXRlVmlld0NoaWxkcmVuLFxuICBjcmVhdGVDb250ZW50Q2hpbGQsXG4gIGNyZWF0ZUNvbnRlbnRDaGlsZHJlbixcbl07XG5cbi8qXG4gKiBSZXNvbHZlIGEgYFR5cGVgIGZvciB7QGxpbmsgRGlyZWN0aXZlfS5cbiAqXG4gKiBUaGlzIGludGVyZmFjZSBjYW4gYmUgb3ZlcnJpZGRlbiBieSB0aGUgYXBwbGljYXRpb24gZGV2ZWxvcGVyIHRvIGNyZWF0ZSBjdXN0b20gYmVoYXZpb3IuXG4gKlxuICogU2VlIHtAbGluayBDb21waWxlcn1cbiAqL1xuZXhwb3J0IGNsYXNzIERpcmVjdGl2ZVJlc29sdmVyIHtcbiAgY29uc3RydWN0b3IocHJpdmF0ZSBfcmVmbGVjdG9yOiBDb21waWxlUmVmbGVjdG9yKSB7fVxuXG4gIGlzRGlyZWN0aXZlKHR5cGU6IFR5cGUpIHtcbiAgICBjb25zdCB0eXBlTWV0YWRhdGEgPSB0aGlzLl9yZWZsZWN0b3IuYW5ub3RhdGlvbnMocmVzb2x2ZUZvcndhcmRSZWYodHlwZSkpO1xuICAgIHJldHVybiB0eXBlTWV0YWRhdGEgJiYgdHlwZU1ldGFkYXRhLnNvbWUoaXNEaXJlY3RpdmVNZXRhZGF0YSk7XG4gIH1cblxuICAvKipcbiAgICogUmV0dXJuIHtAbGluayBEaXJlY3RpdmV9IGZvciBhIGdpdmVuIGBUeXBlYC5cbiAgICovXG4gIHJlc29sdmUodHlwZTogVHlwZSk6IERpcmVjdGl2ZTtcbiAgcmVzb2x2ZSh0eXBlOiBUeXBlLCB0aHJvd0lmTm90Rm91bmQ6IHRydWUpOiBEaXJlY3RpdmU7XG4gIHJlc29sdmUodHlwZTogVHlwZSwgdGhyb3dJZk5vdEZvdW5kOiBib29sZWFuKTogRGlyZWN0aXZlfG51bGw7XG4gIHJlc29sdmUodHlwZTogVHlwZSwgdGhyb3dJZk5vdEZvdW5kID0gdHJ1ZSk6IERpcmVjdGl2ZXxudWxsIHtcbiAgICBjb25zdCB0eXBlTWV0YWRhdGEgPSB0aGlzLl9yZWZsZWN0b3IuYW5ub3RhdGlvbnMocmVzb2x2ZUZvcndhcmRSZWYodHlwZSkpO1xuICAgIGlmICh0eXBlTWV0YWRhdGEpIHtcbiAgICAgIGNvbnN0IG1ldGFkYXRhID0gZmluZExhc3QodHlwZU1ldGFkYXRhLCBpc0RpcmVjdGl2ZU1ldGFkYXRhKTtcbiAgICAgIGlmIChtZXRhZGF0YSkge1xuICAgICAgICBjb25zdCBwcm9wZXJ0eU1ldGFkYXRhID0gdGhpcy5fcmVmbGVjdG9yLnByb3BNZXRhZGF0YSh0eXBlKTtcbiAgICAgICAgY29uc3QgZ3VhcmRzID0gdGhpcy5fcmVmbGVjdG9yLmd1YXJkcyh0eXBlKTtcbiAgICAgICAgcmV0dXJuIHRoaXMuX21lcmdlV2l0aFByb3BlcnR5TWV0YWRhdGEobWV0YWRhdGEsIHByb3BlcnR5TWV0YWRhdGEsIGd1YXJkcywgdHlwZSk7XG4gICAgICB9XG4gICAgfVxuXG4gICAgaWYgKHRocm93SWZOb3RGb3VuZCkge1xuICAgICAgdGhyb3cgbmV3IEVycm9yKGBObyBEaXJlY3RpdmUgYW5ub3RhdGlvbiBmb3VuZCBvbiAke3N0cmluZ2lmeSh0eXBlKX1gKTtcbiAgICB9XG5cbiAgICByZXR1cm4gbnVsbDtcbiAgfVxuXG4gIHByaXZhdGUgX21lcmdlV2l0aFByb3BlcnR5TWV0YWRhdGEoXG4gICAgICBkbTogRGlyZWN0aXZlLCBwcm9wZXJ0eU1ldGFkYXRhOiB7W2tleTogc3RyaW5nXTogYW55W119LCBndWFyZHM6IHtba2V5OiBzdHJpbmddOiBhbnl9LFxuICAgICAgZGlyZWN0aXZlVHlwZTogVHlwZSk6IERpcmVjdGl2ZSB7XG4gICAgY29uc3QgaW5wdXRzOiBzdHJpbmdbXSA9IFtdO1xuICAgIGNvbnN0IG91dHB1dHM6IHN0cmluZ1tdID0gW107XG4gICAgY29uc3QgaG9zdDoge1trZXk6IHN0cmluZ106IHN0cmluZ30gPSB7fTtcbiAgICBjb25zdCBxdWVyaWVzOiB7W2tleTogc3RyaW5nXTogYW55fSA9IHt9O1xuICAgIE9iamVjdC5rZXlzKHByb3BlcnR5TWV0YWRhdGEpLmZvckVhY2goKHByb3BOYW1lOiBzdHJpbmcpID0+IHtcbiAgICAgIGNvbnN0IGlucHV0ID0gZmluZExhc3QocHJvcGVydHlNZXRhZGF0YVtwcm9wTmFtZV0sIChhKSA9PiBjcmVhdGVJbnB1dC5pc1R5cGVPZihhKSk7XG4gICAgICBpZiAoaW5wdXQpIHtcbiAgICAgICAgaWYgKGlucHV0LmJpbmRpbmdQcm9wZXJ0eU5hbWUpIHtcbiAgICAgICAgICBpbnB1dHMucHVzaChgJHtwcm9wTmFtZX06ICR7aW5wdXQuYmluZGluZ1Byb3BlcnR5TmFtZX1gKTtcbiAgICAgICAgfSBlbHNlIHtcbiAgICAgICAgICBpbnB1dHMucHVzaChwcm9wTmFtZSk7XG4gICAgICAgIH1cbiAgICAgIH1cbiAgICAgIGNvbnN0IG91dHB1dCA9IGZpbmRMYXN0KHByb3BlcnR5TWV0YWRhdGFbcHJvcE5hbWVdLCAoYSkgPT4gY3JlYXRlT3V0cHV0LmlzVHlwZU9mKGEpKTtcbiAgICAgIGlmIChvdXRwdXQpIHtcbiAgICAgICAgaWYgKG91dHB1dC5iaW5kaW5nUHJvcGVydHlOYW1lKSB7XG4gICAgICAgICAgb3V0cHV0cy5wdXNoKGAke3Byb3BOYW1lfTogJHtvdXRwdXQuYmluZGluZ1Byb3BlcnR5TmFtZX1gKTtcbiAgICAgICAgfSBlbHNlIHtcbiAgICAgICAgICBvdXRwdXRzLnB1c2gocHJvcE5hbWUpO1xuICAgICAgICB9XG4gICAgICB9XG4gICAgICBjb25zdCBob3N0QmluZGluZ3MgPSBwcm9wZXJ0eU1ldGFkYXRhW3Byb3BOYW1lXS5maWx0ZXIoYSA9PiBjcmVhdGVIb3N0QmluZGluZy5pc1R5cGVPZihhKSk7XG4gICAgICBob3N0QmluZGluZ3MuZm9yRWFjaChob3N0QmluZGluZyA9PiB7XG4gICAgICAgIGlmIChob3N0QmluZGluZy5ob3N0UHJvcGVydHlOYW1lKSB7XG4gICAgICAgICAgY29uc3Qgc3RhcnRXaXRoID0gaG9zdEJpbmRpbmcuaG9zdFByb3BlcnR5TmFtZVswXTtcbiAgICAgICAgICBpZiAoc3RhcnRXaXRoID09PSAnKCcpIHtcbiAgICAgICAgICAgIHRocm93IG5ldyBFcnJvcihgQEhvc3RCaW5kaW5nIGNhbiBub3QgYmluZCB0byBldmVudHMuIFVzZSBASG9zdExpc3RlbmVyIGluc3RlYWQuYCk7XG4gICAgICAgICAgfSBlbHNlIGlmIChzdGFydFdpdGggPT09ICdbJykge1xuICAgICAgICAgICAgdGhyb3cgbmV3IEVycm9yKFxuICAgICAgICAgICAgICAgIGBASG9zdEJpbmRpbmcgcGFyYW1ldGVyIHNob3VsZCBiZSBhIHByb3BlcnR5IG5hbWUsICdjbGFzcy48bmFtZT4nLCBvciAnYXR0ci48bmFtZT4nLmApO1xuICAgICAgICAgIH1cbiAgICAgICAgICBob3N0W2BbJHtob3N0QmluZGluZy5ob3N0UHJvcGVydHlOYW1lfV1gXSA9IHByb3BOYW1lO1xuICAgICAgICB9IGVsc2Uge1xuICAgICAgICAgIGhvc3RbYFske3Byb3BOYW1lfV1gXSA9IHByb3BOYW1lO1xuICAgICAgICB9XG4gICAgICB9KTtcbiAgICAgIGNvbnN0IGhvc3RMaXN0ZW5lcnMgPSBwcm9wZXJ0eU1ldGFkYXRhW3Byb3BOYW1lXS5maWx0ZXIoYSA9PiBjcmVhdGVIb3N0TGlzdGVuZXIuaXNUeXBlT2YoYSkpO1xuICAgICAgaG9zdExpc3RlbmVycy5mb3JFYWNoKGhvc3RMaXN0ZW5lciA9PiB7XG4gICAgICAgIGNvbnN0IGFyZ3MgPSBob3N0TGlzdGVuZXIuYXJncyB8fCBbXTtcbiAgICAgICAgaG9zdFtgKCR7aG9zdExpc3RlbmVyLmV2ZW50TmFtZX0pYF0gPSBgJHtwcm9wTmFtZX0oJHthcmdzLmpvaW4oJywnKX0pYDtcbiAgICAgIH0pO1xuICAgICAgY29uc3QgcXVlcnkgPSBmaW5kTGFzdChcbiAgICAgICAgICBwcm9wZXJ0eU1ldGFkYXRhW3Byb3BOYW1lXSwgKGEpID0+IFFVRVJZX01FVEFEQVRBX0lERU5USUZJRVJTLnNvbWUoaSA9PiBpLmlzVHlwZU9mKGEpKSk7XG4gICAgICBpZiAocXVlcnkpIHtcbiAgICAgICAgcXVlcmllc1twcm9wTmFtZV0gPSBxdWVyeTtcbiAgICAgIH1cbiAgICB9KTtcbiAgICByZXR1cm4gdGhpcy5fbWVyZ2UoZG0sIGlucHV0cywgb3V0cHV0cywgaG9zdCwgcXVlcmllcywgZ3VhcmRzLCBkaXJlY3RpdmVUeXBlKTtcbiAgfVxuXG4gIHByaXZhdGUgX2V4dHJhY3RQdWJsaWNOYW1lKGRlZjogc3RyaW5nKSB7XG4gICAgcmV0dXJuIHNwbGl0QXRDb2xvbihkZWYsIFtudWxsISwgZGVmXSlbMV0udHJpbSgpO1xuICB9XG5cbiAgcHJpdmF0ZSBfZGVkdXBlQmluZGluZ3MoYmluZGluZ3M6IHN0cmluZ1tdKTogc3RyaW5nW10ge1xuICAgIGNvbnN0IG5hbWVzID0gbmV3IFNldDxzdHJpbmc+KCk7XG4gICAgY29uc3QgcHVibGljTmFtZXMgPSBuZXcgU2V0PHN0cmluZz4oKTtcbiAgICBjb25zdCByZXZlcnNlZFJlc3VsdDogc3RyaW5nW10gPSBbXTtcbiAgICAvLyBnbyBsYXN0IHRvIGZpcnN0IHRvIGFsbG93IGxhdGVyIGVudHJpZXMgdG8gb3ZlcndyaXRlIHByZXZpb3VzIGVudHJpZXNcbiAgICBmb3IgKGxldCBpID0gYmluZGluZ3MubGVuZ3RoIC0gMTsgaSA+PSAwOyBpLS0pIHtcbiAgICAgIGNvbnN0IGJpbmRpbmcgPSBiaW5kaW5nc1tpXTtcbiAgICAgIGNvbnN0IG5hbWUgPSB0aGlzLl9leHRyYWN0UHVibGljTmFtZShiaW5kaW5nKTtcbiAgICAgIHB1YmxpY05hbWVzLmFkZChuYW1lKTtcbiAgICAgIGlmICghbmFtZXMuaGFzKG5hbWUpKSB7XG4gICAgICAgIG5hbWVzLmFkZChuYW1lKTtcbiAgICAgICAgcmV2ZXJzZWRSZXN1bHQucHVzaChiaW5kaW5nKTtcbiAgICAgIH1cbiAgICB9XG4gICAgcmV0dXJuIHJldmVyc2VkUmVzdWx0LnJldmVyc2UoKTtcbiAgfVxuXG4gIHByaXZhdGUgX21lcmdlKFxuICAgICAgZGlyZWN0aXZlOiBEaXJlY3RpdmUsIGlucHV0czogc3RyaW5nW10sIG91dHB1dHM6IHN0cmluZ1tdLCBob3N0OiB7W2tleTogc3RyaW5nXTogc3RyaW5nfSxcbiAgICAgIHF1ZXJpZXM6IHtba2V5OiBzdHJpbmddOiBhbnl9LCBndWFyZHM6IHtba2V5OiBzdHJpbmddOiBhbnl9LCBkaXJlY3RpdmVUeXBlOiBUeXBlKTogRGlyZWN0aXZlIHtcbiAgICBjb25zdCBtZXJnZWRJbnB1dHMgPVxuICAgICAgICB0aGlzLl9kZWR1cGVCaW5kaW5ncyhkaXJlY3RpdmUuaW5wdXRzID8gZGlyZWN0aXZlLmlucHV0cy5jb25jYXQoaW5wdXRzKSA6IGlucHV0cyk7XG4gICAgY29uc3QgbWVyZ2VkT3V0cHV0cyA9XG4gICAgICAgIHRoaXMuX2RlZHVwZUJpbmRpbmdzKGRpcmVjdGl2ZS5vdXRwdXRzID8gZGlyZWN0aXZlLm91dHB1dHMuY29uY2F0KG91dHB1dHMpIDogb3V0cHV0cyk7XG4gICAgY29uc3QgbWVyZ2VkSG9zdCA9IGRpcmVjdGl2ZS5ob3N0ID8gey4uLmRpcmVjdGl2ZS5ob3N0LCAuLi5ob3N0fSA6IGhvc3Q7XG4gICAgY29uc3QgbWVyZ2VkUXVlcmllcyA9IGRpcmVjdGl2ZS5xdWVyaWVzID8gey4uLmRpcmVjdGl2ZS5xdWVyaWVzLCAuLi5xdWVyaWVzfSA6IHF1ZXJpZXM7XG4gICAgaWYgKGNyZWF0ZUNvbXBvbmVudC5pc1R5cGVPZihkaXJlY3RpdmUpKSB7XG4gICAgICBjb25zdCBjb21wID0gZGlyZWN0aXZlIGFzIENvbXBvbmVudDtcbiAgICAgIHJldHVybiBjcmVhdGVDb21wb25lbnQoe1xuICAgICAgICBzZWxlY3RvcjogY29tcC5zZWxlY3RvcixcbiAgICAgICAgaW5wdXRzOiBtZXJnZWRJbnB1dHMsXG4gICAgICAgIG91dHB1dHM6IG1lcmdlZE91dHB1dHMsXG4gICAgICAgIGhvc3Q6IG1lcmdlZEhvc3QsXG4gICAgICAgIGV4cG9ydEFzOiBjb21wLmV4cG9ydEFzLFxuICAgICAgICBtb2R1bGVJZDogY29tcC5tb2R1bGVJZCxcbiAgICAgICAgcXVlcmllczogbWVyZ2VkUXVlcmllcyxcbiAgICAgICAgY2hhbmdlRGV0ZWN0aW9uOiBjb21wLmNoYW5nZURldGVjdGlvbixcbiAgICAgICAgcHJvdmlkZXJzOiBjb21wLnByb3ZpZGVycyxcbiAgICAgICAgdmlld1Byb3ZpZGVyczogY29tcC52aWV3UHJvdmlkZXJzLFxuICAgICAgICBlbnRyeUNvbXBvbmVudHM6IGNvbXAuZW50cnlDb21wb25lbnRzLFxuICAgICAgICB0ZW1wbGF0ZTogY29tcC50ZW1wbGF0ZSxcbiAgICAgICAgdGVtcGxhdGVVcmw6IGNvbXAudGVtcGxhdGVVcmwsXG4gICAgICAgIHN0eWxlczogY29tcC5zdHlsZXMsXG4gICAgICAgIHN0eWxlVXJsczogY29tcC5zdHlsZVVybHMsXG4gICAgICAgIGVuY2Fwc3VsYXRpb246IGNvbXAuZW5jYXBzdWxhdGlvbixcbiAgICAgICAgYW5pbWF0aW9uczogY29tcC5hbmltYXRpb25zLFxuICAgICAgICBpbnRlcnBvbGF0aW9uOiBjb21wLmludGVycG9sYXRpb24sXG4gICAgICAgIHByZXNlcnZlV2hpdGVzcGFjZXM6IGRpcmVjdGl2ZS5wcmVzZXJ2ZVdoaXRlc3BhY2VzLFxuICAgICAgfSk7XG4gICAgfSBlbHNlIHtcbiAgICAgIHJldHVybiBjcmVhdGVEaXJlY3RpdmUoe1xuICAgICAgICBzZWxlY3RvcjogZGlyZWN0aXZlLnNlbGVjdG9yLFxuICAgICAgICBpbnB1dHM6IG1lcmdlZElucHV0cyxcbiAgICAgICAgb3V0cHV0czogbWVyZ2VkT3V0cHV0cyxcbiAgICAgICAgaG9zdDogbWVyZ2VkSG9zdCxcbiAgICAgICAgZXhwb3J0QXM6IGRpcmVjdGl2ZS5leHBvcnRBcyxcbiAgICAgICAgcXVlcmllczogbWVyZ2VkUXVlcmllcyxcbiAgICAgICAgcHJvdmlkZXJzOiBkaXJlY3RpdmUucHJvdmlkZXJzLFxuICAgICAgICBndWFyZHNcbiAgICAgIH0pO1xuICAgIH1cbiAgfVxufVxuXG5mdW5jdGlvbiBpc0RpcmVjdGl2ZU1ldGFkYXRhKHR5cGU6IGFueSk6IHR5cGUgaXMgRGlyZWN0aXZlIHtcbiAgcmV0dXJuIGNyZWF0ZURpcmVjdGl2ZS5pc1R5cGVPZih0eXBlKSB8fCBjcmVhdGVDb21wb25lbnQuaXNUeXBlT2YodHlwZSk7XG59XG5cbmV4cG9ydCBmdW5jdGlvbiBmaW5kTGFzdDxUPihhcnI6IFRbXSwgY29uZGl0aW9uOiAodmFsdWU6IFQpID0+IGJvb2xlYW4pOiBUfG51bGwge1xuICBmb3IgKGxldCBpID0gYXJyLmxlbmd0aCAtIDE7IGkgPj0gMDsgaS0tKSB7XG4gICAgaWYgKGNvbmRpdGlvbihhcnJbaV0pKSB7XG4gICAgICByZXR1cm4gYXJyW2ldO1xuICAgIH1cbiAgfVxuICByZXR1cm4gbnVsbDtcbn1cbiJdfQ==