/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
/**
 * Most of the use of `document` in Angular is from within the DI system so it is possible to simply
 * inject the `DOCUMENT` token and are done.
 *
 * Ivy is special because it does not rely upon the DI and must get hold of the document some other
 * way.
 *
 * The solution is to define `getDocument()` and `setDocument()` top-level functions for ivy.
 * Wherever ivy needs the global document, it calls `getDocument()` instead.
 *
 * When running ivy outside of a browser environment, it is necessary to call `setDocument()` to
 * tell ivy what the global `document` is.
 *
 * Angular does this for us in each of the standard platforms (`Browser`, `Server`, and `WebWorker`)
 * by calling `setDocument()` when providing the `DOCUMENT` token.
 */
let DOCUMENT = undefined;
/**
 * Tell ivy what the `document` is for this platform.
 *
 * It is only necessary to call this if the current platform is not a browser.
 *
 * @param document The object representing the global `document` in this environment.
 */
export function setDocument(document) {
    DOCUMENT = document;
}
/**
 * Access the object that represents the `document` for this platform.
 *
 * Ivy calls this whenever it needs to access the `document` object.
 * For example to create the renderer or to do sanitization.
 */
export function getDocument() {
    if (DOCUMENT !== undefined) {
        return DOCUMENT;
    }
    else if (typeof document !== 'undefined') {
        return document;
    }
    // No "document" can be found. This should only happen if we are running ivy outside Angular and
    // the current platform is not a browser. Since this is not a supported scenario at the moment
    // this should not happen in Angular apps.
    // Once we support running ivy outside of Angular we will need to publish `setDocument()` as a
    // public API. Meanwhile we just return `undefined` and let the application fail.
    return undefined;
}
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiZG9jdW1lbnQuanMiLCJzb3VyY2VSb290IjoiIiwic291cmNlcyI6WyIuLi8uLi8uLi8uLi8uLi8uLi8uLi8uLi9wYWNrYWdlcy9jb3JlL3NyYy9yZW5kZXIzL2ludGVyZmFjZXMvZG9jdW1lbnQudHMiXSwibmFtZXMiOltdLCJtYXBwaW5ncyI6IkFBQUE7Ozs7OztHQU1HO0FBRUg7Ozs7Ozs7Ozs7Ozs7OztHQWVHO0FBQ0gsSUFBSSxRQUFRLEdBQXVCLFNBQVMsQ0FBQztBQUU3Qzs7Ozs7O0dBTUc7QUFDSCxNQUFNLFVBQVUsV0FBVyxDQUFDLFFBQTRCO0lBQ3RELFFBQVEsR0FBRyxRQUFRLENBQUM7QUFDdEIsQ0FBQztBQUVEOzs7OztHQUtHO0FBQ0gsTUFBTSxVQUFVLFdBQVc7SUFDekIsSUFBSSxRQUFRLEtBQUssU0FBUyxFQUFFO1FBQzFCLE9BQU8sUUFBUSxDQUFDO0tBQ2pCO1NBQU0sSUFBSSxPQUFPLFFBQVEsS0FBSyxXQUFXLEVBQUU7UUFDMUMsT0FBTyxRQUFRLENBQUM7S0FDakI7SUFDRCxnR0FBZ0c7SUFDaEcsOEZBQThGO0lBQzlGLDBDQUEwQztJQUMxQyw4RkFBOEY7SUFDOUYsaUZBQWlGO0lBQ2pGLE9BQU8sU0FBVSxDQUFDO0FBQ3BCLENBQUMiLCJzb3VyY2VzQ29udGVudCI6WyIvKipcbiAqIEBsaWNlbnNlXG4gKiBDb3B5cmlnaHQgR29vZ2xlIExMQyBBbGwgUmlnaHRzIFJlc2VydmVkLlxuICpcbiAqIFVzZSBvZiB0aGlzIHNvdXJjZSBjb2RlIGlzIGdvdmVybmVkIGJ5IGFuIE1JVC1zdHlsZSBsaWNlbnNlIHRoYXQgY2FuIGJlXG4gKiBmb3VuZCBpbiB0aGUgTElDRU5TRSBmaWxlIGF0IGh0dHBzOi8vYW5ndWxhci5pby9saWNlbnNlXG4gKi9cblxuLyoqXG4gKiBNb3N0IG9mIHRoZSB1c2Ugb2YgYGRvY3VtZW50YCBpbiBBbmd1bGFyIGlzIGZyb20gd2l0aGluIHRoZSBESSBzeXN0ZW0gc28gaXQgaXMgcG9zc2libGUgdG8gc2ltcGx5XG4gKiBpbmplY3QgdGhlIGBET0NVTUVOVGAgdG9rZW4gYW5kIGFyZSBkb25lLlxuICpcbiAqIEl2eSBpcyBzcGVjaWFsIGJlY2F1c2UgaXQgZG9lcyBub3QgcmVseSB1cG9uIHRoZSBESSBhbmQgbXVzdCBnZXQgaG9sZCBvZiB0aGUgZG9jdW1lbnQgc29tZSBvdGhlclxuICogd2F5LlxuICpcbiAqIFRoZSBzb2x1dGlvbiBpcyB0byBkZWZpbmUgYGdldERvY3VtZW50KClgIGFuZCBgc2V0RG9jdW1lbnQoKWAgdG9wLWxldmVsIGZ1bmN0aW9ucyBmb3IgaXZ5LlxuICogV2hlcmV2ZXIgaXZ5IG5lZWRzIHRoZSBnbG9iYWwgZG9jdW1lbnQsIGl0IGNhbGxzIGBnZXREb2N1bWVudCgpYCBpbnN0ZWFkLlxuICpcbiAqIFdoZW4gcnVubmluZyBpdnkgb3V0c2lkZSBvZiBhIGJyb3dzZXIgZW52aXJvbm1lbnQsIGl0IGlzIG5lY2Vzc2FyeSB0byBjYWxsIGBzZXREb2N1bWVudCgpYCB0b1xuICogdGVsbCBpdnkgd2hhdCB0aGUgZ2xvYmFsIGBkb2N1bWVudGAgaXMuXG4gKlxuICogQW5ndWxhciBkb2VzIHRoaXMgZm9yIHVzIGluIGVhY2ggb2YgdGhlIHN0YW5kYXJkIHBsYXRmb3JtcyAoYEJyb3dzZXJgLCBgU2VydmVyYCwgYW5kIGBXZWJXb3JrZXJgKVxuICogYnkgY2FsbGluZyBgc2V0RG9jdW1lbnQoKWAgd2hlbiBwcm92aWRpbmcgdGhlIGBET0NVTUVOVGAgdG9rZW4uXG4gKi9cbmxldCBET0NVTUVOVDogRG9jdW1lbnR8dW5kZWZpbmVkID0gdW5kZWZpbmVkO1xuXG4vKipcbiAqIFRlbGwgaXZ5IHdoYXQgdGhlIGBkb2N1bWVudGAgaXMgZm9yIHRoaXMgcGxhdGZvcm0uXG4gKlxuICogSXQgaXMgb25seSBuZWNlc3NhcnkgdG8gY2FsbCB0aGlzIGlmIHRoZSBjdXJyZW50IHBsYXRmb3JtIGlzIG5vdCBhIGJyb3dzZXIuXG4gKlxuICogQHBhcmFtIGRvY3VtZW50IFRoZSBvYmplY3QgcmVwcmVzZW50aW5nIHRoZSBnbG9iYWwgYGRvY3VtZW50YCBpbiB0aGlzIGVudmlyb25tZW50LlxuICovXG5leHBvcnQgZnVuY3Rpb24gc2V0RG9jdW1lbnQoZG9jdW1lbnQ6IERvY3VtZW50fHVuZGVmaW5lZCk6IHZvaWQge1xuICBET0NVTUVOVCA9IGRvY3VtZW50O1xufVxuXG4vKipcbiAqIEFjY2VzcyB0aGUgb2JqZWN0IHRoYXQgcmVwcmVzZW50cyB0aGUgYGRvY3VtZW50YCBmb3IgdGhpcyBwbGF0Zm9ybS5cbiAqXG4gKiBJdnkgY2FsbHMgdGhpcyB3aGVuZXZlciBpdCBuZWVkcyB0byBhY2Nlc3MgdGhlIGBkb2N1bWVudGAgb2JqZWN0LlxuICogRm9yIGV4YW1wbGUgdG8gY3JlYXRlIHRoZSByZW5kZXJlciBvciB0byBkbyBzYW5pdGl6YXRpb24uXG4gKi9cbmV4cG9ydCBmdW5jdGlvbiBnZXREb2N1bWVudCgpOiBEb2N1bWVudCB7XG4gIGlmIChET0NVTUVOVCAhPT0gdW5kZWZpbmVkKSB7XG4gICAgcmV0dXJuIERPQ1VNRU5UO1xuICB9IGVsc2UgaWYgKHR5cGVvZiBkb2N1bWVudCAhPT0gJ3VuZGVmaW5lZCcpIHtcbiAgICByZXR1cm4gZG9jdW1lbnQ7XG4gIH1cbiAgLy8gTm8gXCJkb2N1bWVudFwiIGNhbiBiZSBmb3VuZC4gVGhpcyBzaG91bGQgb25seSBoYXBwZW4gaWYgd2UgYXJlIHJ1bm5pbmcgaXZ5IG91dHNpZGUgQW5ndWxhciBhbmRcbiAgLy8gdGhlIGN1cnJlbnQgcGxhdGZvcm0gaXMgbm90IGEgYnJvd3Nlci4gU2luY2UgdGhpcyBpcyBub3QgYSBzdXBwb3J0ZWQgc2NlbmFyaW8gYXQgdGhlIG1vbWVudFxuICAvLyB0aGlzIHNob3VsZCBub3QgaGFwcGVuIGluIEFuZ3VsYXIgYXBwcy5cbiAgLy8gT25jZSB3ZSBzdXBwb3J0IHJ1bm5pbmcgaXZ5IG91dHNpZGUgb2YgQW5ndWxhciB3ZSB3aWxsIG5lZWQgdG8gcHVibGlzaCBgc2V0RG9jdW1lbnQoKWAgYXMgYVxuICAvLyBwdWJsaWMgQVBJLiBNZWFud2hpbGUgd2UganVzdCByZXR1cm4gYHVuZGVmaW5lZGAgYW5kIGxldCB0aGUgYXBwbGljYXRpb24gZmFpbC5cbiAgcmV0dXJuIHVuZGVmaW5lZCE7XG59XG4iXX0=