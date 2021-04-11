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
        define("@angular/compiler/src/schema/trusted_types_sinks", ["require", "exports"], factory);
    }
})(function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.isTrustedTypesSink = void 0;
    /**
     * Set of tagName|propertyName corresponding to Trusted Types sinks. Properties applying to all
     * tags use '*'.
     *
     * Extracted from, and should be kept in sync with
     * https://w3c.github.io/webappsec-trusted-types/dist/spec/#integrations
     */
    var TRUSTED_TYPES_SINKS = new Set([
        // NOTE: All strings in this set *must* be lowercase!
        // TrustedHTML
        'iframe|srcdoc',
        '*|innerhtml',
        '*|outerhtml',
        // NB: no TrustedScript here, as the corresponding tags are stripped by the compiler.
        // TrustedScriptURL
        'embed|src',
        'object|codebase',
        'object|data',
    ]);
    /**
     * isTrustedTypesSink returns true if the given property on the given DOM tag is a Trusted Types
     * sink. In that case, use `ElementSchemaRegistry.securityContext` to determine which particular
     * Trusted Type is required for values passed to the sink:
     * - SecurityContext.HTML corresponds to TrustedHTML
     * - SecurityContext.RESOURCE_URL corresponds to TrustedScriptURL
     */
    function isTrustedTypesSink(tagName, propName) {
        // Make sure comparisons are case insensitive, so that case differences between attribute and
        // property names do not have a security impact.
        tagName = tagName.toLowerCase();
        propName = propName.toLowerCase();
        return TRUSTED_TYPES_SINKS.has(tagName + '|' + propName) ||
            TRUSTED_TYPES_SINKS.has('*|' + propName);
    }
    exports.isTrustedTypesSink = isTrustedTypesSink;
});
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoidHJ1c3RlZF90eXBlc19zaW5rcy5qcyIsInNvdXJjZVJvb3QiOiIiLCJzb3VyY2VzIjpbIi4uLy4uLy4uLy4uLy4uLy4uLy4uL3BhY2thZ2VzL2NvbXBpbGVyL3NyYy9zY2hlbWEvdHJ1c3RlZF90eXBlc19zaW5rcy50cyJdLCJuYW1lcyI6W10sIm1hcHBpbmdzIjoiQUFBQTs7Ozs7O0dBTUc7Ozs7Ozs7Ozs7Ozs7SUFFSDs7Ozs7O09BTUc7SUFDSCxJQUFNLG1CQUFtQixHQUFHLElBQUksR0FBRyxDQUFTO1FBQzFDLHFEQUFxRDtRQUVyRCxjQUFjO1FBQ2QsZUFBZTtRQUNmLGFBQWE7UUFDYixhQUFhO1FBRWIscUZBQXFGO1FBRXJGLG1CQUFtQjtRQUNuQixXQUFXO1FBQ1gsaUJBQWlCO1FBQ2pCLGFBQWE7S0FDZCxDQUFDLENBQUM7SUFFSDs7Ozs7O09BTUc7SUFDSCxTQUFnQixrQkFBa0IsQ0FBQyxPQUFlLEVBQUUsUUFBZ0I7UUFDbEUsNkZBQTZGO1FBQzdGLGdEQUFnRDtRQUNoRCxPQUFPLEdBQUcsT0FBTyxDQUFDLFdBQVcsRUFBRSxDQUFDO1FBQ2hDLFFBQVEsR0FBRyxRQUFRLENBQUMsV0FBVyxFQUFFLENBQUM7UUFFbEMsT0FBTyxtQkFBbUIsQ0FBQyxHQUFHLENBQUMsT0FBTyxHQUFHLEdBQUcsR0FBRyxRQUFRLENBQUM7WUFDcEQsbUJBQW1CLENBQUMsR0FBRyxDQUFDLElBQUksR0FBRyxRQUFRLENBQUMsQ0FBQztJQUMvQyxDQUFDO0lBUkQsZ0RBUUMiLCJzb3VyY2VzQ29udGVudCI6WyIvKipcbiAqIEBsaWNlbnNlXG4gKiBDb3B5cmlnaHQgR29vZ2xlIExMQyBBbGwgUmlnaHRzIFJlc2VydmVkLlxuICpcbiAqIFVzZSBvZiB0aGlzIHNvdXJjZSBjb2RlIGlzIGdvdmVybmVkIGJ5IGFuIE1JVC1zdHlsZSBsaWNlbnNlIHRoYXQgY2FuIGJlXG4gKiBmb3VuZCBpbiB0aGUgTElDRU5TRSBmaWxlIGF0IGh0dHBzOi8vYW5ndWxhci5pby9saWNlbnNlXG4gKi9cblxuLyoqXG4gKiBTZXQgb2YgdGFnTmFtZXxwcm9wZXJ0eU5hbWUgY29ycmVzcG9uZGluZyB0byBUcnVzdGVkIFR5cGVzIHNpbmtzLiBQcm9wZXJ0aWVzIGFwcGx5aW5nIHRvIGFsbFxuICogdGFncyB1c2UgJyonLlxuICpcbiAqIEV4dHJhY3RlZCBmcm9tLCBhbmQgc2hvdWxkIGJlIGtlcHQgaW4gc3luYyB3aXRoXG4gKiBodHRwczovL3czYy5naXRodWIuaW8vd2ViYXBwc2VjLXRydXN0ZWQtdHlwZXMvZGlzdC9zcGVjLyNpbnRlZ3JhdGlvbnNcbiAqL1xuY29uc3QgVFJVU1RFRF9UWVBFU19TSU5LUyA9IG5ldyBTZXQ8c3RyaW5nPihbXG4gIC8vIE5PVEU6IEFsbCBzdHJpbmdzIGluIHRoaXMgc2V0ICptdXN0KiBiZSBsb3dlcmNhc2UhXG5cbiAgLy8gVHJ1c3RlZEhUTUxcbiAgJ2lmcmFtZXxzcmNkb2MnLFxuICAnKnxpbm5lcmh0bWwnLFxuICAnKnxvdXRlcmh0bWwnLFxuXG4gIC8vIE5COiBubyBUcnVzdGVkU2NyaXB0IGhlcmUsIGFzIHRoZSBjb3JyZXNwb25kaW5nIHRhZ3MgYXJlIHN0cmlwcGVkIGJ5IHRoZSBjb21waWxlci5cblxuICAvLyBUcnVzdGVkU2NyaXB0VVJMXG4gICdlbWJlZHxzcmMnLFxuICAnb2JqZWN0fGNvZGViYXNlJyxcbiAgJ29iamVjdHxkYXRhJyxcbl0pO1xuXG4vKipcbiAqIGlzVHJ1c3RlZFR5cGVzU2luayByZXR1cm5zIHRydWUgaWYgdGhlIGdpdmVuIHByb3BlcnR5IG9uIHRoZSBnaXZlbiBET00gdGFnIGlzIGEgVHJ1c3RlZCBUeXBlc1xuICogc2luay4gSW4gdGhhdCBjYXNlLCB1c2UgYEVsZW1lbnRTY2hlbWFSZWdpc3RyeS5zZWN1cml0eUNvbnRleHRgIHRvIGRldGVybWluZSB3aGljaCBwYXJ0aWN1bGFyXG4gKiBUcnVzdGVkIFR5cGUgaXMgcmVxdWlyZWQgZm9yIHZhbHVlcyBwYXNzZWQgdG8gdGhlIHNpbms6XG4gKiAtIFNlY3VyaXR5Q29udGV4dC5IVE1MIGNvcnJlc3BvbmRzIHRvIFRydXN0ZWRIVE1MXG4gKiAtIFNlY3VyaXR5Q29udGV4dC5SRVNPVVJDRV9VUkwgY29ycmVzcG9uZHMgdG8gVHJ1c3RlZFNjcmlwdFVSTFxuICovXG5leHBvcnQgZnVuY3Rpb24gaXNUcnVzdGVkVHlwZXNTaW5rKHRhZ05hbWU6IHN0cmluZywgcHJvcE5hbWU6IHN0cmluZyk6IGJvb2xlYW4ge1xuICAvLyBNYWtlIHN1cmUgY29tcGFyaXNvbnMgYXJlIGNhc2UgaW5zZW5zaXRpdmUsIHNvIHRoYXQgY2FzZSBkaWZmZXJlbmNlcyBiZXR3ZWVuIGF0dHJpYnV0ZSBhbmRcbiAgLy8gcHJvcGVydHkgbmFtZXMgZG8gbm90IGhhdmUgYSBzZWN1cml0eSBpbXBhY3QuXG4gIHRhZ05hbWUgPSB0YWdOYW1lLnRvTG93ZXJDYXNlKCk7XG4gIHByb3BOYW1lID0gcHJvcE5hbWUudG9Mb3dlckNhc2UoKTtcblxuICByZXR1cm4gVFJVU1RFRF9UWVBFU19TSU5LUy5oYXModGFnTmFtZSArICd8JyArIHByb3BOYW1lKSB8fFxuICAgICAgVFJVU1RFRF9UWVBFU19TSU5LUy5oYXMoJyp8JyArIHByb3BOYW1lKTtcbn1cbiJdfQ==