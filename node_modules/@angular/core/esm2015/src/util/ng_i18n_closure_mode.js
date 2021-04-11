/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
import { global } from './global';
/**
 * NOTE: changes to the `ngI18nClosureMode` name must be synced with `compiler-cli/src/tooling.ts`.
 */
if (typeof ngI18nClosureMode === 'undefined') {
    // These property accesses can be ignored because ngI18nClosureMode will be set to false
    // when optimizing code and the whole if statement will be dropped.
    // Make sure to refer to ngI18nClosureMode as ['ngI18nClosureMode'] for closure.
    // NOTE: we need to have it in IIFE so that the tree-shaker is happy.
    (function () {
        // tslint:disable-next-line:no-toplevel-property-access
        global['ngI18nClosureMode'] =
            // TODO(FW-1250): validate that this actually, you know, works.
            // tslint:disable-next-line:no-toplevel-property-access
            typeof goog !== 'undefined' && typeof goog.getMsg === 'function';
    })();
}
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoibmdfaTE4bl9jbG9zdXJlX21vZGUuanMiLCJzb3VyY2VSb290IjoiIiwic291cmNlcyI6WyIuLi8uLi8uLi8uLi8uLi8uLi8uLi9wYWNrYWdlcy9jb3JlL3NyYy91dGlsL25nX2kxOG5fY2xvc3VyZV9tb2RlLnRzIl0sIm5hbWVzIjpbXSwibWFwcGluZ3MiOiJBQUFBOzs7Ozs7R0FNRztBQUVILE9BQU8sRUFBQyxNQUFNLEVBQUMsTUFBTSxVQUFVLENBQUM7QUFNaEM7O0dBRUc7QUFDSCxJQUFJLE9BQU8saUJBQWlCLEtBQUssV0FBVyxFQUFFO0lBQzVDLHdGQUF3RjtJQUN4RixtRUFBbUU7SUFDbkUsZ0ZBQWdGO0lBQ2hGLHFFQUFxRTtJQUNyRSxDQUFDO1FBQ0MsdURBQXVEO1FBQ3ZELE1BQU0sQ0FBQyxtQkFBbUIsQ0FBQztZQUN2QiwrREFBK0Q7WUFDL0QsdURBQXVEO1lBQ3ZELE9BQU8sSUFBSSxLQUFLLFdBQVcsSUFBSSxPQUFPLElBQUksQ0FBQyxNQUFNLEtBQUssVUFBVSxDQUFDO0lBQ3ZFLENBQUMsQ0FBQyxFQUFFLENBQUM7Q0FDTiIsInNvdXJjZXNDb250ZW50IjpbIi8qKlxuICogQGxpY2Vuc2VcbiAqIENvcHlyaWdodCBHb29nbGUgTExDIEFsbCBSaWdodHMgUmVzZXJ2ZWQuXG4gKlxuICogVXNlIG9mIHRoaXMgc291cmNlIGNvZGUgaXMgZ292ZXJuZWQgYnkgYW4gTUlULXN0eWxlIGxpY2Vuc2UgdGhhdCBjYW4gYmVcbiAqIGZvdW5kIGluIHRoZSBMSUNFTlNFIGZpbGUgYXQgaHR0cHM6Ly9hbmd1bGFyLmlvL2xpY2Vuc2VcbiAqL1xuXG5pbXBvcnQge2dsb2JhbH0gZnJvbSAnLi9nbG9iYWwnO1xuXG5kZWNsYXJlIGdsb2JhbCB7XG4gIGNvbnN0IG5nSTE4bkNsb3N1cmVNb2RlOiBib29sZWFuO1xufVxuXG4vKipcbiAqIE5PVEU6IGNoYW5nZXMgdG8gdGhlIGBuZ0kxOG5DbG9zdXJlTW9kZWAgbmFtZSBtdXN0IGJlIHN5bmNlZCB3aXRoIGBjb21waWxlci1jbGkvc3JjL3Rvb2xpbmcudHNgLlxuICovXG5pZiAodHlwZW9mIG5nSTE4bkNsb3N1cmVNb2RlID09PSAndW5kZWZpbmVkJykge1xuICAvLyBUaGVzZSBwcm9wZXJ0eSBhY2Nlc3NlcyBjYW4gYmUgaWdub3JlZCBiZWNhdXNlIG5nSTE4bkNsb3N1cmVNb2RlIHdpbGwgYmUgc2V0IHRvIGZhbHNlXG4gIC8vIHdoZW4gb3B0aW1pemluZyBjb2RlIGFuZCB0aGUgd2hvbGUgaWYgc3RhdGVtZW50IHdpbGwgYmUgZHJvcHBlZC5cbiAgLy8gTWFrZSBzdXJlIHRvIHJlZmVyIHRvIG5nSTE4bkNsb3N1cmVNb2RlIGFzIFsnbmdJMThuQ2xvc3VyZU1vZGUnXSBmb3IgY2xvc3VyZS5cbiAgLy8gTk9URTogd2UgbmVlZCB0byBoYXZlIGl0IGluIElJRkUgc28gdGhhdCB0aGUgdHJlZS1zaGFrZXIgaXMgaGFwcHkuXG4gIChmdW5jdGlvbigpIHtcbiAgICAvLyB0c2xpbnQ6ZGlzYWJsZS1uZXh0LWxpbmU6bm8tdG9wbGV2ZWwtcHJvcGVydHktYWNjZXNzXG4gICAgZ2xvYmFsWyduZ0kxOG5DbG9zdXJlTW9kZSddID1cbiAgICAgICAgLy8gVE9ETyhGVy0xMjUwKTogdmFsaWRhdGUgdGhhdCB0aGlzIGFjdHVhbGx5LCB5b3Uga25vdywgd29ya3MuXG4gICAgICAgIC8vIHRzbGludDpkaXNhYmxlLW5leHQtbGluZTpuby10b3BsZXZlbC1wcm9wZXJ0eS1hY2Nlc3NcbiAgICAgICAgdHlwZW9mIGdvb2cgIT09ICd1bmRlZmluZWQnICYmIHR5cGVvZiBnb29nLmdldE1zZyA9PT0gJ2Z1bmN0aW9uJztcbiAgfSkoKTtcbn1cbiJdfQ==