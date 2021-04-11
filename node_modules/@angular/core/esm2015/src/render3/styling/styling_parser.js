/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
import { assertEqual, throwError } from '../../util/assert';
// Global state of the parser. (This makes parser non-reentrant, but that is not an issue)
const parserState = {
    textEnd: 0,
    key: 0,
    keyEnd: 0,
    value: 0,
    valueEnd: 0,
};
/**
 * Retrieves the last parsed `key` of style.
 * @param text the text to substring the key from.
 */
export function getLastParsedKey(text) {
    return text.substring(parserState.key, parserState.keyEnd);
}
/**
 * Retrieves the last parsed `value` of style.
 * @param text the text to substring the key from.
 */
export function getLastParsedValue(text) {
    return text.substring(parserState.value, parserState.valueEnd);
}
/**
 * Initializes `className` string for parsing and parses the first token.
 *
 * This function is intended to be used in this format:
 * ```
 * for (let i = parseClassName(text); i >= 0; i = parseClassNameNext(text, i)) {
 *   const key = getLastParsedKey();
 *   ...
 * }
 * ```
 * @param text `className` to parse
 * @returns index where the next invocation of `parseClassNameNext` should resume.
 */
export function parseClassName(text) {
    resetParserState(text);
    return parseClassNameNext(text, consumeWhitespace(text, 0, parserState.textEnd));
}
/**
 * Parses next `className` token.
 *
 * This function is intended to be used in this format:
 * ```
 * for (let i = parseClassName(text); i >= 0; i = parseClassNameNext(text, i)) {
 *   const key = getLastParsedKey();
 *   ...
 * }
 * ```
 *
 * @param text `className` to parse
 * @param index where the parsing should resume.
 * @returns index where the next invocation of `parseClassNameNext` should resume.
 */
export function parseClassNameNext(text, index) {
    const end = parserState.textEnd;
    if (end === index) {
        return -1;
    }
    index = parserState.keyEnd = consumeClassToken(text, parserState.key = index, end);
    return consumeWhitespace(text, index, end);
}
/**
 * Initializes `cssText` string for parsing and parses the first key/values.
 *
 * This function is intended to be used in this format:
 * ```
 * for (let i = parseStyle(text); i >= 0; i = parseStyleNext(text, i))) {
 *   const key = getLastParsedKey();
 *   const value = getLastParsedValue();
 *   ...
 * }
 * ```
 * @param text `cssText` to parse
 * @returns index where the next invocation of `parseStyleNext` should resume.
 */
export function parseStyle(text) {
    resetParserState(text);
    return parseStyleNext(text, consumeWhitespace(text, 0, parserState.textEnd));
}
/**
 * Parses the next `cssText` key/values.
 *
 * This function is intended to be used in this format:
 * ```
 * for (let i = parseStyle(text); i >= 0; i = parseStyleNext(text, i))) {
 *   const key = getLastParsedKey();
 *   const value = getLastParsedValue();
 *   ...
 * }
 *
 * @param text `cssText` to parse
 * @param index where the parsing should resume.
 * @returns index where the next invocation of `parseStyleNext` should resume.
 */
export function parseStyleNext(text, startIndex) {
    const end = parserState.textEnd;
    let index = parserState.key = consumeWhitespace(text, startIndex, end);
    if (end === index) {
        // we reached an end so just quit
        return -1;
    }
    index = parserState.keyEnd = consumeStyleKey(text, index, end);
    index = consumeSeparator(text, index, end, 58 /* COLON */);
    index = parserState.value = consumeWhitespace(text, index, end);
    index = parserState.valueEnd = consumeStyleValue(text, index, end);
    return consumeSeparator(text, index, end, 59 /* SEMI_COLON */);
}
/**
 * Reset the global state of the styling parser.
 * @param text The styling text to parse.
 */
export function resetParserState(text) {
    parserState.key = 0;
    parserState.keyEnd = 0;
    parserState.value = 0;
    parserState.valueEnd = 0;
    parserState.textEnd = text.length;
}
/**
 * Returns index of next non-whitespace character.
 *
 * @param text Text to scan
 * @param startIndex Starting index of character where the scan should start.
 * @param endIndex Ending index of character where the scan should end.
 * @returns Index of next non-whitespace character (May be the same as `start` if no whitespace at
 *          that location.)
 */
export function consumeWhitespace(text, startIndex, endIndex) {
    while (startIndex < endIndex && text.charCodeAt(startIndex) <= 32 /* SPACE */) {
        startIndex++;
    }
    return startIndex;
}
/**
 * Returns index of last char in class token.
 *
 * @param text Text to scan
 * @param startIndex Starting index of character where the scan should start.
 * @param endIndex Ending index of character where the scan should end.
 * @returns Index after last char in class token.
 */
export function consumeClassToken(text, startIndex, endIndex) {
    while (startIndex < endIndex && text.charCodeAt(startIndex) > 32 /* SPACE */) {
        startIndex++;
    }
    return startIndex;
}
/**
 * Consumes all of the characters belonging to style key and token.
 *
 * @param text Text to scan
 * @param startIndex Starting index of character where the scan should start.
 * @param endIndex Ending index of character where the scan should end.
 * @returns Index after last style key character.
 */
export function consumeStyleKey(text, startIndex, endIndex) {
    let ch;
    while (startIndex < endIndex &&
        ((ch = text.charCodeAt(startIndex)) === 45 /* DASH */ || ch === 95 /* UNDERSCORE */ ||
            ((ch & -33 /* UPPER_CASE */) >= 65 /* A */ && (ch & -33 /* UPPER_CASE */) <= 90 /* Z */) ||
            (ch >= 48 /* ZERO */ && ch <= 57 /* NINE */))) {
        startIndex++;
    }
    return startIndex;
}
/**
 * Consumes all whitespace and the separator `:` after the style key.
 *
 * @param text Text to scan
 * @param startIndex Starting index of character where the scan should start.
 * @param endIndex Ending index of character where the scan should end.
 * @returns Index after separator and surrounding whitespace.
 */
