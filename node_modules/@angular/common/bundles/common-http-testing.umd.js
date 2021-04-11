/**
 * @license Angular v11.2.7
 * (c) 2010-2021 Google LLC. https://angular.io/
 * License: MIT
 */

(function (global, factory) {
    typeof exports === 'object' && typeof module !== 'undefined' ? factory(exports, require('@angular/common/http'), require('@angular/core'), require('rxjs')) :
    typeof define === 'function' && define.amd ? define('@angular/common/http/testing', ['exports', '@angular/common/http', '@angular/core', 'rxjs'], factory) :
    (global = global || self, factory((global.ng = global.ng || {}, global.ng.common = global.ng.common || {}, global.ng.common.http = global.ng.common.http || {}, global.ng.common.http.testing = {}), global.ng.common.http, global.ng.core, global.rxjs));
}(this, (function (exports, http, core, rxjs) { 'use strict';

    /**
     * @license
     * Copyright Google LLC All Rights Reserved.
     *
     * Use of this source code is governed by an MIT-style license that can be
     * found in the LICENSE file at https://angular.io/license
     */
    /**
     * Controller to be injected into tests, that allows for mocking and flushing
     * of requests.
     *
     * @publicApi
     */
    var HttpTestingController = /** @class */ (function () {
        function HttpTestingController() {
        }
        return HttpTestingController;
    }());

    /**
     * @license
     * Copyright Google LLC All Rights Reserved.
     *
     * Use of this source code is governed by an MIT-style license that can be
     * found in the LICENSE file at https://angular.io/license
     */
    /**
     * A mock requests that was received and is ready to be answered.
     *
     * This interface allows access to the underlying `HttpRequest`, and allows
     * responding with `HttpEvent`s or `HttpErrorResponse`s.
     *
     * @publicApi
     */
    var TestRequest = /** @class */ (function () {
        function TestRequest(request, observer) {
            this.request = request;
            this.observer = observer;
            /**
             * @internal set by `HttpClientTestingBackend`
             */
            this._cancelled = false;
        }
        Object.defineProperty(TestRequest.prototype, "cancelled", {
            /**
             * Whether the request was cancelled after it was sent.
             */
            get: function () {
                return this._cancelled;
            },
            enumerable: false,
            configurable: true
        });
        /**
         * Resolve the request by returning a body plus additional HTTP information (such as response
         * headers) if provided.
         * If the request specifies an expected body type, the body is converted into the requested type.
         * Otherwise, the body is converted to `JSON` by default.
         *
         * Both successful and unsuccessful responses can be delivered via `flush()`.
         */
        TestRequest.prototype.flush = function (body, opts) {
            if (opts === void 0) { opts = {}; }
            if (this.cancelled) {
                throw new Error("Cannot flush a cancelled request.");
            }
            var url = this.request.urlWithParams;
            var headers = (opts.headers instanceof http.HttpHeaders) ? opts.headers : new http.HttpHeaders(opts.headers);
            body = _maybeConvertBody(this.request.responseType, body);
            var statusText = opts.statusText;
            var status = opts.status !== undefined ? opts.status : 200;
            if (opts.status === undefined) {
                if (body === null) {
                    status = 204;
                    statusText = statusText || 'No Content';
                }
                else {
                    statusText = statusText || 'OK';
                }
            }
            if (statusText === undefined) {
                throw new Error('statusText is required when setting a custom status.');
            }
            if (status >= 200 && status < 300) {
                this.observer.next(new http.HttpResponse({ body: body, headers: headers, status: status, statusText: statusText, url: url }));
                this.observer.complete();
            }
            else {
                this.observer.error(new http.HttpErrorResponse({ error: body, headers: headers, status: status, statusText: statusText, url: url }));
            }
        };
        /**
         * Resolve the request by returning an `ErrorEvent` (e.g. simulating a network failure).
         */
        TestRequest.prototype.error = function (error, opts) {
            if (opts === void 0) { opts = {}; }
            if (this.cancelled) {
                throw new Error("Cannot return an error for a cancelled request.");
            }
            if (opts.status && opts.status >= 200 && opts.status < 300) {
                throw new Error("error() called with a successful status.");
            }
            var headers = (opts.headers instanceof http.HttpHeaders) ? opts.headers : new http.HttpHeaders(opts.headers);
            this.observer.error(new http.HttpErrorResponse({
                error: error,
                headers: headers,
                status: opts.status || 0,
                statusText: opts.statusText || '',
                url: this.request.urlWithParams,
            }));
        };
        /**
         * Deliver an arbitrary `HttpEvent` (such as a progress event) on the response stream for this
         * request.
         */
        TestRequest.prototype.event = function (event) {
            if (this.cancelled) {
                throw new Error("Cannot send events to a cancelled request.");
            }
            this.observer.next(event);
        };
        return TestRequest;
    }());
    /**
     * Helper function to convert a response body to an ArrayBuffer.
     */
    function _toArrayBufferBody(body) {
        if (typeof ArrayBuffer === 'undefined') {
            throw new Error('ArrayBuffer responses are not supported on this platform.');
        }
        if (body instanceof ArrayBuffer) {
            return body;
        }
        throw new Error('Automatic conversion to ArrayBuffer is not supported for response type.');
    }
    /**
     * Helper function to convert a response body to a Blob.
     */
    function _toBlob(body) {
        if (typeof Blob === 'undefined') {
            throw new Error('Blob responses are not supported on this platform.');
        }
        if (body instanceof Blob) {
            return body;
        }
        if (ArrayBuffer && body instanceof ArrayBuffer) {
            return new Blob([body]);
        }
        throw new Error('Automatic conversion to Blob is not supported for response type.');
    }
    /**
     * Helper function to convert a response body to JSON data.
     */
    function _toJsonBody(body, format) {
        if (format === void 0) { format = 'JSON'; }
        if (typeof ArrayBuffer !== 'undefined' && body instanceof ArrayBuffer) {
            throw new Error("Automatic conversion to " + format + " is not supported for ArrayBuffers.");
        }
        if (typeof Blob !== 'undefined' && body instanceof Blob) {
            throw new Error("Automatic conversion to " + format + " is not supported for Blobs.");
        }
        if (typeof body === 'string' || typeof body === 'number' || typeof body === 'object' ||
            typeof body === 'boolean' || Array.isArray(body)) {
            return body;
        }
        throw new Error("Automatic conversion to " + format + " is not supported for response type.");
    }
    /**
     * Helper function to convert a response body to a string.
     */
    function _toTextBody(body) {
        if (typeof body === 'string') {
            return body;
        }
        if (typeof ArrayBuffer !== 'undefined' && body instanceof ArrayBuffer) {
            throw new Error('Automatic conversion to text is not supported for ArrayBuffers.');
        }
        if (typeof Blob !== 'undefined' && body instanceof Blob) {
            throw new Error('Automatic conversion to text is not supported for Blobs.');
        }
        return JSON.stringify(_toJsonBody(body, 'text'));
    }
    /**
     * Convert a response body to the requested type.
     */
    function _maybeConvertBody(responseType, body) {
        if (body === null) {
            return null;
        }
        switch (responseType) {
            case 'arraybuffer':
                return _toArrayBufferBody(body);
            case 'blob':
                return _toBlob(body);
            case 'json':
                return _toJsonBody(body);
            case 'text':
                return _toTextBody(body);
            default:
                throw new Error("Unsupported responseType: " + responseType);
        }
    }

    /**
     * @license
     * Copyright Google LLC All Rights Reserved.
     *
     * Use of this source code is governed by an MIT-style license that can be
     * found in the LICENSE file at https://angular.io/license
     */
    /**
     * A testing backend for `HttpClient` which both acts as an `HttpBackend`
     * and as the `HttpTestingController`.
     *
     * `HttpClientTestingBackend` works by keeping a list of all open requests.
     * As requests come in, they're added to the list. Users can assert that specific
     * requests were made and then flush them. In the end, a verify() method asserts
     * that no unexpected requests were made.
     *
     *
     */
    var HttpClientTestingBackend = /** @class */ (function () {
        function HttpClientTestingBackend() {
            /**
             * List of pending requests which have not yet been expected.
             */
            this.open = [];
        }
        /**
         * Handle an incoming request by queueing it in the list of open requests.
         */
        HttpClientTestingBackend.prototype.handle = function (req) {
            var _this = this;
            return new rxjs.Observable(function (observer) {
                var testReq = new TestRequest(req, observer);
                _this.open.push(testReq);
                observer.next({ type: http.HttpEventType.Sent });
                return function () {
                    testReq._cancelled = true;
                };
            });
        };
        /**
         * Helper function to search for requests in the list of open requests.
         */
        HttpClientTestingBackend.prototype._match = function (match) {
            if (typeof match === 'string') {
                return this.open.filter(function (testReq) { return testReq.request.urlWithParams === match; });
            }
            else if (typeof match === 'function') {
                return this.open.filter(function (testReq) { return match(testReq.request); });
            }
            else {
                return this.open.filter(function (testReq) { return (!match.method || testReq.request.method === match.method.toUpperCase()) &&
                    (!match.url || testReq.request.urlWithParams === match.url); });
            }
        };
        /**
         * Search for requests in the list of open requests, and return all that match
         * without asserting anything about the number of matches.
         */
        HttpClientTestingBackend.prototype.match = function (match) {
            var _this = this;
            var results = this._match(match);
            results.forEach(function (result) {
                var index = _this.open.indexOf(result);
                if (index !== -1) {
                    _this.open.splice(index, 1);
                }
            });
            return results;
        };
        /**
         * Expect that a single outstanding request matches the given matcher, and return
         * it.
         *
         * Requests returned through this API will no longer be in the list of open requests,
         * and thus will not match twice.
         */
        HttpClientTestingBackend.prototype.expectOne = function (match, description) {
            description = description || this.descriptionFromMatcher(match);
            var matches = this.match(match);
            if (matches.length > 1) {
                throw new Error("Expected one matching request for criteria \"" + description + "\", found " + matches.length + " requests.");
            }
            if (matches.length === 0) {
                var message = "Expected one matching request for criteria \"" + description + "\", found none.";
                if (this.open.length > 0) {
                    // Show the methods and URLs of open requests in the error, for convenience.
                    var requests = this.open
                        .map(function (testReq) {
                        var url = testReq.request.urlWithParams;
                        var method = testReq.request.method;
                        return method + " " + url;
                    })
                        .join(', ');
                    message += " Requests received are: " + requests + ".";
                }
                throw new Error(message);
            }
            return matches[0];
        };
        /**
         * Expect that no outstanding requests match the given matcher, and throw an error
         * if any do.
         */
        HttpClientTestingBackend.prototype.expectNone = function (match, description) {
            description = description || this.descriptionFromMatcher(match);
            var matches = this.match(match);
            if (matches.length > 0) {
                throw new Error("Expected zero matching requests for criteria \"" + description + "\", found " + matches.length + ".");
            }
        };
        /**
         * Validate that there are no outstanding requests.
         */
        HttpClientTestingBackend.prototype.verify = function (opts) {
            if (opts === void 0) { opts = {}; }
            var open = this.open;
            // It's possible that some requests may be cancelled, and this is expected.
            // The user can ask to ignore open requests which have been cancelled.
            if (opts.ignoreCancelled) {
                open = open.filter(function (testReq) { return !testReq.cancelled; });
            }
            if (open.length > 0) {
                // Show the methods and URLs of open requests in the error, for convenience.
                var requests = open.map(function (testReq) {
                    var url = testReq.request.urlWithParams.split('?')[0];
                    var method = testReq.request.method;
                    return method + " " + url;
                })
                    .join(', ');
                throw new Error("Expected no open requests, found " + open.length + ": " + requests);
            }
        };
        HttpClientTestingBackend.prototype.descriptionFromMatcher = function (matcher) {
            if (typeof matcher === 'string') {
                return "Match URL: " + matcher;
            }
            else if (typeof matcher === 'object') {
                var method = matcher.method || '(any)';
                var url = matcher.url || '(any)';
                return "Match method: " + method + ", URL: " + url;
            }
            else {
                return "Match by function: " + matcher.name;
            }
        };
        return HttpClientTestingBackend;
    }());
    HttpClientTestingBackend.decorators = [
        { type: core.Injectable }
    ];

    /**
     * @license
     * Copyright Google LLC All Rights Reserved.
     *
     * Use of this source code is governed by an MIT-style license that can be
     * found in the LICENSE file at https://angular.io/license
     */
    /**
     * Configures `HttpClientTestingBackend` as the `HttpBackend` used by `HttpClient`.
     *
     * Inject `HttpTestingController` to expect and flush requests in your tests.
     *
     * @publicApi
     */
    var HttpClientTestingModule = /** @class */ (function () {
        function HttpClientTestingModule() {
        }
        return HttpClientTestingModule;
    }());
    HttpClientTestingModule.decorators = [
        { type: core.NgModule, args: [{
                    imports: [
                        http.HttpClientModule,
                    ],
                    providers: [
                        HttpClientTestingBackend,
                        { provide: http.HttpBackend, useExisting: HttpClientTestingBackend },
                        { provide: HttpTestingController, useExisting: HttpClientTestingBackend },
                    ],
                },] }
    ];

    /**
     * @license
     * Copyright Google LLC All Rights Reserved.
     *
     * Use of this source code is governed by an MIT-style license that can be
     * found in the LICENSE file at https://angular.io/license
     */

    /**
     * @license
     * Copyright Google LLC All Rights Reserved.
     *
     * Use of this source code is governed by an MIT-style license that can be
     * found in the LICENSE file at https://angular.io/license
     */

    /**
     * Generated bundle index. Do not edit.
     */

    exports.HttpClientTestingModule = HttpClientTestingModule;
    exports.HttpTestingController = HttpTestingController;
    exports.TestRequest = TestRequest;
    exports.ɵangular_packages_common_http_testing_testing_a = HttpClientTestingBackend;

    Object.defineProperty(exports, '__esModule', { value: true });

})));
//# sourceMappingURL=common-http-testing.umd.js.map
