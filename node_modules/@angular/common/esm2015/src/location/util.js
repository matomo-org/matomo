/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
/**
 * Joins two parts of a URL with a slash if needed.
 *
 * @param start  URL string
 * @param end    URL string
 *
 *
 * @returns The joined URL string.
 */
export function joinWithSlash(start, end) {
    if (start.length == 0) {
        return end;
    }
    if (end.length == 0) {
        return start;
    }
    let slashes = 0;
    if (start.endsWith('/')) {
        slashes++;
    }
    if (end.startsWith('/')) {
        slashes++;
    }
    if (slashes == 2) {
        return start + end.substring(1);
    }
    if (slashes == 1) {
        return start + end;
    }
    return start + '/' + end;
}
/**
 * Removes a trailing slash from a URL string if needed.
 * Looks for the first occurrence of either `#`, `?`, or the end of the
 * line as `/` characters and removes the trailing slash if one exists.
 *
 * @param url URL string.
 *
 * @returns The URL string, modified if needed.
 */
export function stripTrailingSlash(url) {
    const match = url.match(/#|\?|$/);
    const pathEndIdx = match && match.index || url.length;
    const droppedSlashIdx = pathEndIdx - (url[pathEndIdx - 1] === '/' ? 1 : 0);
    return url.slice(0, droppedSlashIdx) + url.slice(pathEndIdx);
}
/**
 * Normalizes URL parameters by prepending with `?` if needed.
 *
 * @param  params String of URL parameters.
 *
 * @returns The normalized URL parameters string.
 */
export function normalizeQueryParams(params) {
    return params && params[0] !== '?' ? '?' + params : params;
}
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoidXRpbC5qcyIsInNvdXJjZVJvb3QiOiIiLCJzb3VyY2VzIjpbIi4uLy4uLy4uLy4uLy4uLy4uLy4uL3BhY2thZ2VzL2NvbW1vbi9zcmMvbG9jYXRpb24vdXRpbC50cyJdLCJuYW1lcyI6W10sIm1hcHBpbmdzIjoiQUFBQTs7Ozs7O0dBTUc7QUFHSDs7Ozs7Ozs7R0FRRztBQUNILE1BQU0sVUFBVSxhQUFhLENBQUMsS0FBYSxFQUFFLEdBQVc7SUFDdEQsSUFBSSxLQUFLLENBQUMsTUFBTSxJQUFJLENBQUMsRUFBRTtRQUNyQixPQUFPLEdBQUcsQ0FBQztLQUNaO0lBQ0QsSUFBSSxHQUFHLENBQUMsTUFBTSxJQUFJLENBQUMsRUFBRTtRQUNuQixPQUFPLEtBQUssQ0FBQztLQUNkO0lBQ0QsSUFBSSxPQUFPLEdBQUcsQ0FBQyxDQUFDO0lBQ2hCLElBQUksS0FBSyxDQUFDLFFBQVEsQ0FBQyxHQUFHLENBQUMsRUFBRTtRQUN2QixPQUFPLEVBQUUsQ0FBQztLQUNYO0lBQ0QsSUFBSSxHQUFHLENBQUMsVUFBVSxDQUFDLEdBQUcsQ0FBQyxFQUFFO1FBQ3ZCLE9BQU8sRUFBRSxDQUFDO0tBQ1g7SUFDRCxJQUFJLE9BQU8sSUFBSSxDQUFDLEVBQUU7UUFDaEIsT0FBTyxLQUFLLEdBQUcsR0FBRyxDQUFDLFNBQVMsQ0FBQyxDQUFDLENBQUMsQ0FBQztLQUNqQztJQUNELElBQUksT0FBTyxJQUFJLENBQUMsRUFBRTtRQUNoQixPQUFPLEtBQUssR0FBRyxHQUFHLENBQUM7S0FDcEI7SUFDRCxPQUFPLEtBQUssR0FBRyxHQUFHLEdBQUcsR0FBRyxDQUFDO0FBQzNCLENBQUM7QUFFRDs7Ozs7Ozs7R0FRRztBQUNILE1BQU0sVUFBVSxrQkFBa0IsQ0FBQyxHQUFXO0lBQzVDLE1BQU0sS0FBSyxHQUFHLEdBQUcsQ0FBQyxLQUFLLENBQUMsUUFBUSxDQUFDLENBQUM7SUFDbEMsTUFBTSxVQUFVLEdBQUcsS0FBSyxJQUFJLEtBQUssQ0FBQyxLQUFLLElBQUksR0FBRyxDQUFDLE1BQU0sQ0FBQztJQUN0RCxNQUFNLGVBQWUsR0FBRyxVQUFVLEdBQUcsQ0FBQyxHQUFHLENBQUMsVUFBVSxHQUFHLENBQUMsQ0FBQyxLQUFLLEdBQUcsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQztJQUMzRSxPQUFPLEdBQUcsQ0FBQyxLQUFLLENBQUMsQ0FBQyxFQUFFLGVBQWUsQ0FBQyxHQUFHLEdBQUcsQ0FBQyxLQUFLLENBQUMsVUFBVSxDQUFDLENBQUM7QUFDL0QsQ0FBQztBQUVEOzs7Ozs7R0FNRztBQUNILE1BQU0sVUFBVSxvQkFBb0IsQ0FBQyxNQUFjO0lBQ2pELE9BQU8sTUFBTSxJQUFJLE1BQU0sQ0FBQyxDQUFDLENBQUMsS0FBSyxHQUFHLENBQUMsQ0FBQyxDQUFDLEdBQUcsR0FBRyxNQUFNLENBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQztBQUM3RCxDQUFDIiwic291cmNlc0NvbnRlbnQiOlsiLyoqXG4gKiBAbGljZW5zZVxuICogQ29weXJpZ2h0IEdvb2dsZSBMTEMgQWxsIFJpZ2h0cyBSZXNlcnZlZC5cbiAqXG4gKiBVc2Ugb2YgdGhpcyBzb3VyY2UgY29kZSBpcyBnb3Zlcm5lZCBieSBhbiBNSVQtc3R5bGUgbGljZW5zZSB0aGF0IGNhbiBiZVxuICogZm91bmQgaW4gdGhlIExJQ0VOU0UgZmlsZSBhdCBodHRwczovL2FuZ3VsYXIuaW8vbGljZW5zZVxuICovXG5cblxuLyoqXG4gKiBKb2lucyB0d28gcGFydHMgb2YgYSBVUkwgd2l0aCBhIHNsYXNoIGlmIG5lZWRlZC5cbiAqXG4gKiBAcGFyYW0gc3RhcnQgIFVSTCBzdHJpbmdcbiAqIEBwYXJhbSBlbmQgICAgVVJMIHN0cmluZ1xuICpcbiAqXG4gKiBAcmV0dXJucyBUaGUgam9pbmVkIFVSTCBzdHJpbmcuXG4gKi9cbmV4cG9ydCBmdW5jdGlvbiBqb2luV2l0aFNsYXNoKHN0YXJ0OiBzdHJpbmcsIGVuZDogc3RyaW5nKTogc3RyaW5nIHtcbiAgaWYgKHN0YXJ0Lmxlbmd0aCA9PSAwKSB7XG4gICAgcmV0dXJuIGVuZDtcbiAgfVxuICBpZiAoZW5kLmxlbmd0aCA9PSAwKSB7XG4gICAgcmV0dXJuIHN0YXJ0O1xuICB9XG4gIGxldCBzbGFzaGVzID0gMDtcbiAgaWYgKHN0YXJ0LmVuZHNXaXRoKCcvJykpIHtcbiAgICBzbGFzaGVzKys7XG4gIH1cbiAgaWYgKGVuZC5zdGFydHNXaXRoKCcvJykpIHtcbiAgICBzbGFzaGVzKys7XG4gIH1cbiAgaWYgKHNsYXNoZXMgPT0gMikge1xuICAgIHJldHVybiBzdGFydCArIGVuZC5zdWJzdHJpbmcoMSk7XG4gIH1cbiAgaWYgKHNsYXNoZXMgPT0gMSkge1xuICAgIHJldHVybiBzdGFydCArIGVuZDtcbiAgfVxuICByZXR1cm4gc3RhcnQgKyAnLycgKyBlbmQ7XG59XG5cbi8qKlxuICogUmVtb3ZlcyBhIHRyYWlsaW5nIHNsYXNoIGZyb20gYSBVUkwgc3RyaW5nIGlmIG5lZWRlZC5cbiAqIExvb2tzIGZvciB0aGUgZmlyc3Qgb2NjdXJyZW5jZSBvZiBlaXRoZXIgYCNgLCBgP2AsIG9yIHRoZSBlbmQgb2YgdGhlXG4gKiBsaW5lIGFzIGAvYCBjaGFyYWN0ZXJzIGFuZCByZW1vdmVzIHRoZSB0cmFpbGluZyBzbGFzaCBpZiBvbmUgZXhpc3RzLlxuICpcbiAqIEBwYXJhbSB1cmwgVVJMIHN0cmluZy5cbiAqXG4gKiBAcmV0dXJucyBUaGUgVVJMIHN0cmluZywgbW9kaWZpZWQgaWYgbmVlZGVkLlxuICovXG5leHBvcnQgZnVuY3Rpb24gc3RyaXBUcmFpbGluZ1NsYXNoKHVybDogc3RyaW5nKTogc3RyaW5nIHtcbiAgY29uc3QgbWF0Y2ggPSB1cmwubWF0Y2goLyN8XFw/fCQvKTtcbiAgY29uc3QgcGF0aEVuZElkeCA9IG1hdGNoICYmIG1hdGNoLmluZGV4IHx8IHVybC5sZW5ndGg7XG4gIGNvbnN0IGRyb3BwZWRTbGFzaElkeCA9IHBhdGhFbmRJZHggLSAodXJsW3BhdGhFbmRJZHggLSAxXSA9PT0gJy8nID8gMSA6IDApO1xuICByZXR1cm4gdXJsLnNsaWNlKDAsIGRyb3BwZWRTbGFzaElkeCkgKyB1cmwuc2xpY2UocGF0aEVuZElkeCk7XG59XG5cbi8qKlxuICogTm9ybWFsaXplcyBVUkwgcGFyYW1ldGVycyBieSBwcmVwZW5kaW5nIHdpdGggYD9gIGlmIG5lZWRlZC5cbiAqXG4gKiBAcGFyYW0gIHBhcmFtcyBTdHJpbmcgb2YgVVJMIHBhcmFtZXRlcnMuXG4gKlxuICogQHJldHVybnMgVGhlIG5vcm1hbGl6ZWQgVVJMIHBhcmFtZXRlcnMgc3RyaW5nLlxuICovXG5leHBvcnQgZnVuY3Rpb24gbm9ybWFsaXplUXVlcnlQYXJhbXMocGFyYW1zOiBzdHJpbmcpOiBzdHJpbmcge1xuICByZXR1cm4gcGFyYW1zICYmIHBhcmFtc1swXSAhPT0gJz8nID8gJz8nICsgcGFyYW1zIDogcGFyYW1zO1xufVxuIl19