export function consumeSeparator(text, startIndex, endIndex, separator) {
    startIndex = consumeWhitespace(text, startIndex, endIndex);
    if (startIndex < endIndex) {
        if (ngDevMode && text.charCodeAt(startIndex) !== separator) {
            malformedStyleError(text, String.fromCharCode(separator), startIndex);
        }
        startIndex++;
    }
    return startIndex;
}
/**
 * Consumes style value honoring `url()` and `""` text.
 *
 * @param text Text to scan
 * @param startIndex Starting index of character where the scan should start.
 * @param endIndex Ending index of character where the scan should end.
 * @returns Index after last style value character.
 */
export function consumeStyleValue(text, startIndex, endIndex) {
    let ch1 = -1; // 1st previous character
    let ch2 = -1; // 2nd previous character
    let ch3 = -1; // 3rd previous character
    let i = startIndex;
    let lastChIndex = i;
    while (i < endIndex) {
        const ch = text.charCodeAt(i++);
        if (ch === 59 /* SEMI_COLON */) {
            return lastChIndex;
        }
        else if (ch === 34 /* DOUBLE_QUOTE */ || ch === 39 /* SINGLE_QUOTE */) {
            lastChIndex = i = consumeQuotedText(text, ch, i, endIndex);
        }
        else if (startIndex ===
            i - 4 && // We have seen only 4 characters so far "URL(" (Ignore "foo_URL()")
            ch3 === 85 /* U */ &&
            ch2 === 82 /* R */ && ch1 === 76 /* L */ && ch === 40 /* OPEN_PAREN */) {
            lastChIndex = i = consumeQuotedText(text, 41 /* CLOSE_PAREN */, i, endIndex);
        }
        else if (ch > 32 /* SPACE */) {
            // if we have a non-whitespace character then capture its location
            lastChIndex = i;
        }
        ch3 = ch2;
        ch2 = ch1;
        ch1 = ch & -33 /* UPPER_CASE */;
    }
    return lastChIndex;
}
/**
 * Consumes all of the quoted characters.
 *
 * @param text Text to scan
 * @param quoteCharCode CharCode of either `"` or `'` quote or `)` for `url(...)`.
 * @param startIndex Starting index of character where the scan should start.
 * @param endIndex Ending index of character where the scan should end.
 * @returns Index after quoted characters.
 */
