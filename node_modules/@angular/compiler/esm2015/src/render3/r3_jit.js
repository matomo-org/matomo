/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
/**
 * Implementation of `CompileReflector` which resolves references to @angular/core
 * symbols at runtime, according to a consumer-provided mapping.
 *
 * Only supports `resolveExternalReference`, all other methods throw.
 */
export class R3JitReflector {
    constructor(context) {
        this.context = context;
    }
    resolveExternalReference(ref) {
        // This reflector only handles @angular/core imports.
        if (ref.moduleName !== '@angular/core') {
            throw new Error(`Cannot resolve external reference to ${ref.moduleName}, only references to @angular/core are supported.`);
        }
        if (!this.context.hasOwnProperty(ref.name)) {
            throw new Error(`No value provided for @angular/core symbol '${ref.name}'.`);
        }
        return this.context[ref.name];
    }
    parameters(typeOrFunc) {
        throw new Error('Not implemented.');
    }
    annotations(typeOrFunc) {
        throw new Error('Not implemented.');
    }
    shallowAnnotations(typeOrFunc) {
        throw new Error('Not implemented.');
    }
    tryAnnotations(typeOrFunc) {
        throw new Error('Not implemented.');
    }
    propMetadata(typeOrFunc) {
        throw new Error('Not implemented.');
    }
    hasLifecycleHook(type, lcProperty) {
        throw new Error('Not implemented.');
    }
    guards(typeOrFunc) {
        throw new Error('Not implemented.');
    }
    componentModuleUrl(type, cmpMetadata) {
        throw new Error('Not implemented.');
    }
}
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoicjNfaml0LmpzIiwic291cmNlUm9vdCI6IiIsInNvdXJjZXMiOlsiLi4vLi4vLi4vLi4vLi4vLi4vLi4vcGFja2FnZXMvY29tcGlsZXIvc3JjL3JlbmRlcjMvcjNfaml0LnRzIl0sIm5hbWVzIjpbXSwibWFwcGluZ3MiOiJBQUFBOzs7Ozs7R0FNRztBQUtIOzs7OztHQUtHO0FBQ0gsTUFBTSxPQUFPLGNBQWM7SUFDekIsWUFBb0IsT0FBNkI7UUFBN0IsWUFBTyxHQUFQLE9BQU8sQ0FBc0I7SUFBRyxDQUFDO0lBRXJELHdCQUF3QixDQUFDLEdBQXdCO1FBQy9DLHFEQUFxRDtRQUNyRCxJQUFJLEdBQUcsQ0FBQyxVQUFVLEtBQUssZUFBZSxFQUFFO1lBQ3RDLE1BQU0sSUFBSSxLQUFLLENBQUMsd0NBQ1osR0FBRyxDQUFDLFVBQVUsbURBQW1ELENBQUMsQ0FBQztTQUN4RTtRQUNELElBQUksQ0FBQyxJQUFJLENBQUMsT0FBTyxDQUFDLGNBQWMsQ0FBQyxHQUFHLENBQUMsSUFBSyxDQUFDLEVBQUU7WUFDM0MsTUFBTSxJQUFJLEtBQUssQ0FBQywrQ0FBK0MsR0FBRyxDQUFDLElBQUssSUFBSSxDQUFDLENBQUM7U0FDL0U7UUFDRCxPQUFPLElBQUksQ0FBQyxPQUFPLENBQUMsR0FBRyxDQUFDLElBQUssQ0FBQyxDQUFDO0lBQ2pDLENBQUM7SUFFRCxVQUFVLENBQUMsVUFBZTtRQUN4QixNQUFNLElBQUksS0FBSyxDQUFDLGtCQUFrQixDQUFDLENBQUM7SUFDdEMsQ0FBQztJQUVELFdBQVcsQ0FBQyxVQUFlO1FBQ3pCLE1BQU0sSUFBSSxLQUFLLENBQUMsa0JBQWtCLENBQUMsQ0FBQztJQUN0QyxDQUFDO0lBRUQsa0JBQWtCLENBQUMsVUFBZTtRQUNoQyxNQUFNLElBQUksS0FBSyxDQUFDLGtCQUFrQixDQUFDLENBQUM7SUFDdEMsQ0FBQztJQUVELGNBQWMsQ0FBQyxVQUFlO1FBQzVCLE1BQU0sSUFBSSxLQUFLLENBQUMsa0JBQWtCLENBQUMsQ0FBQztJQUN0QyxDQUFDO0lBRUQsWUFBWSxDQUFDLFVBQWU7UUFDMUIsTUFBTSxJQUFJLEtBQUssQ0FBQyxrQkFBa0IsQ0FBQyxDQUFDO0lBQ3RDLENBQUM7SUFFRCxnQkFBZ0IsQ0FBQyxJQUFTLEVBQUUsVUFBa0I7UUFDNUMsTUFBTSxJQUFJLEtBQUssQ0FBQyxrQkFBa0IsQ0FBQyxDQUFDO0lBQ3RDLENBQUM7SUFFRCxNQUFNLENBQUMsVUFBZTtRQUNwQixNQUFNLElBQUksS0FBSyxDQUFDLGtCQUFrQixDQUFDLENBQUM7SUFDdEMsQ0FBQztJQUVELGtCQUFrQixDQUFDLElBQVMsRUFBRSxXQUFnQjtRQUM1QyxNQUFNLElBQUksS0FBSyxDQUFDLGtCQUFrQixDQUFDLENBQUM7SUFDdEMsQ0FBQztDQUNGIiwic291cmNlc0NvbnRlbnQiOlsiLyoqXG4gKiBAbGljZW5zZVxuICogQ29weXJpZ2h0IEdvb2dsZSBMTEMgQWxsIFJpZ2h0cyBSZXNlcnZlZC5cbiAqXG4gKiBVc2Ugb2YgdGhpcyBzb3VyY2UgY29kZSBpcyBnb3Zlcm5lZCBieSBhbiBNSVQtc3R5bGUgbGljZW5zZSB0aGF0IGNhbiBiZVxuICogZm91bmQgaW4gdGhlIExJQ0VOU0UgZmlsZSBhdCBodHRwczovL2FuZ3VsYXIuaW8vbGljZW5zZVxuICovXG5cbmltcG9ydCB7Q29tcGlsZVJlZmxlY3Rvcn0gZnJvbSAnLi4vY29tcGlsZV9yZWZsZWN0b3InO1xuaW1wb3J0ICogYXMgbyBmcm9tICcuLi9vdXRwdXQvb3V0cHV0X2FzdCc7XG5cbi8qKlxuICogSW1wbGVtZW50YXRpb24gb2YgYENvbXBpbGVSZWZsZWN0b3JgIHdoaWNoIHJlc29sdmVzIHJlZmVyZW5jZXMgdG8gQGFuZ3VsYXIvY29yZVxuICogc3ltYm9scyBhdCBydW50aW1lLCBhY2NvcmRpbmcgdG8gYSBjb25zdW1lci1wcm92aWRlZCBtYXBwaW5nLlxuICpcbiAqIE9ubHkgc3VwcG9ydHMgYHJlc29sdmVFeHRlcm5hbFJlZmVyZW5jZWAsIGFsbCBvdGhlciBtZXRob2RzIHRocm93LlxuICovXG5leHBvcnQgY2xhc3MgUjNKaXRSZWZsZWN0b3IgaW1wbGVtZW50cyBDb21waWxlUmVmbGVjdG9yIHtcbiAgY29uc3RydWN0b3IocHJpdmF0ZSBjb250ZXh0OiB7W2tleTogc3RyaW5nXTogYW55fSkge31cblxuICByZXNvbHZlRXh0ZXJuYWxSZWZlcmVuY2UocmVmOiBvLkV4dGVybmFsUmVmZXJlbmNlKTogYW55IHtcbiAgICAvLyBUaGlzIHJlZmxlY3RvciBvbmx5IGhhbmRsZXMgQGFuZ3VsYXIvY29yZSBpbXBvcnRzLlxuICAgIGlmIChyZWYubW9kdWxlTmFtZSAhPT0gJ0Bhbmd1bGFyL2NvcmUnKSB7XG4gICAgICB0aHJvdyBuZXcgRXJyb3IoYENhbm5vdCByZXNvbHZlIGV4dGVybmFsIHJlZmVyZW5jZSB0byAke1xuICAgICAgICAgIHJlZi5tb2R1bGVOYW1lfSwgb25seSByZWZlcmVuY2VzIHRvIEBhbmd1bGFyL2NvcmUgYXJlIHN1cHBvcnRlZC5gKTtcbiAgICB9XG4gICAgaWYgKCF0aGlzLmNvbnRleHQuaGFzT3duUHJvcGVydHkocmVmLm5hbWUhKSkge1xuICAgICAgdGhyb3cgbmV3IEVycm9yKGBObyB2YWx1ZSBwcm92aWRlZCBmb3IgQGFuZ3VsYXIvY29yZSBzeW1ib2wgJyR7cmVmLm5hbWUhfScuYCk7XG4gICAgfVxuICAgIHJldHVybiB0aGlzLmNvbnRleHRbcmVmLm5hbWUhXTtcbiAgfVxuXG4gIHBhcmFtZXRlcnModHlwZU9yRnVuYzogYW55KTogYW55W11bXSB7XG4gICAgdGhyb3cgbmV3IEVycm9yKCdOb3QgaW1wbGVtZW50ZWQuJyk7XG4gIH1cblxuICBhbm5vdGF0aW9ucyh0eXBlT3JGdW5jOiBhbnkpOiBhbnlbXSB7XG4gICAgdGhyb3cgbmV3IEVycm9yKCdOb3QgaW1wbGVtZW50ZWQuJyk7XG4gIH1cblxuICBzaGFsbG93QW5ub3RhdGlvbnModHlwZU9yRnVuYzogYW55KTogYW55W10ge1xuICAgIHRocm93IG5ldyBFcnJvcignTm90IGltcGxlbWVudGVkLicpO1xuICB9XG5cbiAgdHJ5QW5ub3RhdGlvbnModHlwZU9yRnVuYzogYW55KTogYW55W10ge1xuICAgIHRocm93IG5ldyBFcnJvcignTm90IGltcGxlbWVudGVkLicpO1xuICB9XG5cbiAgcHJvcE1ldGFkYXRhKHR5cGVPckZ1bmM6IGFueSk6IHtba2V5OiBzdHJpbmddOiBhbnlbXTt9IHtcbiAgICB0aHJvdyBuZXcgRXJyb3IoJ05vdCBpbXBsZW1lbnRlZC4nKTtcbiAgfVxuXG4gIGhhc0xpZmVjeWNsZUhvb2sodHlwZTogYW55LCBsY1Byb3BlcnR5OiBzdHJpbmcpOiBib29sZWFuIHtcbiAgICB0aHJvdyBuZXcgRXJyb3IoJ05vdCBpbXBsZW1lbnRlZC4nKTtcbiAgfVxuXG4gIGd1YXJkcyh0eXBlT3JGdW5jOiBhbnkpOiB7W2tleTogc3RyaW5nXTogYW55O30ge1xuICAgIHRocm93IG5ldyBFcnJvcignTm90IGltcGxlbWVudGVkLicpO1xuICB9XG5cbiAgY29tcG9uZW50TW9kdWxlVXJsKHR5cGU6IGFueSwgY21wTWV0YWRhdGE6IGFueSk6IHN0cmluZyB7XG4gICAgdGhyb3cgbmV3IEVycm9yKCdOb3QgaW1wbGVtZW50ZWQuJyk7XG4gIH1cbn1cbiJdfQ==