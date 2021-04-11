/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
/**
 * A pattern that recognizes a commonly useful subset of URLs that are safe.
 *
 * This regular expression matches a subset of URLs that will not cause script
 * execution if used in URL context within a HTML document. Specifically, this
 * regular expression matches if (comment from here on and regex copied from
 * Soy's EscapingConventions):
 * (1) Either an allowed protocol (http, https, mailto or ftp).
 * (2) or no protocol.  A protocol must be followed by a colon. The below
 *     allows that by allowing colons only after one of the characters [/?#].
 *     A colon after a hash (#) must be in the fragment.
 *     Otherwise, a colon after a (?) must be in a query.
 *     Otherwise, a colon after a single solidus (/) must be in a path.
 *     Otherwise, a colon after a double solidus (//) must be in the authority
 *     (before port).
 *
 * The pattern disallows &, used in HTML entity declarations before
 * one of the characters in [/?#]. This disallows HTML entities used in the
 * protocol name, which should never happen, e.g. "h&#116;tp" for "http".
 * It also disallows HTML entities in the first path part of a relative path,
 * e.g. "foo&lt;bar/baz".  Our existing escaping functions should not produce
 * that. More importantly, it disallows masking of a colon,
 * e.g. "javascript&#58;...".
 *
 * This regular expression was taken from the Closure sanitization library.
 */
