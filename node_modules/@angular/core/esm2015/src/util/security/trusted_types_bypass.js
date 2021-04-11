/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
/**
 * @fileoverview
 * A module to facilitate use of a Trusted Types policy internally within
 * Angular specifically for bypassSecurityTrust* and custom sanitizers. It
 * lazily constructs the Trusted Types policy, providing helper utilities for
 * promoting strings to Trusted Types. When Trusted Types are not available,
 * strings are used as a fallback.
 * @security All use of this module is security-sensitive and should go through
 * security review.
 */
import { global } from '../global';
/**
 * The Trusted Types policy, or null if Trusted Types are not
 * enabled/supported, or undefined if the policy has not been created yet.
 */
let policy;
/**
 * Returns the Trusted Types policy, or null if Trusted Types are not
 * enabled/supported. The first call to this function will create the policy.
 */
function getPolicy() {
    if (policy === undefined) {
        policy = null;
        if (global.trustedTypes) {
            try {
                policy = global.trustedTypes
                    .createPolicy('angular#unsafe-bypass', {
                    createHTML: (s) => s,
                    createScript: (s) => s,
                    createScriptURL: (s) => s,
                });
            }
            catch (_a) {
                // trustedTypes.createPolicy throws if called with a name that is
                // already registered, even in report-only mode. Until the API changes,
                // catch the error not to break the applications functionally. In such
                // cases, the code will fall back to using strings.
            }
        }
    }
    return policy;
}
/**
 * Unsafely promote a string to a TrustedHTML, falling back to strings when
 * Trusted Types are not available.
 * @security This is a security-sensitive function; any use of this function
 * must go through security review. In particular, it must be assured that it
 * is only passed strings that come directly from custom sanitizers or the
 * bypassSecurityTrust* functions.
 */
export function trustedHTMLFromStringBypass(html) {
    var _a;
    return ((_a = getPolicy()) === null || _a === void 0 ? void 0 : _a.createHTML(html)) || html;
}
/**
 * Unsafely promote a string to a TrustedScript, falling back to strings when
 * Trusted Types are not available.
 * @security This is a security-sensitive function; any use of this function
 * must go through security review. In particular, it must be assured that it
 * is only passed strings that come directly from custom sanitizers or the
 * bypassSecurityTrust* functions.
 */
export function trustedScriptFromStringBypass(script) {
    var _a;
    return ((_a = getPolicy()) === null || _a === void 0 ? void 0 : _a.createScript(script)) || script;
}
/**
 * Unsafely promote a string to a TrustedScriptURL, falling back to strings
 * when Trusted Types are not available.
 * @security This is a security-sensitive function; any use of this function
 * must go through security review. In particular, it must be assured that it
 * is only passed strings that come directly from custom sanitizers or the
 * bypassSecurityTrust* functions.
 */
