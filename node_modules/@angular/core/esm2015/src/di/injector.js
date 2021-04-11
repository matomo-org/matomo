/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
import { stringify } from '../util/stringify';
import { resolveForwardRef } from './forward_ref';
import { catchInjectorError, formatError, NG_TEMP_TOKEN_PATH, setCurrentInjector, THROW_IF_NOT_FOUND, USE_VALUE, ɵɵinject } from './injector_compatibility';
import { INJECTOR } from './injector_token';
import { getInjectableDef, ɵɵdefineInjectable } from './interface/defs';
import { InjectFlags } from './interface/injector';
import { Inject, Optional, Self, SkipSelf } from './metadata';
import { NullInjector } from './null_injector';
import { createInjector } from './r3_injector';
import { INJECTOR_SCOPE } from './scope';
export function INJECTOR_IMPL__PRE_R3__(providers, parent, name) {
    return new StaticInjector(providers, parent, name);
}
export function INJECTOR_IMPL__POST_R3__(providers, parent, name) {
    return createInjector({ name: name }, parent, providers, name);
}
export const INJECTOR_IMPL = INJECTOR_IMPL__PRE_R3__;
/**
 * Concrete injectors implement this interface. Injectors are configured
 * with [providers](guide/glossary#provider) that associate
 * dependencies of various types with [injection tokens](guide/glossary#di-token).
 *
 * @see ["DI Providers"](guide/dependency-injection-providers).
 * @see `StaticProvider`
 *
 * @usageNotes
 *
 *  The following example creates a service injector instance.
 *
 * {@example core/di/ts/provider_spec.ts region='ConstructorProvider'}
 *
 * ### Usage example
 *
 * {@example core/di/ts/injector_spec.ts region='Injector'}
 *
 * `Injector` returns itself when given `Injector` as a token:
 *
 * {@example core/di/ts/injector_spec.ts region='injectInjector'}
 *
 * @publicApi
 */
export class Injector {
    static create(options, parent) {
        if (Array.isArray(options)) {
            return INJECTOR_IMPL(options, parent, '');
        }
        else {
            return INJECTOR_IMPL(options.providers, options.parent, options.name || '');
        }
    }
}
Injector.THROW_IF_NOT_FOUND = THROW_IF_NOT_FOUND;
Injector.NULL = new NullInjector();
/** @nocollapse */
Injector.ɵprov = ɵɵdefineInjectable({
    token: Injector,
    providedIn: 'any',
    factory: () => ɵɵinject(INJECTOR),
});
/**
 * @internal
 * @nocollapse
 */
