/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
/**
 * Patch a `debug` property on top of the existing object.
 *
 * NOTE: always call this method with `ngDevMode && attachDebugObject(...)`
 *
 * @param obj Object to patch
 * @param debug Value to patch
 */
export function attachDebugObject(obj, debug) {
    if (ngDevMode) {
        Object.defineProperty(obj, 'debug', { value: debug, enumerable: false });
    }
    else {
        throw new Error('This method should be guarded with `ngDevMode` so that it can be tree shaken in production!');
    }
}
/**
 * Patch a `debug` property getter on top of the existing object.
 *
 * NOTE: always call this method with `ngDevMode && attachDebugObject(...)`
 *
 * @param obj Object to patch
 * @param debugGetter Getter returning a value to patch
 */
export function attachDebugGetter(obj, debugGetter) {
    if (ngDevMode) {
        Object.defineProperty(obj, 'debug', { get: debugGetter, enumerable: false });
    }
    else {
        throw new Error('This method should be guarded with `ngDevMode` so that it can be tree shaken in production!');
    }
}
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiZGVidWdfdXRpbHMuanMiLCJzb3VyY2VSb290IjoiIiwic291cmNlcyI6WyIuLi8uLi8uLi8uLi8uLi8uLi8uLi8uLi9wYWNrYWdlcy9jb3JlL3NyYy9yZW5kZXIzL3V0aWwvZGVidWdfdXRpbHMudHMiXSwibmFtZXMiOltdLCJtYXBwaW5ncyI6IkFBQUE7Ozs7OztHQU1HO0FBRUg7Ozs7Ozs7R0FPRztBQUNILE1BQU0sVUFBVSxpQkFBaUIsQ0FBQyxHQUFRLEVBQUUsS0FBVTtJQUNwRCxJQUFJLFNBQVMsRUFBRTtRQUNiLE1BQU0sQ0FBQyxjQUFjLENBQUMsR0FBRyxFQUFFLE9BQU8sRUFBRSxFQUFDLEtBQUssRUFBRSxLQUFLLEVBQUUsVUFBVSxFQUFFLEtBQUssRUFBQyxDQUFDLENBQUM7S0FDeEU7U0FBTTtRQUNMLE1BQU0sSUFBSSxLQUFLLENBQ1gsNkZBQTZGLENBQUMsQ0FBQztLQUNwRztBQUNILENBQUM7QUFFRDs7Ozs7OztHQU9HO0FBQ0gsTUFBTSxVQUFVLGlCQUFpQixDQUFJLEdBQU0sRUFBRSxXQUE2QjtJQUN4RSxJQUFJLFNBQVMsRUFBRTtRQUNiLE1BQU0sQ0FBQyxjQUFjLENBQUMsR0FBRyxFQUFFLE9BQU8sRUFBRSxFQUFDLEdBQUcsRUFBRSxXQUFXLEVBQUUsVUFBVSxFQUFFLEtBQUssRUFBQyxDQUFDLENBQUM7S0FDNUU7U0FBTTtRQUNMLE1BQU0sSUFBSSxLQUFLLENBQ1gsNkZBQTZGLENBQUMsQ0FBQztLQUNwRztBQUNILENBQUMiLCJzb3VyY2VzQ29udGVudCI6WyIvKipcbiAqIEBsaWNlbnNlXG4gKiBDb3B5cmlnaHQgR29vZ2xlIExMQyBBbGwgUmlnaHRzIFJlc2VydmVkLlxuICpcbiAqIFVzZSBvZiB0aGlzIHNvdXJjZSBjb2RlIGlzIGdvdmVybmVkIGJ5IGFuIE1JVC1zdHlsZSBsaWNlbnNlIHRoYXQgY2FuIGJlXG4gKiBmb3VuZCBpbiB0aGUgTElDRU5TRSBmaWxlIGF0IGh0dHBzOi8vYW5ndWxhci5pby9saWNlbnNlXG4gKi9cblxuLyoqXG4gKiBQYXRjaCBhIGBkZWJ1Z2AgcHJvcGVydHkgb24gdG9wIG9mIHRoZSBleGlzdGluZyBvYmplY3QuXG4gKlxuICogTk9URTogYWx3YXlzIGNhbGwgdGhpcyBtZXRob2Qgd2l0aCBgbmdEZXZNb2RlICYmIGF0dGFjaERlYnVnT2JqZWN0KC4uLilgXG4gKlxuICogQHBhcmFtIG9iaiBPYmplY3QgdG8gcGF0Y2hcbiAqIEBwYXJhbSBkZWJ1ZyBWYWx1ZSB0byBwYXRjaFxuICovXG5leHBvcnQgZnVuY3Rpb24gYXR0YWNoRGVidWdPYmplY3Qob2JqOiBhbnksIGRlYnVnOiBhbnkpOiB2b2lkIHtcbiAgaWYgKG5nRGV2TW9kZSkge1xuICAgIE9iamVjdC5kZWZpbmVQcm9wZXJ0eShvYmosICdkZWJ1ZycsIHt2YWx1ZTogZGVidWcsIGVudW1lcmFibGU6IGZhbHNlfSk7XG4gIH0gZWxzZSB7XG4gICAgdGhyb3cgbmV3IEVycm9yKFxuICAgICAgICAnVGhpcyBtZXRob2Qgc2hvdWxkIGJlIGd1YXJkZWQgd2l0aCBgbmdEZXZNb2RlYCBzbyB0aGF0IGl0IGNhbiBiZSB0cmVlIHNoYWtlbiBpbiBwcm9kdWN0aW9uIScpO1xuICB9XG59XG5cbi8qKlxuICogUGF0Y2ggYSBgZGVidWdgIHByb3BlcnR5IGdldHRlciBvbiB0b3Agb2YgdGhlIGV4aXN0aW5nIG9iamVjdC5cbiAqXG4gKiBOT1RFOiBhbHdheXMgY2FsbCB0aGlzIG1ldGhvZCB3aXRoIGBuZ0Rldk1vZGUgJiYgYXR0YWNoRGVidWdPYmplY3QoLi4uKWBcbiAqXG4gKiBAcGFyYW0gb2JqIE9iamVjdCB0byBwYXRjaFxuICogQHBhcmFtIGRlYnVnR2V0dGVyIEdldHRlciByZXR1cm5pbmcgYSB2YWx1ZSB0byBwYXRjaFxuICovXG5leHBvcnQgZnVuY3Rpb24gYXR0YWNoRGVidWdHZXR0ZXI8VD4ob2JqOiBULCBkZWJ1Z0dldHRlcjogKHRoaXM6IFQpID0+IGFueSk6IHZvaWQge1xuICBpZiAobmdEZXZNb2RlKSB7XG4gICAgT2JqZWN0LmRlZmluZVByb3BlcnR5KG9iaiwgJ2RlYnVnJywge2dldDogZGVidWdHZXR0ZXIsIGVudW1lcmFibGU6IGZhbHNlfSk7XG4gIH0gZWxzZSB7XG4gICAgdGhyb3cgbmV3IEVycm9yKFxuICAgICAgICAnVGhpcyBtZXRob2Qgc2hvdWxkIGJlIGd1YXJkZWQgd2l0aCBgbmdEZXZNb2RlYCBzbyB0aGF0IGl0IGNhbiBiZSB0cmVlIHNoYWtlbiBpbiBwcm9kdWN0aW9uIScpO1xuICB9XG59XG4iXX0=