const SAFE_URL_PATTERN = /^(?:(?:https?|mailto|ftp|tel|file|sms):|[^&:/?#]*(?:[/?#]|$))/gi;
/* A pattern that matches safe srcset values */
const SAFE_SRCSET_PATTERN = /^(?:(?:https?|file):|[^&:/?#]*(?:[/?#]|$))/gi;
/** A pattern that matches safe data URLs. Only matches image, video and audio types. */
const DATA_URL_PATTERN = /^data:(?:image\/(?:bmp|gif|jpeg|jpg|png|tiff|webp)|video\/(?:mpeg|mp4|ogg|webm)|audio\/(?:mp3|oga|ogg|opus));base64,[a-z0-9+\/]+=*$/i;
export function _sanitizeUrl(url) {
    url = String(url);
    if (url.match(SAFE_URL_PATTERN) || url.match(DATA_URL_PATTERN))
        return url;
    if (typeof ngDevMode === 'undefined' || ngDevMode) {
        console.warn(`WARNING: sanitizing unsafe URL value ${url} (see https://g.co/ng/security#xss)`);
    }
    return 'unsafe:' + url;
}
export function sanitizeSrcset(srcset) {
    srcset = String(srcset);
    return srcset.split(',').map((srcset) => _sanitizeUrl(srcset.trim())).join(', ');
}
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoidXJsX3Nhbml0aXplci5qcyIsInNvdXJjZVJvb3QiOiIiLCJzb3VyY2VzIjpbIi4uLy4uLy4uLy4uLy4uLy4uLy4uL3BhY2thZ2VzL2NvcmUvc3JjL3Nhbml0aXphdGlvbi91cmxfc2FuaXRpemVyLnRzIl0sIm5hbWVzIjpbXSwibWFwcGluZ3MiOiJBQUFBOzs7Ozs7R0FNRztBQUdIOzs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7O0dBeUJHO0FBQ0gsTUFBTSxnQkFBZ0IsR0FBRyxpRUFBaUUsQ0FBQztBQUUzRiwrQ0FBK0M7QUFDL0MsTUFBTSxtQkFBbUIsR0FBRyw4Q0FBOEMsQ0FBQztBQUUzRSx3RkFBd0Y7QUFDeEYsTUFBTSxnQkFBZ0IsR0FDbEIsc0lBQXNJLENBQUM7QUFFM0ksTUFBTSxVQUFVLFlBQVksQ0FBQyxHQUFXO0lBQ3RDLEdBQUcsR0FBRyxNQUFNLENBQUMsR0FBRyxDQUFDLENBQUM7SUFDbEIsSUFBSSxHQUFHLENBQUMsS0FBSyxDQUFDLGdCQUFnQixDQUFDLElBQUksR0FBRyxDQUFDLEtBQUssQ0FBQyxnQkFBZ0IsQ0FBQztRQUFFLE9BQU8sR0FBRyxDQUFDO0lBRTNFLElBQUksT0FBTyxTQUFTLEtBQUssV0FBVyxJQUFJLFNBQVMsRUFBRTtRQUNqRCxPQUFPLENBQUMsSUFBSSxDQUFDLHdDQUF3QyxHQUFHLHFDQUFxQyxDQUFDLENBQUM7S0FDaEc7SUFFRCxPQUFPLFNBQVMsR0FBRyxHQUFHLENBQUM7QUFDekIsQ0FBQztBQUVELE1BQU0sVUFBVSxjQUFjLENBQUMsTUFBYztJQUMzQyxNQUFNLEdBQUcsTUFBTSxDQUFDLE1BQU0sQ0FBQyxDQUFDO0lBQ3hCLE9BQU8sTUFBTSxDQUFDLEtBQUssQ0FBQyxHQUFHLENBQUMsQ0FBQyxHQUFHLENBQUMsQ0FBQyxNQUFNLEVBQUUsRUFBRSxDQUFDLFlBQVksQ0FBQyxNQUFNLENBQUMsSUFBSSxFQUFFLENBQUMsQ0FBQyxDQUFDLElBQUksQ0FBQyxJQUFJLENBQUMsQ0FBQztBQUNuRixDQUFDIiwic291cmNlc0NvbnRlbnQiOlsiLyoqXG4gKiBAbGljZW5zZVxuICogQ29weXJpZ2h0IEdvb2dsZSBMTEMgQWxsIFJpZ2h0cyBSZXNlcnZlZC5cbiAqXG4gKiBVc2Ugb2YgdGhpcyBzb3VyY2UgY29kZSBpcyBnb3Zlcm5lZCBieSBhbiBNSVQtc3R5bGUgbGljZW5zZSB0aGF0IGNhbiBiZVxuICogZm91bmQgaW4gdGhlIExJQ0VOU0UgZmlsZSBhdCBodHRwczovL2FuZ3VsYXIuaW8vbGljZW5zZVxuICovXG5cblxuLyoqXG4gKiBBIHBhdHRlcm4gdGhhdCByZWNvZ25pemVzIGEgY29tbW9ubHkgdXNlZnVsIHN1YnNldCBvZiBVUkxzIHRoYXQgYXJlIHNhZmUuXG4gKlxuICogVGhpcyByZWd1bGFyIGV4cHJlc3Npb24gbWF0Y2hlcyBhIHN1YnNldCBvZiBVUkxzIHRoYXQgd2lsbCBub3QgY2F1c2Ugc2NyaXB0XG4gKiBleGVjdXRpb24gaWYgdXNlZCBpbiBVUkwgY29udGV4dCB3aXRoaW4gYSBIVE1MIGRvY3VtZW50LiBTcGVjaWZpY2FsbHksIHRoaXNcbiAqIHJlZ3VsYXIgZXhwcmVzc2lvbiBtYXRjaGVzIGlmIChjb21tZW50IGZyb20gaGVyZSBvbiBhbmQgcmVnZXggY29waWVkIGZyb21cbiAqIFNveSdzIEVzY2FwaW5nQ29udmVudGlvbnMpOlxuICogKDEpIEVpdGhlciBhbiBhbGxvd2VkIHByb3RvY29sIChodHRwLCBodHRwcywgbWFpbHRvIG9yIGZ0cCkuXG4gKiAoMikgb3Igbm8gcHJvdG9jb2wuICBBIHByb3RvY29sIG11c3QgYmUgZm9sbG93ZWQgYnkgYSBjb2xvbi4gVGhlIGJlbG93XG4gKiAgICAgYWxsb3dzIHRoYXQgYnkgYWxsb3dpbmcgY29sb25zIG9ubHkgYWZ0ZXIgb25lIG9mIHRoZSBjaGFyYWN0ZXJzIFsvPyNdLlxuICogICAgIEEgY29sb24gYWZ0ZXIgYSBoYXNoICgjKSBtdXN0IGJlIGluIHRoZSBmcmFnbWVudC5cbiAqICAgICBPdGhlcndpc2UsIGEgY29sb24gYWZ0ZXIgYSAoPykgbXVzdCBiZSBpbiBhIHF1ZXJ5LlxuICogICAgIE90aGVyd2lzZSwgYSBjb2xvbiBhZnRlciBhIHNpbmdsZSBzb2xpZHVzICgvKSBtdXN0IGJlIGluIGEgcGF0aC5cbiAqICAgICBPdGhlcndpc2UsIGEgY29sb24gYWZ0ZXIgYSBkb3VibGUgc29saWR1cyAoLy8pIG11c3QgYmUgaW4gdGhlIGF1dGhvcml0eVxuICogICAgIChiZWZvcmUgcG9ydCkuXG4gKlxuICogVGhlIHBhdHRlcm4gZGlzYWxsb3dzICYsIHVzZWQgaW4gSFRNTCBlbnRpdHkgZGVjbGFyYXRpb25zIGJlZm9yZVxuICogb25lIG9mIHRoZSBjaGFyYWN0ZXJzIGluIFsvPyNdLiBUaGlzIGRpc2FsbG93cyBIVE1MIGVudGl0aWVzIHVzZWQgaW4gdGhlXG4gKiBwcm90b2NvbCBuYW1lLCB3aGljaCBzaG91bGQgbmV2ZXIgaGFwcGVuLCBlLmcuIFwiaCYjMTE2O3RwXCIgZm9yIFwiaHR0cFwiLlxuICogSXQgYWxzbyBkaXNhbGxvd3MgSFRNTCBlbnRpdGllcyBpbiB0aGUgZmlyc3QgcGF0aCBwYXJ0IG9mIGEgcmVsYXRpdmUgcGF0aCxcbiAqIGUuZy4gXCJmb28mbHQ7YmFyL2JhelwiLiAgT3VyIGV4aXN0aW5nIGVzY2FwaW5nIGZ1bmN0aW9ucyBzaG91bGQgbm90IHByb2R1Y2VcbiAqIHRoYXQuIE1vcmUgaW1wb3J0YW50bHksIGl0IGRpc2FsbG93cyBtYXNraW5nIG9mIGEgY29sb24sXG4gKiBlLmcuIFwiamF2YXNjcmlwdCYjNTg7Li4uXCIuXG4gKlxuICogVGhpcyByZWd1bGFyIGV4cHJlc3Npb24gd2FzIHRha2VuIGZyb20gdGhlIENsb3N1cmUgc2FuaXRpemF0aW9uIGxpYnJhcnkuXG4gKi9cbmNvbnN0IFNBRkVfVVJMX1BBVFRFUk4gPSAvXig/Oig/Omh0dHBzP3xtYWlsdG98ZnRwfHRlbHxmaWxlfHNtcyk6fFteJjovPyNdKig/OlsvPyNdfCQpKS9naTtcblxuLyogQSBwYXR0ZXJuIHRoYXQgbWF0Y2hlcyBzYWZlIHNyY3NldCB2YWx1ZXMgKi9cbmNvbnN0IFNBRkVfU1JDU0VUX1BBVFRFUk4gPSAvXig/Oig/Omh0dHBzP3xmaWxlKTp8W14mOi8/I10qKD86Wy8/I118JCkpL2dpO1xuXG4vKiogQSBwYXR0ZXJuIHRoYXQgbWF0Y2hlcyBzYWZlIGRhdGEgVVJMcy4gT25seSBtYXRjaGVzIGltYWdlLCB2aWRlbyBhbmQgYXVkaW8gdHlwZXMuICovXG5jb25zdCBEQVRBX1VSTF9QQVRURVJOID1cbiAgICAvXmRhdGE6KD86aW1hZ2VcXC8oPzpibXB8Z2lmfGpwZWd8anBnfHBuZ3x0aWZmfHdlYnApfHZpZGVvXFwvKD86bXBlZ3xtcDR8b2dnfHdlYm0pfGF1ZGlvXFwvKD86bXAzfG9nYXxvZ2d8b3B1cykpO2Jhc2U2NCxbYS16MC05K1xcL10rPSokL2k7XG5cbmV4cG9ydCBmdW5jdGlvbiBfc2FuaXRpemVVcmwodXJsOiBzdHJpbmcpOiBzdHJpbmcge1xuICB1cmwgPSBTdHJpbmcodXJsKTtcbiAgaWYgKHVybC5tYXRjaChTQUZFX1VSTF9QQVRURVJOKSB8fCB1cmwubWF0Y2goREFUQV9VUkxfUEFUVEVSTikpIHJldHVybiB1cmw7XG5cbiAgaWYgKHR5cGVvZiBuZ0Rldk1vZGUgPT09ICd1bmRlZmluZWQnIHx8IG5nRGV2TW9kZSkge1xuICAgIGNvbnNvbGUud2FybihgV0FSTklORzogc2FuaXRpemluZyB1bnNhZmUgVVJMIHZhbHVlICR7dXJsfSAoc2VlIGh0dHBzOi8vZy5jby9uZy9zZWN1cml0eSN4c3MpYCk7XG4gIH1cblxuICByZXR1cm4gJ3Vuc2FmZTonICsgdXJsO1xufVxuXG5leHBvcnQgZnVuY3Rpb24gc2FuaXRpemVTcmNzZXQoc3Jjc2V0OiBzdHJpbmcpOiBzdHJpbmcge1xuICBzcmNzZXQgPSBTdHJpbmcoc3Jjc2V0KTtcbiAgcmV0dXJuIHNyY3NldC5zcGxpdCgnLCcpLm1hcCgoc3Jjc2V0KSA9PiBfc2FuaXRpemVVcmwoc3Jjc2V0LnRyaW0oKSkpLmpvaW4oJywgJyk7XG59XG4iXX0=