Injector.__NG_ELEMENT_ID__ = -1 /* Injector */;
const IDENT = function (value) {
    return value;
};
const ɵ0 = IDENT;
const EMPTY = [];
const CIRCULAR = IDENT;
const MULTI_PROVIDER_FN = function () {
    return Array.prototype.slice.call(arguments);
};
const ɵ1 = MULTI_PROVIDER_FN;
const NO_NEW_LINE = 'ɵ';
export class StaticInjector {
    constructor(providers, parent = Injector.NULL, source = null) {
        this.parent = parent;
        this.source = source;
        const records = this._records = new Map();
        records.set(Injector, { token: Injector, fn: IDENT, deps: EMPTY, value: this, useNew: false });
        records.set(INJECTOR, { token: INJECTOR, fn: IDENT, deps: EMPTY, value: this, useNew: false });
        this.scope = recursivelyProcessProviders(records, providers);
    }
    get(token, notFoundValue, flags = InjectFlags.Default) {
        const records = this._records;
        let record = records.get(token);
        if (record === undefined) {
            // This means we have never seen this record, see if it is tree shakable provider.
            const injectableDef = getInjectableDef(token);
            if (injectableDef) {
                const providedIn = injectableDef && injectableDef.providedIn;
                if (providedIn === 'any' || providedIn != null && providedIn === this.scope) {
                    records.set(token, record = resolveProvider({ provide: token, useFactory: injectableDef.factory, deps: EMPTY }));
                }
            }
            if (record === undefined) {
                // Set record to null to make sure that we don't go through expensive lookup above again.
                records.set(token, null);
            }
        }
        let lastInjector = setCurrentInjector(this);
        try {
            return tryResolveToken(token, record, records, this.parent, notFoundValue, flags);
        }
        catch (e) {
            return catchInjectorError(e, token, 'StaticInjectorError', this.source);
        }
        finally {
            setCurrentInjector(lastInjector);
        }
    }
    toString() {
        const tokens = [], records = this._records;
        records.forEach((v, token) => tokens.push(stringify(token)));
        return `StaticInjector[${tokens.join(', ')}]`;
    }
}
function resolveProvider(provider) {
    const deps = computeDeps(provider);
    let fn = IDENT;
    let value = EMPTY;
    let useNew = false;
    let provide = resolveForwardRef(provider.provide);
    if (USE_VALUE in provider) {
        // We need to use USE_VALUE in provider since provider.useValue could be defined as undefined.
        value = provider.useValue;
    }
    else if (provider.useFactory) {
        fn = provider.useFactory;
    }
    else if (provider.useExisting) {
        // Just use IDENT
    }
    else if (provider.useClass) {
        useNew = true;
        fn = resolveForwardRef(provider.useClass);
    }
    else if (typeof provide == 'function') {
        useNew = true;
        fn = provide;
    }
    else {
        throw staticError('StaticProvider does not have [useValue|useFactory|useExisting|useClass] or [provide] is not newable', provider);
    }
    return { deps, fn, useNew, value };
}
function multiProviderMixError(token) {
    return staticError('Cannot mix multi providers and regular providers', token);
}
function recursivelyProcessProviders(records, provider) {
    let scope = null;
    if (provider) {
        provider = resolveForwardRef(provider);
        if (Array.isArray(provider)) {
            // if we have an array recurse into the array
            for (let i = 0; i < provider.length; i++) {
                scope = recursivelyProcessProviders(records, provider[i]) || scope;
            }
        }
        else if (typeof provider === 'function') {
            // Functions were supported in ReflectiveInjector, but are not here. For safety give useful
            // error messages
            throw staticError('Function/Class not supported', provider);
        }
        else if (provider && typeof provider === 'object' && provider.provide) {
            // At this point we have what looks like a provider: {provide: ?, ....}
            let token = resolveForwardRef(provider.provide);
            const resolvedProvider = resolveProvider(provider);
            if (provider.multi === true) {
                // This is a multi provider.
                let multiProvider = records.get(token);
                if (multiProvider) {
                    if (multiProvider.fn !== MULTI_PROVIDER_FN) {
                        throw multiProviderMixError(token);
                    }
                }
                else {
                    // Create a placeholder factory which will look up the constituents of the multi provider.
                    records.set(token, multiProvider = {
                        token: provider.provide,
                        deps: [],
                        useNew: false,
                        fn: MULTI_PROVIDER_FN,
                        value: EMPTY
                    });
                }
                // Treat the provider as the token.
                token = provider;
                multiProvider.deps.push({ token, options: 6 /* Default */ });
            }
            const record = records.get(token);
            if (record && record.fn == MULTI_PROVIDER_FN) {
                throw multiProviderMixError(token);
            }
            if (token === INJECTOR_SCOPE) {
                scope = resolvedProvider.value;
            }
            records.set(token, resolvedProvider);
        }
        else {
            throw staticError('Unexpected provider', provider);
        }
    }
    return scope;
}
function tryResolveToken(token, record, records, parent, notFoundValue, flags) {
    try {
        return resolveToken(token, record, records, parent, notFoundValue, flags);
    }
    catch (e) {
        // ensure that 'e' is of type Error.
        if (!(e instanceof Error)) {
            e = new Error(e);
        }
        const path = e[NG_TEMP_TOKEN_PATH] = e[NG_TEMP_TOKEN_PATH] || [];
        path.unshift(token);
        if (record && record.value == CIRCULAR) {
            // Reset the Circular flag.
            record.value = EMPTY;
        }
        throw e;
    }
}
function resolveToken(token, record, records, parent, notFoundValue, flags) {
    let value;
    if (record && !(flags & InjectFlags.SkipSelf)) {
        // If we don't have a record, this implies that we don't own the provider hence don't know how
        // to resolve it.
        value = record.value;
        if (value == CIRCULAR) {
            throw Error(NO_NEW_LINE + 'Circular dependency');
        }
        else if (value === EMPTY) {
            record.value = CIRCULAR;
            let obj = undefined;
            let useNew = record.useNew;
            let fn = record.fn;
            let depRecords = record.deps;
            let deps = EMPTY;
            if (depRecords.length) {
                deps = [];
                for (let i = 0; i < depRecords.length; i++) {
                    const depRecord = depRecords[i];
                    const options = depRecord.options;
                    const childRecord = options & 2 /* CheckSelf */ ? records.get(depRecord.token) : undefined;
                    deps.push(tryResolveToken(
                    // Current Token to resolve
                    depRecord.token, 
                    // A record which describes how to resolve the token.
                    // If undefined, this means we don't have such a record
                    childRecord, 
                    // Other records we know about.
                    records, 
                    // If we don't know how to resolve dependency and we should not check parent for it,
                    // than pass in Null injector.
                    !childRecord && !(options & 4 /* CheckParent */) ? Injector.NULL : parent, options & 1 /* Optional */ ? null : Injector.THROW_IF_NOT_FOUND, InjectFlags.Default));
                }
            }
            record.value = value = useNew ? new fn(...deps) : fn.apply(obj, deps);
        }
    }
    else if (!(flags & InjectFlags.Self)) {
        value = parent.get(token, notFoundValue, InjectFlags.Default);
    }
    else if (!(flags & InjectFlags.Optional)) {
        value = Injector.NULL.get(token, notFoundValue);
    }
    else {
        value = Injector.NULL.get(token, typeof notFoundValue !== 'undefined' ? notFoundValue : null);
    }
    return value;
}
function computeDeps(provider) {
    let deps = EMPTY;
    const providerDeps = provider.deps;
    if (providerDeps && providerDeps.length) {
        deps = [];
        for (let i = 0; i < providerDeps.length; i++) {
            let options = 6 /* Default */;
            let token = resolveForwardRef(providerDeps[i]);
            if (Array.isArray(token)) {
                for (let j = 0, annotations = token; j < annotations.length; j++) {
                    const annotation = annotations[j];
                    if (annotation instanceof Optional || annotation == Optional) {
                        options = options | 1 /* Optional */;
                    }
                    else if (annotation instanceof SkipSelf || annotation == SkipSelf) {
                        options = options & ~2 /* CheckSelf */;
                    }
                    else if (annotation instanceof Self || annotation == Self) {
                        options = options & ~4 /* CheckParent */;
                    }
                    else if (annotation instanceof Inject) {
                        token = annotation.token;
                    }
                    else {
                        token = resolveForwardRef(annotation);
                    }
                }
            }
            deps.push({ token, options });
        }
    }
    else if (provider.useExisting) {
        const token = resolveForwardRef(provider.useExisting);
        deps = [{ token, options: 6 /* Default */ }];
    }
    else if (!providerDeps && !(USE_VALUE in provider)) {
        // useValue & useExisting are the only ones which are exempt from deps all others need it.
        throw staticError('\'deps\' required', provider);
    }
    return deps;
}
function staticError(text, obj) {
    return new Error(formatError(text, obj, 'StaticInjectorError'));
}
export { ɵ0, ɵ1 };
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiaW5qZWN0b3IuanMiLCJzb3VyY2VSb290IjoiIiwic291cmNlcyI6WyIuLi8uLi8uLi8uLi8uLi8uLi8uLi9wYWNrYWdlcy9jb3JlL3NyYy9kaS9pbmplY3Rvci50cyJdLCJuYW1lcyI6W10sIm1hcHBpbmdzIjoiQUFBQTs7Ozs7O0dBTUc7QUFHSCxPQUFPLEVBQUMsU0FBUyxFQUFDLE1BQU0sbUJBQW1CLENBQUM7QUFDNUMsT0FBTyxFQUFDLGlCQUFpQixFQUFDLE1BQU0sZUFBZSxDQUFDO0FBRWhELE9BQU8sRUFBQyxrQkFBa0IsRUFBRSxXQUFXLEVBQUUsa0JBQWtCLEVBQUUsa0JBQWtCLEVBQUUsa0JBQWtCLEVBQUUsU0FBUyxFQUFFLFFBQVEsRUFBQyxNQUFNLDBCQUEwQixDQUFDO0FBRTFKLE9BQU8sRUFBQyxRQUFRLEVBQUMsTUFBTSxrQkFBa0IsQ0FBQztBQUMxQyxPQUFPLEVBQUMsZ0JBQWdCLEVBQUUsa0JBQWtCLEVBQUMsTUFBTSxrQkFBa0IsQ0FBQztBQUN0RSxPQUFPLEVBQUMsV0FBVyxFQUFDLE1BQU0sc0JBQXNCLENBQUM7QUFFakQsT0FBTyxFQUFDLE1BQU0sRUFBRSxRQUFRLEVBQUUsSUFBSSxFQUFFLFFBQVEsRUFBQyxNQUFNLFlBQVksQ0FBQztBQUM1RCxPQUFPLEVBQUMsWUFBWSxFQUFDLE1BQU0saUJBQWlCLENBQUM7QUFDN0MsT0FBTyxFQUFDLGNBQWMsRUFBQyxNQUFNLGVBQWUsQ0FBQztBQUM3QyxPQUFPLEVBQUMsY0FBYyxFQUFDLE1BQU0sU0FBUyxDQUFDO0FBRXZDLE1BQU0sVUFBVSx1QkFBdUIsQ0FDbkMsU0FBMkIsRUFBRSxNQUEwQixFQUFFLElBQVk7SUFDdkUsT0FBTyxJQUFJLGNBQWMsQ0FBQyxTQUFTLEVBQUUsTUFBTSxFQUFFLElBQUksQ0FBQyxDQUFDO0FBQ3JELENBQUM7QUFFRCxNQUFNLFVBQVUsd0JBQXdCLENBQ3BDLFNBQTJCLEVBQUUsTUFBMEIsRUFBRSxJQUFZO0lBQ3ZFLE9BQU8sY0FBYyxDQUFDLEVBQUMs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QixFQUFFLFFBQVEsQ0FBQyxDQUFDO0tBQ2xEO0lBQ0QsT0FBTyxJQUFJLENBQUM7QUFDZCxDQUFDO0FBRUQsU0FBUyxXQUFXLENBQUMsSUFBWSxFQUFFLEdBQVE7SUFDekMsT0FBTyxJQUFJLEtBQUssQ0FBQyxXQUFXLENBQUMsSUFBSSxFQUFFLEdBQUcsRUFBRSxxQkFBcUIsQ0FBQyxDQUFDLENBQUM7QUFDbEUsQ0FBQyIsInNvdXJjZXNDb250ZW50IjpbIi8qKlxuICogQGxpY2Vuc2VcbiAqIENvcHlyaWdodCBHb29nbGUgTExDIEFsbCBSaWdodHMgUmVzZXJ2ZWQuXG4gKlxuICogVXNlIG9mIHRoaXMgc291cmNlIGNvZGUgaXMgZ292ZXJuZWQgYnkgYW4gTUlULXN0eWxlIGxpY2Vuc2UgdGhhdCBjYW4gYmVcbiAqIGZvdW5kIGluIHRoZSBMSUNFTlNFIGZpbGUgYXQgaHR0cHM6Ly9hbmd1bGFyLmlvL2xpY2Vuc2VcbiAqL1xuXG5pbXBvcnQge0Fic3RyYWN0VHlwZSwgVHlwZX0gZnJvbSAnLi4vaW50ZXJmYWNlL3R5cGUnO1xuaW1wb3J0IHtzdHJpbmdpZnl9IGZyb20gJy4uL3V0aWwvc3RyaW5naWZ5JztcbmltcG9ydCB7cmVzb2x2ZUZvcndhcmRSZWZ9IGZyb20gJy4vZm9yd2FyZF9yZWYnO1xuaW1wb3J0IHtJbmplY3Rpb25Ub2tlbn0gZnJvbSAnLi9pbmplY3Rpb25fdG9rZW4nO1xuaW1wb3J0IHtjYXRjaEluamVjdG9yRXJyb3IsIGZvcm1hdEVycm9yLCBOR19URU1QX1RPS0VOX1BBVEgsIHNldEN1cnJlbnRJbmplY3RvciwgVEhST1dfSUZfTk9UX0ZPVU5ELCBVU0VfVkFMVUUsIMm1ybVpbmplY3R9IGZyb20gJy4vaW5qZWN0b3JfY29tcGF0aWJpbGl0eSc7XG5pbXBvcnQge0luamVjdG9yTWFya2Vyc30gZnJvbSAnLi9pbmplY3Rvcl9tYXJrZXInO1xuaW1wb3J0IHtJTkpFQ1RPUn0gZnJvbSAnLi9pbmplY3Rvcl90b2tlbic7XG5pbXBvcnQge2dldEluamVjdGFibGVEZWYsIMm1ybVkZWZpbmVJbmplY3RhYmxlfSBmcm9tICcuL2ludGVyZmFjZS9kZWZzJztcbmltcG9ydCB7SW5qZWN0RmxhZ3N9IGZyb20gJy4vaW50ZXJmYWNlL2luamVjdG9yJztcbmltcG9ydCB7Q29uc3RydWN0b3JQcm92aWRlciwgRXhpc3RpbmdQcm92aWRlciwgRmFjdG9yeVByb3ZpZGVyLCBTdGF0aWNDbGFzc1Byb3ZpZGVyLCBTdGF0aWNQcm92aWRlciwgVmFsdWVQcm92aWRlcn0gZnJvbSAnLi9pbnRlcmZhY2UvcHJvdmlkZXInO1xuaW1wb3J0IHtJbmplY3QsIE9wdGlvbmFsLCBTZWxmLCBTa2lwU2VsZn0gZnJvbSAnLi9tZXRhZGF0YSc7XG5pbXBvcnQge051bGxJbmplY3Rvcn0gZnJvbSAnLi9udWxsX2luamVjdG9yJztcbmltcG9ydCB7Y3JlYXRlSW5qZWN0b3J9IGZyb20gJy4vcjNfaW5qZWN0b3InO1xuaW1wb3J0IHtJTkpFQ1RPUl9TQ09QRX0gZnJvbSAnLi9zY29wZSc7XG5cbmV4cG9ydCBmdW5jdGlvbiBJTkpFQ1RPUl9JTVBMX19QUkVfUjNfXyhcbiAgICBwcm92aWRlcnM6IFN0YXRpY1Byb3ZpZGVyW10sIHBhcmVudDogSW5qZWN0b3J8dW5kZWZpbmVkLCBuYW1lOiBzdHJpbmcpIHtcbiAgcmV0dXJuIG5ldyBTdGF0aWNJbmplY3Rvcihwcm92aWRlcnMsIHBhcmVudCwgbmFtZSk7XG59XG5cbmV4cG9ydCBmdW5jdGlvbiBJTkpFQ1RPUl9JTVBMX19QT1NUX1IzX18oXG4gICAgcHJvdmlkZXJzOiBTdGF0aWNQcm92aWRlcltdLCBwYXJlbnQ6IEluamVjdG9yfHVuZGVmaW5lZCwgbmFtZTogc3RyaW5nKSB7XG4gIHJldHVybiBjcmVhdGVJbmplY3Rvcih7bmFtZTogbmFtZX0sIHBhcmVudCwgcHJvdmlkZXJzLCBuYW1lKTtcbn1cblxuZXhwb3J0IGNvbnN0IElOSkVDVE9SX0lNUEwgPSBJTkpFQ1RPUl9JTVBMX19QUkVfUjNfXztcblxuLyoqXG4gKiBDb25jcmV0ZSBpbmplY3RvcnMgaW1wbGVtZW50IHRoaXMgaW50ZXJmYWNlLiBJbmplY3RvcnMgYXJlIGNvbmZpZ3VyZWRcbiAqIHdpdGggW3Byb3ZpZGVyc10oZ3VpZGUvZ2xvc3NhcnkjcHJvdmlkZXIpIHRoYXQgYXNzb2NpYXRlXG4gKiBkZXBlbmRlbmNpZXMgb2YgdmFyaW91cyB0eXBlcyB3aXRoIFtpbmplY3Rpb24gdG9rZW5zXShndWlkZS9nbG9zc2FyeSNkaS10b2tlbikuXG4gKlxuICogQHNlZSBbXCJESSBQcm92aWRlcnNcIl0oZ3VpZGUvZGVwZW5kZW5jeS1pbmplY3Rpb24tcHJvdmlkZXJzKS5cbiAqIEBzZWUgYFN0YXRpY1Byb3ZpZGVyYFxuICpcbiAqIEB1c2FnZU5vdGVzXG4gKlxuICogIFRoZSBmb2xsb3dpbmcgZXhhbXBsZSBjcmVhdGVzIGEgc2VydmljZSBpbmplY3RvciBpbnN0YW5jZS5cbiAqXG4gKiB7QGV4YW1wbGUgY29yZS9kaS90cy9wcm92aWRlcl9zcGVjLnRzIHJlZ2lvbj0nQ29uc3RydWN0b3JQcm92aWRlcid9XG4gKlxuICogIyMjIFVzYWdlIGV4YW1wbGVcbiAqXG4gKiB7QGV4YW1wbGUgY29yZS9kaS90cy9pbmplY3Rvcl9zcGVjLnRzIHJlZ2lvbj0nSW5qZWN0b3InfVxuICpcbiAqIGBJbmplY3RvcmAgcmV0dXJucyBpdHNlbGYgd2hlbiBnaXZlbiBgSW5qZWN0b3JgIGFzIGEgdG9rZW46XG4gKlxuICoge0BleGFtcGxlIGNvcmUvZGkvdHMvaW5qZWN0b3Jfc3BlYy50cyByZWdpb249J2luamVjdEluamVjdG9yJ31cbiAqXG4gKiBAcHVibGljQXBpXG4gKi9cbmV4cG9ydCBhYnN0cmFjdCBjbGFzcyBJbmplY3RvciB7XG4gIHN0YXRpYyBUSFJPV19JRl9OT1RfRk9VTkQgPSBUSFJPV19JRl9OT1RfRk9VTkQ7XG4gIHN0YXRpYyBOVUxMOiBJbmplY3RvciA9IG5ldyBOdWxsSW5qZWN0b3IoKTtcblxuICAvKipcbiAgICogUmV0cmlldmVzIGFuIGluc3RhbmNlIGZyb20gdGhlIGluamVjdG9yIGJhc2VkIG9uIHRoZSBwcm92aWRlZCB0b2tlbi5cbiAgICogQHJldHVybnMgVGhlIGluc3RhbmNlIGZyb20gdGhlIGluamVjdG9yIGlmIGRlZmluZWQsIG90aGVyd2lzZSB0aGUgYG5vdEZvdW5kVmFsdWVgLlxuICAgKiBAdGhyb3dzIFdoZW4gdGhlIGBub3RGb3VuZFZhbHVlYCBpcyBgdW5kZWZpbmVkYCBvciBgSW5qZWN0b3IuVEhST1dfSUZfTk9UX0ZPVU5EYC5cbiAgICovXG4gIGFic3RyYWN0IGdldDxUPihcbiAgICAgIHRva2VuOiBUeXBlPFQ+fEFic3RyYWN0VHlwZTxUPnxJbmplY3Rpb25Ub2tlbjxUPiwgbm90Rm91bmRWYWx1ZT86IFQsIGZsYWdzPzogSW5qZWN0RmxhZ3MpOiBUO1xuICAvKipcbiAgICogQGRlcHJlY2F0ZWQgZnJvbSB2NC4wLjAgdXNlIFR5cGU8VD4sIEFic3RyYWN0VHlwZTxUPiBvciBJbmplY3Rpb25Ub2tlbjxUPlxuICAgKiBAc3VwcHJlc3Mge2R1cGxpY2F0ZX1cbiAgICovXG4gIGFic3RyYWN0IGdldCh0b2tlbjogYW55LCBub3RGb3VuZFZhbHVlPzogYW55KTogYW55O1xuXG4gIC8qKlxuICAgKiBAZGVwcmVjYXRlZCBmcm9tIHY1IHVzZSB0aGUgbmV3IHNpZ25hdHVyZSBJbmplY3Rvci5jcmVhdGUob3B0aW9ucylcbiAgICovXG4gIHN0YXRpYyBjcmVhdGUocHJvdmlkZXJzOiBTdGF0aWNQcm92aWRlcltdLCBwYXJlbnQ/OiBJbmplY3Rvcik6IEluamVjdG9yO1xuXG4gIC8qKlxuICAgKiBDcmVhdGVzIGEgbmV3IGluamVjdG9yIGluc3RhbmNlIHRoYXQgcHJvdmlkZXMgb25lIG9yIG1vcmUgZGVwZW5kZW5jaWVzLFxuICAgKiBhY2NvcmRpbmcgdG8gYSBnaXZlbiB0eXBlIG9yIHR5cGVzIG9mIGBTdGF0aWNQcm92aWRlcmAuXG4gICAqXG4gICAqIEBwYXJhbSBvcHRpb25zIEFuIG9iamVjdCB3aXRoIHRoZSBmb2xsb3dpbmcgcHJvcGVydGllczpcbiAgICogKiBgcHJvdmlkZXJzYDogQW4gYXJyYXkgb2YgcHJvdmlkZXJzIG9mIHRoZSBbU3RhdGljUHJvdmlkZXIgdHlwZV0oYXBpL2NvcmUvU3RhdGljUHJvdmlkZXIpLlxuICAgKiAqIGBwYXJlbnRgOiAob3B0aW9uYWwpIEEgcGFyZW50IGluamVjdG9yLlxuICAgKiAqIGBuYW1lYDogKG9wdGlvbmFsKSBBIGRldmVsb3Blci1kZWZpbmVkIGlkZW50aWZ5aW5nIG5hbWUgZm9yIHRoZSBuZXcgaW5qZWN0b3IuXG4gICAqXG4gICAqIEByZXR1cm5zIFRoZSBuZXcgaW5qZWN0b3IgaW5zdGFuY2UuXG4gICAqXG4gICAqL1xuICBzdGF0aWMgY3JlYXRlKG9wdGlvbnM6IHtwcm92aWRlcnM6IFN0YXRpY1Byb3ZpZGVyW10sIHBhcmVudD86IEluamVjdG9yLCBuYW1lPzogc3RyaW5nfSk6IEluamVjdG9yO1xuXG5cbiAgc3RhdGljIGNyZWF0ZShcbiAgICAgIG9wdGlvbnM6IFN0YXRpY1Byb3ZpZGVyW118e3Byb3ZpZGVyczogU3RhdGljUHJvdmlkZXJbXSwgcGFyZW50PzogSW5qZWN0b3IsIG5hbWU/OiBzdHJpbmd9LFxuICAgICAgcGFyZW50PzogSW5qZWN0b3IpOiBJbmplY3RvciB7XG4gICAgaWYgKEFycmF5LmlzQXJyYXkob3B0aW9ucykpIHtcbiAgICAgIHJldHVybiBJTkpFQ1RPUl9JTVBMKG9wdGlvbnMsIHBhcmVudCwgJycpO1xuICAgIH0gZWxzZSB7XG4gICAgICByZXR1cm4gSU5KRUNUT1JfSU1QTChvcHRpb25zLnByb3ZpZGVycywgb3B0aW9ucy5wYXJlbnQsIG9wdGlvbnMubmFtZSB8fCAnJyk7XG4gICAgfVxuICB9XG5cbiAgLyoqIEBub2NvbGxhcHNlICovXG4gIHN0YXRpYyDJtXByb3YgPSDJtcm1ZGVmaW5lSW5qZWN0YWJsZSh7XG4gICAgdG9rZW46IEluamVjdG9yLFxuICAgIHByb3ZpZGVkSW46ICdhbnknIGFzIGFueSxcbiAgICBmYWN0b3J5OiAoKSA9PiDJtcm1aW5qZWN0KElOSkVDVE9SKSxcbiAgfSk7XG5cbiAgLyoqXG4gICAqIEBpbnRlcm5hbFxuICAgKiBAbm9jb2xsYXBzZVxuICAgKi9cbiAgc3RhdGljIF9fTkdfRUxFTUVOVF9JRF9fID0gSW5qZWN0b3JNYXJrZXJzLkluamVjdG9yO1xufVxuXG5cblxuY29uc3QgSURFTlQgPSBmdW5jdGlvbjxUPih2YWx1ZTogVCk6IFQge1xuICByZXR1cm4gdmFsdWU7XG59O1xuY29uc3QgRU1QVFkgPSA8YW55W10+W107XG5jb25zdCBDSVJDVUxBUiA9IElERU5UO1xuY29uc3QgTVVMVElfUFJPVklERVJfRk4gPSBmdW5jdGlvbigpOiBhbnlbXSB7XG4gIHJldHVybiBBcnJheS5wcm90b3R5cGUuc2xpY2UuY2FsbChhcmd1bWVudHMpO1xufTtcblxuY29uc3QgZW51bSBPcHRpb25GbGFncyB7XG4gIE9wdGlvbmFsID0gMSA8PCAwLFxuICBDaGVja1NlbGYgPSAxIDw8IDEsXG4gIENoZWNrUGFyZW50ID0gMSA8PCAyLFxuICBEZWZhdWx0ID0gQ2hlY2tTZWxmIHwgQ2hlY2tQYXJlbnRcbn1cbmNvbnN0IE5PX05FV19MSU5FID0gJ8m1JztcblxuZXhwb3J0IGNsYXNzIFN0YXRpY0luamVjdG9yIGltcGxlbWVudHMgSW5qZWN0b3Ige1xuICByZWFkb25seSBwYXJlbnQ6IEluamVjdG9yO1xuICByZWFkb25seSBzb3VyY2U6IHN0cmluZ3xudWxsO1xuICByZWFkb25seSBzY29wZTogc3RyaW5nfG51bGw7XG5cbiAgcHJpdmF0ZSBfcmVjb3JkczogTWFwPGFueSwgUmVjb3JkfG51bGw+O1xuXG4gIGNvbnN0cnVjdG9yKFxuICAgICAgcHJvdmlkZXJzOiBTdGF0aWNQcm92aWRlcltdLCBwYXJlbnQ6IEluamVjdG9yID0gSW5qZWN0b3IuTlVMTCwgc291cmNlOiBzdHJpbmd8bnVsbCA9IG51bGwpIHtcbiAgICB0aGlzLnBhcmVudCA9IHBhcmVudDtcbiAgICB0aGlzLnNvdXJjZSA9IHNvdXJjZTtcbiAgICBjb25zdCByZWNvcmRzID0gdGhpcy5fcmVjb3JkcyA9IG5ldyBNYXA8YW55LCBSZWNvcmQ+KCk7XG4gICAgcmVjb3Jkcy5zZXQoXG4gICAgICAgIEluamVjdG9yLCA8UmVjb3JkPnt0b2tlbjogSW5qZWN0b3IsIGZuOiBJREVOVCwgZGVwczogRU1QVFksIHZhbHVlOiB0aGlzLCB1c2VOZXc6IGZhbHNlfSk7XG4gICAgcmVjb3Jkcy5zZXQoXG4gICAgICAgIElOSkVDVE9SLCA8UmVjb3JkPnt0b2tlbjogSU5KRUNUT1IsIGZuOiBJREVOVCwgZGVwczogRU1QVFksIHZhbHVlOiB0aGlzLCB1c2VOZXc6IGZhbHNlfSk7XG4gICAgdGhpcy5zY29wZSA9IHJlY3Vyc2l2ZWx5UHJvY2Vzc1Byb3ZpZGVycyhyZWNvcmRzLCBwcm92aWRlcnMpO1xuICB9XG5cbiAgZ2V0PFQ+KHRva2VuOiBUeXBlPFQ+fEFic3RyYWN0VHlwZTxUPnxJbmplY3Rpb25Ub2tlbjxUPiwgbm90Rm91bmRWYWx1ZT86IFQsIGZsYWdzPzogSW5qZWN0RmxhZ3MpOlxuICAgICAgVDtcbiAgZ2V0KHRva2VuOiBhbnksIG5vdEZvdW5kVmFsdWU/OiBhbnkpOiBhbnk7XG4gIGdldCh0b2tlbjogYW55LCBub3RGb3VuZFZhbHVlPzogYW55LCBmbGFnczogSW5qZWN0RmxhZ3MgPSBJbmplY3RGbGFncy5EZWZhdWx0KTogYW55IHtcbiAgICBjb25zdCByZWNvcmRzID0gdGhpcy5fcmVjb3JkcztcbiAgICBsZXQgcmVjb3JkID0gcmVjb3Jkcy5nZXQodG9rZW4pO1xuICAgIGlmIChyZWNvcmQgPT09IHVuZGVmaW5lZCkge1xuICAgICAgLy8gVGhpcyBtZWFucyB3ZSBoYXZlIG5ldmVyIHNlZW4gdGhpcyByZWNvcmQsIHNlZSBpZiBpdCBpcyB0cmVlIHNoYWthYmxlIHByb3ZpZGVyLlxuICAgICAgY29uc3QgaW5qZWN0YWJsZURlZiA9IGdldEluamVjdGFibGVEZWYodG9rZW4pO1xuICAgICAgaWYgKGluamVjdGFibGVEZWYpIHtcbiAgICAgICAgY29uc3QgcHJvdmlkZWRJbiA9IGluamVjdGFibGVEZWYgJiYgaW5qZWN0YWJsZURlZi5wcm92aWRlZEluO1xuICAgICAgICBpZiAocHJvdmlkZWRJbiA9PT0gJ2FueScgfHwgcHJvdmlkZWRJbiAhPSBudWxsICYmIHByb3ZpZGVkSW4gPT09IHRoaXMuc2NvcGUpIHtcbiAgICAgICAgICByZWNvcmRzLnNldChcbiAgICAgICAgICAgICAgdG9rZW4sXG4gICAgICAgICAgICAgIHJlY29yZCA9IHJlc29sdmVQcm92aWRlcihcbiAgICAgICAgICAgICAgICAgIHtwcm92aWRlOiB0b2tlbiwgdXNlRmFjdG9yeTogaW5qZWN0YWJsZURlZi5mYWN0b3J5LCBkZXBzOiBFTVBUWX0pKTtcbiAgICAgICAgfVxuICAgICAgfVxuICAgICAgaWYgKHJlY29yZCA9PT0gdW5kZWZpbmVkKSB7XG4gICAgICAgIC8vIFNldCByZWNvcmQgdG8gbnVsbCB0byBtYWtlIHN1cmUgdGhhdCB3ZSBkb24ndCBnbyB0aHJvdWdoIGV4cGVuc2l2ZSBsb29rdXAgYWJvdmUgYWdhaW4uXG4gICAgICAgIHJlY29yZHMuc2V0KHRva2VuLCBudWxsKTtcbiAgICAgIH1cbiAgICB9XG4gICAgbGV0IGxhc3RJbmplY3RvciA9IHNldEN1cnJlbnRJbmplY3Rvcih0aGlzKTtcbiAgICB0cnkge1xuICAgICAgcmV0dXJuIHRyeVJlc29sdmVUb2tlbih0b2tlbiwgcmVjb3JkLCByZWNvcmRzLCB0aGlzLnBhcmVudCwgbm90Rm91bmRWYWx1ZSwgZmxhZ3MpO1xuICAgIH0gY2F0Y2ggKGUpIHtcbiAgICAgIHJldHVybiBjYXRjaEluamVjdG9yRXJyb3IoZSwgdG9rZW4sICdTdGF0aWNJbmplY3RvckVycm9yJywgdGhpcy5zb3VyY2UpO1xuICAgIH0gZmluYWxseSB7XG4gICAgICBzZXRDdXJyZW50SW5qZWN0b3IobGFzdEluamVjdG9yKTtcbiAgICB9XG4gIH1cblxuICB0b1N0cmluZygpIHtcbiAgICBjb25zdCB0b2tlbnMgPSA8c3RyaW5nW10+W10sIHJlY29yZHMgPSB0aGlzLl9yZWNvcmRzO1xuICAgIHJlY29yZHMuZm9yRWFjaCgodiwgdG9rZW4pID0+IHRva2Vucy5wdXNoKHN0cmluZ2lmeSh0b2tlbikpKTtcbiAgICByZXR1cm4gYFN0YXRpY0luamVjdG9yWyR7dG9rZW5zLmpvaW4oJywgJyl9XWA7XG4gIH1cbn1cblxudHlwZSBTdXBwb3J0ZWRQcm92aWRlciA9XG4gICAgVmFsdWVQcm92aWRlcnxFeGlzdGluZ1Byb3ZpZGVyfFN0YXRpY0NsYXNzUHJvdmlkZXJ8Q29uc3RydWN0b3JQcm92aWRlcnxGYWN0b3J5UHJvdmlkZXI7XG5cbmludGVyZmFjZSBSZWNvcmQge1xuICBmbjogRnVuY3Rpb247XG4gIHVzZU5ldzogYm9vbGVhbjtcbiAgZGVwczogRGVwZW5kZW5jeVJlY29yZFtdO1xuICB2YWx1ZTogYW55O1xufVxuXG5pbnRlcmZhY2UgRGVwZW5kZW5jeVJlY29yZCB7XG4gIHRva2VuOiBhbnk7XG4gIG9wdGlvbnM6IG51bWJlcjtcbn1cblxuZnVuY3Rpb24gcmVzb2x2ZVByb3ZpZGVyKHByb3ZpZGVyOiBTdXBwb3J0ZWRQcm92aWRlcik6IFJlY29yZCB7XG4gIGNvbnN0IGRlcHMgPSBjb21wdXRlRGVwcyhwcm92aWRlcik7XG4gIGxldCBmbjogRnVuY3Rpb24gPSBJREVOVDtcbiAgbGV0IHZhbHVlOiBhbnkgPSBFTVBUWTtcbiAgbGV0IHVzZU5ldzogYm9vbGVhbiA9IGZhbHNlO1xuICBsZXQgcHJvdmlkZSA9IHJlc29sdmVGb3J3YXJkUmVmKHByb3ZpZGVyLnByb3ZpZGUpO1xuICBpZiAoVVNFX1ZBTFVFIGluIHByb3ZpZGVyKSB7XG4gICAgLy8gV2UgbmVlZCB0byB1c2UgVVNFX1ZBTFVFIGluIHByb3ZpZGVyIHNpbmNlIHByb3ZpZGVyLnVzZVZhbHVlIGNvdWxkIGJlIGRlZmluZWQgYXMgdW5kZWZpbmVkLlxuICAgIHZhbHVlID0gKHByb3ZpZGVyIGFzIFZhbHVlUHJvdmlkZXIpLnVzZVZhbHVlO1xuICB9IGVsc2UgaWYgKChwcm92aWRlciBhcyBGYWN0b3J5UHJvdmlkZXIpLnVzZUZhY3RvcnkpIHtcbiAgICBmbiA9IChwcm92aWRlciBhcyBGYWN0b3J5UHJvdmlkZXIpLnVzZUZhY3Rvcnk7XG4gIH0gZWxzZSBpZiAoKHByb3ZpZGVyIGFzIEV4aXN0aW5nUHJvdmlkZXIpLnVzZUV4aXN0aW5nKSB7XG4gICAgLy8gSnVzdCB1c2UgSURFTlRcbiAgfSBlbHNlIGlmICgocHJvdmlkZXIgYXMgU3RhdGljQ2xhc3NQcm92aWRlcikudXNlQ2xhc3MpIHtcbiAgICB1c2VOZXcgPSB0cnVlO1xuICAgIGZuID0gcmVzb2x2ZUZvcndhcmRSZWYoKHByb3ZpZGVyIGFzIFN0YXRpY0NsYXNzUHJvdmlkZXIpLnVzZUNsYXNzKTtcbiAgfSBlbHNlIGlmICh0eXBlb2YgcHJvdmlkZSA9PSAnZnVuY3Rpb24nKSB7XG4gICAgdXNlTmV3ID0gdHJ1ZTtcbiAgICBmbiA9IHByb3ZpZGU7XG4gIH0gZWxzZSB7XG4gICAgdGhyb3cgc3RhdGljRXJyb3IoXG4gICAgICAgICdTdGF0aWNQcm92aWRlciBkb2VzIG5vdCBoYXZlIFt1c2VWYWx1ZXx1c2VGYWN0b3J5fHVzZUV4aXN0aW5nfHVzZUNsYXNzXSBvciBbcHJvdmlkZV0gaXMgbm90IG5ld2FibGUnLFxuICAgICAgICBwcm92aWRlcik7XG4gIH1cbiAgcmV0dXJuIHtkZXBzLCBmbiwgdXNlTmV3LCB2YWx1ZX07XG59XG5cbmZ1bmN0aW9uIG11bHRpUHJvdmlkZXJNaXhFcnJvcih0b2tlbjogYW55KSB7XG4gIHJldHVybiBzdGF0aWNFcnJvcignQ2Fubm90IG1peCBtdWx0aSBwcm92aWRlcnMgYW5kIHJlZ3VsYXIgcHJvdmlkZXJzJywgdG9rZW4pO1xufVxuXG5mdW5jdGlvbiByZWN1cnNpdmVseVByb2Nlc3NQcm92aWRlcnMocmVjb3JkczogTWFwPGFueSwgUmVjb3JkPiwgcHJvdmlkZXI6IFN0YXRpY1Byb3ZpZGVyKTogc3RyaW5nfFxuICAgIG51bGwge1xuICBsZXQgc2NvcGU6IHN0cmluZ3xudWxsID0gbnVsbDtcbiAgaWYgKHByb3ZpZGVyKSB7XG4gICAgcHJvdmlkZXIgPSByZXNvbHZlRm9yd2FyZFJlZihwcm92aWRlcik7XG4gICAgaWYgKEFycmF5LmlzQXJyYXkocHJvdmlkZXIpKSB7XG4gICAgICAvLyBpZiB3ZSBoYXZlIGFuIGFycmF5IHJlY3Vyc2UgaW50byB0aGUgYXJyYXlcbiAgICAgIGZvciAobGV0IGkgPSAwOyBpIDwgcHJvdmlkZXIubGVuZ3RoOyBpKyspIHtcbiAgICAgICAgc2NvcGUgPSByZWN1cnNpdmVseVByb2Nlc3NQcm92aWRlcnMocmVjb3JkcywgcHJvdmlkZXJbaV0pIHx8IHNjb3BlO1xuICAgICAgfVxuICAgIH0gZWxzZSBpZiAodHlwZW9mIHByb3ZpZGVyID09PSAnZnVuY3Rpb24nKSB7XG4gICAgICAvLyBGdW5jdGlvbnMgd2VyZSBzdXBwb3J0ZWQgaW4gUmVmbGVjdGl2ZUluamVjdG9yLCBidXQgYXJlIG5vdCBoZXJlLiBGb3Igc2FmZXR5IGdpdmUgdXNlZnVsXG4gICAgICAvLyBlcnJvciBtZXNzYWdlc1xuICAgICAgdGhyb3cgc3RhdGljRXJyb3IoJ0Z1bmN0aW9uL0NsYXNzIG5vdCBzdXBwb3J0ZWQnLCBwcm92aWRlcik7XG4gICAgfSBlbHNlIGlmIChwcm92aWRlciAmJiB0eXBlb2YgcHJvdmlkZXIgPT09ICdvYmplY3QnICYmIHByb3ZpZGVyLnByb3ZpZGUpIHtcbiAgICAgIC8vIEF0IHRoaXMgcG9pbnQgd2UgaGF2ZSB3aGF0IGxvb2tzIGxpa2UgYSBwcm92aWRlcjoge3Byb3ZpZGU6ID8sIC4uLi59XG4gICAgICBsZXQgdG9rZW4gPSByZXNvbHZlRm9yd2FyZFJlZihwcm92aWRlci5wcm92aWRlKTtcbiAgICAgIGNvbnN0IHJlc29sdmVkUHJvdmlkZXIgPSByZXNvbHZlUHJvdmlkZXIocHJvdmlkZXIpO1xuICAgICAgaWYgKHByb3ZpZGVyLm11bHRpID09PSB0cnVlKSB7XG4gICAgICAgIC8vIFRoaXMgaXMgYSBtdWx0aSBwcm92aWRlci5cbiAgICAgICAgbGV0IG11bHRpUHJvdmlkZXI6IFJlY29yZHx1bmRlZmluZWQgPSByZWNvcmRzLmdldCh0b2tlbik7XG4gICAgICAgIGlmIChtdWx0aVByb3ZpZGVyKSB7XG4gICAgICAgICAgaWYgKG11bHRpUHJvdmlkZXIuZm4gIT09IE1VTFRJX1BST1ZJREVSX0ZOKSB7XG4gICAgICAgICAgICB0aHJvdyBtdWx0aVByb3ZpZGVyTWl4RXJyb3IodG9rZW4pO1xuICAgICAgICAgIH1cbiAgICAgICAgfSBlbHNlIHtcbiAgICAgICAgICAvLyBDcmVhdGUgYSBwbGFjZWhvbGRlciBmYWN0b3J5IHdoaWNoIHdpbGwgbG9vayB1cCB0aGUgY29uc3RpdHVlbnRzIG9mIHRoZSBtdWx0aSBwcm92aWRlci5cbiAgICAgICAgICByZWNvcmRzLnNldCh0b2tlbiwgbXVsdGlQcm92aWRlciA9IDxSZWNvcmQ+e1xuICAgICAgICAgICAgdG9rZW46IHByb3ZpZGVyLnByb3ZpZGUsXG4gICAgICAgICAgICBkZXBzOiBbXSxcbiAgICAgICAgICAgIHVzZU5ldzogZmFsc2UsXG4gICAgICAgICAgICBmbjogTVVMVElfUFJPVklERVJfRk4sXG4gICAgICAgICAgICB2YWx1ZTogRU1QVFlcbiAgICAgICAgICB9KTtcbiAgICAgICAgfVxuICAgICAgICAvLyBUcmVhdCB0aGUgcHJvdmlkZXIgYXMgdGhlIHRva2VuLlxuICAgICAgICB0b2tlbiA9IHByb3ZpZGVyO1xuICAgICAgICBtdWx0aVByb3ZpZGVyLmRlcHMucHVzaCh7dG9rZW4sIG9wdGlvbnM6IE9wdGlvbkZsYWdzLkRlZmF1bHR9KTtcbiAgICAgIH1cbiAgICAgIGNvbnN0IHJlY29yZCA9IHJlY29yZHMuZ2V0KHRva2VuKTtcbiAgICAgIGlmIChyZWNvcmQgJiYgcmVjb3JkLmZuID09IE1VTFRJX1BST1ZJREVSX0ZOKSB7XG4gICAgICAgIHRocm93IG11bHRpUHJvdmlkZXJNaXhFcnJvcih0b2tlbik7XG4gICAgICB9XG4gICAgICBpZiAodG9rZW4gPT09IElOSkVDVE9SX1NDT1BFKSB7XG4gICAgICAgIHNjb3BlID0gcmVzb2x2ZWRQcm92aWRlci52YWx1ZTtcbiAgICAgIH1cbiAgICAgIHJlY29yZHMuc2V0KHRva2VuLCByZXNvbHZlZFByb3ZpZGVyKTtcbiAgICB9IGVsc2Uge1xuICAgICAgdGhyb3cgc3RhdGljRXJyb3IoJ1VuZXhwZWN0ZWQgcHJvdmlkZXInLCBwcm92aWRlcik7XG4gICAgfVxuICB9XG4gIHJldHVybiBzY29wZTtcbn1cblxuZnVuY3Rpb24gdHJ5UmVzb2x2ZVRva2VuKFxuICAgIHRva2VuOiBhbnksIHJlY29yZDogUmVjb3JkfHVuZGVmaW5lZHxudWxsLCByZWNvcmRzOiBNYXA8YW55LCBSZWNvcmR8bnVsbD4sIHBhcmVudDogSW5qZWN0b3IsXG4gICAgbm90Rm91bmRWYWx1ZTogYW55LCBmbGFnczogSW5qZWN0RmxhZ3MpOiBhbnkge1xuICB0cnkge1xuICAgIHJldHVybiByZXNvbHZlVG9rZW4odG9rZW4sIHJlY29yZCwgcmVjb3JkcywgcGFyZW50LCBub3RGb3VuZFZhbHVlLCBmbGFncyk7XG4gIH0gY2F0Y2ggKGUpIHtcbiAgICAvLyBlbnN1cmUgdGhhdCAnZScgaXMgb2YgdHlwZSBFcnJvci5cbiAgICBpZiAoIShlIGluc3RhbmNlb2YgRXJyb3IpKSB7XG4gICAgICBlID0gbmV3IEVycm9yKGUpO1xuICAgIH1cbiAgICBjb25zdCBwYXRoOiBhbnlbXSA9IGVbTkdfVEVNUF9UT0tFTl9QQVRIXSA9IGVbTkdfVEVNUF9UT0tFTl9QQVRIXSB8fCBbXTtcbiAgICBwYXRoLnVuc2hpZnQodG9rZW4pO1xuICAgIGlmIChyZWNvcmQgJiYgcmVjb3JkLnZhbHVlID09IENJUkNVTEFSKSB7XG4gICAgICAvLyBSZXNldCB0aGUgQ2lyY3VsYXIgZmxhZy5cbiAgICAgIHJlY29yZC52YWx1ZSA9IEVNUFRZO1xuICAgIH1cbiAgICB0aHJvdyBlO1xuICB9XG59XG5cbmZ1bmN0aW9uIHJlc29sdmVUb2tlbihcbiAgICB0b2tlbjogYW55LCByZWNvcmQ6IFJlY29yZHx1bmRlZmluZWR8bnVsbCwgcmVjb3JkczogTWFwPGFueSwgUmVjb3JkfG51bGw+LCBwYXJlbnQ6IEluamVjdG9yLFxuICAgIG5vdEZvdW5kVmFsdWU6IGFueSwgZmxhZ3M6IEluamVjdEZsYWdzKTogYW55IHtcbiAgbGV0IHZhbHVlO1xuICBpZiAocmVjb3JkICYmICEoZmxhZ3MgJiBJbmplY3RGbGFncy5Ta2lwU2VsZikpIHtcbiAgICAvLyBJZiB3ZSBkb24ndCBoYXZlIGEgcmVjb3JkLCB0aGlzIGltcGxpZXMgdGhhdCB3ZSBkb24ndCBvd24gdGhlIHByb3ZpZGVyIGhlbmNlIGRvbid0IGtub3cgaG93XG4gICAgLy8gdG8gcmVzb2x2ZSBpdC5cbiAgICB2YWx1ZSA9IHJlY29yZC52YWx1ZTtcbiAgICBpZiAodmFsdWUgPT0gQ0lSQ1VMQVIpIHtcbiAgICAgIHRocm93IEVycm9yKE5PX05FV19MSU5FICsgJ0NpcmN1bGFyIGRlcGVuZGVuY3knKTtcbiAgICB9IGVsc2UgaWYgKHZhbHVlID09PSBFTVBUWSkge1xuICAgICAgcmVjb3JkLnZhbHVlID0gQ0lSQ1VMQVI7XG4gICAgICBsZXQgb2JqID0gdW5kZWZpbmVkO1xuICAgICAgbGV0IHVzZU5ldyA9IHJlY29yZC51c2VOZXc7XG4gICAgICBsZXQgZm4gPSByZWNvcmQuZm47XG4gICAgICBsZXQgZGVwUmVjb3JkcyA9IHJlY29yZC5kZXBzO1xuICAgICAgbGV0IGRlcHMgPSBFTVBUWTtcbiAgICAgIGlmIChkZXBSZWNvcmRzLmxlbmd0aCkge1xuICAgICAgICBkZXBzID0gW107XG4gICAgICAgIGZvciAobGV0IGkgPSAwOyBpIDwgZGVwUmVjb3Jkcy5sZW5ndGg7IGkrKykge1xuICAgICAgICAgIGNvbnN0IGRlcFJlY29yZDogRGVwZW5kZW5jeVJlY29yZCA9IGRlcFJlY29yZHNbaV07XG4gICAgICAgICAgY29uc3Qgb3B0aW9ucyA9IGRlcFJlY29yZC5vcHRpb25zO1xuICAgICAgICAgIGNvbnN0IGNoaWxkUmVjb3JkID1cbiAgICAgICAgICAgICAgb3B0aW9ucyAmIE9wdGlvbkZsYWdzLkNoZWNrU2VsZiA/IHJlY29yZHMuZ2V0KGRlcFJlY29yZC50b2tlbikgOiB1bmRlZmluZWQ7XG4gICAgICAgICAgZGVwcy5wdXNoKHRyeVJlc29sdmVUb2tlbihcbiAgICAgICAgICAgICAgLy8gQ3VycmVudCBUb2tlbiB0byByZXNvbHZlXG4gICAgICAgICAgICAgIGRlcFJlY29yZC50b2tlbixcbiAgICAgICAgICAgICAgLy8gQSByZWNvcmQgd2hpY2ggZGVzY3JpYmVzIGhvdyB0byByZXNvbHZlIHRoZSB0b2tlbi5cbiAgICAgICAgICAgICAgLy8gSWYgdW5kZWZpbmVkLCB0aGlzIG1lYW5zIHdlIGRvbid0IGhhdmUgc3VjaCBhIHJlY29yZFxuICAgICAgICAgICAgICBjaGlsZFJlY29yZCxcbiAgICAgICAgICAgICAgLy8gT3RoZXIgcmVjb3JkcyB3ZSBrbm93IGFib3V0LlxuICAgICAgICAgICAgICByZWNvcmRzLFxuICAgICAgICAgICAgICAvLyBJZiB3ZSBkb24ndCBrbm93IGhvdyB0byByZXNvbHZlIGRlcGVuZGVuY3kgYW5kIHdlIHNob3VsZCBub3QgY2hlY2sgcGFyZW50IGZvciBpdCxcbiAgICAgICAgICAgICAgLy8gdGhhbiBwYXNzIGluIE51bGwgaW5qZWN0b3IuXG4gICAgICAgICAgICAgICFjaGlsZFJlY29yZCAmJiAhKG9wdGlvbnMgJiBPcHRpb25GbGFncy5DaGVja1BhcmVudCkgPyBJbmplY3Rvci5OVUxMIDogcGFyZW50LFxuICAgICAgICAgICAgICBvcHRpb25zICYgT3B0aW9uRmxhZ3MuT3B0aW9uYWwgPyBudWxsIDogSW5qZWN0b3IuVEhST1dfSUZfTk9UX0ZPVU5ELFxuICAgICAgICAgICAgICBJbmplY3RGbGFncy5EZWZhdWx0KSk7XG4gICAgICAgIH1cbiAgICAgIH1cbiAgICAgIHJlY29yZC52YWx1ZSA9IHZhbHVlID0gdXNlTmV3ID8gbmV3IChmbiBhcyBhbnkpKC4uLmRlcHMpIDogZm4uYXBwbHkob2JqLCBkZXBzKTtcbiAgICB9XG4gIH0gZWxzZSBpZiAoIShmbGFncyAmIEluamVjdEZsYWdzLlNlbGYpKSB7XG4gICAgdmFsdWUgPSBwYXJlbnQuZ2V0KHRva2VuLCBub3RGb3VuZFZhbHVlLCBJbmplY3RGbGFncy5EZWZhdWx0KTtcbiAgfSBlbHNlIGlmICghKGZsYWdzICYgSW5qZWN0RmxhZ3MuT3B0aW9uYWwpKSB7XG4gICAgdmFsdWUgPSBJbmplY3Rvci5OVUxMLmdldCh0b2tlbiwgbm90Rm91bmRWYWx1ZSk7XG4gIH0gZWxzZSB7XG4gICAgdmFsdWUgPSBJbmplY3Rvci5OVUxMLmdldCh0b2tlbiwgdHlwZW9mIG5vdEZvdW5kVmFsdWUgIT09ICd1bmRlZmluZWQnID8gbm90Rm91bmRWYWx1ZSA6IG51bGwpO1xuICB9XG4gIHJldHVybiB2YWx1ZTtcbn1cblxuZnVuY3Rpb24gY29tcHV0ZURlcHMocHJvdmlkZXI6IFN0YXRpY1Byb3ZpZGVyKTogRGVwZW5kZW5jeVJlY29yZFtdIHtcbiAgbGV0IGRlcHM6IERlcGVuZGVuY3lSZWNvcmRbXSA9IEVNUFRZO1xuICBjb25zdCBwcm92aWRlckRlcHM6IGFueVtdID1cbiAgICAgIChwcm92aWRlciBhcyBFeGlzdGluZ1Byb3ZpZGVyICYgU3RhdGljQ2xhc3NQcm92aWRlciAmIENvbnN0cnVjdG9yUHJvdmlkZXIpLmRlcHM7XG4gIGlmIChwcm92aWRlckRlcHMgJiYgcHJvdmlkZXJEZXBzLmxlbmd0aCkge1xuICAgIGRlcHMgPSBbXTtcbiAgICBmb3IgKGxldCBpID0gMDsgaSA8IHByb3ZpZGVyRGVwcy5sZW5ndGg7IGkrKykge1xuICAgICAgbGV0IG9wdGlvbnMgPSBPcHRpb25GbGFncy5EZWZhdWx0O1xuICAgICAgbGV0IHRva2VuID0gcmVzb2x2ZUZvcndhcmRSZWYocHJvdmlkZXJEZXBzW2ldKTtcbiAgICAgIGlmIChBcnJheS5pc0FycmF5KHRva2VuKSkge1xuICAgICAgICBmb3IgKGxldCBqID0gMCwgYW5ub3RhdGlvbnMgPSB0b2tlbjsgaiA8IGFubm90YXRpb25zLmxlbmd0aDsgaisrKSB7XG4gICAgICAgICAgY29uc3QgYW5ub3RhdGlvbiA9IGFubm90YXRpb25zW2pdO1xuICAgICAgICAgIGlmIChhbm5vdGF0aW9uIGluc3RhbmNlb2YgT3B0aW9uYWwgfHwgYW5ub3RhdGlvbiA9PSBPcHRpb25hbCkge1xuICAgICAgICAgICAgb3B0aW9ucyA9IG9wdGlvbnMgfCBPcHRpb25GbGFncy5PcHRpb25hbDtcbiAgICAgICAgICB9IGVsc2UgaWYgKGFubm90YXRpb24gaW5zdGFuY2VvZiBTa2lwU2VsZiB8fCBhbm5vdGF0aW9uID09IFNraXBTZWxmKSB7XG4gICAgICAgICAgICBvcHRpb25zID0gb3B0aW9ucyAmIH5PcHRpb25GbGFncy5DaGVja1NlbGY7XG4gICAgICAgICAgfSBlbHNlIGlmIChhbm5vdGF0aW9uIGluc3RhbmNlb2YgU2VsZiB8fCBhbm5vdGF0aW9uID09IFNlbGYpIHtcbiAgICAgICAgICAgIG9wdGlvbnMgPSBvcHRpb25zICYgfk9wdGlvbkZsYWdzLkNoZWNrUGFyZW50O1xuICAgICAgICAgIH0gZWxzZSBpZiAoYW5ub3RhdGlvbiBpbnN0YW5jZW9mIEluamVjdCkge1xuICAgICAgICAgICAgdG9rZW4gPSAoYW5ub3RhdGlvbiBhcyBJbmplY3QpLnRva2VuO1xuICAgICAgICAgIH0gZWxzZSB7XG4gICAgICAgICAgICB0b2tlbiA9IHJlc29sdmVGb3J3YXJkUmVmKGFubm90YXRpb24pO1xuICAgICAgICAgIH1cbiAgICAgICAgfVxuICAgICAgfVxuICAgICAgZGVwcy5wdXNoKHt0b2tlbiwgb3B0aW9uc30pO1xuICAgIH1cbiAgfSBlbHNlIGlmICgocHJvdmlkZXIgYXMgRXhpc3RpbmdQcm92aWRlcikudXNlRXhpc3RpbmcpIHtcbiAgICBjb25zdCB0b2tlbiA9IHJlc29sdmVGb3J3YXJkUmVmKChwcm92aWRlciBhcyBFeGlzdGluZ1Byb3ZpZGVyKS51c2VFeGlzdGluZyk7XG4gICAgZGVwcyA9IFt7dG9rZW4sIG9wdGlvbnM6IE9wdGlvbkZsYWdzLkRlZmF1bHR9XTtcbiAgfSBlbHNlIGlmICghcHJvdmlkZXJEZXBzICYmICEoVVNFX1ZBTFVFIGluIHByb3ZpZGVyKSkge1xuICAgIC8vIHVzZVZhbHVlICYgdXNlRXhpc3RpbmcgYXJlIHRoZSBvbmx5IG9uZXMgd2hpY2ggYXJlIGV4ZW1wdCBmcm9tIGRlcHMgYWxsIG90aGVycyBuZWVkIGl0LlxuICAgIHRocm93IHN0YXRpY0Vycm9yKCdcXCdkZXBzXFwnIHJlcXVpcmVkJywgcHJvdmlkZXIpO1xuICB9XG4gIHJldHVybiBkZXBzO1xufVxuXG5mdW5jdGlvbiBzdGF0aWNFcnJvcih0ZXh0OiBzdHJpbmcsIG9iajogYW55KTogRXJyb3Ige1xuICByZXR1cm4gbmV3IEVycm9yKGZvcm1hdEVycm9yKHRleHQsIG9iaiwgJ1N0YXRpY0luamVjdG9yRXJyb3InKSk7XG59XG4iXX0=