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
        define("@angular/core/schematics/migrations/undecorated-classes-with-di/update_recorder", ["require", "exports"], factory);
    }
})(function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
});
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoidXBkYXRlX3JlY29yZGVyLmpzIiwic291cmNlUm9vdCI6IiIsInNvdXJjZXMiOlsiLi4vLi4vLi4vLi4vLi4vLi4vLi4vLi4vcGFja2FnZXMvY29yZS9zY2hlbWF0aWNzL21pZ3JhdGlvbnMvdW5kZWNvcmF0ZWQtY2xhc3Nlcy13aXRoLWRpL3VwZGF0ZV9yZWNvcmRlci50cyJdLCJuYW1lcyI6W10sIm1hcHBpbmdzIjoiQUFDQTs7Ozs7O0dBTUciLCJzb3VyY2VzQ29udGVudCI6WyJcbi8qKlxuICogQGxpY2Vuc2VcbiAqIENvcHlyaWdodCBHb29nbGUgTExDIEFsbCBSaWdodHMgUmVzZXJ2ZWQuXG4gKlxuICogVXNlIG9mIHRoaXMgc291cmNlIGNvZGUgaXMgZ292ZXJuZWQgYnkgYW4gTUlULXN0eWxlIGxpY2Vuc2UgdGhhdCBjYW4gYmVcbiAqIGZvdW5kIGluIHRoZSBMSUNFTlNFIGZpbGUgYXQgaHR0cHM6Ly9hbmd1bGFyLmlvL2xpY2Vuc2VcbiAqL1xuXG5pbXBvcnQgKiBhcyB0cyBmcm9tICd0eXBlc2NyaXB0JztcbmltcG9ydCB7SW1wb3J0TWFuYWdlclVwZGF0ZVJlY29yZGVyfSBmcm9tICcuLi8uLi91dGlscy9pbXBvcnRfbWFuYWdlcic7XG5cbi8qKlxuICogVXBkYXRlIHJlY29yZGVyIGludGVyZmFjZSB0aGF0IGlzIHVzZWQgdG8gdHJhbnNmb3JtIHNvdXJjZSBmaWxlcyBpbiBhIG5vbi1jb2xsaWRpbmdcbiAqIHdheS4gQWxzbyB0aGlzIGluZGlyZWN0aW9uIG1ha2VzIGl0IHBvc3NpYmxlIHRvIHJlLXVzZSB0cmFuc2Zvcm1hdGlvbiBsb2dpYyB3aXRoXG4gKiBkaWZmZXJlbnQgcmVwbGFjZW1lbnQgdG9vbHMgKGUuZy4gVFNMaW50IG9yIENMSSBkZXZraXQpLlxuICovXG5leHBvcnQgaW50ZXJmYWNlIFVwZGF0ZVJlY29yZGVyIGV4dGVuZHMgSW1wb3J0TWFuYWdlclVwZGF0ZVJlY29yZGVyIHtcbiAgYWRkQ2xhc3NEZWNvcmF0b3Iobm9kZTogdHMuQ2xhc3NEZWNsYXJhdGlvbiwgdGV4dDogc3RyaW5nKTogdm9pZDtcbiAgYWRkQ2xhc3NDb21tZW50KG5vZGU6IHRzLkNsYXNzRGVjbGFyYXRpb24sIHRleHQ6IHN0cmluZyk6IHZvaWQ7XG4gIGNvbW1pdFVwZGF0ZSgpOiB2b2lkO1xufVxuIl19