export function consumeQuotedText(text, quoteCharCode, startIndex, endIndex) {
    let ch1 = -1; // 1st previous character
    let index = startIndex;
    while (index < endIndex) {
        const ch = text.charCodeAt(index++);
        if (ch == quoteCharCode && ch1 !== 92 /* BACK_SLASH */) {
            return index;
        }
        if (ch == 92 /* BACK_SLASH */ && ch1 === 92 /* BACK_SLASH */) {
            // two back slashes cancel each other out. For example `"\\"` should properly end the
            // quotation. (It should not assume that the last `"` is escaped.)
            ch1 = 0;
        }
        else {
            ch1 = ch;
        }
    }
    throw ngDevMode ? malformedStyleError(text, String.fromCharCode(quoteCharCode), endIndex) :
        new Error();
}
function malformedStyleError(text, expecting, index) {
    ngDevMode && assertEqual(typeof text === 'string', true, 'String expected here');
    throw throwError(`Malformed style at location ${index} in string '` + text.substring(0, index) + '[>>' +
        text.substring(index, index + 1) + '<<]' + text.substr(index + 1) +
        `'. Expecting '${expecting}'.`);
}
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoic3R5bGluZ19wYXJzZXIuanMiLCJzb3VyY2VSb290IjoiIiwic291cmNlcyI6WyIuLi8uLi8uLi8uLi8uLi8uLi8uLi8uLi9wYWNrYWdlcy9jb3JlL3NyYy9yZW5kZXIzL3N0eWxpbmcvc3R5bGluZ19wYXJzZXIudHMiXSwibmFtZXMiOltdLCJtYXBwaW5ncyI6IkFBQUE7Ozs7OztHQU1HO0FBRUgsT0FBTyxFQUFDLFdBQVcsRUFBRSxVQUFVLEVBQUMsTUFBTSxtQkFBbUIsQ0FBQztBQWtDMUQsMEZBQTBGO0FBQzFGLE1BQU0sV0FBVyxHQUFnQjtJQUMvQixPQUFPLEVBQUUsQ0FBQztJQUNWLEdBQUcsRUFBRSxDQUFDO0lBQ04sTUFBTSxFQUFFLENBQUM7SUFDVCxLQUFLLEVBQUUsQ0FBQztJQUNSLFFBQVEsRUFBRSxDQUFDO0NBQ1osQ0FBQztBQUVGOzs7R0FHRztBQUNILE1BQU0sVUFBVSxnQkFBZ0IsQ0FBQyxJQUFZO0lBQzNDLE9BQU8sSUFBSSxDQUFDLFNBQVMsQ0FBQyxXQUFXLENBQUMsR0FBRyxFQUFFLFdBQVcsQ0FBQyxNQUFNLENBQUMsQ0FBQztBQUM3RCxDQUFDO0FBRUQ7OztHQUdHO0FBQ0gsTUFBTSxVQUFVLGtCQUFrQixDQUFDLElBQVk7SUFDN0MsT0FBTyxJQUFJLENBQUMsU0FBUyxDQUFDLFdBQVcsQ0FBQyxLQUFLLEVBQUUsV0FBVyxDQUFDLFFBQVEsQ0FBQyxDQUFDO0FBQ2pFLENBQUM7QUFFRDs7Ozs7Ozs7Ozs7O0dBWUc7QUFDSCxNQUFNLFVBQVUsY0FBYyxDQUFDLElBQVk7SUFDekMsZ0JBQWdCLENBQUMsSUFBSSxDQUFDLENBQUM7SUFDdkIsT0FBTyxrQkFBa0IsQ0FBQyxJQUFJLEVBQUUsaUJBQWlCLENBQUMsSUFBSSxFQUFFLENBQUMsRUFBRSxXQUFXLENBQUMsT0FBTyxDQUFDLENBQUMsQ0FBQztBQUNuRixDQUFDO0FBRUQ7Ozs7Ozs7Ozs7Ozs7O0dBY0c7QUFDSCxNQUFNLFVBQVUsa0JBQWtCLENBQUMsSUFBWSxFQUFFLEtBQWE7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b3B5cmlnaHQgR29vZ2xlIExMQyBBbGwgUmlnaHRzIFJlc2VydmVkLlxuICpcbiAqIFVzZSBvZiB0aGlzIHNvdXJjZSBjb2RlIGlzIGdvdmVybmVkIGJ5IGFuIE1JVC1zdHlsZSBsaWNlbnNlIHRoYXQgY2FuIGJlXG4gKiBmb3VuZCBpbiB0aGUgTElDRU5TRSBmaWxlIGF0IGh0dHBzOi8vYW5ndWxhci5pby9saWNlbnNlXG4gKi9cblxuaW1wb3J0IHthc3NlcnRFcXVhbCwgdGhyb3dFcnJvcn0gZnJvbSAnLi4vLi4vdXRpbC9hc3NlcnQnO1xuaW1wb3J0IHtDaGFyQ29kZX0gZnJvbSAnLi4vLi4vdXRpbC9jaGFyX2NvZGUnO1xuXG4vKipcbiAqIFN0b3JlcyB0aGUgbG9jYXRpb25zIG9mIGtleS92YWx1ZSBpbmRleGVzIHdoaWxlIHBhcnNpbmcgc3R5bGluZy5cbiAqXG4gKiBJbiBjYXNlIG9mIGBjc3NUZXh0YCBwYXJzaW5nIHRoZSBpbmRleGVzIGFyZSBsaWtlIHNvOlxuICogYGBgXG4gKiAgIFwia2V5MTogdmFsdWUxOyBrZXkyOiB2YWx1ZTI7IGtleTM6IHZhbHVlM1wiXG4gKiAgICAgICAgICAgICAgICAgIF4gICBeIF4gICAgIF4gICAgICAgICAgICAgXlxuICogICAgICAgICAgICAgICAgICB8ICAgfCB8ICAgICB8ICAgICAgICAgICAgICstLSB0ZXh0RW5kXG4gKiAgICAgICAgICAgICAgICAgIHwgICB8IHwgICAgICstLS0tLS0tLS0tLS0tLS0tIHZhbHVlRW5kXG4gKiAgICAgICAgICAgICAgICAgIHwgICB8ICstLS0tLS0tLS0tLS0tLS0tLS0tLS0tIHZhbHVlXG4gKiAgICAgICAgICAgICAgICAgIHwgICArLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tIGtleUVuZFxuICogICAgICAgICAgICAgICAgICArLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLSBrZXlcbiAqIGBgYFxuICpcbiAqIEluIGNhc2Ugb2YgYGNsYXNzTmFtZWAgcGFyc2luZyB0aGUgaW5kZXhlcyBhcmUgbGlrZSBzbzpcbiAqIGBgYFxuICogICBcImtleTEga2V5MiBrZXkzXCJcbiAqICAgICAgICAgXiAgIF4gICAgXlxuICogICAgICAgICB8ICAgfCAgICArLS0gdGV4dEVuZFxuICogICAgICAgICB8ICAgKy0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLSBrZXlFbmRcbiAqICAgICAgICAgKy0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0ga2V5XG4gKiBgYGBcbiAqIE5PVEU6IGB2YWx1ZWAgYW5kIGB2YWx1ZUVuZGAgYXJlIHVzZWQgb25seSBmb3Igc3R5bGVzLCBub3QgY2xhc3Nlcy5cbiAqL1xuaW50ZXJmYWNlIFBhcnNlclN0YXRlIHtcbiAgdGV4dEVuZDogbnVtYmVyO1xuICBrZXk6IG51bWJlcjtcbiAga2V5RW5kOiBudW1iZXI7XG4gIHZhbHVlOiBudW1iZXI7XG4gIHZhbHVlRW5kOiBudW1iZXI7XG59XG4vLyBHbG9iYWwgc3RhdGUgb2YgdGhlIHBhcnNlci4gKFRoaXMgbWFrZXMgcGFyc2VyIG5vbi1yZWVudHJhbnQsIGJ1dCB0aGF0IGlzIG5vdCBhbiBpc3N1ZSlcbmNvbnN0IHBhcnNlclN0YXRlOiBQYXJzZXJTdGF0ZSA9IHtcbiAgdGV4dEVuZDogMCxcbiAga2V5OiAwLFxuICBrZXlFbmQ6IDAsXG4gIHZhbHVlOiAwLFxuICB2YWx1ZUVuZDogMCxcbn07XG5cbi8qKlxuICogUmV0cmlldmVzIHRoZSBsYXN0IHBhcnNlZCBga2V5YCBvZiBzdHlsZS5cbiAqIEBwYXJhbSB0ZXh0IHRoZSB0ZXh0IHRvIHN1YnN0cmluZyB0aGUga2V5IGZyb20uXG4gKi9cbmV4cG9ydCBmdW5jdGlvbiBnZXRMYXN0UGFyc2VkS2V5KHRleHQ6IHN0cmluZyk6IHN0cmluZyB7XG4gIHJldHVybiB0ZXh0LnN1YnN0cmluZyhwYXJzZXJTdGF0ZS5rZXksIHBhcnNlclN0YXRlLmtleUVuZCk7XG59XG5cbi8qKlxuICogUmV0cmlldmVzIHRoZSBsYXN0IHBhcnNlZCBgdmFsdWVgIG9mIHN0eWxlLlxuICogQHBhcmFtIHRleHQgdGhlIHRleHQgdG8gc3Vic3RyaW5nIHRoZSBrZXkgZnJvbS5cbiAqL1xuZXhwb3J0IGZ1bmN0aW9uIGdldExhc3RQYXJzZWRWYWx1ZSh0ZXh0OiBzdHJpbmcpOiBzdHJpbmcge1xuICByZXR1cm4gdGV4dC5zdWJzdHJpbmcocGFyc2VyU3RhdGUudmFsdWUsIHBhcnNlclN0YXRlLnZhbHVlRW5kKTtcbn1cblxuLyoqXG4gKiBJbml0aWFsaXplcyBgY2xhc3NOYW1lYCBzdHJpbmcgZm9yIHBhcnNpbmcgYW5kIHBhcnNlcyB0aGUgZmlyc3QgdG9rZW4uXG4gKlxuICogVGhpcyBmdW5jdGlvbiBpcyBpbnRlbmRlZCB0byBiZSB1c2VkIGluIHRoaXMgZm9ybWF0OlxuICogYGBgXG4gKiBmb3IgKGxldCBpID0gcGFyc2VDbGFzc05hbWUodGV4dCk7IGkgPj0gMDsgaSA9IHBhcnNlQ2xhc3NOYW1lTmV4dCh0ZXh0LCBpKSkge1xuICogICBjb25zdCBrZXkgPSBnZXRMYXN0UGFyc2VkS2V5KCk7XG4gKiAgIC4uLlxuICogfVxuICogYGBgXG4gKiBAcGFyYW0gdGV4dCBgY2xhc3NOYW1lYCB0byBwYXJzZVxuICogQHJldHVybnMgaW5kZXggd2hlcmUgdGhlIG5leHQgaW52b2NhdGlvbiBvZiBgcGFyc2VDbGFzc05hbWVOZXh0YCBzaG91bGQgcmVzdW1lLlxuICovXG5leHBvcnQgZnVuY3Rpb24gcGFyc2VDbGFzc05hbWUodGV4dDogc3RyaW5nKTogbnVtYmVyIHtcbiAgcmVzZXRQYXJzZXJTdGF0ZSh0ZXh0KTtcbiAgcmV0dXJuIHBhcnNlQ2xhc3NOYW1lTmV4dCh0ZXh0LCBjb25zdW1lV2hpdGVzcGFjZSh0ZXh0LCAwLCBwYXJzZXJTdGF0ZS50ZXh0RW5kKSk7XG59XG5cbi8qKlxuICogUGFyc2VzIG5leHQgYGNsYXNzTmFtZWAgdG9rZW4uXG4gKlxuICogVGhpcyBmdW5jdGlvbiBpcyBpbnRlbmRlZCB0byBiZSB1c2VkIGluIHRoaXMgZm9ybWF0OlxuICogYGBgXG4gKiBmb3IgKGxldCBpID0gcGFyc2VDbGFzc05hbWUodGV4dCk7IGkgPj0gMDsgaSA9IHBhcnNlQ2xhc3NOYW1lTmV4dCh0ZXh0LCBpKSkge1xuICogICBjb25zdCBrZXkgPSBnZXRMYXN0UGFyc2VkS2V5KCk7XG4gKiAgIC4uLlxuICogfVxuICogYGBgXG4gKlxuICogQHBhcmFtIHRleHQgYGNsYXNzTmFtZWAgdG8gcGFyc2VcbiAqIEBwYXJhbSBpbmRleCB3aGVyZSB0aGUgcGFyc2luZyBzaG91bGQgcmVzdW1lLlxuICogQHJldHVybnMgaW5kZXggd2hlcmUgdGhlIG5leHQgaW52b2NhdGlvbiBvZiBgcGFyc2VDbGFzc05hbWVOZXh0YCBzaG91bGQgcmVzdW1lLlxuICovXG5leHBvcnQgZnVuY3Rpb24gcGFyc2VDbGFzc05hbWVOZXh0KHRleHQ6IHN0cmluZywgaW5kZXg6IG51bWJlcik6IG51bWJlciB7XG4gIGNvbnN0IGVuZCA9IHBhcnNlclN0YXRlLnRleHRFbmQ7XG4gIGlmIChlbmQgPT09IGluZGV4KSB7XG4gICAgcmV0dXJuIC0xO1xuICB9XG4gIGluZGV4ID0gcGFyc2VyU3RhdGUua2V5RW5kID0gY29uc3VtZUNsYXNzVG9rZW4odGV4dCwgcGFyc2VyU3RhdGUua2V5ID0gaW5kZXgsIGVuZCk7XG4gIHJldHVybiBjb25zdW1lV2hpdGVzcGFjZSh0ZXh0LCBpbmRleCwgZW5kKTtcbn1cblxuLyoqXG4gKiBJbml0aWFsaXplcyBgY3NzVGV4dGAgc3RyaW5nIGZvciBwYXJzaW5nIGFuZCBwYXJzZXMgdGhlIGZpcnN0IGtleS92YWx1ZXMuXG4gKlxuICogVGhpcyBmdW5jdGlvbiBpcyBpbnRlbmRlZCB0byBiZSB1c2VkIGluIHRoaXMgZm9ybWF0OlxuICogYGBgXG4gKiBmb3IgKGxldCBpID0gcGFyc2VTdHlsZSh0ZXh0KTsgaSA+PSAwOyBpID0gcGFyc2VTdHlsZU5leHQodGV4dCwgaSkpKSB7XG4gKiAgIGNvbnN0IGtleSA9IGdldExhc3RQYXJzZWRLZXkoKTtcbiAqICAgY29uc3QgdmFsdWUgPSBnZXRMYXN0UGFyc2VkVmFsdWUoKTtcbiAqICAgLi4uXG4gKiB9XG4gKiBgYGBcbiAqIEBwYXJhbSB0ZXh0IGBjc3NUZXh0YCB0byBwYXJzZVxuICogQHJldHVybnMgaW5kZXggd2hlcmUgdGhlIG5leHQgaW52b2NhdGlvbiBvZiBgcGFyc2VTdHlsZU5leHRgIHNob3VsZCByZXN1bWUuXG4gKi9cbmV4cG9ydCBmdW5jdGlvbiBwYXJzZVN0eWxlKHRleHQ6IHN0cmluZyk6IG51bWJlciB7XG4gIHJlc2V0UGFyc2VyU3RhdGUodGV4dCk7XG4gIHJldHVybiBwYXJzZVN0eWxlTmV4dCh0ZXh0LCBjb25zdW1lV2hpdGVzcGFjZSh0ZXh0LCAwLCBwYXJzZXJTdGF0ZS50ZXh0RW5kKSk7XG59XG5cbi8qKlxuICogUGFyc2VzIHRoZSBuZXh0IGBjc3NUZXh0YCBrZXkvdmFsdWVzLlxuICpcbiAqIFRoaXMgZnVuY3Rpb24gaXMgaW50ZW5kZWQgdG8gYmUgdXNlZCBpbiB0aGlzIGZvcm1hdDpcbiAqIGBgYFxuICogZm9yIChsZXQgaSA9IHBhcnNlU3R5bGUodGV4dCk7IGkgPj0gMDsgaSA9IHBhcnNlU3R5bGVOZXh0KHRleHQsIGkpKSkge1xuICogICBjb25zdCBrZXkgPSBnZXRMYXN0UGFyc2VkS2V5KCk7XG4gKiAgIGNvbnN0IHZhbHVlID0gZ2V0TGFzdFBhcnNlZFZhbHVlKCk7XG4gKiAgIC4uLlxuICogfVxuICpcbiAqIEBwYXJhbSB0ZXh0IGBjc3NUZXh0YCB0byBwYXJzZVxuICogQHBhcmFtIGluZGV4IHdoZXJlIHRoZSBwYXJzaW5nIHNob3VsZCByZXN1bWUuXG4gKiBAcmV0dXJucyBpbmRleCB3aGVyZSB0aGUgbmV4dCBpbnZvY2F0aW9uIG9mIGBwYXJzZVN0eWxlTmV4dGAgc2hvdWxkIHJlc3VtZS5cbiAqL1xuZXhwb3J0IGZ1bmN0aW9uIHBhcnNlU3R5bGVOZXh0KHRleHQ6IHN0cmluZywgc3RhcnRJbmRleDogbnVtYmVyKTogbnVtYmVyIHtcbiAgY29uc3QgZW5kID0gcGFyc2VyU3RhdGUudGV4dEVuZDtcbiAgbGV0IGluZGV4ID0gcGFyc2VyU3RhdGUua2V5ID0gY29uc3VtZVdoaXRlc3BhY2UodGV4dCwgc3RhcnRJbmRleCwgZW5kKTtcbiAgaWYgKGVuZCA9PT0gaW5kZXgpIHtcbiAgICAvLyB3ZSByZWFjaGVkIGFuIGVuZCBzbyBqdXN0IHF1aXRcbiAgICByZXR1cm4gLTE7XG4gIH1cbiAgaW5kZXggPSBwYXJzZXJTdGF0ZS5rZXlFbmQgPSBjb25zdW1lU3R5bGVLZXkodGV4dCwgaW5kZXgsIGVuZCk7XG4gIGluZGV4ID0gY29uc3VtZVNlcGFyYXRvcih0ZXh0LCBpbmRleCwgZW5kLCBDaGFyQ29kZS5DT0xPTik7XG4gIGluZGV4ID0gcGFyc2VyU3RhdGUudmFsdWUgPSBjb25zdW1lV2hpdGVzcGFjZSh0ZXh0LCBpbmRleCwgZW5kKTtcbiAgaW5kZXggPSBwYXJzZXJTdGF0ZS52YWx1ZUVuZCA9IGNvbnN1bWVTdHlsZVZhbHVlKHRleHQsIGluZGV4LCBlbmQpO1xuICByZXR1cm4gY29uc3VtZVNlcGFyYXRvcih0ZXh0LCBpbmRleCwgZW5kLCBDaGFyQ29kZS5TRU1JX0NPTE9OKTtcbn1cblxuLyoqXG4gKiBSZXNldCB0aGUgZ2xvYmFsIHN0YXRlIG9mIHRoZSBzdHlsaW5nIHBhcnNlci5cbiAqIEBwYXJhbSB0ZXh0IFRoZSBzdHlsaW5nIHRleHQgdG8gcGFyc2UuXG4gKi9cbmV4cG9ydCBmdW5jdGlvbiByZXNldFBhcnNlclN0YXRlKHRleHQ6IHN0cmluZyk6IHZvaWQge1xuICBwYXJzZXJTdGF0ZS5rZXkgPSAwO1xuICBwYXJzZXJTdGF0ZS5rZXlFbmQgPSAwO1xuICBwYXJzZXJTdGF0ZS52YWx1ZSA9IDA7XG4gIHBhcnNlclN0YXRlLnZhbHVlRW5kID0gMDtcbiAgcGFyc2VyU3RhdGUudGV4dEVuZCA9IHRleHQubGVuZ3RoO1xufVxuXG4vKipcbiAqIFJldHVybnMgaW5kZXggb2YgbmV4dCBub24td2hpdGVzcGFjZSBjaGFyYWN0ZXIuXG4gKlxuICogQHBhcmFtIHRleHQgVGV4dCB0byBzY2FuXG4gKiBAcGFyYW0gc3RhcnRJbmRleCBTdGFydGluZyBpbmRleCBvZiBjaGFyYWN0ZXIgd2hlcmUgdGhlIHNjYW4gc2hvdWxkIHN0YXJ0LlxuICogQHBhcmFtIGVuZEluZGV4IEVuZGluZyBpbmRleCBvZiBjaGFyYWN0ZXIgd2hlcmUgdGhlIHNjYW4gc2hvdWxkIGVuZC5cbiAqIEByZXR1cm5zIEluZGV4IG9mIG5leHQgbm9uLXdoaXRlc3BhY2UgY2hhcmFjdGVyIChNYXkgYmUgdGhlIHNhbWUgYXMgYHN0YXJ0YCBpZiBubyB3aGl0ZXNwYWNlIGF0XG4gKiAgICAgICAgICB0aGF0IGxvY2F0aW9uLilcbiAqL1xuZXhwb3J0IGZ1bmN0aW9uIGNvbnN1bWVXaGl0ZXNwYWNlKHRleHQ6IHN0cmluZywgc3RhcnRJbmRleDogbnVtYmVyLCBlbmRJbmRleDogbnVtYmVyKTogbnVtYmVyIHtcbiAgd2hpbGUgKHN0YXJ0SW5kZXggPCBlbmRJbmRleCAmJiB0ZXh0LmNoYXJDb2RlQXQoc3RhcnRJbmRleCkgPD0gQ2hhckNvZGUuU1BBQ0UpIHtcbiAgICBzdGFydEluZGV4Kys7XG4gIH1cbiAgcmV0dXJuIHN0YXJ0SW5kZXg7XG59XG5cbi8qKlxuICogUmV0dXJucyBpbmRleCBvZiBsYXN0IGNoYXIgaW4gY2xhc3MgdG9rZW4uXG4gKlxuICogQHBhcmFtIHRleHQgVGV4dCB0byBzY2FuXG4gKiBAcGFyYW0gc3RhcnRJbmRleCBTdGFydGluZyBpbmRleCBvZiBjaGFyYWN0ZXIgd2hlcmUgdGhlIHNjYW4gc2hvdWxkIHN0YXJ0LlxuICogQHBhcmFtIGVuZEluZGV4IEVuZGluZyBpbmRleCBvZiBjaGFyYWN0ZXIgd2hlcmUgdGhlIHNjYW4gc2hvdWxkIGVuZC5cbiAqIEByZXR1cm5zIEluZGV4IGFmdGVyIGxhc3QgY2hhciBpbiBjbGFzcyB0b2tlbi5cbiAqL1xuZXhwb3J0IGZ1bmN0aW9uIGNvbnN1bWVDbGFzc1Rva2VuKHRleHQ6IHN0cmluZywgc3RhcnRJbmRleDogbnVtYmVyLCBlbmRJbmRleDogbnVtYmVyKTogbnVtYmVyIHtcbiAgd2hpbGUgKHN0YXJ0SW5kZXggPCBlbmRJbmRleCAmJiB0ZXh0LmNoYXJDb2RlQXQoc3RhcnRJbmRleCkgPiBDaGFyQ29kZS5TUEFDRSkge1xuICAgIHN0YXJ0SW5kZXgrKztcbiAgfVxuICByZXR1cm4gc3RhcnRJbmRleDtcbn1cblxuLyoqXG4gKiBDb25zdW1lcyBhbGwgb2YgdGhlIGNoYXJhY3RlcnMgYmVsb25naW5nIHRvIHN0eWxlIGtleSBhbmQgdG9rZW4uXG4gKlxuICogQHBhcmFtIHRleHQgVGV4dCB0byBzY2FuXG4gKiBAcGFyYW0gc3RhcnRJbmRleCBTdGFydGluZyBpbmRleCBvZiBjaGFyYWN0ZXIgd2hlcmUgdGhlIHNjYW4gc2hvdWxkIHN0YXJ0LlxuICogQHBhcmFtIGVuZEluZGV4IEVuZGluZyBpbmRleCBvZiBjaGFyYWN0ZXIgd2hlcmUgdGhlIHNjYW4gc2hvdWxkIGVuZC5cbiAqIEByZXR1cm5zIEluZGV4IGFmdGVyIGxhc3Qgc3R5bGUga2V5IGNoYXJhY3Rlci5cbiAqL1xuZXhwb3J0IGZ1bmN0aW9uIGNvbnN1bWVTdHlsZUtleSh0ZXh0OiBzdHJpbmcsIHN0YXJ0SW5kZXg6IG51bWJlciwgZW5kSW5kZXg6IG51bWJlcik6IG51bWJlciB7XG4gIGxldCBjaDogbnVtYmVyO1xuICB3aGlsZSAoc3RhcnRJbmRleCA8IGVuZEluZGV4ICYmXG4gICAgICAgICAoKGNoID0gdGV4dC5jaGFyQ29kZUF0KHN0YXJ0SW5kZXgpKSA9PT0gQ2hhckNvZGUuREFTSCB8fCBjaCA9PT0gQ2hhckNvZGUuVU5ERVJTQ09SRSB8fFxuICAgICAgICAgICgoY2ggJiBDaGFyQ29kZS5VUFBFUl9DQVNFKSA+PSBDaGFyQ29kZS5BICYmIChjaCAmIENoYXJDb2RlLlVQUEVSX0NBU0UpIDw9IENoYXJDb2RlLlopIHx8XG4gICAgICAgICAgKGNoID49IENoYXJDb2RlLlpFUk8gJiYgY2ggPD0gQ2hhckNvZGUuTklORSkpKSB7XG4gICAgc3RhcnRJbmRleCsrO1xuICB9XG4gIHJldHVybiBzdGFydEluZGV4O1xufVxuXG4vKipcbiAqIENvbnN1bWVzIGFsbCB3aGl0ZXNwYWNlIGFuZCB0aGUgc2VwYXJhdG9yIGA6YCBhZnRlciB0aGUgc3R5bGUga2V5LlxuICpcbiAqIEBwYXJhbSB0ZXh0IFRleHQgdG8gc2NhblxuICogQHBhcmFtIHN0YXJ0SW5kZXggU3RhcnRpbmcgaW5kZXggb2YgY2hhcmFjdGVyIHdoZXJlIHRoZSBzY2FuIHNob3VsZCBzdGFydC5cbiAqIEBwYXJhbSBlbmRJbmRleCBFbmRpbmcgaW5kZXggb2YgY2hhcmFjdGVyIHdoZXJlIHRoZSBzY2FuIHNob3VsZCBlbmQuXG4gKiBAcmV0dXJucyBJbmRleCBhZnRlciBzZXBhcmF0b3IgYW5kIHN1cnJvdW5kaW5nIHdoaXRlc3BhY2UuXG4gKi9cbmV4cG9ydCBmdW5jdGlvbiBjb25zdW1lU2VwYXJhdG9yKFxuICAgIHRleHQ6IHN0cmluZywgc3RhcnRJbmRleDogbnVtYmVyLCBlbmRJbmRleDogbnVtYmVyLCBzZXBhcmF0b3I6IG51bWJlcik6IG51bWJlciB7XG4gIHN0YXJ0SW5kZXggPSBjb25zdW1lV2hpdGVzcGFjZSh0ZXh0LCBzdGFydEluZGV4LCBlbmRJbmRleCk7XG4gIGlmIChzdGFydEluZGV4IDwgZW5kSW5kZXgpIHtcbiAgICBpZiAobmdEZXZNb2RlICYmIHRleHQuY2hhckNvZGVBdChzdGFydEluZGV4KSAhPT0gc2VwYXJhdG9yKSB7XG4gICAgICBtYWxmb3JtZWRTdHlsZUVycm9yKHRleHQsIFN0cmluZy5mcm9tQ2hhckNvZGUoc2VwYXJhdG9yKSwgc3RhcnRJbmRleCk7XG4gICAgfVxuICAgIHN0YXJ0SW5kZXgrKztcbiAgfVxuICByZXR1cm4gc3RhcnRJbmRleDtcbn1cblxuXG4vKipcbiAqIENvbnN1bWVzIHN0eWxlIHZhbHVlIGhvbm9yaW5nIGB1cmwoKWAgYW5kIGBcIlwiYCB0ZXh0LlxuICpcbiAqIEBwYXJhbSB0ZXh0IFRleHQgdG8gc2NhblxuICogQHBhcmFtIHN0YXJ0SW5kZXggU3RhcnRpbmcgaW5kZXggb2YgY2hhcmFjdGVyIHdoZXJlIHRoZSBzY2FuIHNob3VsZCBzdGFydC5cbiAqIEBwYXJhbSBlbmRJbmRleCBFbmRpbmcgaW5kZXggb2YgY2hhcmFjdGVyIHdoZXJlIHRoZSBzY2FuIHNob3VsZCBlbmQuXG4gKiBAcmV0dXJucyBJbmRleCBhZnRlciBsYXN0IHN0eWxlIHZhbHVlIGNoYXJhY3Rlci5cbiAqL1xuZXhwb3J0IGZ1bmN0aW9uIGNvbnN1bWVTdHlsZVZhbHVlKHRleHQ6IHN0cmluZywgc3RhcnRJbmRleDogbnVtYmVyLCBlbmRJbmRleDogbnVtYmVyKTogbnVtYmVyIHtcbiAgbGV0IGNoMSA9IC0xOyAgLy8gMXN0IHByZXZpb3VzIGNoYXJhY3RlclxuICBsZXQgY2gyID0gLTE7ICAvLyAybmQgcHJldmlvdXMgY2hhcmFjdGVyXG4gIGxldCBjaDMgPSAtMTsgIC8vIDNyZCBwcmV2aW91cyBjaGFyYWN0ZXJcbiAgbGV0IGkgPSBzdGFydEluZGV4O1xuICBsZXQgbGFzdENoSW5kZXggPSBpO1xuICB3aGlsZSAoaSA8IGVuZEluZGV4KSB7XG4gICAgY29uc3QgY2g6IG51bWJlciA9IHRleHQuY2hhckNvZGVBdChpKyspO1xuICAgIGlmIChjaCA9PT0gQ2hhckNvZGUuU0VNSV9DT0xPTikge1xuICAgICAgcmV0dXJuIGxhc3RDaEluZGV4O1xuICAgIH0gZWxzZSBpZiAoY2ggPT09IENoYXJDb2RlLkRPVUJMRV9RVU9URSB8fCBjaCA9PT0gQ2hhckNvZGUuU0lOR0xFX1FVT1RFKSB7XG4gICAgICBsYXN0Q2hJbmRleCA9IGkgPSBjb25zdW1lUXVvdGVkVGV4dCh0ZXh0LCBjaCwgaSwgZW5kSW5kZXgpO1xuICAgIH0gZWxzZSBpZiAoXG4gICAgICAgIHN0YXJ0SW5kZXggPT09XG4gICAgICAgICAgICBpIC0gNCAmJiAgLy8gV2UgaGF2ZSBzZWVuIG9ubHkgNCBjaGFyYWN0ZXJzIHNvIGZhciBcIlVSTChcIiAoSWdub3JlIFwiZm9vX1VSTCgpXCIpXG4gICAgICAgIGNoMyA9PT0gQ2hhckNvZGUuVSAmJlxuICAgICAgICBjaDIgPT09IENoYXJDb2RlLlIgJiYgY2gxID09PSBDaGFyQ29kZS5MICYmIGNoID09PSBDaGFyQ29kZS5PUEVOX1BBUkVOKSB7XG4gICAgICBsYXN0Q2hJbmRleCA9IGkgPSBjb25zdW1lUXVvdGVkVGV4dCh0ZXh0LCBDaGFyQ29kZS5DTE9TRV9QQVJFTiwgaSwgZW5kSW5kZXgpO1xuICAgIH0gZWxzZSBpZiAoY2ggPiBDaGFyQ29kZS5TUEFDRSkge1xuICAgICAgLy8gaWYgd2UgaGF2ZSBhIG5vbi13aGl0ZXNwYWNlIGNoYXJhY3RlciB0aGVuIGNhcHR1cmUgaXRzIGxvY2F0aW9uXG4gICAgICBsYXN0Q2hJbmRleCA9IGk7XG4gICAgfVxuICAgIGNoMyA9IGNoMjtcbiAgICBjaDIgPSBjaDE7XG4gICAgY2gxID0gY2ggJiBDaGFyQ29kZS5VUFBFUl9DQVNFO1xuICB9XG4gIHJldHVybiBsYXN0Q2hJbmRleDtcbn1cblxuLyoqXG4gKiBDb25zdW1lcyBhbGwgb2YgdGhlIHF1b3RlZCBjaGFyYWN0ZXJzLlxuICpcbiAqIEBwYXJhbSB0ZXh0IFRleHQgdG8gc2NhblxuICogQHBhcmFtIHF1b3RlQ2hhckNvZGUgQ2hhckNvZGUgb2YgZWl0aGVyIGBcImAgb3IgYCdgIHF1b3RlIG9yIGApYCBmb3IgYHVybCguLi4pYC5cbiAqIEBwYXJhbSBzdGFydEluZGV4IFN0YXJ0aW5nIGluZGV4IG9mIGNoYXJhY3RlciB3aGVyZSB0aGUgc2NhbiBzaG91bGQgc3RhcnQuXG4gKiBAcGFyYW0gZW5kSW5kZXggRW5kaW5nIGluZGV4IG9mIGNoYXJhY3RlciB3aGVyZSB0aGUgc2NhbiBzaG91bGQgZW5kLlxuICogQHJldHVybnMgSW5kZXggYWZ0ZXIgcXVvdGVkIGNoYXJhY3RlcnMuXG4gKi9cbmV4cG9ydCBmdW5jdGlvbiBjb25zdW1lUXVvdGVkVGV4dChcbiAgICB0ZXh0OiBzdHJpbmcsIHF1b3RlQ2hhckNvZGU6IG51bWJlciwgc3RhcnRJbmRleDogbnVtYmVyLCBlbmRJbmRleDogbnVtYmVyKTogbnVtYmVyIHtcbiAgbGV0IGNoMSA9IC0xOyAgLy8gMXN0IHByZXZpb3VzIGNoYXJhY3RlclxuICBsZXQgaW5kZXggPSBzdGFydEluZGV4O1xuICB3aGlsZSAoaW5kZXggPCBlbmRJbmRleCkge1xuICAgIGNvbnN0IGNoID0gdGV4dC5jaGFyQ29kZUF0KGluZGV4KyspO1xuICAgIGlmIChjaCA9PSBxdW90ZUNoYXJDb2RlICYmIGNoMSAhPT0gQ2hhckNvZGUuQkFDS19TTEFTSCkge1xuICAgICAgcmV0dXJuIGluZGV4O1xuICAgIH1cbiAgICBpZiAoY2ggPT0gQ2hhckNvZGUuQkFDS19TTEFTSCAmJiBjaDEgPT09IENoYXJDb2RlLkJBQ0tfU0xBU0gpIHtcbiAgICAgIC8vIHR3byBiYWNrIHNsYXNoZXMgY2FuY2VsIGVhY2ggb3RoZXIgb3V0LiBGb3IgZXhhbXBsZSBgXCJcXFxcXCJgIHNob3VsZCBwcm9wZXJseSBlbmQgdGhlXG4gICAgICAvLyBxdW90YXRpb24uIChJdCBzaG91bGQgbm90IGFzc3VtZSB0aGF0IHRoZSBsYXN0IGBcImAgaXMgZXNjYXBlZC4pXG4gICAgICBjaDEgPSAwO1xuICAgIH0gZWxzZSB7XG4gICAgICBjaDEgPSBjaDtcbiAgICB9XG4gIH1cbiAgdGhyb3cgbmdEZXZNb2RlID8gbWFsZm9ybWVkU3R5bGVFcnJvcih0ZXh0LCBTdHJpbmcuZnJvbUNoYXJDb2RlKHF1b3RlQ2hhckNvZGUpLCBlbmRJbmRleCkgOlxuICAgICAgICAgICAgICAgICAgICBuZXcgRXJyb3IoKTtcbn1cblxuZnVuY3Rpb24gbWFsZm9ybWVkU3R5bGVFcnJvcih0ZXh0OiBzdHJpbmcsIGV4cGVjdGluZzogc3RyaW5nLCBpbmRleDogbnVtYmVyKTogbmV2ZXIge1xuICBuZ0Rldk1vZGUgJiYgYXNzZXJ0RXF1YWwodHlwZW9mIHRleHQgPT09ICdzdHJpbmcnLCB0cnVlLCAnU3RyaW5nIGV4cGVjdGVkIGhlcmUnKTtcbiAgdGhyb3cgdGhyb3dFcnJvcihcbiAgICAgIGBNYWxmb3JtZWQgc3R5bGUgYXQgbG9jYXRpb24gJHtpbmRleH0gaW4gc3RyaW5nICdgICsgdGV4dC5zdWJzdHJpbmcoMCwgaW5kZXgpICsgJ1s+PicgK1xuICAgICAgdGV4dC5zdWJzdHJpbmcoaW5kZXgsIGluZGV4ICsgMSkgKyAnPDxdJyArIHRleHQuc3Vic3RyKGluZGV4ICsgMSkgK1xuICAgICAgYCcuIEV4cGVjdGluZyAnJHtleHBlY3Rpbmd9Jy5gKTtcbn1cbiJdfQ==