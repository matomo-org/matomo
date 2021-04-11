/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
/**
 * Disallowed strings in the comment.
 *
 * see: https://html.spec.whatwg.org/multipage/syntax.html#comments
 */
const COMMENT_DISALLOWED = /^>|^->|<!--|-->|--!>|<!-$/g;
/**
 * Delimiter in the disallowed strings which needs to be wrapped with zero with character.
 */
const COMMENT_DELIMITER = /(<|>)/;
const COMMENT_DELIMITER_ESCAPED = '\u200B$1\u200B';
/**
 * Escape the content of comment strings so that it can be safely inserted into a comment node.
 *
 * The issue is that HTML does not specify any way to escape comment end text inside the comment.
 * Consider: `<!-- The way you close a comment is with ">", and "->" at the beginning or by "-->" or
 * "--!>" at the end. -->`. Above the `"-->"` is meant to be text not an end to the comment. This
 * can be created programmatically through DOM APIs. (`<!--` are also disallowed.)
 *
 * see: https://html.spec.whatwg.org/multipage/syntax.html#comments
 *
 * ```
 * div.innerHTML = div.innerHTML
 * ```
 *
 * One would expect that the above code would be safe to do, but it turns out that because comment
 * text is not escaped, the comment may contain text which will prematurely close the comment
 * opening up the application for XSS attack. (In SSR we programmatically create comment nodes which
 * may contain such text and expect them to be safe.)
 *
 * This function escapes the comment text by looking for comment delimiters (`<` and `>`) and
 * surrounding them with `_>_` where the `_` is a zero width space `\u200B`. The result is that if a
 * comment contains any of the comment start/end delimiters (such as `<!--`, `-->` or `--!>`) the
 * text it will render normally but it will not cause the HTML parser to close/open the comment.
 *
 * @param value text to make safe for comment node by escaping the comment open/close character
 *     sequence.
 */
