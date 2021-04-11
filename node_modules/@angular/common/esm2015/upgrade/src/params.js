/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
/**
 * A codec for encoding and decoding URL parts.
 *
 * @publicApi
 **/
export class UrlCodec {
}
/**
 * A `UrlCodec` that uses logic from AngularJS to serialize and parse URLs
 * and URL parameters.
 *
 * @publicApi
 */
export class AngularJSUrlCodec {
    // https://github.com/angular/angular.js/blob/864c7f0/src/ng/location.js#L15
    encodePath(path) {
        const segments = path.split('/');
        let i = segments.length;
        while (i--) {
            // decode forward slashes to prevent them from being double encoded
            segments[i] = encodeUriSegment(segments[i].replace(/%2F/g, '/'));
        }
        path = segments.join('/');
        return _stripIndexHtml((path && path[0] !== '/' && '/' || '') + path);
    }
    // https://github.com/angular/angular.js/blob/864c7f0/src/ng/location.js#L42
    encodeSearch(search) {
        if (typeof search === 'string') {
            search = parseKeyValue(search);
        }
        search = toKeyValue(search);
        return search ? '?' + search : '';
    }
    // https://github.com/angular/angular.js/blob/864c7f0/src/ng/location.js#L44
    encodeHash(hash) {
        hash = encodeUriSegment(hash);
        return hash ? '#' + hash : '';
    }
    // https://github.com/angular/angular.js/blob/864c7f0/src/ng/location.js#L27
    decodePath(path, html5Mode = true) {
        const segments = path.split('/');
        let i = segments.length;
        while (i--) {
            segments[i] = decodeURIComponent(segments[i]);
            if (html5Mode) {
                // encode forward slashes to prevent them from being mistaken for path separators
                segments[i] = segments[i].replace(/\//g, '%2F');
            }
        }
        return segments.join('/');
    }
    // https://github.com/angular/angular.js/blob/864c7f0/src/ng/location.js#L72
    decodeSearch(search) {
        return parseKeyValue(search);
    }
    // https://github.com/angular/angular.js/blob/864c7f0/src/ng/location.js#L73
    decodeHash(hash) {
        hash = decodeURIComponent(hash);
        return hash[0] === '#' ? hash.substring(1) : hash;
    }
    normalize(pathOrHref, search, hash, baseUrl) {
        if (arguments.length === 1) {
            const parsed = this.parse(pathOrHref, baseUrl);
            if (typeof parsed === 'string') {
                return parsed;
            }
            const serverUrl = `${parsed.protocol}://${parsed.hostname}${parsed.port ? ':' + parsed.port : ''}`;
            return this.normalize(this.decodePath(parsed.pathname), this.decodeSearch(parsed.search), this.decodeHash(parsed.hash), serverUrl);
        }
        else {
            const encPath = this.encodePath(pathOrHref);
            const encSearch = search && this.encodeSearch(search) || '';
            const encHash = hash && this.encodeHash(hash) || '';
            let joinedPath = (baseUrl || '') + encPath;
            if (!joinedPath.length || joinedPath[0] !== '/') {
                joinedPath = '/' + joinedPath;
            }
            return joinedPath + encSearch + encHash;
        }
    }
    areEqual(valA, valB) {
        return this.normalize(valA) === this.normalize(valB);
    }
    // https://github.com/angular/angular.js/blob/864c7f0/src/ng/urlUtils.js#L60
    parse(url, base) {
        try {
            // Safari 12 throws an error when the URL constructor is called with an undefined base.
            const parsed = !base ? new URL(url) : new URL(url, base);
            return {
                href: parsed.href,
                protocol: parsed.protocol ? parsed.protocol.replace(/:$/, '') : '',
                host: parsed.host,
                search: parsed.search ? parsed.search.replace(/^\?/, '') : '',
                hash: parsed.hash ? parsed.hash.replace(/^#/, '') : '',
                hostname: parsed.hostname,
                port: parsed.port,
                pathname: (parsed.pathname.charAt(0) === '/') ? parsed.pathname : '/' + parsed.pathname
            };
        }
        catch (e) {
            throw new Error(`Invalid URL (${url}) with base (${base})`);
        }
    }
}
function _stripIndexHtml(url) {
    return url.replace(/\/index.html$/, '');
}
/**
 * Tries to decode the URI component without throwing an exception.
 *
 * @param str value potential URI component to check.
 * @returns the decoded URI if it can be decoded or else `undefined`.
 */
function tryDecodeURIComponent(value) {
    try {
        return decodeURIComponent(value);
    }
    catch (e) {
        // Ignore any invalid uri component.
        return undefined;
    }
}
/**
 * Parses an escaped url query string into key-value pairs. Logic taken from
 * https://github.com/angular/angular.js/blob/864c7f0/src/Angular.js#L1382
 */
function parseKeyValue(keyValue) {
    const obj = {};
    (keyValue || '').split('&').forEach((keyValue) => {
        let splitPoint, key, val;
        if (keyValue) {
            key = keyValue = keyValue.replace(/\+/g, '%20');
            splitPoint = keyValue.indexOf('=');
            if (splitPoint !== -1) {
                key = keyValue.substring(0, splitPoint);
                val = keyValue.substring(splitPoint + 1);
            }
            key = tryDecodeURIComponent(key);
            if (typeof key !== 'undefined') {
                val = typeof val !== 'undefined' ? tryDecodeURIComponent(val) : true;
                if (!obj.hasOwnProperty(key)) {
                    obj[key] = val;
                }
                else if (Array.isArray(obj[key])) {
                    obj[key].push(val);
                }
                else {
                    obj[key] = [obj[key], val];
                }
            }
        }
    });
    return obj;
}
/**
 * Serializes into key-value pairs. Logic taken from
 * https://github.com/angular/angular.js/blob/864c7f0/src/Angular.js#L1409
 */
function toKeyValue(obj) {
    const parts = [];
    for (const key in obj) {
        let value = obj[key];
        if (Array.isArray(value)) {
            value.forEach((arrayValue) => {
                parts.push(encodeUriQuery(key, true) +
                    (arrayValue === true ? '' : '=' + encodeUriQuery(arrayValue, true)));
            });
        }
        else {
            parts.push(encodeUriQuery(key, true) +
                (value === true ? '' : '=' + encodeUriQuery(value, true)));
        }
    }
    return parts.length ? parts.join('&') : '';
}
/**
 * We need our custom method because encodeURIComponent is too aggressive and doesn't follow
 * https://tools.ietf.org/html/rfc3986 with regards to the character set (pchar) allowed in path
 * segments:
 *    segment       = *pchar
 *    pchar         = unreserved / pct-encoded / sub-delims / ":" / "@"
 *    pct-encoded   = "%" HEXDIG HEXDIG
 *    unreserved    = ALPHA / DIGIT / "-" / "." / "_" / "~"
 *    sub-delims    = "!" / "$" / "&" / "'" / "(" / ")"
 *                     / "*" / "+" / "," / ";" / "="
 *
 * Logic from https://github.com/angular/angular.js/blob/864c7f0/src/Angular.js#L1437
 */
function encodeUriSegment(val) {
    return encodeUriQuery(val, true).replace(/%26/g, '&').replace(/%3D/gi, '=').replace(/%2B/gi, '+');
}
/**
 * This method is intended for encoding *key* or *value* parts of query component. We need a custom
 * method because encodeURIComponent is too aggressive and encodes stuff that doesn't have to be
 * encoded per https://tools.ietf.org/html/rfc3986:
 *    query         = *( pchar / "/" / "?" )
 *    pchar         = unreserved / pct-encoded / sub-delims / ":" / "@"
 *    unreserved    = ALPHA / DIGIT / "-" / "." / "_" / "~"
 *    pct-encoded   = "%" HEXDIG HEXDIG
 *    sub-delims    = "!" / "$" / "&" / "'" / "(" / ")"
 *                     / "*" / "+" / "," / ";" / "="
 *
 * Logic from https://github.com/angular/angular.js/blob/864c7f0/src/Angular.js#L1456
 */
function encodeUriQuery(val, pctEncodeSpaces = false) {
    return encodeURIComponent(val)
        .replace(/%40/g, '@')
        .replace(/%3A/gi, ':')
        .replace(/%24/g, '$')
        .replace(/%2C/gi, ',')
        .replace(/%3B/gi, ';')
        .replace(/%20/g, (pctEncodeSpaces ? '%20' : '+'));
}
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoicGFyYW1zLmpzIiwic291cmNlUm9vdCI6IiIsInNvdXJjZXMiOlsiLi4vLi4vLi4vLi4vLi4vLi4vLi4vcGFja2FnZXMvY29tbW9uL3VwZ3JhZGUvc3JjL3BhcmFtcy50cyJdLCJuYW1lcyI6W10sIm1hcHBpbmdzIjoiQUFBQTs7Ozs7O0dBTUc7QUFFSDs7OztJQUlJO0FBQ0osTUFBTSxPQUFnQixRQUFRO0NBcUY3QjtBQUVEOzs7OztHQUtHO0FBQ0gsTUFBTSxPQUFPLGlCQUFpQjtJQUM1Qiw0RUFBNEU7SUFDNUUsVUFBVSxDQUFDLElBQVk7UUFDckIsTUFBTSxRQUFRLEdBQUcsSUFBSSxDQUFDLEtBQUssQ0FBQyxHQUFHLENBQUMsQ0FBQztRQUNqQyxJQUFJLENBQUMsR0FBRyxRQUFRLENBQUMsTUFBTSxDQUFDO1FBRXhCLE9BQU8sQ0FBQyxFQUFFLEVBQUU7WUFDVixtRUFBbUU7WUFDbkUsUUFBUSxDQUFDLENBQUMsQ0FBQyxHQUFHLGdCQUFnQixDQUFDLFFBQVEsQ0FBQyxDQUFDLENBQUMsQ0FBQyxPQUFPLENBQUMsTUFBTSxFQUFFLEdBQUcsQ0FBQyxDQUFDLENBQUM7U0FDbEU7UUFFRCxJQUFJLEdBQUcsUUFBUSxDQUFDLElBQUksQ0FBQyxHQUFHLENBQUMsQ0FBQztRQUMxQixPQUFPLGVBQWUsQ0FBQyxDQUFDLElBQUksSUFBSSxJQUFJLENBQUMsQ0FBQyxDQUFDLEtBQUssR0FBRyxJQUFJLEdBQUcsSUFBSSxFQUFFLENBQUMsR0FBRyxJQUFJLENBQUMsQ0FBQztJQUN4RSxDQUFDO0lBRUQsNEVBQTRFO0lBQzVFLFlBQVksQ0FBQyxNQUFxQztRQUNoRCxJQUFJLE9BQU8sTUFBTSxLQUFLLFFBQVEsRUFBRTtZQUM5QixNQUFNLEdBQUcsYUFBYSxDQUFDLE1BQU0sQ0FBQyxDQUFDO1NBQ2hDO1FBRUQsTUFBTSxHQUFHLFVBQVUsQ0FBQyxNQUFNLENBQUMsQ0FBQztRQUM1QixPQUFPLE1BQU0sQ0FBQyxDQUFDLENBQUMsR0FBRyxHQUFHLE1BQU0sQ0FBQyxD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LEVBQUUsR0FBRyxDQUFDO1NBQ3JCLE9BQU8sQ0FBQyxNQUFNLEVBQUUsR0FBRyxDQUFDO1NBQ3BCLE9BQU8sQ0FBQyxPQUFPLEVBQUUsR0FBRyxDQUFDO1NBQ3JCLE9BQU8sQ0FBQyxPQUFPLEVBQUUsR0FBRyxDQUFDO1NBQ3JCLE9BQU8sQ0FBQyxNQUFNLEVBQUUsQ0FBQyxlQUFlLENBQUMsQ0FBQyxDQUFDLEtBQUssQ0FBQyxDQUFDLENBQUMsR0FBRyxDQUFDLENBQUMsQ0FBQztBQUN4RCxDQUFDIiwic291cmNlc0NvbnRlbnQiOlsiLyoqXG4gKiBAbGljZW5zZVxuICogQ29weXJpZ2h0IEdvb2dsZSBMTEMgQWxsIFJpZ2h0cyBSZXNlcnZlZC5cbiAqXG4gKiBVc2Ugb2YgdGhpcyBzb3VyY2UgY29kZSBpcyBnb3Zlcm5lZCBieSBhbiBNSVQtc3R5bGUgbGljZW5zZSB0aGF0IGNhbiBiZVxuICogZm91bmQgaW4gdGhlIExJQ0VOU0UgZmlsZSBhdCBodHRwczovL2FuZ3VsYXIuaW8vbGljZW5zZVxuICovXG5cbi8qKlxuICogQSBjb2RlYyBmb3IgZW5jb2RpbmcgYW5kIGRlY29kaW5nIFVSTCBwYXJ0cy5cbiAqXG4gKiBAcHVibGljQXBpXG4gKiovXG5leHBvcnQgYWJzdHJhY3QgY2xhc3MgVXJsQ29kZWMge1xuICAvKipcbiAgICogRW5jb2RlcyB0aGUgcGF0aCBmcm9tIHRoZSBwcm92aWRlZCBzdHJpbmdcbiAgICpcbiAgICogQHBhcmFtIHBhdGggVGhlIHBhdGggc3RyaW5nXG4gICAqL1xuICBhYnN0cmFjdCBlbmNvZGVQYXRoKHBhdGg6IHN0cmluZyk6IHN0cmluZztcblxuICAvKipcbiAgICogRGVjb2RlcyB0aGUgcGF0aCBmcm9tIHRoZSBwcm92aWRlZCBzdHJpbmdcbiAgICpcbiAgICogQHBhcmFtIHBhdGggVGhlIHBhdGggc3RyaW5nXG4gICAqL1xuICBhYnN0cmFjdCBkZWNvZGVQYXRoKHBhdGg6IHN0cmluZyk6IHN0cmluZztcblxuICAvKipcbiAgICogRW5jb2RlcyB0aGUgc2VhcmNoIHN0cmluZyBmcm9tIHRoZSBwcm92aWRlZCBzdHJpbmcgb3Igb2JqZWN0XG4gICAqXG4gICAqIEBwYXJhbSBwYXRoIFRoZSBwYXRoIHN0cmluZyBvciBvYmplY3RcbiAgICovXG4gIGFic3RyYWN0IGVuY29kZVNlYXJjaChzZWFyY2g6IHN0cmluZ3x7W2s6IHN0cmluZ106IHVua25vd259KTogc3RyaW5nO1xuXG4gIC8qKlxuICAgKiBEZWNvZGVzIHRoZSBzZWFyY2ggb2JqZWN0cyBmcm9tIHRoZSBwcm92aWRlZCBzdHJpbmdcbiAgICpcbiAgICogQHBhcmFtIHBhdGggVGhlIHBhdGggc3RyaW5nXG4gICAqL1xuICBhYnN0cmFjdCBkZWNvZGVTZWFyY2goc2VhcmNoOiBzdHJpbmcpOiB7W2s6IHN0cmluZ106IHVua25vd259O1xuXG4gIC8qKlxuICAgKiBFbmNvZGVzIHRoZSBoYXNoIGZyb20gdGhlIHByb3ZpZGVkIHN0cmluZ1xuICAgKlxuICAgKiBAcGFyYW0gcGF0aCBUaGUgaGFzaCBzdHJpbmdcbiAgICovXG4gIGFic3RyYWN0IGVuY29kZUhhc2goaGFzaDogc3RyaW5nKTogc3RyaW5nO1xuXG4gIC8qKlxuICAgKiBEZWNvZGVzIHRoZSBoYXNoIGZyb20gdGhlIHByb3ZpZGVkIHN0cmluZ1xuICAgKlxuICAgKiBAcGFyYW0gcGF0aCBUaGUgaGFzaCBzdHJpbmdcbiAgICovXG4gIGFic3RyYWN0IGRlY29kZUhhc2goaGFzaDogc3RyaW5nKTogc3RyaW5nO1xuXG4gIC8qKlxuICAgKiBOb3JtYWxpemVzIHRoZSBVUkwgZnJvbSB0aGUgcHJvdmlkZWQgc3RyaW5nXG4gICAqXG4gICAqIEBwYXJhbSBwYXRoIFRoZSBVUkwgc3RyaW5nXG4gICAqL1xuICBhYnN0cmFjdCBub3JtYWxpemUoaHJlZjogc3RyaW5nKTogc3RyaW5nO1xuXG5cbiAgLyoqXG4gICAqIE5vcm1hbGl6ZXMgdGhlIFVSTCBmcm9tIHRoZSBwcm92aWRlZCBzdHJpbmcsIHNlYXJjaCwgaGFzaCwgYW5kIGJhc2UgVVJMIHBhcmFtZXRlcnNcbiAgICpcbiAgICogQHBhcmFtIHBhdGggVGhlIFVSTCBwYXRoXG4gICAqIEBwYXJhbSBzZWFyY2ggVGhlIHNlYXJjaCBvYmplY3RcbiAgICogQHBhcmFtIGhhc2ggVGhlIGhhcyBzdHJpbmdcbiAgICogQHBhcmFtIGJhc2VVcmwgVGhlIGJhc2UgVVJMIGZvciB0aGUgVVJMXG4gICAqL1xuICBhYnN0cmFjdCBub3JtYWxpemUocGF0aDogc3RyaW5nLCBzZWFyY2g6IHtbazogc3RyaW5nXTogdW5rbm93bn0sIGhhc2g6IHN0cmluZywgYmFzZVVybD86IHN0cmluZyk6XG4gICAgICBzdHJpbmc7XG5cbiAgLyoqXG4gICAqIENoZWNrcyB3aGV0aGVyIHRoZSB0d28gc3RyaW5ncyBhcmUgZXF1YWxcbiAgICogQHBhcmFtIHZhbEEgRmlyc3Qgc3RyaW5nIGZvciBjb21wYXJpc29uXG4gICAqIEBwYXJhbSB2YWxCIFNlY29uZCBzdHJpbmcgZm9yIGNvbXBhcmlzb25cbiAgICovXG4gIGFic3RyYWN0IGFyZUVxdWFsKHZhbEE6IHN0cmluZywgdmFsQjogc3RyaW5nKTogYm9vbGVhbjtcblxuICAvKipcbiAgICogUGFyc2VzIHRoZSBVUkwgc3RyaW5nIGJhc2VkIG9uIHRoZSBiYXNlIFVSTFxuICAgKlxuICAgKiBAcGFyYW0gdXJsIFRoZSBmdWxsIFVSTCBzdHJpbmdcbiAgICogQHBhcmFtIGJhc2UgVGhlIGJhc2UgZm9yIHRoZSBVUkxcbiAgICovXG4gIGFic3RyYWN0IHBhcnNlKHVybDogc3RyaW5nLCBiYXNlPzogc3RyaW5nKToge1xuICAgIGhyZWY6IHN0cmluZyxcbiAgICBwcm90b2NvbDogc3RyaW5nLFxuICAgIGhvc3Q6IHN0cmluZyxcbiAgICBzZWFyY2g6IHN0cmluZyxcbiAgICBoYXNoOiBzdHJpbmcsXG4gICAgaG9zdG5hbWU6IHN0cmluZyxcbiAgICBwb3J0OiBzdHJpbmcsXG4gICAgcGF0aG5hbWU6IHN0cmluZ1xuICB9O1xufVxuXG4vKipcbiAqIEEgYFVybENvZGVjYCB0aGF0IHVzZXMgbG9naWMgZnJvbSBBbmd1bGFySlMgdG8gc2VyaWFsaXplIGFuZCBwYXJzZSBVUkxzXG4gKiBhbmQgVVJMIHBhcmFtZXRlcnMuXG4gKlxuICogQHB1YmxpY0FwaVxuICovXG5leHBvcnQgY2xhc3MgQW5ndWxhckpTVXJsQ29kZWMgaW1wbGVtZW50cyBVcmxDb2RlYyB7XG4gIC8vIGh0dHBzOi8vZ2l0aHViLmNvbS9hbmd1bGFyL2FuZ3VsYXIuanMvYmxvYi84NjRjN2YwL3NyYy9uZy9sb2NhdGlvbi5qcyNMMTVcbiAgZW5jb2RlUGF0aChwYXRoOiBzdHJpbmcpOiBzdHJpbmcge1xuICAgIGNvbnN0IHNlZ21lbnRzID0gcGF0aC5zcGxpdCgnLycpO1xuICAgIGxldCBpID0gc2VnbWVudHMubGVuZ3RoO1xuXG4gICAgd2hpbGUgKGktLSkge1xuICAgICAgLy8gZGVjb2RlIGZvcndhcmQgc2xhc2hlcyB0byBwcmV2ZW50IHRoZW0gZnJvbSBiZWluZyBkb3VibGUgZW5jb2RlZFxuICAgICAgc2VnbWVudHNbaV0gPSBlbmNvZGVVcmlTZWdtZW50KHNlZ21lbnRzW2ldLnJlcGxhY2UoLyUyRi9nLCAnLycpKTtcbiAgICB9XG5cbiAgICBwYXRoID0gc2VnbWVudHMuam9pbignLycpO1xuICAgIHJldHVybiBfc3RyaXBJbmRleEh0bWwoKHBhdGggJiYgcGF0aFswXSAhPT0gJy8nICYmICcvJyB8fCAnJykgKyBwYXRoKTtcbiAgfVxuXG4gIC8vIGh0dHBzOi8vZ2l0aHViLmNvbS9hbmd1bGFyL2FuZ3VsYXIuanMvYmxvYi84NjRjN2YwL3NyYy9uZy9sb2NhdGlvbi5qcyNMNDJcbiAgZW5jb2RlU2VhcmNoKHNlYXJjaDogc3RyaW5nfHtbazogc3RyaW5nXTogdW5rbm93bn0pOiBzdHJpbmcge1xuICAgIGlmICh0eXBlb2Ygc2VhcmNoID09PSAnc3RyaW5nJykge1xuICAgICAgc2VhcmNoID0gcGFyc2VLZXlWYWx1ZShzZWFyY2gpO1xuICAgIH1cblxuICAgIHNlYXJjaCA9IHRvS2V5VmFsdWUoc2VhcmNoKTtcbiAgICByZXR1cm4gc2VhcmNoID8gJz8nICsgc2VhcmNoIDogJyc7XG4gIH1cblxuICAvLyBodHRwczovL2dpdGh1Yi5jb20vYW5ndWxhci9hbmd1bGFyLmpzL2Jsb2IvODY0YzdmMC9zcmMvbmcvbG9jYXRpb24uanMjTDQ0XG4gIGVuY29kZUhhc2goaGFzaDogc3RyaW5nKSB7XG4gICAgaGFzaCA9IGVuY29kZVVyaVNlZ21lbnQoaGFzaCk7XG4gICAgcmV0dXJuIGhhc2ggPyAnIycgKyBoYXNoIDogJyc7XG4gIH1cblxuICAvLyBodHRwczovL2dpdGh1Yi5jb20vYW5ndWxhci9hbmd1bGFyLmpzL2Jsb2IvODY0YzdmMC9zcmMvbmcvbG9jYXRpb24uanMjTDI3XG4gIGRlY29kZVBhdGgocGF0aDogc3RyaW5nLCBodG1sNU1vZGUgPSB0cnVlKTogc3RyaW5nIHtcbiAgICBjb25zdCBzZWdtZW50cyA9IHBhdGguc3BsaXQoJy8nKTtcbiAgICBsZXQgaSA9IHNlZ21lbnRzLmxlbmd0aDtcblxuICAgIHdoaWxlIChpLS0pIHtcbiAgICAgIHNlZ21lbnRzW2ldID0gZGVjb2RlVVJJQ29tcG9uZW50KHNlZ21lbnRzW2ldKTtcbiAgICAgIGlmIChodG1sNU1vZGUpIHtcbiAgICAgICAgLy8gZW5jb2RlIGZvcndhcmQgc2xhc2hlcyB0byBwcmV2ZW50IHRoZW0gZnJvbSBiZWluZyBtaXN0YWtlbiBmb3IgcGF0aCBzZXBhcmF0b3JzXG4gICAgICAgIHNlZ21lbnRzW2ldID0gc2VnbWVudHNbaV0ucmVwbGFjZSgvXFwvL2csICclMkYnKTtcbiAgICAgIH1cbiAgICB9XG5cbiAgICByZXR1cm4gc2VnbWVudHMuam9pbignLycpO1xuICB9XG5cbiAgLy8gaHR0cHM6Ly9naXRodWIuY29tL2FuZ3VsYXIvYW5ndWxhci5qcy9ibG9iLzg2NGM3ZjAvc3JjL25nL2xvY2F0aW9uLmpzI0w3MlxuICBkZWNvZGVTZWFyY2goc2VhcmNoOiBzdHJpbmcpIHtcbiAgICByZXR1cm4gcGFyc2VLZXlWYWx1ZShzZWFyY2gpO1xuICB9XG5cbiAgLy8gaHR0cHM6Ly9naXRodWIuY29tL2FuZ3VsYXIvYW5ndWxhci5qcy9ibG9iLzg2NGM3ZjAvc3JjL25nL2xvY2F0aW9uLmpzI0w3M1xuICBkZWNvZGVIYXNoKGhhc2g6IHN0cmluZykge1xuICAgIGhhc2ggPSBkZWNvZGVVUklDb21wb25lbnQoaGFzaCk7XG4gICAgcmV0dXJuIGhhc2hbMF0gPT09ICcjJyA/IGhhc2guc3Vic3RyaW5nKDEpIDogaGFzaDtcbiAgfVxuXG4gIC8vIGh0dHBzOi8vZ2l0aHViLmNvbS9hbmd1bGFyL2FuZ3VsYXIuanMvYmxvYi84NjRjN2YwL3NyYy9uZy9sb2NhdGlvbi5qcyNMMTQ5XG4gIC8vIGh0dHBzOi8vZ2l0aHViLmNvbS9hbmd1bGFyL2FuZ3VsYXIuanMvYmxvYi84NjRjN2YwL3NyYy9uZy9sb2NhdGlvbi5qcyNMNDJcbiAgbm9ybWFsaXplKGhyZWY6IHN0cmluZyk6IHN0cmluZztcbiAgbm9ybWFsaXplKHBhdGg6IHN0cmluZywgc2VhcmNoOiB7W2s6IHN0cmluZ106IHVua25vd259LCBoYXNoOiBzdHJpbmcsIGJhc2VVcmw/OiBzdHJpbmcpOiBzdHJpbmc7XG4gIG5vcm1hbGl6ZShwYXRoT3JIcmVmOiBzdHJpbmcsIHNlYXJjaD86IHtbazogc3RyaW5nXTogdW5rbm93bn0sIGhhc2g/OiBzdHJpbmcsIGJhc2VVcmw/OiBzdHJpbmcpOlxuICAgICAgc3RyaW5nIHtcbiAgICBpZiAoYXJndW1lbnRzLmxlbmd0aCA9PT0gMSkge1xuICAgICAgY29uc3QgcGFyc2VkID0gdGhpcy5wYXJzZShwYXRoT3JIcmVmLCBiYXNlVXJsKTtcblxuICAgICAgaWYgKHR5cGVvZiBwYXJzZWQgPT09ICdzdHJpbmcnKSB7XG4gICAgICAgIHJldHVybiBwYXJzZWQ7XG4gICAgICB9XG5cbiAgICAgIGNvbnN0IHNlcnZlclVybCA9XG4gICAgICAgICAgYCR7cGFyc2VkLnByb3RvY29sfTovLyR7cGFyc2VkLmhvc3RuYW1lfSR7cGFyc2VkLnBvcnQgPyAnOicgKyBwYXJzZWQucG9ydCA6ICcnfWA7XG5cbiAgICAgIHJldHVybiB0aGlzLm5vcm1hbGl6ZShcbiAgICAgICAgICB0aGlzLmRlY29kZVBhdGgocGFyc2VkLnBhdGhuYW1lKSwgdGhpcy5kZWNvZGVTZWFyY2gocGFyc2VkLnNlYXJjaCksXG4gICAgICAgICAgdGhpcy5kZWNvZGVIYXNoKHBhcnNlZC5oYXNoKSwgc2VydmVyVXJsKTtcbiAgICB9IGVsc2Uge1xuICAgICAgY29uc3QgZW5jUGF0aCA9IHRoaXMuZW5jb2RlUGF0aChwYXRoT3JIcmVmKTtcbiAgICAgIGNvbnN0IGVuY1NlYXJjaCA9IHNlYXJjaCAmJiB0aGlzLmVuY29kZVNlYXJjaChzZWFyY2gpIHx8ICcnO1xuICAgICAgY29uc3QgZW5jSGFzaCA9IGhhc2ggJiYgdGhpcy5lbmNvZGVIYXNoKGhhc2gpIHx8ICcnO1xuXG4gICAgICBsZXQgam9pbmVkUGF0aCA9IChiYXNlVXJsIHx8ICcnKSArIGVuY1BhdGg7XG5cbiAgICAgIGlmICgham9pbmVkUGF0aC5sZW5ndGggfHwgam9pbmVkUGF0aFswXSAhPT0gJy8nKSB7XG4gICAgICAgIGpvaW5lZFBhdGggPSAnLycgKyBqb2luZWRQYXRoO1xuICAgICAgfVxuICAgICAgcmV0dXJuIGpvaW5lZFBhdGggKyBlbmNTZWFyY2ggKyBlbmNIYXNoO1xuICAgIH1cbiAgfVxuXG4gIGFyZUVxdWFsKHZhbEE6IHN0cmluZywgdmFsQjogc3RyaW5nKSB7XG4gICAgcmV0dXJuIHRoaXMubm9ybWFsaXplKHZhbEEpID09PSB0aGlzLm5vcm1hbGl6ZSh2YWxCKTtcbiAgfVxuXG4gIC8vIGh0dHBzOi8vZ2l0aHViLmNvbS9hbmd1bGFyL2FuZ3VsYXIuanMvYmxvYi84NjRjN2YwL3NyYy9uZy91cmxVdGlscy5qcyNMNjBcbiAgcGFyc2UodXJsOiBzdHJpbmcsIGJhc2U/OiBzdHJpbmcpIHtcbiAgICB0cnkge1xuICAgICAgLy8gU2FmYXJpIDEyIHRocm93cyBhbiBlcnJvciB3aGVuIHRoZSBVUkwgY29uc3RydWN0b3IgaXMgY2FsbGVkIHdpdGggYW4gdW5kZWZpbmVkIGJhc2UuXG4gICAgICBjb25zdCBwYXJzZWQgPSAhYmFzZSA/IG5ldyBVUkwodXJsKSA6IG5ldyBVUkwodXJsLCBiYXNlKTtcbiAgICAgIHJldHVybiB7XG4gICAgICAgIGhyZWY6IHBhcnNlZC5ocmVmLFxuICAgICAgICBwcm90b2NvbDogcGFyc2VkLnByb3RvY29sID8gcGFyc2VkLnByb3RvY29sLnJlcGxhY2UoLzokLywgJycpIDogJycsXG4gICAgICAgIGhvc3Q6IHBhcnNlZC5ob3N0LFxuICAgICAgICBzZWFyY2g6IHBhcnNlZC5zZWFyY2ggPyBwYXJzZWQuc2VhcmNoLnJlcGxhY2UoL15cXD8vLCAnJykgOiAnJyxcbiAgICAgICAgaGFzaDogcGFyc2VkLmhhc2ggPyBwYXJzZWQuaGFzaC5yZXBsYWNlKC9eIy8sICcnKSA6ICcnLFxuICAgICAgICBob3N0bmFtZTogcGFyc2VkLmhvc3RuYW1lLFxuICAgICAgICBwb3J0OiBwYXJzZWQucG9ydCxcbiAgICAgICAgcGF0aG5hbWU6IChwYXJzZWQucGF0aG5hbWUuY2hhckF0KDApID09PSAnLycpID8gcGFyc2VkLnBhdGhuYW1lIDogJy8nICsgcGFyc2VkLnBhdGhuYW1lXG4gICAgICB9O1xuICAgIH0gY2F0Y2ggKGUpIHtcbiAgICAgIHRocm93IG5ldyBFcnJvcihgSW52YWxpZCBVUkwgKCR7dXJsfSkgd2l0aCBiYXNlICgke2Jhc2V9KWApO1xuICAgIH1cbiAgfVxufVxuXG5mdW5jdGlvbiBfc3RyaXBJbmRleEh0bWwodXJsOiBzdHJpbmcpOiBzdHJpbmcge1xuICByZXR1cm4gdXJsLnJlcGxhY2UoL1xcL2luZGV4Lmh0bWwkLywgJycpO1xufVxuXG4vKipcbiAqIFRyaWVzIHRvIGRlY29kZSB0aGUgVVJJIGNvbXBvbmVudCB3aXRob3V0IHRocm93aW5nIGFuIGV4Y2VwdGlvbi5cbiAqXG4gKiBAcGFyYW0gc3RyIHZhbHVlIHBvdGVudGlhbCBVUkkgY29tcG9uZW50IHRvIGNoZWNrLlxuICogQHJldHVybnMgdGhlIGRlY29kZWQgVVJJIGlmIGl0IGNhbiBiZSBkZWNvZGVkIG9yIGVsc2UgYHVuZGVmaW5lZGAuXG4gKi9cbmZ1bmN0aW9uIHRyeURlY29kZVVSSUNvbXBvbmVudCh2YWx1ZTogc3RyaW5nKTogc3RyaW5nfHVuZGVmaW5lZCB7XG4gIHRyeSB7XG4gICAgcmV0dXJuIGRlY29kZVVSSUNvbXBvbmVudCh2YWx1ZSk7XG4gIH0gY2F0Y2ggKGUpIHtcbiAgICAvLyBJZ25vcmUgYW55IGludmFsaWQgdXJpIGNvbXBvbmVudC5cbiAgICByZXR1cm4gdW5kZWZpbmVkO1xuICB9XG59XG5cblxuLyoqXG4gKiBQYXJzZXMgYW4gZXNjYXBlZCB1cmwgcXVlcnkgc3RyaW5nIGludG8ga2V5LXZhbHVlIHBhaXJzLiBMb2dpYyB0YWtlbiBmcm9tXG4gKiBodHRwczovL2dpdGh1Yi5jb20vYW5ndWxhci9hbmd1bGFyLmpzL2Jsb2IvODY0YzdmMC9zcmMvQW5ndWxhci5qcyNMMTM4MlxuICovXG5mdW5jdGlvbiBwYXJzZUtleVZhbHVlKGtleVZhbHVlOiBzdHJpbmcpOiB7W2s6IHN0cmluZ106IHVua25vd259IHtcbiAgY29uc3Qgb2JqOiB7W2s6IHN0cmluZ106IHVua25vd259ID0ge307XG4gIChrZXlWYWx1ZSB8fCAnJykuc3BsaXQoJyYnKS5mb3JFYWNoKChrZXlWYWx1ZSkgPT4ge1xuICAgIGxldCBzcGxpdFBvaW50LCBrZXksIHZhbDtcbiAgICBpZiAoa2V5VmFsdWUpIHtcbiAgICAgIGtleSA9IGtleVZhbHVlID0ga2V5VmFsdWUucmVwbGFjZSgvXFwrL2csICclMjAnKTtcbiAgICAgIHNwbGl0UG9pbnQgPSBrZXlWYWx1ZS5pbmRleE9mKCc9Jyk7XG4gICAgICBpZiAoc3BsaXRQb2ludCAhPT0gLTEpIHtcbiAgICAgICAga2V5ID0ga2V5VmFsdWUuc3Vic3RyaW5nKDAsIHNwbGl0UG9pbnQpO1xuICAgICAgICB2YWwgPSBrZXlWYWx1ZS5zdWJzdHJpbmcoc3BsaXRQb2ludCArIDEpO1xuICAgICAgfVxuICAgICAga2V5ID0gdHJ5RGVjb2RlVVJJQ29tcG9uZW50KGtleSk7XG4gICAgICBpZiAodHlwZW9mIGtleSAhPT0gJ3VuZGVmaW5lZCcpIHtcbiAgICAgICAgdmFsID0gdHlwZW9mIHZhbCAhPT0gJ3VuZGVmaW5lZCcgPyB0cnlEZWNvZGVVUklDb21wb25lbnQodmFsKSA6IHRydWU7XG4gICAgICAgIGlmICghb2JqLmhhc093blByb3BlcnR5KGtleSkpIHtcbiAgICAgICAgICBvYmpba2V5XSA9IHZhbDtcbiAgICAgICAgfSBlbHNlIGlmIChBcnJheS5pc0FycmF5KG9ialtrZXldKSkge1xuICAgICAgICAgIChvYmpba2V5XSBhcyB1bmtub3duW10pLnB1c2godmFsKTtcbiAgICAgICAgfSBlbHNlIHtcbiAgICAgICAgICBvYmpba2V5XSA9IFtvYmpba2V5XSwgdmFsXTtcbiAgICAgICAgfVxuICAgICAgfVxuICAgIH1cbiAgfSk7XG4gIHJldHVybiBvYmo7XG59XG5cbi8qKlxuICogU2VyaWFsaXplcyBpbnRvIGtleS12YWx1ZSBwYWlycy4gTG9naWMgdGFrZW4gZnJvbVxuICogaHR0cHM6Ly9naXRodWIuY29tL2FuZ3VsYXIvYW5ndWxhci5qcy9ibG9iLzg2NGM3ZjAvc3JjL0FuZ3VsYXIuanMjTDE0MDlcbiAqL1xuZnVuY3Rpb24gdG9LZXlWYWx1ZShvYmo6IHtbazogc3RyaW5nXTogdW5rbm93bn0pIHtcbiAgY29uc3QgcGFydHM6IHVua25vd25bXSA9IFtdO1xuICBmb3IgKGNvbnN0IGtleSBpbiBvYmopIHtcbiAgICBsZXQgdmFsdWUgPSBvYmpba2V5XTtcbiAgICBpZiAoQXJyYXkuaXNBcnJheSh2YWx1ZSkpIHtcbiAgICAgIHZhbHVlLmZvckVhY2goKGFycmF5VmFsdWUpID0+IHtcbiAgICAgICAgcGFydHMucHVzaChcbiAgICAgICAgICAgIGVuY29kZVVyaVF1ZXJ5KGtleSwgdHJ1ZSkgK1xuICAgICAgICAgICAgKGFycmF5VmFsdWUgPT09IHRydWUgPyAnJyA6ICc9JyArIGVuY29kZVVyaVF1ZXJ5KGFycmF5VmFsdWUsIHRydWUpKSk7XG4gICAgICB9KTtcbiAgICB9IGVsc2Uge1xuICAgICAgcGFydHMucHVzaChcbiAgICAgICAgICBlbmNvZGVVcmlRdWVyeShrZXksIHRydWUpICtcbiAgICAgICAgICAodmFsdWUgPT09IHRydWUgPyAnJyA6ICc9JyArIGVuY29kZVVyaVF1ZXJ5KHZhbHVlIGFzIGFueSwgdHJ1ZSkpKTtcbiAgICB9XG4gIH1cbiAgcmV0dXJuIHBhcnRzLmxlbmd0aCA/IHBhcnRzLmpvaW4oJyYnKSA6ICcnO1xufVxuXG5cbi8qKlxuICogV2UgbmVlZCBvdXIgY3VzdG9tIG1ldGhvZCBiZWNhdXNlIGVuY29kZVVSSUNvbXBvbmVudCBpcyB0b28gYWdncmVzc2l2ZSBhbmQgZG9lc24ndCBmb2xsb3dcbiAqIGh0dHBzOi8vdG9vbHMuaWV0Zi5vcmcvaHRtbC9yZmMzOTg2IHdpdGggcmVnYXJkcyB0byB0aGUgY2hhcmFjdGVyIHNldCAocGNoYXIpIGFsbG93ZWQgaW4gcGF0aFxuICogc2VnbWVudHM6XG4gKiAgICBzZWdtZW50ICAgICAgID0gKnBjaGFyXG4gKiAgICBwY2hhciAgICAgICAgID0gdW5yZXNlcnZlZCAvIHBjdC1lbmNvZGVkIC8gc3ViLWRlbGltcyAvIFwiOlwiIC8gXCJAXCJcbiAqICAgIHBjdC1lbmNvZGVkICAgPSBcIiVcIiBIRVhESUcgSEVYRElHXG4gKiAgICB1bnJlc2VydmVkICAgID0gQUxQSEEgLyBESUdJVCAvIFwiLVwiIC8gXCIuXCIgLyBcIl9cIiAvIFwiflwiXG4gKiAgICBzdWItZGVsaW1zICAgID0gXCIhXCIgLyBcIiRcIiAvIFwiJlwiIC8gXCInXCIgLyBcIihcIiAvIFwiKVwiXG4gKiAgICAgICAgICAgICAgICAgICAgIC8gXCIqXCIgLyBcIitcIiAvIFwiLFwiIC8gXCI7XCIgLyBcIj1cIlxuICpcbiAqIExvZ2ljIGZyb20gaHR0cHM6Ly9naXRodWIuY29tL2FuZ3VsYXIvYW5ndWxhci5qcy9ibG9iLzg2NGM3ZjAvc3JjL0FuZ3VsYXIuanMjTDE0MzdcbiAqL1xuZnVuY3Rpb24gZW5jb2RlVXJpU2VnbWVudCh2YWw6IHN0cmluZykge1xuICByZXR1cm4gZW5jb2RlVXJpUXVlcnkodmFsLCB0cnVlKS5yZXBsYWNlKC8lMjYvZywgJyYnKS5yZXBsYWNlKC8lM0QvZ2ksICc9JykucmVwbGFjZSgvJTJCL2dpLCAnKycpO1xufVxuXG5cbi8qKlxuICogVGhpcyBtZXRob2QgaXMgaW50ZW5kZWQgZm9yIGVuY29kaW5nICprZXkqIG9yICp2YWx1ZSogcGFydHMgb2YgcXVlcnkgY29tcG9uZW50LiBXZSBuZWVkIGEgY3VzdG9tXG4gKiBtZXRob2QgYmVjYXVzZSBlbmNvZGVVUklDb21wb25lbnQgaXMgdG9vIGFnZ3Jlc3NpdmUgYW5kIGVuY29kZXMgc3R1ZmYgdGhhdCBkb2Vzbid0IGhhdmUgdG8gYmVcbiAqIGVuY29kZWQgcGVyIGh0dHBzOi8vdG9vbHMuaWV0Zi5vcmcvaHRtbC9yZmMzOTg2OlxuICogICAgcXVlcnkgICAgICAgICA9ICooIHBjaGFyIC8gXCIvXCIgLyBcIj9cIiApXG4gKiAgICBwY2hhciAgICAgICAgID0gdW5yZXNlcnZlZCAvIHBjdC1lbmNvZGVkIC8gc3ViLWRlbGltcyAvIFwiOlwiIC8gXCJAXCJcbiAqICAgIHVucmVzZXJ2ZWQgICAgPSBBTFBIQSAvIERJR0lUIC8gXCItXCIgLyBcIi5cIiAvIFwiX1wiIC8gXCJ+XCJcbiAqICAgIHBjdC1lbmNvZGVkICAgPSBcIiVcIiBIRVhESUcgSEVYRElHXG4gKiAgICBzdWItZGVsaW1zICAgID0gXCIhXCIgLyBcIiRcIiAvIFwiJlwiIC8gXCInXCIgLyBcIihcIiAvIFwiKVwiXG4gKiAgICAgICAgICAgICAgICAgICAgIC8gXCIqXCIgLyBcIitcIiAvIFwiLFwiIC8gXCI7XCIgLyBcIj1cIlxuICpcbiAqIExvZ2ljIGZyb20gaHR0cHM6Ly9naXRodWIuY29tL2FuZ3VsYXIvYW5ndWxhci5qcy9ibG9iLzg2NGM3ZjAvc3JjL0FuZ3VsYXIuanMjTDE0NTZcbiAqL1xuZnVuY3Rpb24gZW5jb2RlVXJpUXVlcnkodmFsOiBzdHJpbmcsIHBjdEVuY29kZVNwYWNlczogYm9vbGVhbiA9IGZhbHNlKSB7XG4gIHJldHVybiBlbmNvZGVVUklDb21wb25lbnQodmFsKVxuICAgICAgLnJlcGxhY2UoLyU0MC9nLCAnQCcpXG4gICAgICAucmVwbGFjZSgvJTNBL2dpLCAnOicpXG4gICAgICAucmVwbGFjZSgvJTI0L2csICckJylcbiAgICAgIC5yZXBsYWNlKC8lMkMvZ2ksICcsJylcbiAgICAgIC5yZXBsYWNlKC8lM0IvZ2ksICc7JylcbiAgICAgIC5yZXBsYWNlKC8lMjAvZywgKHBjdEVuY29kZVNwYWNlcyA/ICclMjAnIDogJysnKSk7XG59XG4iXX0=