export function trustedScriptURLFromStringBypass(url) {
    var _a;
    return ((_a = getPolicy()) === null || _a === void 0 ? void 0 : _a.createScriptURL(url)) || url;
}
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoidHJ1c3RlZF90eXBlc19ieXBhc3MuanMiLCJzb3VyY2VSb290IjoiIiwic291cmNlcyI6WyIuLi8uLi8uLi8uLi8uLi8uLi8uLi8uLi9wYWNrYWdlcy9jb3JlL3NyYy91dGlsL3NlY3VyaXR5L3RydXN0ZWRfdHlwZXNfYnlwYXNzLnRzIl0sIm5hbWVzIjpbXSwibWFwcGluZ3MiOiJBQUFBOzs7Ozs7R0FNRztBQUVIOzs7Ozs7Ozs7R0FTRztBQUVILE9BQU8sRUFBQyxNQUFNLEVBQUMsTUFBTSxXQUFXLENBQUM7QUFHakM7OztHQUdHO0FBQ0gsSUFBSSxNQUF3QyxDQUFDO0FBRTdDOzs7R0FHRztBQUNILFNBQVMsU0FBUztJQUNoQixJQUFJLE1BQU0sS0FBSyxTQUFTLEVBQUU7UUFDeEIsTUFBTSxHQUFHLElBQUksQ0FBQztRQUNkLElBQUksTUFBTSxDQUFDLFlBQVksRUFBRTtZQUN2QixJQUFJO2dCQUNGLE1BQU0sR0FBSSxNQUFNLENBQUMsWUFBeUM7cUJBQzVDLFlBQVksQ0FBQyx1QkFBdUIsRUFBRTtvQkFDckMsVUFBVSxFQUFFLENBQUMsQ0FBUyxFQUFFLEVBQUUsQ0FBQyxDQUFDO29CQUM1QixZQUFZLEVBQUUsQ0FBQyxDQUFTLEVBQUUsRUFBRSxDQUFDLENBQUM7b0JBQzlCLGVBQWUsRUFBRSxDQUFDLENBQVMsRUFBRSxFQUFFLENBQUMsQ0FBQztpQkFDbEMsQ0FBQyxDQUFDO2FBQ2pCO1lBQUMsV0FBTTtnQkFDTixpRUFBaUU7Z0JBQ2pFLHVFQUF1RTtnQkFDdkUsc0VBQXNFO2dCQUN0RSxtREFBbUQ7YUFDcEQ7U0FDRjtLQUNGO0lBQ0QsT0FBTyxNQUFNLENBQUM7QUFDaEIsQ0FBQztBQUVEOzs7Ozs7O0dBT0c7QUFDSCxNQUFNLFVBQVUsMkJBQTJCLENBQUMsSUFBWTs7SUFDdEQsT0FBTyxPQUFBLFNBQVMsRUFBRSwwQ0FBRSxVQUFVLENBQUMsSUFBSSxNQUFLLElBQUksQ0FBQztBQUMvQyxDQUFDO0FBRUQ7Ozs7Ozs7R0FPRztBQUNILE1BQU0sVUFBVSw2QkFBNkIsQ0FBQyxNQUFjOztJQUMxRCxPQUFPLE9BQUEsU0FBUyxFQUFFLDBDQUFFLFlBQVksQ0FBQyxNQUFNLE1BQUssTUFBTSxDQUFDO0FBQ3JELENBQUM7QUFFRDs7Ozs7OztHQU9HO0FBQ0gsTUFBTSxVQUFVLGdDQUFnQyxDQUFDLEdBQVc7O0lBQzFELE9BQU8sT0FBQSxTQUFTLEVBQUUsMENBQUUsZUFBZSxDQUFDLEdBQUcsTUFBSyxHQUFHLENBQUM7QUFDbEQsQ0FBQyIsInNvdXJjZXNDb250ZW50IjpbIi8qKlxuICogQGxpY2Vuc2VcbiAqIENvcHlyaWdodCBHb29nbGUgTExDIEFsbCBSaWdodHMgUmVzZXJ2ZWQuXG4gKlxuICogVXNlIG9mIHRoaXMgc291cmNlIGNvZGUgaXMgZ292ZXJuZWQgYnkgYW4gTUlULXN0eWxlIGxpY2Vuc2UgdGhhdCBjYW4gYmVcbiAqIGZvdW5kIGluIHRoZSBMSUNFTlNFIGZpbGUgYXQgaHR0cHM6Ly9hbmd1bGFyLmlvL2xpY2Vuc2VcbiAqL1xuXG4vKipcbiAqIEBmaWxlb3ZlcnZpZXdcbiAqIEEgbW9kdWxlIHRvIGZhY2lsaXRhdGUgdXNlIG9mIGEgVHJ1c3RlZCBUeXBlcyBwb2xpY3kgaW50ZXJuYWxseSB3aXRoaW5cbiAqIEFuZ3VsYXIgc3BlY2lmaWNhbGx5IGZvciBieXBhc3NTZWN1cml0eVRydXN0KiBhbmQgY3VzdG9tIHNhbml0aXplcnMuIEl0XG4gKiBsYXppbHkgY29uc3RydWN0cyB0aGUgVHJ1c3RlZCBUeXBlcyBwb2xpY3ksIHByb3ZpZGluZyBoZWxwZXIgdXRpbGl0aWVzIGZvclxuICogcHJvbW90aW5nIHN0cmluZ3MgdG8gVHJ1c3RlZCBUeXBlcy4gV2hlbiBUcnVzdGVkIFR5cGVzIGFyZSBub3QgYXZhaWxhYmxlLFxuICogc3RyaW5ncyBhcmUgdXNlZCBhcyBhIGZhbGxiYWNrLlxuICogQHNlY3VyaXR5IEFsbCB1c2Ugb2YgdGhpcyBtb2R1bGUgaXMgc2VjdXJpdHktc2Vuc2l0aXZlIGFuZCBzaG91bGQgZ28gdGhyb3VnaFxuICogc2VjdXJpdHkgcmV2aWV3LlxuICovXG5cbmltcG9ydCB7Z2xvYmFsfSBmcm9tICcuLi9nbG9iYWwnO1xuaW1wb3J0IHtUcnVzdGVkSFRNTCwgVHJ1c3RlZFNjcmlwdCwgVHJ1c3RlZFNjcmlwdFVSTCwgVHJ1c3RlZFR5cGVQb2xpY3ksIFRydXN0ZWRUeXBlUG9saWN5RmFjdG9yeX0gZnJvbSAnLi90cnVzdGVkX3R5cGVfZGVmcyc7XG5cbi8qKlxuICogVGhlIFRydXN0ZWQgVHlwZXMgcG9saWN5LCBvciBudWxsIGlmIFRydXN0ZWQgVHlwZXMgYXJlIG5vdFxuICogZW5hYmxlZC9zdXBwb3J0ZWQsIG9yIHVuZGVmaW5lZCBpZiB0aGUgcG9saWN5IGhhcyBub3QgYmVlbiBjcmVhdGVkIHlldC5cbiAqL1xubGV0IHBvbGljeTogVHJ1c3RlZFR5cGVQb2xpY3l8bnVsbHx1bmRlZmluZWQ7XG5cbi8qKlxuICogUmV0dXJucyB0aGUgVHJ1c3RlZCBUeXBlcyBwb2xpY3ksIG9yIG51bGwgaWYgVHJ1c3RlZCBUeXBlcyBhcmUgbm90XG4gKiBlbmFibGVkL3N1cHBvcnRlZC4gVGhlIGZpcnN0IGNhbGwgdG8gdGhpcyBmdW5jdGlvbiB3aWxsIGNyZWF0ZSB0aGUgcG9saWN5LlxuICovXG5mdW5jdGlvbiBnZXRQb2xpY3koKTogVHJ1c3RlZFR5cGVQb2xpY3l8bnVsbCB7XG4gIGlmIChwb2xpY3kgPT09IHVuZGVmaW5lZCkge1xuICAgIHBvbGljeSA9IG51bGw7XG4gICAgaWYgKGdsb2JhbC50cnVzdGVkVHlwZXMpIHtcbiAgICAgIHRyeSB7XG4gICAgICAgIHBvbGljeSA9IChnbG9iYWwudHJ1c3RlZFR5cGVzIGFzIFRydXN0ZWRUeXBlUG9saWN5RmFjdG9yeSlcbiAgICAgICAgICAgICAgICAgICAgIC5jcmVhdGVQb2xpY3koJ2FuZ3VsYXIjdW5zYWZlLWJ5cGFzcycsIHtcbiAgICAgICAgICAgICAgICAgICAgICAgY3JlYXRlSFRNTDogKHM6IHN0cmluZykgPT4gcyxcbiAgICAgICAgICAgICAgICAgICAgICAgY3JlYXRlU2NyaXB0OiAoczogc3RyaW5nKSA9PiBzLFxuICAgICAgICAgICAgICAgICAgICAgICBjcmVhdGVTY3JpcHRVUkw6IChzOiBzdHJpbmcpID0+IHMsXG4gICAgICAgICAgICAgICAgICAgICB9KTtcbiAgICAgIH0gY2F0Y2gge1xuICAgICAgICAvLyB0cnVzdGVkVHlwZXMuY3JlYXRlUG9saWN5IHRocm93cyBpZiBjYWxsZWQgd2l0aCBhIG5hbWUgdGhhdCBpc1xuICAgICAgICAvLyBhbHJlYWR5IHJlZ2lzdGVyZWQsIGV2ZW4gaW4gcmVwb3J0LW9ubHkgbW9kZS4gVW50aWwgdGhlIEFQSSBjaGFuZ2VzLFxuICAgICAgICAvLyBjYXRjaCB0aGUgZXJyb3Igbm90IHRvIGJyZWFrIHRoZSBhcHBsaWNhdGlvbnMgZnVuY3Rpb25hbGx5LiBJbiBzdWNoXG4gICAgICAgIC8vIGNhc2VzLCB0aGUgY29kZSB3aWxsIGZhbGwgYmFjayB0byB1c2luZyBzdHJpbmdzLlxuICAgICAgfVxuICAgIH1cbiAgfVxuICByZXR1cm4gcG9saWN5O1xufVxuXG4vKipcbiAqIFVuc2FmZWx5IHByb21vdGUgYSBzdHJpbmcgdG8gYSBUcnVzdGVkSFRNTCwgZmFsbGluZyBiYWNrIHRvIHN0cmluZ3Mgd2hlblxuICogVHJ1c3RlZCBUeXBlcyBhcmUgbm90IGF2YWlsYWJsZS5cbiAqIEBzZWN1cml0eSBUaGlzIGlzIGEgc2VjdXJpdHktc2Vuc2l0aXZlIGZ1bmN0aW9uOyBhbnkgdXNlIG9mIHRoaXMgZnVuY3Rpb25cbiAqIG11c3QgZ28gdGhyb3VnaCBzZWN1cml0eSByZXZpZXcuIEluIHBhcnRpY3VsYXIsIGl0IG11c3QgYmUgYXNzdXJlZCB0aGF0IGl0XG4gKiBpcyBvbmx5IHBhc3NlZCBzdHJpbmdzIHRoYXQgY29tZSBkaXJlY3RseSBmcm9tIGN1c3RvbSBzYW5pdGl6ZXJzIG9yIHRoZVxuICogYnlwYXNzU2VjdXJpdHlUcnVzdCogZnVuY3Rpb25zLlxuICovXG5leHBvcnQgZnVuY3Rpb24gdHJ1c3RlZEhUTUxGcm9tU3RyaW5nQnlwYXNzKGh0bWw6IHN0cmluZyk6IFRydXN0ZWRIVE1MfHN0cmluZyB7XG4gIHJldHVybiBnZXRQb2xpY3koKT8uY3JlYXRlSFRNTChodG1sKSB8fCBodG1sO1xufVxuXG4vKipcbiAqIFVuc2FmZWx5IHByb21vdGUgYSBzdHJpbmcgdG8gYSBUcnVzdGVkU2NyaXB0LCBmYWxsaW5nIGJhY2sgdG8gc3RyaW5ncyB3aGVuXG4gKiBUcnVzdGVkIFR5cGVzIGFyZSBub3QgYXZhaWxhYmxlLlxuICogQHNlY3VyaXR5IFRoaXMgaXMgYSBzZWN1cml0eS1zZW5zaXRpdmUgZnVuY3Rpb247IGFueSB1c2Ugb2YgdGhpcyBmdW5jdGlvblxuICogbXVzdCBnbyB0aHJvdWdoIHNlY3VyaXR5IHJldmlldy4gSW4gcGFydGljdWxhciwgaXQgbXVzdCBiZSBhc3N1cmVkIHRoYXQgaXRcbiAqIGlzIG9ubHkgcGFzc2VkIHN0cmluZ3MgdGhhdCBjb21lIGRpcmVjdGx5IGZyb20gY3VzdG9tIHNhbml0aXplcnMgb3IgdGhlXG4gKiBieXBhc3NTZWN1cml0eVRydXN0KiBmdW5jdGlvbnMuXG4gKi9cbmV4cG9ydCBmdW5jdGlvbiB0cnVzdGVkU2NyaXB0RnJvbVN0cmluZ0J5cGFzcyhzY3JpcHQ6IHN0cmluZyk6IFRydXN0ZWRTY3JpcHR8c3RyaW5nIHtcbiAgcmV0dXJuIGdldFBvbGljeSgpPy5jcmVhdGVTY3JpcHQoc2NyaXB0KSB8fCBzY3JpcHQ7XG59XG5cbi8qKlxuICogVW5zYWZlbHkgcHJvbW90ZSBhIHN0cmluZyB0byBhIFRydXN0ZWRTY3JpcHRVUkwsIGZhbGxpbmcgYmFjayB0byBzdHJpbmdzXG4gKiB3aGVuIFRydXN0ZWQgVHlwZXMgYXJlIG5vdCBhdmFpbGFibGUuXG4gKiBAc2VjdXJpdHkgVGhpcyBpcyBhIHNlY3VyaXR5LXNlbnNpdGl2ZSBmdW5jdGlvbjsgYW55IHVzZSBvZiB0aGlzIGZ1bmN0aW9uXG4gKiBtdXN0IGdvIHRocm91Z2ggc2VjdXJpdHkgcmV2aWV3LiBJbiBwYXJ0aWN1bGFyLCBpdCBtdXN0IGJlIGFzc3VyZWQgdGhhdCBpdFxuICogaXMgb25seSBwYXNzZWQgc3RyaW5ncyB0aGF0IGNvbWUgZGlyZWN0bHkgZnJvbSBjdXN0b20gc2FuaXRpemVycyBvciB0aGVcbiAqIGJ5cGFzc1NlY3VyaXR5VHJ1c3QqIGZ1bmN0aW9ucy5cbiAqL1xuZXhwb3J0IGZ1bmN0aW9uIHRydXN0ZWRTY3JpcHRVUkxGcm9tU3RyaW5nQnlwYXNzKHVybDogc3RyaW5nKTogVHJ1c3RlZFNjcmlwdFVSTHxzdHJpbmcge1xuICByZXR1cm4gZ2V0UG9saWN5KCk/LmNyZWF0ZVNjcmlwdFVSTCh1cmwpIHx8IHVybDtcbn1cbiJdfQ==