export function escapeCommentText(value) {
    return value.replace(COMMENT_DISALLOWED, (text) => text.replace(COMMENT_DELIMITER, COMMENT_DELIMITER_ESCAPED));
}
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiZG9tLmpzIiwic291cmNlUm9vdCI6IiIsInNvdXJjZXMiOlsiLi4vLi4vLi4vLi4vLi4vLi4vLi4vcGFja2FnZXMvY29yZS9zcmMvdXRpbC9kb20udHMiXSwibmFtZXMiOltdLCJtYXBwaW5ncyI6IkFBQUE7Ozs7OztHQU1HO0FBRUg7Ozs7R0FJRztBQUNILE1BQU0sa0JBQWtCLEdBQUcsNEJBQTRCLENBQUM7QUFDeEQ7O0dBRUc7QUFDSCxNQUFNLGlCQUFpQixHQUFHLE9BQU8sQ0FBQztBQUNsQyxNQUFNLHlCQUF5QixHQUFHLGdCQUFnQixDQUFDO0FBRW5EOzs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7OztHQTBCRztBQUNILE1BQU0sVUFBVSxpQkFBaUIsQ0FBQyxLQUFhO0lBQzdDLE9BQU8sS0FBSyxDQUFDLE9BQU8sQ0FDaEIsa0JBQWtCLEVBQUUsQ0FBQyxJQUFJLEVBQUUsRUFBRSxDQUFDLElBQUksQ0FBQyxPQUFPLENBQUMsaUJBQWlCLEVBQUUseUJBQXlCLENBQUMsQ0FBQyxDQUFDO0FBQ2hHLENBQUMiLCJzb3VyY2VzQ29udGVudCI6WyIvKipcbiAqIEBsaWNlbnNlXG4gKiBDb3B5cmlnaHQgR29vZ2xlIExMQyBBbGwgUmlnaHRzIFJlc2VydmVkLlxuICpcbiAqIFVzZSBvZiB0aGlzIHNvdXJjZSBjb2RlIGlzIGdvdmVybmVkIGJ5IGFuIE1JVC1zdHlsZSBsaWNlbnNlIHRoYXQgY2FuIGJlXG4gKiBmb3VuZCBpbiB0aGUgTElDRU5TRSBmaWxlIGF0IGh0dHBzOi8vYW5ndWxhci5pby9saWNlbnNlXG4gKi9cblxuLyoqXG4gKiBEaXNhbGxvd2VkIHN0cmluZ3MgaW4gdGhlIGNvbW1lbnQuXG4gKlxuICogc2VlOiBodHRwczovL2h0bWwuc3BlYy53aGF0d2cub3JnL211bHRpcGFnZS9zeW50YXguaHRtbCNjb21tZW50c1xuICovXG5jb25zdCBDT01NRU5UX0RJU0FMTE9XRUQgPSAvXj58Xi0+fDwhLS18LS0+fC0tIT58PCEtJC9nO1xuLyoqXG4gKiBEZWxpbWl0ZXIgaW4gdGhlIGRpc2FsbG93ZWQgc3RyaW5ncyB3aGljaCBuZWVkcyB0byBiZSB3cmFwcGVkIHdpdGggemVybyB3aXRoIGNoYXJhY3Rlci5cbiAqL1xuY29uc3QgQ09NTUVOVF9ERUxJTUlURVIgPSAvKDx8PikvO1xuY29uc3QgQ09NTUVOVF9ERUxJTUlURVJfRVNDQVBFRCA9ICdcXHUyMDBCJDFcXHUyMDBCJztcblxuLyoqXG4gKiBFc2NhcGUgdGhlIGNvbnRlbnQgb2YgY29tbWVudCBzdHJpbmdzIHNvIHRoYXQgaXQgY2FuIGJlIHNhZmVseSBpbnNlcnRlZCBpbnRvIGEgY29tbWVudCBub2RlLlxuICpcbiAqIFRoZSBpc3N1ZSBpcyB0aGF0IEhUTUwgZG9lcyBub3Qgc3BlY2lmeSBhbnkgd2F5IHRvIGVzY2FwZSBjb21tZW50IGVuZCB0ZXh0IGluc2lkZSB0aGUgY29tbWVudC5cbiAqIENvbnNpZGVyOiBgPCEtLSBUaGUgd2F5IHlvdSBjbG9zZSBhIGNvbW1lbnQgaXMgd2l0aCBcIj5cIiwgYW5kIFwiLT5cIiBhdCB0aGUgYmVnaW5uaW5nIG9yIGJ5IFwiLS0+XCIgb3JcbiAqIFwiLS0hPlwiIGF0IHRoZSBlbmQuIC0tPmAuIEFib3ZlIHRoZSBgXCItLT5cImAgaXMgbWVhbnQgdG8gYmUgdGV4dCBub3QgYW4gZW5kIHRvIHRoZSBjb21tZW50LiBUaGlzXG4gKiBjYW4gYmUgY3JlYXRlZCBwcm9ncmFtbWF0aWNhbGx5IHRocm91Z2ggRE9NIEFQSXMuIChgPCEtLWAgYXJlIGFsc28gZGlzYWxsb3dlZC4pXG4gKlxuICogc2VlOiBodHRwczovL2h0bWwuc3BlYy53aGF0d2cub3JnL211bHRpcGFnZS9zeW50YXguaHRtbCNjb21tZW50c1xuICpcbiAqIGBgYFxuICogZGl2LmlubmVySFRNTCA9IGRpdi5pbm5lckhUTUxcbiAqIGBgYFxuICpcbiAqIE9uZSB3b3VsZCBleHBlY3QgdGhhdCB0aGUgYWJvdmUgY29kZSB3b3VsZCBiZSBzYWZlIHRvIGRvLCBidXQgaXQgdHVybnMgb3V0IHRoYXQgYmVjYXVzZSBjb21tZW50XG4gKiB0ZXh0IGlzIG5vdCBlc2NhcGVkLCB0aGUgY29tbWVudCBtYXkgY29udGFpbiB0ZXh0IHdoaWNoIHdpbGwgcHJlbWF0dXJlbHkgY2xvc2UgdGhlIGNvbW1lbnRcbiAqIG9wZW5pbmcgdXAgdGhlIGFwcGxpY2F0aW9uIGZvciBYU1MgYXR0YWNrLiAoSW4gU1NSIHdlIHByb2dyYW1tYXRpY2FsbHkgY3JlYXRlIGNvbW1lbnQgbm9kZXMgd2hpY2hcbiAqIG1heSBjb250YWluIHN1Y2ggdGV4dCBhbmQgZXhwZWN0IHRoZW0gdG8gYmUgc2FmZS4pXG4gKlxuICogVGhpcyBmdW5jdGlvbiBlc2NhcGVzIHRoZSBjb21tZW50IHRleHQgYnkgbG9va2luZyBmb3IgY29tbWVudCBkZWxpbWl0ZXJzIChgPGAgYW5kIGA+YCkgYW5kXG4gKiBzdXJyb3VuZGluZyB0aGVtIHdpdGggYF8+X2Agd2hlcmUgdGhlIGBfYCBpcyBhIHplcm8gd2lkdGggc3BhY2UgYFxcdTIwMEJgLiBUaGUgcmVzdWx0IGlzIHRoYXQgaWYgYVxuICogY29tbWVudCBjb250YWlucyBhbnkgb2YgdGhlIGNvbW1lbnQgc3RhcnQvZW5kIGRlbGltaXRlcnMgKHN1Y2ggYXMgYDwhLS1gLCBgLS0+YCBvciBgLS0hPmApIHRoZVxuICogdGV4dCBpdCB3aWxsIHJlbmRlciBub3JtYWxseSBidXQgaXQgd2lsbCBub3QgY2F1c2UgdGhlIEhUTUwgcGFyc2VyIHRvIGNsb3NlL29wZW4gdGhlIGNvbW1lbnQuXG4gKlxuICogQHBhcmFtIHZhbHVlIHRleHQgdG8gbWFrZSBzYWZlIGZvciBjb21tZW50IG5vZGUgYnkgZXNjYXBpbmcgdGhlIGNvbW1lbnQgb3Blbi9jbG9zZSBjaGFyYWN0ZXJcbiAqICAgICBzZXF1ZW5jZS5cbiAqL1xuZXhwb3J0IGZ1bmN0aW9uIGVzY2FwZUNvbW1lbnRUZXh0KHZhbHVlOiBzdHJpbmcpOiBzdHJpbmcge1xuICByZXR1cm4gdmFsdWUucmVwbGFjZShcbiAgICAgIENPTU1FTlRfRElTQUxMT1dFRCwgKHRleHQpID0+IHRleHQucmVwbGFjZShDT01NRU5UX0RFTElNSVRFUiwgQ09NTUVOVF9ERUxJTUlURVJfRVNDQVBFRCkpO1xufSJdfQ==